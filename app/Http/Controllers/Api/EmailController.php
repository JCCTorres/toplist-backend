<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class EmailController extends Controller
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Send contact form email
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendContactEmail(Request $request): JsonResponse
    {
        try {
            $name = $request->input('name');
            $email = $request->input('email');
            $phone = $request->input('phone');
            $message = $request->input('message');

            // Basic validations
            if (!$name || !$email || !$phone || !$message) {
                return response()->json([
                    'success' => false,
                    'error' => 'MISSING_FIELDS',
                    'message' => 'All fields are required: name, email, phone, message'
                ], 400);
            }

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'success' => false,
                    'error' => 'INVALID_EMAIL',
                    'message' => 'Invalid email format'
                ], 400);
            }

            // Validate phone (at least 10 digits)
            if (!preg_match('/^[\d\s\-\+\(\)]{10,}$/', $phone)) {
                return response()->json([
                    'success' => false,
                    'error' => 'INVALID_PHONE',
                    'message' => 'Invalid phone format'
                ], 400);
            }

            // Validate message length
            if (strlen($message) < 10) {
                return response()->json([
                    'success' => false,
                    'error' => 'MESSAGE_TOO_SHORT',
                    'message' => 'Message must be at least 10 characters long'
                ], 400);
            }

            if (strlen($message) > 2000) {
                return response()->json([
                    'success' => false,
                    'error' => 'MESSAGE_TOO_LONG',
                    'message' => 'Message must be no more than 2000 characters'
                ], 400);
            }

            // Send email
            $result = $this->emailService->sendContactEmail([
                'name' => trim($name),
                'email' => strtolower(trim($email)),
                'phone' => trim($phone),
                'message' => trim($message)
            ]);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thank you for your message! We will get back to you soon.',
                    'data' => [
                        'timestamp' => now()->toISOString(),
                        'contact' => [
                            'name' => $name,
                            'email' => $email,
                            'phone' => $phone
                        ]
                    ]
                ]);
            } else {
                Log::error('Error sending email: ' . $result['message']);
                return response()->json([
                    'success' => false,
                    'error' => 'EMAIL_SEND_ERROR',
                    'message' => 'Error sending email. Please try again in a few minutes.'
                ], 500);
            }

        } catch (\Exception $error) {
            Log::error('Error in email controller: ' . $error->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'INTERNAL_SERVER_ERROR',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Send management request email
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendManagementRequestEmail(Request $request): JsonResponse
    {
        try {
            $name = $request->input('name');
            $email = $request->input('email');
            $propertyType = $request->input('propertyType');
            $bedrooms = $request->input('bedrooms');
            $message = $request->input('message');

            // Basic validations
            if (!$name || !$email || !$propertyType || !$bedrooms || !$message) {
                return response()->json([
                    'success' => false,
                    'error' => 'MISSING_FIELDS',
                    'message' => 'All fields are required: name, email, propertyType, bedrooms, message'
                ], 400);
            }

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'success' => false,
                    'error' => 'INVALID_EMAIL',
                    'message' => 'Invalid email format'
                ], 400);
            }

            // Validate message length
            if (strlen($message) < 10) {
                return response()->json([
                    'success' => false,
                    'error' => 'MESSAGE_TOO_SHORT',
                    'message' => 'Message must be at least 10 characters long'
                ], 400);
            }

            if (strlen($message) > 2000) {
                return response()->json([
                    'success' => false,
                    'error' => 'MESSAGE_TOO_LONG',
                    'message' => 'Message must be no more than 2000 characters'
                ], 400);
            }

            // Send email
            $result = $this->emailService->sendManagementRequestEmail([
                'name' => trim($name),
                'email' => strtolower(trim($email)),
                'property_type' => trim($propertyType),
                'bedrooms' => trim($bedrooms),
                'message' => trim($message)
            ]);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thank you for your management request! We will get back to you soon.',
                    'data' => [
                        'timestamp' => now()->toISOString(),
                        'contact' => [
                            'name' => $name,
                            'email' => $email
                        ],
                        'property' => [
                            'type' => $propertyType,
                            'bedrooms' => $bedrooms
                        ]
                    ]
                ]);
            } else {
                Log::error('Error sending management request email: ' . $result['message']);
                return response()->json([
                    'success' => false,
                    'error' => 'EMAIL_SEND_ERROR',
                    'message' => 'Error sending email. Please try again in a few minutes.'
                ], 500);
            }

        } catch (\Exception $error) {
            Log::error('Error in management request controller: ' . $error->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'INTERNAL_SERVER_ERROR',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Test email connection
     *
     * @return JsonResponse
     */
    public function testConnection(): JsonResponse
    {
        try {
            $result = $this->emailService->testConnection();
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gmail connection established successfully!',
                    'data' => [
                        'timestamp' => now()->toISOString(),
                        'service' => 'gmail'
                    ]
                ]);
            } else {
                Log::error('Gmail connection error: ' . $result['message']);
                return response()->json([
                    'success' => false,
                    'error' => 'CONNECTION_ERROR',
                    'message' => 'Error connecting to Gmail. Please check credentials.',
                    'details' => $result['message']
                ], 500);
            }

        } catch (\Exception $error) {
            Log::error('Error testing connection: ' . $error->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'INTERNAL_SERVER_ERROR',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Send email using tabinfo.com.br SMTP (replicando sua função PHP original)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function enviarEmailTabinfo(Request $request): JsonResponse
    {
        try {
            $data = [
                'name' => $request->input('name', 'Usuario'),
                'titulo_email' => $request->input('titulo_email', 'Contato TopList'),
                'link' => $request->input('link', ''),
                'message' => $request->input('message', ''),
                'body_content' => $request->input('body_content', $request->input('message', ''))
            ];

            $result = $this->emailService->enviaEmail($data);

            // Retorna no formato original da sua função PHP
            return response()->json($result, $result['Status'] == 1 ? 200 : 500);

        } catch (\Exception $error) {
            Log::error('Error in enviarEmailTabinfo: ' . $error->getMessage());
            return response()->json([
                'Status' => 0,
                'Message' => 'Erro interno do servidor',
                'fail' => $error->getMessage(),
                'success' => false
            ], 500);
        }
    }
}