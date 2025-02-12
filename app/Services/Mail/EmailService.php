<?php

namespace App\Services\Mail;

use App\Repositories\StoresEmailConfigRepository;
use Exception;
use Illuminate\Support\Facades\Log;

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

        $mailMailer = env('MAIL_MAILER');
        $mailHost = env('MAIL_HOST');
        $mailPort = env('MAIL_PORT');
        $mailUsername = env('MAIL_USERNAME');
        $mailPassword = env('MAIL_PASSWORD');
        $mailEncryption = env('MAIL_ENCRYPTION');
        $mailFromAddress = env('MAIL_FROM_ADDRESS');
        $mailFromName = env('MAIL_FROM_NAME');
        $mailReplyToAddress = env('MAIL_REPLY_TO_ADDRESS');


        $storeId = $storeId ?? auth()->user()->store_id;
        if ($storeId){
            $storeConfig = $this->storesEmailConfigRepository->getConfigByStoreId($storeId);
            if ($storeConfig){
                $mailMailer = $storeConfig->mail_mailer;
                $mailHost = $storeConfig->mail_host;
                $mailPort = $storeConfig->mail_port;
                $mailUsername = $storeConfig->mail_username;
                $mailPassword = $storeConfig->mail_password;
                $mailEncryption = $storeConfig->mail_encryption;
                $mailFromAddress = $storeConfig->mail_from_address;
                $mailFromName = $storeConfig->mail_from_name ?? env('MAIL_FROM_NAME');
                $mailReplyToAddress = $storeConfig->mail_reply_to_address ?? env('MAIL_REPLY_TO_ADDRESS');
                Log::info("Información de configuración de correo recuperada para la tienda {$storeId}");
            }
        }

        // Configura el mailer dinámicamente
        config([
            'mail.default' => $mailMailer,
            'mail.mailers.smtp.host' => $mailHost,
            'mail.mailers.smtp.port' => $mailPort,
            'mail.mailers.smtp.username' => $mailUsername,
            'mail.mailers.smtp.password' => $mailPassword,
            'mail.mailers.smtp.encryption' => $mailEncryption,
            'mail.from.address' => $mailFromAddress,
            'mail.from.name' => $mailFromName,
        ]);

        $this->from = $mailFromAddress;
        $this->replyTo = $mailReplyToAddress;
        $data = array_merge([
            'from' => $this->from,
            'replyTo' => $this->replyTo,
        ], $data);
        $content = $this->renderTemplate($template, $data);
        return $this->mailer->send($to, $subject, $content, $this->from, $this->replyTo, $pdfPath, $attachmentName);
    }

    protected function renderTemplate(string $template, array $data): string
    {
        return view($template, compact('data'))->render();
    }
}
