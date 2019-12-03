<?php


namespace App\Service;

use App\Entity\Podcast;
use App\Entity\Subscriber;
use App\Entity\User;
use App\Interfaces\MailableEntity;
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
    private const FROM_EMAIL = 'krepsinio.podcast@gmail.com';
    private const FROM_NAME = 'Krepšinio podcastai';
    private const NEWSLETTER_SUBJECT_LINE = 'Nauji podkastai';
    private const CONFIRMATION_SUBJECT_LINE = 'El. pašto patvirtinimas';
    private const RESET_PASSWORD_SUBJECT_LINE = 'Slaptažodžio atkūrimas';
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

    public function sendVerification(MailableEntity $mailableEntity): bool
    {
        if ($mailableEntity instanceof User) {
            $path = 'confirm_user';
        } else {
            $path = 'confirm_subscriber';
        }

        $body = $this->twig->render(
            'emails/subscriber_verification.html.twig',
            [
                'token' => $mailableEntity->getConfirmationToken(),
                'path' => $path
            ]
        );

        return $this->sendMessage($mailableEntity, self::CONFIRMATION_SUBJECT_LINE, $body);
    }

    public function sendDailyNewsletterToSubscribers()
    {
        $subscribers = $this->subscriberRepository->findBy(['isConfirmed' => true]);
        $newPodcasts = $this->podcastRepository->findAllTodaysNewPodcasts();
        $today = date("Y-m-d");
        $subjectLine = self::NEWSLETTER_SUBJECT_LINE .' ' . $today;

        if ($newPodcasts && $subscribers) {
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

            return true;
        }

        return false;
    }

    public function sendPasswordResetEmail(User $user)
    {
        $this->sendMessage(
            $user,
            self::RESET_PASSWORD_SUBJECT_LINE,
            $this->twig->render('emails/reset_password_email.html.twig', [
                'user' => $user
            ])
        );
    }

    /**
     * @param MailableEntity $mailableEntity
     * @param string $subject
     * @param string $body
     * @return bool
     */
    private function sendMessage(MailableEntity $mailableEntity, string $subject, string $body): bool
    {
        $message = (new Swift_Message())
            ->setSubject($subject)
            ->setFrom([self::FROM_EMAIL => self::FROM_NAME])
            ->setTo($mailableEntity->getEmail())
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
