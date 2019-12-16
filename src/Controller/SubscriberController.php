<?php

namespace App\Controller;

use App\Entity\Subscriber;
use App\Form\SubscriberType;
use App\Service\MailService;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class SubscriberController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TokenGenerator
     */
    private $tokenGenerator;

    public function __construct(EntityManagerInterface $entityManager, TokenGenerator $tokenGenerator)
    {
        $this->entityManager = $entityManager;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * @Route("prenumeruoti", name="new_subscriber")
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function createSubscriber(Request $request)
    {
        $form = $this->createForm(SubscriberType::class, null, [
            'action' => $this->generateUrl('new_subscriber')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', 'Toks prenumeratorius jau egzistuoja!');

            return $this->redirectToRoute('podcasts');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Subscriber $subscriber */
            $subscriber = $form->getData();
            $subscriber->setConfirmationToken($this->tokenGenerator->getRandomSecureToken(100));

            $this->entityManager->persist($subscriber);
            $this->entityManager->flush();

            $this->addFlash('success', 'El. pašto patvirtinimo laiškas išsiųstas!');

            return $this->redirectToRoute('podcasts');
        }

        return $this->render('front/layout/_subscribtion_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("prenumeratorius/patvirtinimas/{confirmationToken}", name="confirm_subscriber")
     * @param Subscriber $subscriber
     * @return Response|NotFoundHttpException
     * @throws Exception
     */
    public function confirmSubscriber(Subscriber $subscriber)
    {
        if ($subscriber) {
            $subscriber->setIsConfirmed(true);
            $subscriber->setConfirmationToken(null);
            $subscriber->setUnsubscribeToken($this->tokenGenerator->getRandomSecureToken(100));

            $this->entityManager->flush();

            return $this->render('emails/confirm_email.html.twig', [
                'subscriber' => $subscriber
            ]);
        }

        throw $this->createNotFoundException();
    }

    /**
     * @Route("prenumeratos_atsisakymas/{unsubscribeToken}", name="unsubscribe")
     * @param Subscriber $subscriber
     * @return Response
     */
    public function deleteSubscriber(Subscriber $subscriber)
    {
        if ($subscriber) {
            $this->entityManager->remove($subscriber);
            $this->entityManager->flush();

            return $this->render('emails/unsubscribe.html.twig');
        }

        throw $this->createNotFoundException();
    }
}
