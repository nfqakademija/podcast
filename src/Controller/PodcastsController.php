<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Podcast;
use App\Entity\Source;
use App\Entity\Tag;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\PodcastRepository;
use App\Repository\SourceRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PodcastsController extends AbstractController
{
    private $sourceRepository;

    private $tagRepository;

    private $podcastRepository;

    public function __construct(
        SourceRepository $sourceRepository,
        TagRepository $tagRepository,
        PodcastRepository $podcastRepository
    ) {
        $this->sourceRepository = $sourceRepository;
        $this->tagRepository = $tagRepository;
        $this->podcastRepository = $podcastRepository;
    }

    /**
     * @Route("/{page}", name="podcasts", defaults={"page":1}, requirements={"page"="\d+"})
     */
    public function front($page)
    {
        return $this->render('front/pages/posts/index.html.twig', [
            'podcasts' => $this->podcastRepository->getAllPodcastsPaginated($page),
            'sources' => $this->sourceRepository->findAll(),
            'tags' => $this->tagRepository->findAll()
        ]);
    }

    /**
     * @Route("podkastai/{source}/{page}", name="podcasts_by_source", defaults={"page":1})
     */
    public function showPodcastsBySource(Source $source, $page)
    {
        return $this->render('front/pages/posts/index.html.twig', [
            'podcasts' => $this->podcastRepository->findAllPaginatedPodcastsBySource($source, $page),
            'sources' => $this->sourceRepository->findAll(),
            'tags' => $this->tagRepository->findAll()
        ]);
    }

    /**
     * @Route("podkastas/{podcast}/", name="single_podcast")
     */
    public function showPodcast(
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

            $this->addFlash('success', 'Naujas komentaras sukurtas');

            return $this->redirectToRoute('single_podcast', [
                'podcast' => $podcast->getId()
            ]);
        }

        return $this->render('front/pages/posts/show.html.twig', [
            'podcast' => $podcast,
            'comments' => $commentRepository->getAllCommentsByPodcast($podcast),
            'sources' => $this->sourceRepository->findAll(),
            'tags' => $this->tagRepository->findAll(),
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("tag/{tag}/{page}", name="podcasts_by_tag", defaults={"page":1})
     */
    public function showPodcastsByTag(Tag $tag, $page)
    {
        return $this->render('front/pages/posts/index.html.twig', [
            'podcasts' => $this->podcastRepository->findAllPaginatedPodcastsByTag($tag, $page),
            'sources' => $this->sourceRepository->findAll(),
            'tags' => $this->tagRepository->findAll()
        ]);
    }

    /**
     * @Route("/paieska/{page}", name="search_podcasts", defaults={"page":1})
     */
    public function searchPodcasts(Request $request, $page)
    {
        $query = $request->get('q');
        $podcasts = $this->podcastRepository->searchPodcasts($query, $page);

        return $this->render('front/pages/posts/index.html.twig', [
            'podcasts' => $podcasts,
            'sources' => $this->sourceRepository->findAll(),
            'tags' => $this->tagRepository->findAll(),
            'podcastsCount' => $this->podcastRepository->getSearchResultsCount($query),
            'search' => true
        ]);
    }
}
