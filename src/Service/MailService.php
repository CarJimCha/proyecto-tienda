<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailService
{
    public function __construct(private MailerInterface $mailer) {}

    public function enviarCorreo(string $to, string $subject, string $content): void
    {
        $email = (new Email())
            ->from('proyectoygdramar@gmail.com')
            ->to($to)
            ->subject($subject)
            ->text($content);

        $this->mailer->send($email);
    }

    public function enviarCorreoHtml(string $to, string $subject, string $htmlContent): void
    {
        $email = (new Email())
            ->from('proyectoygdramar@gmail.com')
            ->to($to)
            ->subject($subject)
            ->html($htmlContent);
            // attach -> Archivos adjuntos

        $this->mailer->send($email);
    }

}
