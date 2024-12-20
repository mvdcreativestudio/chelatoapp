<?php

namespace App\Services\Mail;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NativeMailer implements MailProviderInterface
{
    public function send(
        string $to,
        string $subject,
        string $content,
        string $from,
        string $replyTo,
        string $pdfPath = null,
        string $attachmentName = 'document.pdf' // Nombre por defecto del archivo adjunto
    ): bool {
        try {
            Mail::send([], [], function ($message) use ($to, $subject, $content, $from, $replyTo, $pdfPath, $attachmentName) {
                $message->to($to)
                        ->from($from)
                        ->replyTo($replyTo)
                        ->subject($subject)
                        ->html($content);

                // Solo adjuntar el PDF si se proporciona una ruta
                if ($pdfPath) {
                    $message->attach($pdfPath, [
                        'as' => $attachmentName, // Nombre del archivo adjunto personalizado
                        'mime' => 'application/pdf',
                    ]);
                }
            });

            Log::info('Correo enviado a ' . $to . ' con el asunto ' . $subject);
            return true;
        } catch (\Exception $e) {
            Log::error('Error enviando correo: ' . $e->getMessage());
            return false;
        }
    }
}
