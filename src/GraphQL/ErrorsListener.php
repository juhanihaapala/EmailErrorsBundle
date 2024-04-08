<?php

declare(strict_types=1);

namespace Sparklink\EmailErrorsBundle\GraphQL;

use Overblog\GraphQLBundle\Event\ExecutorResultEvent;
use Sparklink\EmailErrorsBundle\ExceptionMailer\ExceptionMailer;
use Symfony\Component\HttpFoundation\RequestStack;

class ErrorsListener
{
    public function __construct(
        protected ExceptionMailer $exceptionMailer,
        protected RequestStack $requestStack,
    ) {
    }

    public function onPostExecutor(ExecutorResultEvent $executorResultEvent)
    {
        $request = $this->requestStack->getCurrentRequest();

        foreach ($executorResultEvent->getResult()->errors as $exception) {
            $this->exceptionMailer->sendException($exception, $request);
        }
    }
}
