<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class EmailService
{
    /**
     * Send contact form email
     *
     * @param array $data
     * @return array
     */
    public function sendContactEmail(array $data): array
    {
        try {
            $name = $data['name'];
            $email = $data['email'];
            $phone = $data['phone'];
            $message = $data['message'];

            $mailData = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'message' => $message,
                'date' => now()->format('M d, Y g:i A'),
                'subject' => "New Contact Form Message - {$name}"
            ];

            // Log antes do envio
            Log::info('Tentando enviar email de contato', [
                'from' => config('mail.from.address'),
                'to' => 'joao_cct@hotmail.com', // Email que funciona (temporário)
                'subject' => $mailData['subject']
            ]);

            $sendMail = Mail::send([], [], function ($mail) use ($mailData) {
                $mail->from(config('mail.from.address'), config('mail.from.name'))
                    ->to('joao_cct@hotmail.com') // Email que funciona
                    ->replyTo($mailData['email']) // Cliente pode ser contactado via reply
                    ->subject($mailData['subject'])
                    ->html($this->getContactEmailTemplate($mailData));
            });
            Log::info('Email de contato enviado com sucesso para: joao_cct@hotmail.com');

            return [
                'success' => true,
                'message' => 'Email sent successfully!'
            ];
        } catch (Exception $e) {
            Log::error('Error sending contact email: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Send management request email
     *
     * @param array $data
     * @return array
     */
    public function sendManagementRequestEmail(array $data): array
    {
        try {
            $name = $data['name'];
            $email = $data['email'];
            $propertyType = $data['property_type'] ?? $data['propertyType'] ?? '';
            $bedrooms = $data['bedrooms'];
            $message = $data['message'];

            $mailData = [
                'name' => $name,
                'email' => $email,
                'property_type' => $propertyType,
                'bedrooms' => $bedrooms,
                'message' => $message,
                'date' => now()->format('M d, Y g:i A'),
                'subject' => "New Management Request - {$name}"
            ];

            // Log para debug
            Log::info('Tentando enviar email de management request', [
                'from' => config('mail.from.address'),
                'to' => 'joao_cct@hotmail.com', // Email que funciona (temporário)
                'subject' => $mailData['subject']
            ]);

            Mail::send([], [], function ($mail) use ($mailData) {
                $mail->from(config('mail.from.address'), config('mail.from.name'))
                    ->to('joao_cct@hotmail.com') // Email que funciona
                    ->replyTo($mailData['email']) // Cliente pode ser contactado via reply
                    ->subject($mailData['subject'])
                    ->html($this->getManagementEmailTemplate($mailData));
            });

            return [
                'success' => true,
                'message' => 'Management request email sent successfully!'
            ];
        } catch (Exception $e) {
            Log::error('Error sending management request email: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Test email connection with detailed diagnostics
     *
     * @return array
     */
    public function testConnection(): array
    {
        try {
            // Verificar problemas de configuração
            $configIssues = $this->checkConfigurationIssues();
            if (!empty($configIssues)) {
                return [
                    'success' => false,
                    'message' => 'Problemas de configuração detectados',
                    'issues' => $configIssues
                ];
            }

            // Log detalhado das configurações
            $config = [
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'username' => config('mail.mailers.smtp.username'),
                'encryption' => config('mail.mailers.smtp.encryption'),
                'from_address' => config('mail.from.address'),
                'password_length' => strlen(config('mail.mailers.smtp.password'))
            ];



            Log::info('Testando SMTP com configurações:', $config);

            // Teste de conexão SMTP mais simples
            Mail::raw('TESTE: Este é um email de teste para verificar conexão SMTP tabinfo.com.br', function ($mail) {
                $mail->from(config('mail.from.address'), config('mail.from.name'))
                    ->to('iagomarinst@gmail.com') // Email fixo da sua função PHP original
                    ->subject('TESTE SMTP - ' . now()->format('d/m/Y H:i:s'));
            });

            Log::info('✅ Email de teste enviado com sucesso via tabinfo.com.br');

            return [
                'success' => true,
                'message' => '✅ Conexão SMTP tabinfo.com.br funcionando! Email enviado para iagomarinst@gmail.com',
                'config_used' => $config
            ];
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();

            Log::error('❌ Erro no teste SMTP:', [
                'error' => $errorMessage,
                'config' => [
                    'host' => config('mail.mailers.smtp.host'),
                    'username' => config('mail.mailers.smtp.username'),
                    'port' => config('mail.mailers.smtp.port')
                ]
            ]);

            // Detectar tipos específicos de erro
            if (str_contains($errorMessage, '535')) {
                return [
                    'success' => false,
                    'message' => '❌ ERRO 535: Credenciais incorretas',
                    'details' => [
                        'problema' => 'Username ou senha incorretos',
                        'usuario_atual' => config('mail.mailers.smtp.username'),
                        'sugestoes' => [
                            '1. Verifique se o email noreply@tabinfo.com.br existe no servidor',
                            '2. Confirme a senha no painel de controle do servidor',
                            '3. Pode precisar criar a conta de email primeiro',
                            '4. Teste com outro email @tabinfo.com.br se disponível'
                        ]
                    ],
                    'error_code' => '535'
                ];
            }

            return [
                'success' => false,
                'message' => 'Erro na conexão SMTP',
                'error' => $errorMessage
            ];
        }
    }

    /**
     * Send email using the same config as your PHP function
     * Baseado na função envia_email() original que funciona
     *
     * @param array $data
     * @return array
     */
    public function enviaEmail(array $data): array
    {
        try {
            // Log das configurações (replicando seu setup PHP)
            Log::info('Enviando email com configurações tabinfo.com.br', [
                'host' => 'mail.tabinfo.com.br',
                'username' => 'noreply@tabinfo.com.br',
                'port' => 587,
                'to' => 'joao_cct@hotmail.com'
            ]);

            $mailData = [
                'name' => $data['name'] ?? 'Usuario',
                'titulo_email' => $data['titulo_email'] ?? 'Contato TopList',
                'link' => $data['link'] ?? '',
                'body_content' => $data['message'] ?? $data['body_content'] ?? 'Mensagem de contato'
            ];

            // Usar Mail do Laravel com as mesmas configurações do seu PHP
            Mail::send([], [], function ($mail) use ($mailData) {
                $mail->from('noreply@tabinfo.com.br', 'TopList')
                    ->replyTo('noreply@tabinfo.com.br')
                    ->to('joao_cct@hotmail.com', 'TopList Orlando')
                    ->subject($mailData['titulo_email'])
                    ->html($this->getEmailBody($mailData));
            });

            Log::info('Email enviado com sucesso via tabinfo.com.br');

            return [
                'Status' => 1,
                'Message' => 'E-mail enviado.',
                'fail' => '',
                'success' => true
            ];
        } catch (Exception $e) {
            Log::error('Erro ao enviar email: ' . $e->getMessage());

            return [
                'Status' => 0,
                'Message' => 'Falha ao enviar o e-mail.',
                'fail' => $e->getMessage(),
                'success' => false
            ];
        }
    }

    /**
     * Get email body (replicando o HTML da sua função original)
     */
    private function getEmailBody(array $data): string
    {
        return '<html>
                    <body>
                        Olá ' . $data['name'] . '.<br><br>
                        ' . $data['body_content'] . '<br><br>
                        ' . (!empty($data['link']) ? 'Link: <a target="blank" href="' . $data['link'] . '">Clique aqui</a>.<br><br>' : '') . '
                        <br>
                        TopList!
                    </body>
                </html>';
    }

    /**
     * Get contact email plain text (para usar com Mail::raw como no testConnection)
     */
    private function getContactEmailPlainText(array $data): string
    {
        return "
📧 NEW CONTACT FORM MESSAGE

👤 CONTACT INFORMATION:
Name: {$data['name']}
Email: {$data['email']}
Phone: {$data['phone']}

💬 MESSAGE:
{$data['message']}

📅 Date/Time: {$data['date']}
🌐 Sent via: TopList Contact Form
        ";
    }

    /**
     * Get contact email HTML template
     *
     * @param array $data
     * @return string
     */
    private function getContactEmailTemplate(array $data): string
    {
        return "
        <div style=\"font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 600px; margin: 0 auto; background-color: #f8f9fa; padding: 20px;\">
            <div style=\"background-color: #ffffff; border-radius: 10px; padding: 30px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\">
                <div style=\"text-align: center; margin-bottom: 30px;\">
                    <h1 style=\"color: #2c3e50; margin: 0; font-size: 28px; font-weight: 600;\">
                        📧 New Contact Form Message
                    </h1>
                    <p style=\"color: #7f8c8d; margin: 10px 0 0 0; font-size: 16px;\">
                        You have received a new message from your website contact form
                    </p>
                </div>
                
                <div style=\"background-color: #ecf0f1; padding: 25px; border-radius: 8px; margin-bottom: 25px;\">
                    <h2 style=\"color: #2c3e50; margin: 0 0 20px 0; font-size: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px;\">
                        👤 Contact Information
                    </h2>
                    
                    <table style=\"width: 100%; border-collapse: collapse;\">
                        <tr>
                            <td style=\"padding: 12px 8px; font-weight: 600; color: #34495e; width: 120px; vertical-align: top;\">Name:</td>
                            <td style=\"padding: 12px 8px; color: #2c3e50; font-weight: 500;\">{$data['name']}</td>
                        </tr>
                        <tr>
                            <td style=\"padding: 12px 8px; font-weight: 600; color: #34495e; vertical-align: top;\">Email:</td>
                            <td style=\"padding: 12px 8px; color: #2c3e50;\">
                                <a href=\"mailto:{$data['email']}\" style=\"color: #3498db; text-decoration: none; font-weight: 500;\">{$data['email']}</a>
                            </td>
                        </tr>
                        <tr>
                            <td style=\"padding: 12px 8px; font-weight: 600; color: #34495e; vertical-align: top;\">Phone:</td>
                            <td style=\"padding: 12px 8px; color: #2c3e50;\">
                                <a href=\"tel:{$data['phone']}\" style=\"color: #3498db; text-decoration: none; font-weight: 500;\">{$data['phone']}</a>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div style=\"background-color: #e8f4fd; padding: 25px; border-radius: 8px; margin-bottom: 25px;\">
                    <h2 style=\"color: #2c3e50; margin: 0 0 20px 0; font-size: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px;\">
                        💬 Message
                    </h2>
                    <p style=\"color: #2c3e50; line-height: 1.7; margin: 0; font-size: 16px;\">{$data['message']}</p>
                </div>
                
                <div style=\"background-color: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; font-size: 14px; color: #7f8c8d;\">
                    <p style=\"margin: 0;\">
                        <strong>📅 Date/Time:</strong> {$data['date']}<br>
                        <strong>🌐 Sent via:</strong> TopList Contact Form
                    </p>
                </div>
            </div>
        </div>
        ";
    }

    /**
     * Get management email plain text
     */
    private function getManagementEmailPlainText(array $data): string
    {
        return "
🏠 NEW MANAGEMENT REQUEST

👤 CONTACT INFORMATION:
Name: {$data['name']}
Email: {$data['email']}

🏘️ PROPERTY DETAILS:
Property Type: {$data['property_type']}
Bedrooms: {$data['bedrooms']}

💬 MESSAGE:
{$data['message']}

📅 Date/Time: {$data['date']}
🌐 Sent via: TopList Management Request Form
        ";
    }

    /**
     * Get management request email HTML template
     *
     * @param array $data
     * @return string
     */
    private function getManagementEmailTemplate(array $data): string
    {
        return "
        <div style=\"font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 600px; margin: 0 auto; background-color: #f8f9fa; padding: 20px;\">
            <div style=\"background-color: #ffffff; border-radius: 10px; padding: 30px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);\">
                <div style=\"text-align: center; margin-bottom: 30px;\">
                    <h1 style=\"color: #2c3e50; margin: 0; font-size: 28px; font-weight: 600;\">
                        🏠 New Management Request
                    </h1>
                    <p style=\"color: #7f8c8d; margin: 10px 0 0 0; font-size: 16px;\">
                        You have received a new property management request from your website
                    </p>
                </div>
                
                <div style=\"background-color: #ecf0f1; padding: 25px; border-radius: 8px; margin-bottom: 25px;\">
                    <h2 style=\"color: #2c3e50; margin: 0 0 20px 0; font-size: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px;\">
                        👤 Contact Information
                    </h2>
                    
                    <table style=\"width: 100%; border-collapse: collapse;\">
                        <tr>
                            <td style=\"padding: 12px 8px; font-weight: 600; color: #34495e; width: 120px; vertical-align: top;\">Name:</td>
                            <td style=\"padding: 12px 8px; color: #2c3e50; font-weight: 500;\">{$data['name']}</td>
                        </tr>
                        <tr>
                            <td style=\"padding: 12px 8px; font-weight: 600; color: #34495e; vertical-align: top;\">Email:</td>
                            <td style=\"padding: 12px 8px; color: #2c3e50;\">
                                <a href=\"mailto:{$data['email']}\" style=\"color: #3498db; text-decoration: none; font-weight: 500;\">{$data['email']}</a>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div style=\"background-color: #e8f4fd; padding: 25px; border-radius: 8px; margin-bottom: 25px;\">
                    <h2 style=\"color: #2c3e50; margin: 0 0 20px 0; font-size: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px;\">
                        🏘️ Property Details
                    </h2>
                    
                    <table style=\"width: 100%; border-collapse: collapse;\">
                        <tr>
                            <td style=\"padding: 12px 8px; font-weight: 600; color: #34495e; width: 120px; vertical-align: top;\">Property Type:</td>
                            <td style=\"padding: 12px 8px; color: #2c3e50; font-weight: 500;\">{$data['property_type']}</td>
                        </tr>
                        <tr>
                            <td style=\"padding: 12px 8px; font-weight: 600; color: #34495e; vertical-align: top;\">Bedrooms:</td>
                            <td style=\"padding: 12px 8px; color: #2c3e50; font-weight: 500;\">{$data['bedrooms']}</td>
                        </tr>
                    </table>
                </div>
                
                <div style=\"background-color: #e8f4fd; padding: 25px; border-radius: 8px; margin-bottom: 25px;\">
                    <h2 style=\"color: #2c3e50; margin: 0 0 20px 0; font-size: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px;\">
                        💬 Message
                    </h2>
                    <p style=\"color: #2c3e50; line-height: 1.7; margin: 0; font-size: 16px;\">{$data['message']}</p>
                </div>
                
                <div style=\"background-color: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; font-size: 14px; color: #7f8c8d;\">
                    <p style=\"margin: 0;\">
                        <strong>📅 Date/Time:</strong> {$data['date']}<br>
                        <strong>🌐 Sent via:</strong> TopList Management Request Form
                    </p>
                </div>
            </div>
        </div>
        ";
    }

    /**
     * Check for common configuration issues
     *
     * @return array
     */
    private function checkConfigurationIssues(): array
    {
        $issues = [];

        $host = config('mail.mailers.smtp.host');
        $username = config('mail.mailers.smtp.username');
        $fromAddress = config('mail.from.address');

        // Check Gmail specific issues
        if ($host === 'smtp.gmail.com' && !str_ends_with($username, '@gmail.com')) {
            $issues[] = "❌ Gmail SMTP requer username @gmail.com, atual: {$username}";
            $issues[] = "✅ Solução: Mude MAIL_HOST para 'mail.tabinfo.com.br'";
        }

        // Check if from address matches username
        if ($fromAddress !== $username) {
            $issues[] = "⚠️ MAIL_FROM_ADDRESS ({$fromAddress}) deve ser igual ao MAIL_USERNAME ({$username})";
        }

        // Check for correct tabinfo.com.br configuration
        if (str_contains($username, 'tabinfo.com.br') && $host !== 'mail.tabinfo.com.br') {
            $issues[] = "❌ Para email @tabinfo.com.br, use MAIL_HOST=mail.tabinfo.com.br";
            $issues[] = "✅ Configuração correta baseada na sua função PHP original";
        }

        return $issues;
    }
}
