<?php

namespace App\Controller;

use App\Entity\Subscriber;
use App\Repository\SubscriberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriberController extends AbstractController
{
    /**
     * @Route("/subscriber/veritify/{email}", name="subscriber_veritify_email")
     * @ParamDecryptor(params={"email"})
     * @param SubscriberRepository $subscriberRepository
     * @param EntityManagerInterface $entityManager
     * @param $email
     * @return Response
     */
    public function veritify(
        SubscriberRepository $subscriberRepository,
        EntityManagerInterface $entityManager,
        $email
    ) {
        /** @var Subscriber $subscriber */
        $subscriber = $subscriberRepository->findOneBy([
            'email' => $email
        ]);
        if (!empty($subscriber)) {
            $subscriber->setIsConfirmed(true);
            $entityManager->merge($subscriber);
            $entityManager->flush();

            return $this->render(
                'emails/subscriberVerificationComplete.html.twig',
                ['email' => $email]
            );
        } else {
            throw $this->createNotFoundException();
        }
    }

    /**
     * @Route("/subscriber/cancel/{email}", name="subscriber_cancel")
     * @ParamDecryptor(params={"email"})
     * @param SubscriberRepository $subscriberRepository
     * @param EntityManagerInterface $entityManager
     * @param $email
     * @return Response
     */
    public function cancel(
        SubscriberRepository $subscriberRepository,
        EntityManagerInterface $entityManager,
        $email
    ) {
        /** @var Subscriber $subscriber */
        $subscriber = $subscriberRepository->findOneBy([
            'email' => $email
        ]);
        if (!empty($subscriber)) {
            $entityManager->remove($subscriber);
            $entityManager->flush();

            return $this->render(
                'emails/subscriberCancelComplete.html.twig',
                ['email' => $email]
            );
        } else {
            throw $this->createNotFoundException();
        }
    }

//    /**
//     * @Route("/subscriber/add/{email}", methods={"POST"}, name="subscriber_add")
//     *
//     */
//    public function add(
//        $email
//    ) {
//        dd($email);
//    }
}
