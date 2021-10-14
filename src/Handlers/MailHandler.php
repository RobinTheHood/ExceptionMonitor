<?php

namespace RobinTheHood\ExceptionMonitor\Handlers;

class MailHandler
{
    private static $mailAddress = '';
    private static $failGuard = null;

    public static function init($options)
    {
        if (isset($options['mail'])) {
            self::$mailAddress = $options['mail'];
        }

        if (isset($options['failGuard'])) {
            self::$failGuard = $options['failGuard'];
        }
    }

    public static function exceptionHandlerMail($exception)
    {
        if (!self::$mailAddress) {
            return;
        }

        $content = self::exceptionToString($exception);
        $hash = md5($content);
        $subject = 'ErrorReport: ' . $_SERVER['SERVER_NAME'] . ' - ' . $hash;

        self::sendMail(self::$mailAddress, $subject, $content);

        if (self::$failGuard) {
            self::$failGuard->addFail();
            self::$failGuard->saveClient();
        }
    }

    public static function exceptionToString($exception)
    {
        $str = '';

        $str .= 'Message: ' . $exception->getMessage() . "\n";
        $str .= 'File: ' . $exception->getFile() . "\n";
        $str .= 'Line: ' . $exception->getLine() . "\n";

        $str .= "\n" . '---------- TRACE ----------' . "\n\n";

        foreach ($exception->getTrace() as $entry) {
            $str .= 'File: ' . $entry['file'] . "\n";
            $str .= 'Line: ' . $entry['line'] . "\n";
            $str .= 'Func: ' . $entry['function'] . "\n";
            $str .= 'Class: ' . $entry['class'] . "\n";
            $str .= '---------------------------' . "\n\n";
        }

        $str .= 'SERVER:' . "\n\n";
        $str .= print_r($_SERVER, true);

        return $str;
    }

    private static function sendMail($toAddress, $subject, $content)
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
