<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Form\SearchPostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/posts")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="post_index", methods={"GET", "POST"})
     */
    public function index(PostRepository $postRepository, Request $request): Response
    {
        $form = $this->createForm(SearchPostType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData()['searchPost'];
            $posts = $postRepository->findByRefAndTag($search);
        } else {
            $posts = $postRepository->findAll();
        }
        return $this->render('post/index.html.twig', [
            'form' => $form->createView(),
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/my-posts", name="post_user_show", methods={"GET"})
     */
    public function showUserPosts(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findBy(['owner'=>$this->getUser()]);

        return $this->render('post/userPost/index.html.twig', [
            'posts' => $postRepository->findBy(['owner'=>$this->getUser()]),
        ]);
    }

    /**
     * @Route("/new", name="post_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setOwner($this->getUser());
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('post_user_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="post_show", methods={"GET"})
     */
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="post_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('post_user_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="post_delete", methods={"POST"})
     */
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('post_user_show', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{id}/favori", name="post_add_fav")
     */
    public function addToFavorite(Post $post, EntityManagerInterface $em): Response
    {
        if ($this->getUser()->isInFavorite($post)) {
            $this->getUser()->removeFromFavorite($post);
        } else {
            $this->getUser()->addToFavorite($post);
        }
        $em->flush();
        return $this->redirectToRoute('post_show',['id' => $post->getId()],Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{id}/like", name="post_add_like")
     */
    public function addLike(Post $post, EntityManagerInterface $em): Response
    {
        if ($this->getUser()->isLiked($post)) {
            $this->getUser()->removeLike($post);
        } else {
            $this->getUser()->addLike($post);
        }
        $em->flush();
        return $this->redirectToRoute('post_show',['id' => $post->getId()], Response::HTTP_SEE_OTHER);
    }
}
