<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Podcast;
use App\Entity\Source;
use App\Entity\Tag;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\PodcastRepository;
use App\Service\LikePodcastService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ListenLaterService;

class PodcastsController extends AbstractController
{
    /**
     * @var PodcastRepository
     */
    private $podcastRepository;

    /**
     * @var ListenLaterService
     *
     */
    private $listenLaterService;

    /**
     * @var LikePodcastService
     */
    private $likePodcastService;

    public function __construct(
        PodcastRepository $podcastRepository,
        ListenLaterService $listenLaterService,
        LikePodcastService $likePodcastService
    ) {
        $this->podcastRepository = $podcastRepository;
        $this->listenLaterService = $listenLaterService;
        $this->likePodcastService = $likePodcastService;
    }

    /**
     * @Route("/{page}", name="podcasts", defaults={"page":1}, requirements={"page"="\d+"})
     * @param int $page
     * @return Response
     */
    public function frontPage(int $page): Response
    {
        return $this->render('front/pages/podcasts/index.html.twig', [
            'podcasts' => $this->podcastRepository->getAllPodcastsPaginated($page),
            'podcastsLater' => $this->listenLaterService->getPodcasts(),
            'likedPodcasts' => $this->likePodcastService->getLikedPodcasts()
        ]);
    }

    /**
     * @Route("podkastai/tipas/{type}/{page}", name="podcasts_by_type", defaults={"page":1})
     * @param string $type
     * @param int $page
     * @return Response
     * @throws Exception
     */
    public function showPodcastsByType(string $type, int $page): Response
    {
        if ($type == 'video' || $type == 'audio') {
            if ($type == 'video') {
                $type = 'Youtube';
            }
        } else {
            throw new Exception('Kažkas bandote rankom vesti reikšmę, reikia spausti tik nuorodas.');
        }

        return $this->render('front/pages/podcasts/index.html.twig', [
            'podcasts' => $this->podcastRepository->findAllPaginatedPodcastsByType($type, $page),
            'podcastsLater' => $this->listenLaterService->getPodcasts(),
            'likedPodcasts' => $this->likePodcastService->getLikedPodcasts()
        ]);
    }

    /**
     * @Route("podkastai/{slug}/{page}", name="podcasts_by_source", defaults={"page":1})
     * @param Source $source
     * @param int $page
     * @return Response
     */
    public function showPodcastsBySource(Source $source, int $page): Response
    {
        return $this->render('front/pages/podcasts/index.html.twig', [
            'podcasts' => $this->podcastRepository->findAllPaginatedPodcastsBySource($source, $page),
            'podcastsLater' => $this->listenLaterService->getPodcasts(),
            'likedPodcasts' => $this->likePodcastService->getLikedPodcasts()
        ]);
    }

    /**
     * @Route("podkastas/{slug}/", name="single_podcast")
     * @param Podcast $podcast
     * @param EntityManagerInterface $entityManager
     * @param CommentRepository $commentRepository
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function showSinglePodcast(
        Podcast $podcast,
        EntityManagerInterface $entityManager,
        CommentRepository $commentRepository,
        Request $request
    ) {
        $form = $this->createForm(CommentType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Comment $comment */
            $comment = $form->getData();
            $comment->setUser($this->getUser());
            $comment->setPodcast($podcast);
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Naujas komentaras pridėtas.');

            return $this->redirectToRoute('single_podcast', [
                'slug' => $podcast->getSlug()
            ]);
        }

        return $this->render('front/pages/podcasts/show.html.twig', [
            'podcast' => $podcast,
            'comments' => $commentRepository->getAllCommentsByPodcast($podcast),
            'form' => $form->createView(),
            'podcastsLater' => $this->listenLaterService->getPodcasts(),
            'likedPodcasts' => $this->likePodcastService->getLikedPodcasts()
        ]);
    }

    /**
     * @Route("tagai/{slug}/{page}", name="podcasts_by_tag", defaults={"page":1})
     * @param Tag $tag
     * @param int $page
     * @return Response
     */
    public function showPodcastsByTag(Tag $tag, int $page): Response
    {
        return $this->render('front/pages/podcasts/index.html.twig', [
            'podcasts' => $this->podcastRepository->findAllPaginatedPodcastsByTag($tag, $page),
            'podcastsLater' => $this->listenLaterService->getPodcasts(),
            'likedPodcasts' => $this->likePodcastService->getLikedPodcasts()
        ]);
    }

    /**
     * @Route("/paieska/{page}", name="search_podcasts", defaults={"page":1})
     * @param Request $request
     * @param int $page
     * @return Response
     */
    public function searchPodcasts(Request $request, int $page): Response
    {
        $query = $request->get('q');
        $podcasts = $this->podcastRepository->searchPodcasts($query, $page);

        return $this->render('front/pages/podcasts/index.html.twig', [
            'podcasts' => $podcasts,
            'podcastsCount' => $this->podcastRepository->getSearchResultsCount($query),
            'search' => true,
            'podcastsLater' => $this->listenLaterService->getPodcasts(),
            'likedPodcasts' => $this->likePodcastService->getLikedPodcasts()
        ]);
    }
}
