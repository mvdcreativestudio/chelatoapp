<?php

namespace App\Services\Mail;

interface MailProviderInterface
{
    public function send(string $to, string $subject, string $content, string $from, string $replyTo): bool;
}
