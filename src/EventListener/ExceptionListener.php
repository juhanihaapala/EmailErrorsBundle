<?php

declare(strict_types=1);

namespace Sparklink\EmailErrorsBundle\EventListener;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class ExceptionListener
{
    public function __construct(
        protected MailerInterface $mailer,
        protected Environment $twig,
        protected string $from,
        protected string $to,
        protected string $subject,
        protected array $ignoredClasses = []
    ) {
    }

    public function onKernelException(ExceptionEvent $event)
    {
        if (in_array($event->getThrowable()::class, $this->ignoredClasses)) {
            return;
        }

        $mail = new Email();
        $mail->from(Address::create($this->from));
        $mail->to(Address::create($this->to));
        $mail->subject(sprintf('%s %s', $this->subject, $event->getThrowable()->getMessage()));


        $exception = $event->getThrowable();
        $exceptions = [];
        $exceptions[] = ['class' => $exception::class, 'instance' => $exception];

        while ($exception = $exception->getPrevious()) {
            $exceptions[] = ['class' => $exception::class, 'instance' => $exception];
        }

        $mail->html($this->twig->render('@EmailErrors/exception.html.twig', [
            'subject' => $this->subject,
            'exceptions' => $exceptions,
            'request' => $event->getRequest(),
            'kernel' => $event->getKernel(),
        ]));


        $this->mailer->send($mail);
    }
}
