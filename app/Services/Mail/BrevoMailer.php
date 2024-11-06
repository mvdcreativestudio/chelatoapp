<?php

namespace App\Services\Mail;

use SendinBlue\Client\Api\TransactionalEmailsApi;
use GuzzleHttp\Client;
use SendinBlue\Client\Configuration;

class BrevoMailer implements MailProviderInterface
{
    protected $client;

    public function __construct()
    {
        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', env('BREVO_API_KEY'));
        $this->client = new TransactionalEmailsApi(new Client(), $config);
    }

    public function send(string $to, string $subject, string $content, string $from, string $replyTo): bool
    {
        $sendSmtpEmail = [
            'sender' => ['email' => $from],
            'to' => [['email' => $to]],
            'subject' => $subject,
            'htmlContent' => $content,
            'replyTo' => ['email' => $replyTo],
        ];

        $response = $this->client->sendTransacEmail($sendSmtpEmail);

        return isset($response['messageId']);
    }
}
