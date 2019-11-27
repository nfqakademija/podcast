<?php


namespace App\Service;

use App\Entity\Podcast;
use App\Entity\Subscriber;
use App\Entity\User;
use App\Interfaces\Confirmable;
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

    public function __construct(
        Swift_Mailer $mailer,
        Environment $twig,
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        SubscriberRepository $subscriberRepository
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->subscriberRepository = $subscriberRepository;
    }

    public function sendVerification(Confirmable $confirmable): bool
    {
        if ($confirmable instanceof User) {
            $path = 'confirm_user';
        } else {
            $path = 'confirm_subscriber';
        }

        $subject = 'El. pašto patvirtinimas || Krepšinio Podkastai';

        $body = $this->twig->render(
            'emails/subscriberVerification.html.twig', [
                'token' => $confirmable->getConfirmationToken(),
                'path' => $path
        ]);

        return $this->sendMessage($confirmable, $subject, $body);
    }

//    /**
//     * @var Podcast $podcast
//     */
//    public function sendNotification($podcast): bool
//    {
//        $subscribers = $this->subscriberRepository->findBy([
//            'isConfirmed' => true,
//        ]);
//
//        /** @var Subscriber $subscriber */
//        foreach ($subscribers as $subscriber) {
//            try {
//                $message = (new Swift_Message())
//                    ->setSubject('Naujas įrašas|| Krepšinio Podkastai')
//                    ->setFrom(SENDERS_EMAIL)
//                    ->setTo($subscriber->getEmail())
//                    ->setBody(
//                        $this->templating->render(
//                            'emails/subscriberNotification.html.twig',
//                            [
//                                'email' => $subscriber->getEmail(),
//                                'podcast' => $podcast,
//                            ]
//                        ),
//                        'text/html'
//                    );
//            } catch (Throwable $e) {
//                $this->logger->error($e);
//                return false;
//            }
//
//            if ($this->mailer->send($message)) {
//                return true;
//            }
//
//            return false;
//        }
//    }

    /**
     * @param Confirmable $confirmable
     * @param string $subject
     * @param string $sender
     * @param string $body
     * @return bool
     */
    private function sendMessage(Confirmable $confirmable, string $subject, string $body): bool
    {
        $message = (new Swift_Message())
            ->setSubject($subject)
            ->setFrom(['krepsinio.podcast@gmail.com' => 'Krepšinio podkastai'])
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
