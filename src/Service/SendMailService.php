<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class SendMailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer){
        $this->mailer = $mailer;
    }

    /**
     * This function send an email
     *
     * @param string $from
     * @param string $to
     * @param string $subject
     * @param array $context
     * @return void
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function send(string $from, string $to, string $subject, array $context):void{
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate('email/emailActivation.html.twig')
            ->context($context);

        $this->mailer->send($email);

    }

}