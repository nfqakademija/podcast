<?php

namespace App\Controller;

use App\Entity\Subscriber;
use App\Form\SubscriberType;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SubscriberController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("subscribe", name="new_subscriber")
     */
    public function createSubscriber(
        Request $request,
        TokenGenerator $tokenGenerator
    ) {
        $form = $this->createForm(SubscriberType::class, null, [
            'action' => $this->generateUrl('new_subscriber')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Subscriber $subscriber */
            $subscriber = $form->getData();
            $subscriber->setConfirmationToken($tokenGenerator->getRandomSecureToken(100));

            $this->entityManager->persist($subscriber);
            $this->entityManager->flush();

            $this->addFlash('success', 'El. pašto patvirtinimo laiškas išsiūstas!');

            return $this->redirectToRoute('podcasts');
        }

        return $this->render('front/layout/_subscribtion_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("subscriber/confirmation/{confirmationToken}", name="confirm_subscriber")
     */
    public function confirmUser(Subscriber $subscriber)
    {
        if ($subscriber) {
            $subscriber->setIsConfirmed(true);
            $subscriber->setConfirmationToken(null);

            $this->entityManager->flush();

            return $this->render('emails/confirm_email.html.twig', [
                'subscriber' => $subscriber
            ]);
        }

        $this->createNotFoundException();
    }
}
