<?php

namespace App\Services\Mail;

use App\Repositories\StoresEmailConfigRepository;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    protected MailProviderInterface $mailer;
    protected string $from;
    protected string $replyTo;
    protected $storesEmailConfigRepository;
    protected $storeId;

    public function __construct(MailProviderInterface $mailer, StoresEmailConfigRepository $storesEmailConfigRepository)
    {
        $this->mailer = $mailer;
        $this->storesEmailConfigRepository = $storesEmailConfigRepository;
    }

    public function sendMail(
        string $to,
        string $subject,
        string $template,
        string $pdfPath = null,
        string $attachmentName = 'document.pdf',
        array $data = [],
        int $storeId = null
    ): bool {
        try {
            $storeId = $storeId ?? auth()->user()->store_id;

            if (is_null($storeId)) {
                throw new Exception("Este usuario no está asociado a una tienda. Por favor, asócielo a una tienda antes de enviar correos.");
            }

            // Recupera la configuración de la tienda desde la base de datos
            $storeConfig = $this->storesEmailConfigRepository->getConfigByStoreId($storeId);

            Log::info("Información de configuración de correo recuperada para la tienda {$storeId}");

            // Configura el mailer dinámicamente
            config([
                'mail.default' => $storeConfig->mail_mailer,
                'mail.mailers.smtp.host' => $storeConfig->mail_host,
                'mail.mailers.smtp.port' => $storeConfig->mail_port,
                'mail.mailers.smtp.username' => $storeConfig->mail_username,
                'mail.mailers.smtp.password' => $storeConfig->mail_password,
                'mail.mailers.smtp.encryption' => $storeConfig->mail_encryption,
                'mail.from.address' => $storeConfig->mail_from_address,
                'mail.from.name' => $storeConfig->mail_from_name,
            ]);

            $this->from = $storeConfig->mail_from_address ?? 'default@example.com';
            $this->replyTo = $storeConfig->mail_reply_to_address ?? 'noreply@example.com';

            // Enviar el correo
            Mail::send($template, $data, function ($message) use ($to, $subject, $pdfPath, $attachmentName) {
                $message->to($to)
                    ->subject($subject);

                if ($pdfPath && $attachmentName) {
                    $message->attach($pdfPath, [
                        'as' => $attachmentName
                    ]);
                }
            });

            return true;

        } catch (\Exception $e) {
            Log::error('Error enviando email: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function renderTemplate(string $template, array $data): string
    {
        return view($template, compact('data'))->render();
    }
}
