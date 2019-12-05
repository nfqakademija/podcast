<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use App\Service\MailService;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends AbstractController
{
    /**
     * @Route("/vartotojo_panele", name="user_panel")
     * @IsGranted("ROLE_USER")
     */
    public function panel(Request $request, TagRepository $tagRepository, EntityManagerInterface $entityManager)
    {
//        $form = $this->createForm(RegistrationFormType::class);
        $token = $request->request->get('token');
        $userTags = $tagRepository->findTagsByUser($this->getUser());
        $allTags = $tagRepository->findAll();
        /** @var User $user */
        $user = $this->getUser();

        if ($this->isCsrfTokenValid('add_tags', $token)) {
            $submittedTags = $request->request->get('tags');
            if ($submittedTags) {
                foreach ($submittedTags as $submittedTag) {
                    $existingTag = $tagRepository->findOneBy(['tag' => $submittedTag]);
                    if (!$existingTag) {
                        $tag = new Tag();
                        $tag->setTag($submittedTag);
                        $entityManager->persist($tag);
                        $user->addTag($tag);
                    } else {
                        $user->addTag($existingTag);
                    }
                }

                foreach ($userTags as $userTag) {
                    $tagExists = array_filter($submittedTags, function ($submittedTag) use ($userTag) {
                        return $submittedTag === $userTag->getTag();
                    });

                    if (!$tagExists) {
                        $user->removeTag($userTag);
                    }
                }
            } else {
                foreach ($userTags as $userTag) {
                    $user->removeTag($userTag);
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('user_panel');
        }
        return $this->render('front/pages/users/panel.html.twig', [
//            'form' => $form->createView()
            'tags' => $userTags
        ]);
    }

    /**
     * @Route("slaptazodzio-atkurimas", name="recover_password", methods={"GET", "POST"})
     */
    public function sendResetPasswordEmail(
        UserRepository $userRepository,
        MailService $mailService,
        EntityManagerInterface $entityManager,
        TokenGenerator $tokenGenerator,
        Request $request
    ) {
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('reset_password', $submittedToken)) {
            $email = $request->request->get('username');
            $user = $userRepository->findOneBy(['username' => $email]);

            if ($user) {
                $user->setPasswordResetToken($tokenGenerator->getRandomSecureToken(200));
                $entityManager->flush();
                $mailService->sendPasswordResetEmail($user);
                $this->addFlash('success', 'Slaptažodžio atkūrimas pradėtas, patikrinkite el. paštą');

                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('danger', 'Toks vartotojas neegzistuoja!');

                return $this->redirectToRoute('recover_password');
            }
        }

        return $this->render('front/pages/users/request_reset_password.html.twig');
    }
}
