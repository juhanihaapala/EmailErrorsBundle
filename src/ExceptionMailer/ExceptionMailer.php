<?php

declare(strict_types=1);

namespace Sparklink\EmailErrorsBundle\ExceptionMailer;

use Psr\Log\LoggerInterface;
use Sparklink\EmailErrorsBundle\Twig\DataPanel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class ExceptionMailer
{
    public function __construct(
        protected MailerInterface $mailer,
        protected Environment $twig,
        protected ?LoggerInterface $logger,
        protected string $from,
        protected string $to,
        protected string $subject,
        protected array $ignoredClasses = [],
        protected array $ignoredMessages = [],
        protected array $ignoredIpAddresses = [],
    ) {
    }

    public function sendException(\Throwable $exception, ?Request $request = null, ?HttpKernelInterface $kernel = null)
    {
        try {
            // Ignored exceptions
            if (\in_array($exception::class, $this->ignoredClasses)) {
                return;
            }

            // Ignored exceptions messages
            foreach ($this->ignoredMessages as $ignoredMessage) {
                if (str_contains($exception->getMessage(), $ignoredMessage)) {
                    return;
                }
            }

            // Ignored IP addresses
            if ($request) {
                foreach ($request->getClientIps() as $ip) {
                    if (\in_array($ip, $this->ignoredIpAddresses)) {
                        return;
                    }
                }
            }

            $mail = new Email();
            $mail->from(Address::create($this->from));
            $mail->to(Address::create($this->to));
            $mail->subject(sprintf('%s %s', $this->subject, $exception->getMessage()));

            $exceptions = [];
            $exceptions[] = ['class' => $exception::class, 'instance' => $exception];

            while ($exception = $exception->getPrevious()) {
                $exceptions[] = ['class' => $exception::class, 'instance' => $exception];
            }

            $mail->html($this->twig->render('@EmailErrors/exception.html.twig', [
                'subject' => $this->subject,
                'exceptions' => $exceptions,
                'request' => $request,
                'request_panels' => $request ? $this->getRequestDataPanels($request) : [],
                'kernel' => $kernel,
            ]));

            $this->mailer->send($mail);
        } catch (\Exception $e) {
            $this->logger?->error('Error sending exception email', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * @return DataPanel[]
     */
    protected function getRequestDataPanels(Request $request): array
    {
        $panels = [];
        if (!empty($request->query->all())) {
            $panels[] = new DataPanel('GET Parameters', $request->query->all());
        }
        if (!empty($request->request->all())) {
            $panels[] = new DataPanel('POST Parameters', $request->request->all());
        }

        if ($request->getContent()) {
            $content = $request->getContent();
            $type = DataPanel::TYPE_STRING;
            try {
                $content = json_decode($content, true);
                $type = DataPanel::TYPE_JSON;
            } catch (\Exception $e) {
            }

            $panels[] = new DataPanel('Request content', $content, $type);
        }

        if (!empty($request->attributes->all())) {
            $panels[] = new DataPanel('Attributes', $request->attributes->all());
        }

        if (!empty($request->headers->all())) {
            $panels[] = new DataPanel('Headers', $request->headers->all());
        }

        if (!empty($request->files->all())) {
            $panels[] = new DataPanel('Files', $request->files->all());
        }

        if (!empty($request->cookies->all())) {
            $panels[] = new DataPanel('Cookies', $request->cookies->all());
        }

        if ($request->hasSession(true)) {
            $panels[] = new DataPanel('Session', $request->getSession()->all());
        }

        return $panels;
    }
}
