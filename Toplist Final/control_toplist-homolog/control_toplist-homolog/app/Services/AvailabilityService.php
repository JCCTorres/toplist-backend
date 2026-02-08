<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class AvailabilityService
{
    private BookervilleService $bookervilleService;
    
    // Configuração manual de disponibilidade real
    // Esta configuração pode ser movida para um banco de dados ou arquivo de configuração
    private const REAL_AVAILABILITY_CONFIG = [
        '11684' => [
            '2025-07' => ['17', '18', '19', '20'], // Apenas estes dias estão realmente disponíveis
            '2025-08' => ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10'], // Exemplo para agosto
            '2025-09' => ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15'] // Exemplo para setembro
        ]
        // Adicionar outras propriedades conforme necessário
    ];

    public function __construct(BookervilleService $bookervilleService)
    {
        $this->bookervilleService = $bookervilleService;
    }

    /**
     * Obtém a disponibilidade real de uma propriedade baseada nas reservas confirmadas
     */
    public function getRealAvailability(
        string $propertyId, 
        ?string $startDate = null, 
        ?string $endDate = null
    ): array {
        try {
            // Buscar reservas confirmadas da API do Bookerville
            $bookedStays = $this->getBookedStays($propertyId);

            // Se startDate e endDate não forem fornecidos, usar hoje até o último dia do mês subsequente
            if (empty($startDate) && empty($endDate)) {
                $startDate = Carbon::now()->toDateString();
                $endDate = Carbon::now()->addMonth()->endOfMonth()->toDateString();
            }
       
            // Buscar rates configurados para obter preços
            $propertyDetails = $this->bookervilleService->getPropertyDetails(['propertyId' => $propertyId]);
            $rates = $propertyDetails['success'] ? ($propertyDetails['data']['rates'] ?? []) : [];

            // Calcular datas disponíveis baseadas nas reservas
            $availableDates = $this->calculateAvailableDates($bookedStays, $rates, $startDate, $endDate);

            $result = [
                'propertyId' => $propertyId,
                'availableDates' => $availableDates,
                'totalAvailable' => count($availableDates),
                'lastUpdated' => now()->toISOString(),
                'note' => 'Disponibilidade real baseada em reservas da API Bookerville',
                'bookedStaysCount' => count($bookedStays),
                'bookedStays' => array_map(function ($stay) {
                    return [
                        'arrivalDate' => $stay['arrivalDate'],
                        'departureDate' => $stay['departureDate'],
                        'status' => $stay['bookingStatus'] ?? 'Unknown',
                    ];
                }, $bookedStays),
            ];
        
            return $this->createSuccessResponse($result);

        } catch (Exception $error) {
            Log::error('Erro ao buscar disponibilidade real: ' . $error->getMessage(), [
                'propertyId' => $propertyId,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'trace' => $error->getTraceAsString()
            ]);
            
            return $this->createErrorResponse(
                'AVAILABILITY_ERROR',
                'Erro ao buscar disponibilidade: ' . $error->getMessage(),
                500
            );
        }
    }

    /**
     * Busca reservas confirmadas da API PropertyAvailability
     */
    private function getBookedStays(string $propertyId): array
    {
        try {
            // Configurações da API
            $baseUrl = 'https://www.bookerville.com';
            $apiKey = 'T7AL0LO0KN6QAYPI38OBI4SB2AF6P';
            $accountId = '1538';
            
            $url = "{$baseUrl}/API-PropertyAvailability?s3cr3tK3y={$apiKey}&bkvPropertyId={$propertyId}";
            
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->withHeaders([
                    'Accept' => 'application/xml, text/xml, */*',
                    'User-Agent' => 'TopList-Backend/1.0'
                ])
                ->get($url);
       
            if (!$response->successful()) {
                Log::warning('Falha na requisição da API PropertyAvailability', [
                    'propertyId' => $propertyId,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return [];
            }

            // Parsear XML para extrair BookedStays
            $xmlData = $response->body();
            $xml = simplexml_load_string($xmlData);
            
            if ($xml === false) {
                Log::warning('Falha ao parsear XML da API PropertyAvailability', [
                    'propertyId' => $propertyId,
                    'xmlData' => $xmlData
                ]);
                return [];
            }

            // Extrair BookedStays
            if (!isset($xml->BookedStays)) {
                return [];
            }

            $bookedStays = $xml->BookedStays->BookedStay ?? [];
            
            // Normalizar para array
            if (!is_array($bookedStays) && !($bookedStays instanceof \Traversable)) {
                $bookedStays = [$bookedStays];
            }
            
            $staysArray = [];
            foreach ($bookedStays as $stay) {
                $status = (string) $stay->BookingStatus;
                // Include all bookings that block dates (Confirmed, Unconfirmed, Completed)
                // Only skip Cancelled bookings
                if (strtolower($status) !== 'cancelled') {
                    $staysArray[] = [
                        'confirmCode' => (string) $stay->ConfirmCode,
                        'bookingStatus' => $status,
                        'arrivalDate' => (string) $stay->ArrivalDate,
                        'departureDate' => (string) $stay->DepartureDate,
                        'dateBooked' => (string) $stay->DateBooked,
                        'occupancy' => (string) $stay->Occupancy
                    ];
                }
            }

            return $staysArray;

        } catch (Exception $error) {
            Log::error('Erro ao buscar reservas: ' . $error->getMessage(), [
                'propertyId' => $propertyId,
                'trace' => $error->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Calcula datas disponíveis baseadas nas reservas confirmadas
     */
    private function calculateAvailableDates(
        array $bookedStays, 
        array $rates, 
        ?string $startDate = null, 
        ?string $endDate = null
    ): array {
        // Ordenar reservas por data de chegada
        usort($bookedStays, function ($a, $b) {
            return Carbon::parse($a['arrivalDate'])->timestamp - Carbon::parse($b['arrivalDate'])->timestamp;
        });

        $availableDates = [];
        
        // Definir período de análise baseado nas reservas reais
        $analysisStart = $startDate ?: $this->getEarliestDate($bookedStays);
        $analysisEnd = $endDate ?: $this->getLatestDate($bookedStays);
        
        $currentDate = Carbon::parse($analysisStart);
        $endDateObj = Carbon::parse($analysisEnd);

        while ($currentDate->lte($endDateObj)) {
            $dateStr = $currentDate->toDateString();
            
            // Verificar se a data está em alguma reserva
            $isBooked = false;
            
            foreach ($bookedStays as $stay) {
                $arrival = Carbon::parse($stay['arrivalDate']);
                $departure = Carbon::parse($stay['departureDate']);
                $checkDate = Carbon::parse($dateStr);
                
                // Verificar se é o dia de chegada ou saída
                $isArrivalDay = $checkDate->equalTo($arrival);
                $isDepartureDay = $checkDate->equalTo($departure);
                
                // Verificar se há outra reserva que chega no mesmo dia que esta sai
                $hasOverlappingArrival = false;
                foreach ($bookedStays as $otherStay) {
                    if ($otherStay['confirmCode'] === $stay['confirmCode']) continue; // mesma reserva
                    $otherArrival = Carbon::parse($otherStay['arrivalDate']);
                    if ($otherArrival->equalTo($departure)) { // chega no dia que esta sai
                        $hasOverlappingArrival = true;
                        break;
                    }
                }
                
                // Se é o dia de saída E há chegada no mesmo dia, está reservado
                if ($isDepartureDay && $hasOverlappingArrival) {
                    $isBooked = true;
                    break;
                }
                
                // Se é o dia de chegada E há saída no mesmo dia, está reservado (cobre as pontas)
                if ($isArrivalDay) {
                    foreach ($bookedStays as $otherStay) {
                        if ($otherStay['confirmCode'] === $stay['confirmCode']) continue;
                        $otherDeparture = Carbon::parse($otherStay['departureDate']);
                        if ($otherDeparture->equalTo($arrival)) {
                            $isBooked = true;
                            break 2;
                        }
                    }
                }
                
                // Se é apenas o dia de chegada (sem saída no mesmo dia), está disponível
                if ($isArrivalDay && !$isDepartureDay) {
                    continue;
                }
                
                // Para outros casos: data está reservada se: arrival <= checkDate < departure
                if ($checkDate->gte($arrival) && $checkDate->lt($departure)) {
                    $isBooked = true;
                    break;
                }
            }

            // Se não está reservada, adicionar como disponível
            if (!$isBooked) {
                // Buscar rate correspondente para obter preços
                $rate = null;
                foreach ($rates as $r) {
                
                    $rateStart = Carbon::parse($r['start_date']);
                    $rateEnd = Carbon::parse($r['end_date']);
                    $checkDate = Carbon::parse($dateStr);
                    
                    if ($checkDate->gte($rateStart) && $checkDate->lte($rateEnd)) {
                        $rate = $r;
                        break;
                    }
                }

                $availableDates[] = [
                    'startDate' => $dateStr,
                    'endDate' => $dateStr,
                    'nightlyRate' => $rate ? ($rate['nightlyRate'] ?? 0) : 120,
                    'weekendRate' => $rate['weekendRate'] ?? 130,
                    'weeklyRate' => $rate['weeklyRate'] ?? 840,
                    'monthlyRate' => $rate['monthlyRate'] ?? 3600,
                    'minimumStay' => $rate['minimumStay'] ?? 1,
                    'weekendNights' => $rate['weekendNights'] ?? 'Fri|Sat'
                ];
            }

            // Avançar para o próximo dia
            $currentDate->addDay();
        }

        return $availableDates;
    }

    /**
     * Obtém a data mais antiga das reservas
     */
    private function getEarliestDate(array $bookedStays): string
    {
        if (empty($bookedStays)) {
            return '2025-01-01';
        }
        
        $earliest = $bookedStays[0]['arrivalDate'];
        foreach ($bookedStays as $stay) {
            if ($stay['arrivalDate'] < $earliest) {
                $earliest = $stay['arrivalDate'];
            }
        }
        
        return $earliest;
    }

    /**
     * Obtém a data mais recente das reservas
     */
    private function getLatestDate(array $bookedStays): string
    {
        if (empty($bookedStays)) {
            return '2025-12-31';
        }
        
        $latest = $bookedStays[0]['departureDate'];
        foreach ($bookedStays as $stay) {
            if ($stay['departureDate'] > $latest) {
                $latest = $stay['departureDate'];
            }
        }
        
        return $latest;
    }

    /**
     * Atualiza a configuração de disponibilidade de uma propriedade
     */
    public function updateAvailabilityConfig(string $propertyId, array $config): void
    {
        // Método mantido para compatibilidade, mas agora usa dados reais
        Log::info('updateAvailabilityConfig called - now using real data from PropertyAvailability API', [
            'propertyId' => $propertyId,
            'config' => $config
        ]);
    }

    /**
     * Obtém a configuração atual de disponibilidade
     */
    public function getAvailabilityConfig(): array
    {
        return [
            'note' => 'Agora usando dados reais da API PropertyAvailability'
        ];
    }

    /**
     * Cria uma resposta de sucesso
     */
    private function createSuccessResponse($data): array
    {
        return [
            'success' => true,
            'data' => $data,
            'message' => 'Success',
            'statusCode' => 200
        ];
    }

    /**
     * Cria uma resposta de erro
     */
    private function createErrorResponse(string $error, string $message, int $statusCode): array
    {
        return [
            'success' => false,
            'error' => $error,
            'message' => $message,
            'statusCode' => $statusCode
        ];
    }
}