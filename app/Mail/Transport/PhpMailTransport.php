<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class PhpMailTransport extends AbstractTransport
{
    /**
     * Send the email message.
     */
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        
        $to = implode(', ', array_map(function ($address) {
            return $address->toString();
        }, $email->getTo()));

        $subject = $email->getSubject();
        
        // Extract headers and exclude To/Subject to avoid duplicates
        $preparedHeaders = $email->getPreparedHeaders();
        $headers = clone $preparedHeaders;
        $headers->remove('To');
        $headers->remove('Subject');

        $headersString = $headers->toString();
        $bodyString = $email->getBody()->toString();

        // Send using PHP native mail function
        $success = @mail($to, $subject, $bodyString, $headersString);
        
        if (!$success) {
            throw new \RuntimeException('Failed to send email using PHP native mail() function.');
        }
    }

    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'phpmail';
    }
}
