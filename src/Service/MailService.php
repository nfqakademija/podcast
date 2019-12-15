<?php


namespace App\Service;

use App\Entity\Podcast;
use App\Entity\Subscriber;
use App\Entity\User;
use App\Interfaces\MailableEntity;
use App\Repository\PodcastRepository;
use App\Repository\SubscriberRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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

    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SubscriberRepository
     */
    private $subscriberRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var PodcastRepository
     */
    private $podcastRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(
        Swift_Mailer $mailer,
        Environment $twig,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        SubscriberRepository $subscriberRepository,
        PodcastRepository $podcastRepository,
        UserRepository $userRepository
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->subscriberRepository = $subscriberRepository;
        $this->podcastRepository = $podcastRepository;
        $this->userRepository = $userRepository;
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

    /**
     * @return bool
     * @throws Exception
     */
    public function sendDailyNewsletterBySelectedTagsToRegisteredUsers(): bool
    {
        $matchingUsers = $this->userRepository->getAllUsersWithTagsAndDailyPodcasts();
        $today = date("Y-m-d");
        $subjectLine = self::NEWSLETTER_SUBJECT_LINE .' ' . $today;

        if ($matchingUsers) {
            foreach ($matchingUsers as $user) {
                $podcasts = [];
                foreach ($user->getTags() as $tag) {
                    foreach ($tag->getPodcasts() as $podcast) {
                        if (!in_array($podcast, $podcasts)) {
                            $podcasts[] = $podcast;
                        }
                    }
                }
                $this->sendMessage(
                    $user,
                    $subjectLine,
                    $this->twig->render('emails/daily_podcasts.html.twig', [
                        'podcasts' => $podcasts,
                    ])
                );
            }

            return true;
        }

        return false;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function sendDailyNewsletterToSubscribers(): bool
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

    /**
     * @param User $user
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function sendPasswordResetEmail(User $user): void
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
