<?php


namespace App\Service;

use App\Entity\Podcast;
use App\Entity\Subscriber;
use App\Entity\User;
use App\Interfaces\Confirmable;
use App\Repository\PodcastRepository;
use App\Repository\SubscriberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Swift_Mailer;
use Swift_Message;
use Throwable;
use Twig\Environment;

class MailService
{
    private $mailer;
    private $twig;
    private $logger;
    private $subscriberRepository;
    private $entityManager;
    private $podcastRepository;

    public function __construct(
        Swift_Mailer $mailer,
        Environment $twig,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        SubscriberRepository $subscriberRepository,
        PodcastRepository $podcastRepository
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->subscriberRepository = $subscriberRepository;
        $this->podcastRepository = $podcastRepository;
    }

    public function sendVerification(Confirmable $confirmable): bool
    {
        if ($confirmable instanceof User) {
            $path = 'confirm_user';
        } else {
            $path = 'confirm_subscriber';
        }

        $subject = 'El. pašto patvirtinimas || Krepšinio Podcastai';

        $body = $this->twig->render(
            'emails/subscriber_verification.html.twig',
            [
                'token' => $confirmable->getConfirmationToken(),
                'path' => $path
            ]
        );

        return $this->sendMessage($confirmable, $subject, $body);
    }

    public function sendDailyNewsletterToSubscribers()
    {
        $subscribers = $this->subscriberRepository->findBy(['isConfirmed' => true]);
        $newPodcasts = $this->podcastRepository->findAllTodaysNewPodcasts();
        $today = date("Y-m-d");
        $subjectLine = 'Nauji podkastai ' . $today;

        if ($newPodcasts) {
            foreach ($subscribers as $subscriber) {
                $this->sendMessage(
                    $subscriber,
                    $subjectLine,
                    $this->twig->render('emails/daily_podcasts.html.twig', [
                        'podcasts' => $newPodcasts,
                        'subscriber' => $subscriber
                    ])
                );
            }
        }
    }

    /**
     * @param Confirmable $confirmable
     * @param string $subject
     * @param string $body
     * @return bool
     */
    private function sendMessage(Confirmable $confirmable, string $subject, string $body): bool
    {
        $message = (new Swift_Message())
            ->setSubject($subject)
            ->setFrom(['krepsinio.podcast@gmail.com' => 'Krepšinio podcastai'])
            ->setTo($confirmable->getEmail())
            ->setBody($body, 'text/html');

        try {
            $this->mailer->send($message);
            return true;
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }
}
