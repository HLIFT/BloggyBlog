<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository as PostRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

class PostController extends AbstractController
{
    /**
     * 
     * @Route("/admin/post", name="admin.post.index")
     */
    public function index(HttpFoundationRequest $request): Response
    {
        $routeName = $request->attributes->get('_route');

        $postRepository = $this->getDoctrine()->getRepository(Post::class);
        $posts = $postRepository->findAll();

        if($routeName == "admin.post.index")
        {
            return $this->render('admin/post/index.html.twig', [
                'posts' => $posts,
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
     * @Route("/post", name="post.list")
     * @Route("/admin/post/list", name="admin.post.list")
     */
    public function list(HttpFoundationRequest $request, PostRepository $postRepositoryCustom): Response
    {
        $routeName = $request->attributes->get('_route');

        $postRepository = $this->getDoctrine()->getRepository(Post::class);
        $posts = $postRepository->findAll();
        dump($posts);
        $cinqPosts = $postRepositoryCustom->findLastFive();

        if($routeName == "admin.post.list")
        {
            return $this->render('admin/post/list.html.twig', [
                'posts' => $posts,
            ]);
        }
        else
        {
            return $this->render('user/post/index.html.twig', [
                'posts' => $cinqPosts,
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
            //...
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
        // On récupère le `repository` en rapport avec l'entity `Post` 
        $postRepository = $this->getDoctrine()->getRepository(Post::class);
        // On fait appel à la méthode générique `find` qui permet de SELECT en fonction d'un Id
        $post = $postRepository->findBy(['slug' => $slug]);
        $post = $post[0];


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
            ]);
        }
        else
        {
            return $this->render('user/post/show.html.twig', [
                'post' => $post,
            ]);
        }       
    }

    /**
     * @Route("/admin/post/{slug}/edit", name="post.edit")
     */
    public function update(string $slug, HttpFoundationRequest $request): Response
    {
        // On récupère le manager des entities
        $entityManager = $this->getDoctrine()->getManager();
        // On récupère le `repository` en rapport avec l'entity `Post`
        $postRepository = $entityManager->getRepository(Post::class);
        // On fait appel à la méthode générique `find` qui permet de SELECT en fonction d'un Id
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
        // On dit au manager que l'on veux supprimer cet objet en base de données
        $entityManager->remove($post);
        // On met à jour en base de données en supprimant la ligne correspondante (i.e. la requête DELETE)
        $entityManager->flush();

        return $this->redirectToRoute('admin.post.list');
    }

}
