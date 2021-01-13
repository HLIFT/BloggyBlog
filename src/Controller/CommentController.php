<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Repository\CategoryRepository as CategoryRepository;
use App\Repository\CommentRepository as CommentRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    /**
     * @Route("/admin/comment/list", name="comment.list")
     */
    public function list(CommentRepository $commentRespositoryCustom): Response
    {
        $commentRepository = $this->getDoctrine()->getRepository(Comment::class);
        $comments = $commentRespositoryCustom->findAllRecent();
        return $this->render('admin/comment/index.html.twig', [
            'comments' => $comments,
            ]);
    }

    /**
     * @Route("/comment/{slug}", name="comment.post.show")
     */
    public function showPostComment(String $slug): Response
    {
        // On récupère le `repository` en rapport avec l'entity `Post` 
        $postRepository = $this->getDoctrine()->getRepository(Post::class);
        // On fait appel à la méthode générique `find` qui permet de SELECT en fonction d'un Id
        $post = $postRepository->findBy(['slug' => $slug]);

        if(!$post) {
            throw $this->createNotFoundException(
                "Pas de Post trouvé avec le slug ".$slug
            );
        }

        $commentRepository = $this->getDoctrine()->getRepository(Comment::class);
        $comments = $commentRepository->findBy(['post' => $post]);

        return $this->render('user/comment/show.post.html.twig', [
            'comments' => $comments,
            'post' => $post,
        ]);
    }

    /**
     * @Route("/comment/{idComment}/show", name="comment.show", requirements={"idComment"="\d+"})
     */
    public function showComment(int $idComment): Response
    {
        // On récupère le `repository` en rapport avec l'entity `Post` 
        $commentRepository = $this->getDoctrine()->getRepository(Comment::class);
        // On fait appel à la méthode générique `find` qui permet de SELECT en fonction d'un Id
        $comment = $commentRepository->find($idComment);

        if(!$comment) {
            throw $this->createNotFoundException(
                "Pas de Comment trouvé avec l'id ".$idComment
            );
        }

        $post = $comment->getPost();

        return $this->render('user/comment/show.html.twig', [
            'comment' => $comment,
            'post' => $post,
        ]);
    }

    /**
     * @Route("/comment/create/{idPost}", name="comment.create", requirements={"idPost"="\d+"})
     */

    public function create(int $idPost, HttpFoundationRequest $request): Response
    {
        // On récupère le manager des entities
        $entityManager = $this->getDoctrine()->getManager();
        // On récupère le `repository` en rapport avec l'entity `Post`
        $postRepository = $entityManager->getRepository(Post::class);
        // On fait appel à la méthode générique `find` qui permet de SELECT en fonction d'un Id
        $post = $postRepository->find($idPost);

        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            //...
            $date = new DateTime();
            $comment = $form->getData();
            $comment->setValid(false);
            $comment->setCreatedAt($date);
            $comment->setPost($post);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            $slug = $post->getSlug();
            return $this->redirectToRoute('post.show', ['slug' => $slug]);
        }

        return $this->render('user/comment/create.html.twig', [
            'formView' => $form->createView(),
            'post' => $post,
        ]);
    }

    /**
     * @Route("/admin/comment/{id}/remove", name="comment.remove", requirements={"id"="\d+"})
     */
    public function remove($id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $commentRepository = $entityManager->getRepository(Comment::class);

        $comment = $commentRepository->find($id);

        if(!$comment) {
            throw $this->createNotFoundException(
                "Pas de Commentaire trouvé avec l'id ".$id
            );
        }
        // On dit au manager que l'on veux supprimer cet objet en base de données
        $entityManager->remove($comment);
        // On met à jour en base de données en supprimant la ligne correspondante (i.e. la requête DELETE)
        $entityManager->flush();

        return $this->redirectToRoute('comment.list');
    }

    /**
     * @Route("/admin/comment/{id}/validate", name="comment.validate", requirements={"id"="\d+"})
     */
    public function validate(int $id, HttpFoundationRequest $request): Response
    {
        // On récupère le manager des entities
        $entityManager = $this->getDoctrine()->getManager();
        // On récupère le `repository` en rapport avec l'entity `Post`
        $commentRepository = $entityManager->getRepository(Comment::class);
        // On fait appel à la méthode générique `find` qui permet de SELECT en fonction d'un Id
        $comment = $commentRepository->find($id);

        //...
        $comment->setValid(true);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->redirectToRoute('comment.list');
    }
}
