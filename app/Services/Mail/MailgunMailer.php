<?php

namespace App\Services\Mail;

use Mailgun\Mailgun;

class MailgunMailer implements MailProviderInterface
{
    protected $client;

    public function __construct()
    {
        $this->client = Mailgun::create(env('MAILGUN_API_KEY')); // Configurar API Key de Mailgun en `.env`
    }

    public function send(string $to, string $subject, string $content, string $from, string $replyTo): bool
    {
        $domain = env('MAILGUN_DOMAIN');
        $result = $this->client->messages()->send($domain, [
            'from'    => $from,
            'to'      => $to,
            'subject' => $subject,
            'html'    => $content,
            'h:Reply-To' => $replyTo
        ]);

        return $result->getMessage() === 'Queued. Thank you.';
    }
}
