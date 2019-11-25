<?php


namespace App\Service;

use App\Entity\Subscriber;
use App\Repository\SubscriberRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    private $subscriberRepository;

    public function __construct(
        Swift_Mailer $mailer,
        Environment $templating,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        SubscriberRepository $subscriberRepository
    ) {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->subscriberRepository = $subscriberRepository;
    }

    public function sendVerification($email)
    {
        try {
            $message = (new \Swift_Message())
                ->setSubject('Prenumeratos patvirtinimas || Krepšinio Podkastai')
                ->setFrom('krepsinio.podcast@gmail.com')
                ->setTo($email)
                ->setBody(
                    $this->templating->render(
                        'emails/subscriberVerification.html.twig',
                        ['email' => $email]
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

        if ($this->mailer->send($message) && $this->checkIfEmailExists($email)) {
            $subscriber = new Subscriber();
            $subscriber->setEmail($email);

            $this->entityManager->persist($subscriber);
            $this->entityManager->flush();
        }
    }
    private function checkIfEmailExists($email): bool
    {
        return empty($this->subscriberRepository->findOneBy(['email' => $email]))?true:false;
    }
}
