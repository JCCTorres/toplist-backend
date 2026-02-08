<?php

/**
 * Arquivo de teste para o AvailabilityController
 * Teste das rotas da API de disponibilidade
 * 
 * Execute este arquivo para testar as rotas:
 * php test_availability_controller.php
 */

echo "=== Teste do AvailabilityController - Rotas da API ===\n\n";

// Configurações base
$baseUrl = 'http://localhost/control_toplist/public/api/bookerville/availability';
$propertyId = '11684';

// Headers comuns
$headers = [
    'Content-Type: application/json',
    'Accept: application/json',
    'User-Agent: TopList-Test/1.0'
];

/**
 * Função para fazer requisições HTTP
 */
function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    return [
        'response' => $response,
        'httpCode' => $httpCode,
        'error' => $error
    ];
}

/**
 * Função para exibir resultado do teste
 */
function displayResult($testName, $result) {
    echo "=== {$testName} ===\n";
    
    if (!empty($result['error'])) {
        echo "❌ Erro cURL: {$result['error']}\n";
        return;
    }
    
    echo "Status HTTP: {$result['httpCode']}\n";
    
    if ($result['httpCode'] >= 200 && $result['httpCode'] < 300) {
        echo "✅ Sucesso!\n";
    } else {
        echo "❌ Erro HTTP\n";
    }
    
    $responseData = json_decode($result['response'], true);
    if ($responseData) {
        echo "Resposta: " . json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "Resposta bruta: " . substr($result['response'], 0, 500) . "\n";
    }
    
    echo "\n" . str_repeat('-', 50) . "\n\n";
}

// Teste 1: Health Check
echo "Testando URL base: {$baseUrl}\n\n";

$result1 = makeRequest("{$baseUrl}/health", 'GET', null, $headers);
displayResult('Health Check', $result1);

// Teste 2: Obter configuração de disponibilidade
$result2 = makeRequest("{$baseUrl}/config", 'GET', null, $headers);
displayResult('Obter Configuração', $result2);

// Teste 3: Obter disponibilidade real de uma propriedade
$result3 = makeRequest("{$baseUrl}/properties/{$propertyId}", 'GET', null, $headers);
displayResult("Disponibilidade Real - Propriedade {$propertyId}", $result3);

// Teste 4: Obter disponibilidade com parâmetros de data
$startDate = '2025-09-01';
$endDate = '2025-09-30';
$url4 = "{$baseUrl}/properties/{$propertyId}?startDate={$startDate}&endDate={$endDate}";
$result4 = makeRequest($url4, 'GET', null, $headers);
displayResult("Disponibilidade com Datas - {$startDate} até {$endDate}", $result4);

// Teste 5: Obter estatísticas de disponibilidade
$result5 = makeRequest("{$baseUrl}/properties/{$propertyId}/stats", 'GET', null, $headers);
displayResult("Estatísticas de Disponibilidade", $result5);

// Teste 6: Atualizar configuração (POST)
$configData = json_encode([
    'config' => [
        'testProperty' => [
            '2025-10' => ['01', '02', '03', '04', '05']
        ]
    ]
]);

$result6 = makeRequest("{$baseUrl}/properties/{$propertyId}/config", 'POST', $configData, $headers);
displayResult('Atualizar Configuração', $result6);

// Teste 7: Verificar URLs alternativas (caso o Laravel esteja em subpasta)
echo "=== Testando URLs alternativas ===\n";

$alternativeUrls = [
    'http://localhost:8000/api/bookerville/availability/health',
    'http://127.0.0.1/control_toplist/public/api/bookerville/availability/health',
    'http://localhost/api/bookerville/availability/health',
    'http://control-toplist.test/api/bookerville/availability/health'
];

foreach ($alternativeUrls as $url) {
    echo "Testando: {$url}\n";
    $result = makeRequest($url, 'GET', null, $headers);
    
    if (empty($result['error']) && $result['httpCode'] === 200) {
        echo "✅ URL funcional encontrada: {$url}\n";
        echo "Resposta: " . $result['response'] . "\n";
        break;
    } else {
        echo "❌ HTTP {$result['httpCode']} - {$result['error']}\n";
    }
}

echo "\n=== Resumo dos Endpoints Criados ===\n";
echo "GET  /api/bookerville/availability/health\n";
echo "GET  /api/bookerville/availability/config\n";
echo "GET  /api/bookerville/availability/properties/{propertyId}\n";
echo "GET  /api/bookerville/availability/properties/{propertyId}/stats\n";
echo "POST /api/bookerville/availability/properties/{propertyId}/config\n";

echo "\n=== Parâmetros de Query Suportados ===\n";
echo "startDate: Data de início (formato: Y-m-d)\n";
echo "endDate: Data de fim (formato: Y-m-d)\n";
echo "groupBy: Agrupamento para estatísticas (day, week, month)\n";

echo "\n=== Exemplo de uso com cURL ===\n";
echo "curl -X GET \"{$baseUrl}/properties/{$propertyId}?startDate=2025-09-01&endDate=2025-09-30\" \\\n";
echo "     -H \"Content-Type: application/json\" \\\n";
echo "     -H \"Accept: application/json\"\n";

echo "\n=== Testes concluídos ===\n";