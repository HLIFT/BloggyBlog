<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\PostType;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

class PostController extends AbstractController
{
    /**
     * @Route("/post", name="post.list")
     * @Route("/admin/post/list", name="admin.post.list")
     */
    public function list(HttpFoundationRequest $request, PaginatorInterface $paginator): Response
    {
        $routeName = $request->attributes->get('_route');

        $postRepository = $this->getDoctrine()->getRepository(Post::class);

        /** @var PostRepository $postRepository */

        $allPosts = $postRepository->findAllRecent();

        $donnees = $postRepository->findAllRecentPublished();

        $posts = $paginator->paginate(
            $donnees,
            $request->query->getInt('page', 1),
            5
        );

        if($routeName == "admin.post.list")
        {
            return $this->render('admin/post/list.html.twig', [
                'posts' => $allPosts,
            ]);
        }
        else
        {
            return $this->render('user/post/index.html.twig', [
                'posts' => $posts,
            ]);
        }       
    }

     /**
     * @Route("/admin/post/create", name="post.create")
     */

    public function create(HttpFoundationRequest $request): Response
    {
        $post = new Post();

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $slugger = new AsciiSlugger();
            $date = new DateTime();
            $post = $form->getData();
            $post->setCreatedAt($date);
            $post->setUpdatedAt($date);
            $post->setSlug($slugger->slug($post->getTitle()));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
            $slug = $post->getSlug();
            return $this->redirectToRoute('admin.post.show', ['slug' => $slug]);
        }

        return $this->render('admin/post/create.html.twig', [
            'formView' => $form->createView(),
        ]);
    }

    /**
     * @Route("/post/{slug}", name="post.show")
     * @Route("/admin/post/{slug}", name="admin.post.show")
     */
    public function show(String $slug, HttpFoundationRequest $request): Response
    {
        $postRepository = $this->getDoctrine()->getRepository(Post::class);

        $post = $postRepository->findOneBy(['slug' => $slug]);

        $commentRepository = $this->getDoctrine()->getRepository(Comment::class);

        /** @var CommentRepository $commentRepository */

        $comments = $commentRepository->findPostRecent($post);


        if(!$post) {
            throw $this->createNotFoundException(
                "Pas de Post trouvé avec le slug ".$slug
            );
        }

        $routeName = $request->attributes->get('_route');

        if($routeName == "admin.post.show")
        {
            return $this->render('admin/post/show.html.twig', [
                'post' => $post,
                'comments' => $comments,
            ]);
        }
        else
        {
            return $this->render('user/post/show.html.twig', [
                'post' => $post,
                'comments' => $comments,
            ]);
        }       
    }

    /**
     * @Route("/admin/post/{slug}/edit", name="post.edit")
     */
    public function update(string $slug, HttpFoundationRequest $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $postRepository = $entityManager->getRepository(Post::class);

        $post = $postRepository->findOneBy(['slug' => $slug]);

        if(!$post) {
            throw $this->createNotFoundException(
                "Pas de Post trouvé avec le slug ".$slug
            );
        }

        $form = $this->createForm(PostType::class, $post);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $date = new DateTime();
            $slugger = new AsciiSlugger();
            $post = $form->getData();
            $post->setUpdatedAt($date);
            $post->setSlug($slugger->slug($post->getTitle()));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
            
            $slug = $post->getSlug();
            return $this->redirectToRoute('admin.post.show', ['slug' => $slug]);
        }

        return $this->render('admin/post/edit.html.twig', [
            'formView' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/post/{id}/remove", name="post.remove", requirements={"id"="\d+"})
     */
    public function remove($id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $postRepository = $entityManager->getRepository(Post::class);

        $post = $postRepository->find($id);

        if(!$post) {
            throw $this->createNotFoundException(
                "Pas de Post trouvé avec l'id ".$id
            );
        }

        $entityManager->remove($post);

        $entityManager->flush();

        return $this->redirectToRoute('admin.post.list');
    }

}
