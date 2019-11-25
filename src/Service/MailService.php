<?php


namespace App\Service;

use App\Entity\Podcast;
use App\Entity\Subscriber;
use App\Repository\SubscriberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Swift_Mailer;
use Swift_Message;
use Throwable;
use Twig\Environment;

const SENDERS_EMAIL = 'krepsinio.podcast@gmail.com';


class MailService
{
    private $mailer;
    private $templating;
    private $logger;
    private $subscriberRepository;
    private $entityManager;

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

    public function sendVerification($email): bool
    {
        try {
            $message = (new Swift_Message())
                ->setSubject('Prenumeratos patvirtinimas || Krepšinio Podkastai')
                ->setFrom(SENDERS_EMAIL)
                ->setTo($email)
                ->setBody(
                    $this->templating->render(
                        'emails/subscriberVerification.html.twig',
                        ['email' => $email]
                    ),
                    'text/html'
                );
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            return false;
        }

        if ($this->mailer->send($message) && $this->checkIfEmailExists($email)) {
            $subscriber = new Subscriber();
            $subscriber->setEmail($email);

            $this->entityManager->persist($subscriber);
            $this->entityManager->flush();
            return true;
        }

        return false;
    }

    private function checkIfEmailExists($email): bool
    {
        return empty($this->subscriberRepository->findOneBy(['email' => $email]))?true:false;
    }

    /**
     * @var Podcast $podcast
     */
    public function sendNotification($podcast): bool
    {
        $subscribers = $this->subscriberRepository->findBy([
            'isConfirmed' => true,
        ]);

        /** @var Subscriber $subscriber */
        foreach ($subscribers as $subscriber) {
            try {
                $message = (new Swift_Message())
                    ->setSubject('Naujas įrašas|| Krepšinio Podkastai')
                    ->setFrom(SENDERS_EMAIL)
                    ->setTo($subscriber->getEmail())
                    ->setBody(
                        $this->templating->render(
                            'emails/subscriberNotification.html.twig',
                            [
                                'email' => $subscriber->getEmail(),
                                'podcast' => $podcast,
                            ]
                        ),
                        'text/html'
                    );
            } catch (Throwable $e) {
                $this->logger->error($e);
                return false;
            }

            if ($this->mailer->send($message)) {
                return true;
            }

            return false;
        }
    }
}
