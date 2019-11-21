<?php


namespace App\Service;

use Psr\Log\LoggerInterface;
use Swift_Mailer;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MailService
{
    private $mailer;
    private $templating;
    private $logger;

    public function __construct(Swift_Mailer $mailer, Environment $templating, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->logger = $logger;
    }

    public function sendVertification($email)
    {
        try {
            $message = (new \Swift_Message())
                ->setSubject('Prenumeratos patvirtinimas || Krepšinio Podkastai')
                ->setFrom('krepsinio.podcast@gmail.com')
                ->setTo($email)
                ->setBody(
                    $this->templating->render(
                        'emails/subscriberVersification.html.twig',
                        ['url' => 'kazis']
                    ),
                    'text/html'
                );
        } catch (LoaderError $e) {
            $this->logger->error($e->getMessage());
            return false;
        } catch (RuntimeError $e) {
            $this->logger->error($e->getMessage());
            return false;
        } catch (SyntaxError $e) {
            $this->logger->error($e->getMessage());
            return false;
        }

        $this->mailer->send($message);
    }
}
