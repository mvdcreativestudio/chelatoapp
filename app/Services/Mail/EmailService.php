<?php

namespace App\Services\Mail;

class EmailService
{
    protected MailProviderInterface $mailer;
    protected string $from;
    protected string $replyTo;

    public function __construct(MailProviderInterface $mailer, string $from = null, string $replyTo = null)
    {
        $this->mailer = $mailer;
        $this->from = $from ?? 'default@example.com';
        $this->replyTo = $replyTo ?? 'noreply@example.com';
    }

    public function sendMail(
        string $to,
        string $subject,
        string $template,
        array $data = [],
        string $pdfPath = null,
        string $attachmentName = 'document.pdf' // Nombre por defecto del archivo adjunto
    ): bool {
        $content = $this->renderTemplate($template, $data);
        return $this->mailer->send($to, $subject, $content, $this->from, $this->replyTo, $pdfPath, $attachmentName);
    }

    protected function renderTemplate(string $template, array $data): string
    {
        return view($template, $data)->render();
    }
}
