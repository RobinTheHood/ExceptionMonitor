<?php

declare(strict_types=1);

namespace RobinTheHood\ExceptionMonitor\Handler;

use Throwable;

class MailHandler implements HandlerInterface
{
    private $mailAddress = '';
    private $failGuard = null;

    public function __construct(array $options = [])
    {
        $this->init($options);
    }

    public function init($options)
    {
        if (isset($options['mail'])) {
            $this->mailAddress = $options['mail'];
        }

        if (isset($options['failGuard'])) {
            $this->failGuard = $options['failGuard'];
        }
    }

    public function handle(Throwable $exception): void
    {
        if (!$this->mailAddress) {
            return;
        }

        $content = $this->exceptionToString($exception);
        $hash = md5($content);
        $subject = 'ErrorReport: ' . $_SERVER['SERVER_NAME'] . ' - ' . $hash;

        $this->sendMail($this->mailAddress, $subject, $content);

        if ($this->failGuard) {
            $this->failGuard->addFail();
            $this->failGuard->saveClient();
        }
    }

    public function exceptionToString($exception)
    {
        $str = '';

        $str .= 'Message: ' . $exception->getMessage() . "\n";
        $str .= 'File: ' . $exception->getFile() . "\n";
        $str .= 'Line: ' . $exception->getLine() . "\n";

        $str .= "\n" . '---------- TRACE ----------' . "\n\n";

        foreach ($exception->getTrace() as $entry) {
            $str .= 'File: ' . ($entry['file'] ?? '') . "\n";
            $str .= 'Line: ' . ($entry['line'] ?? '') . "\n";
            $str .= 'Func: ' . ($entry['function'] ?? '') . "\n";
            $str .= 'Class: ' . ($entry['class'] ?? '') . "\n";
            $str .= '---------------------------' . "\n\n";
        }

        $str .= 'SERVER:' . "\n\n";
        $str .= print_r($_SERVER, true);

        return $str;
    }

    private function sendMail($toAddress, $subject, $content)
    {
        $header[] = 'MIME-Version: 1.0';
        $header[] = 'Content-type: text/plain; charset=UTF-8';
        $header[] = 'Content-Transfer-Encoding: quoted-printable';
        //$header[] = 'From: '           . $encodedMeta['from'];
        //$header[] = 'Reply-To: '       . $encodedMeta['replyTo'];
        $header[] = 'X-Mailer: PHP/'   . phpversion();
        $header = implode("\r\n", $header);

        // Set bounce - option
        $options =  '-f ' . $toAddress;

        // Send mail
        mail($toAddress, $subject, quoted_printable_encode($content), $header, $options);
    }
}
