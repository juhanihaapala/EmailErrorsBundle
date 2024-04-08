<?php

declare(strict_types=1);

namespace Sparklink\EmailErrorsBundle\EventListener;

use Sparklink\EmailErrorsBundle\ExceptionMailer\ExceptionMailer;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    public function __construct(
        protected ExceptionMailer $exceptionMailer
    ) {
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $this->exceptionMailer->sendException($event->getThrowable(), $event->getRequest(), $event->getKernel());
    }
}
