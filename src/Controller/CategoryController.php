<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use PhpParser\Node\Stmt\Catch_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @Route("/admin/category/list", name="category.list")
     */
    public function list(): Response
    {
        $categoryRepository = $this->getDoctrine()->getRepository(Category::class);

        /** @var CategoryRepository $categoryRepository */

        $categories = $categoryRepository->findAllABC();

        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories,
            ]);
    }

    /**
     * @Route("/admin/category/create", name="category.create")
     */

    public function create(HttpFoundationRequest $request): Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $category = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();
            return $this->redirectToRoute('category.list');
        }

        return $this->render('admin/category/create.html.twig', [
            'formView' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/category/{id}", name="admin.category.show")
     * @Route("/category/{id}/show", name="category.show")
     */
    public function show(int $id, HttpFoundationRequest $request): Response
    {
        $categoryRepository = $this->getDoctrine()->getRepository(Category::class);

        $category = $categoryRepository->findOneBy(['id' => $id]);

        $postRepository = $this->getDoctrine()->getRepository(Post::class);

        /** @var PostRepository $postRepository */

        $posts = $postRepository->findAllRecentPublishedByCategory($category);

        if(!$category) {
            throw $this->createNotFoundException(
                "Pas de Category trouvé avec l'id ".$id
            );
        }

        $routeName = $request->attributes->get('_route');

        if($routeName == "admin.category.show")
        {
            return $this->render('admin/category/show.html.twig', [
                'category' => $category,
                'posts' => $posts,
            ]);
        }
        else
        {
            return $this->render('user/category/show.html.twig', [
                'category' => $category,
                'posts' => $posts,
            ]);
        }       
    }
    /**
     * @Route("/admin/category/{id}/edit", name="category.edit", requirements={"id"="\d+"})
     */
    public function update(int $id, HttpFoundationRequest $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $categoryRepository = $entityManager->getRepository(Category::class);

        $category = $categoryRepository->find($id);

        if(!$category) {
            throw $this->createNotFoundException(
                "Pas de Catégorie trouvée avec l'id ".$id
            );
        }

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $category = $form->getData();
            $entityManager->persist($category);
            $entityManager->flush();
            $id = $category->getId();
            return $this->redirectToRoute('admin.category.show', ['id' => $id]);
        }

        return $this->render('admin/category/edit.html.twig', [
            'formView' => $form->createView(),
        ]);
    }

     /**
     * @Route("/admin/category/{id}/remove", name="category.remove", requirements={"id"="\d+"})
     */
    public function remove($id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        
        $categoryRepository = $entityManager->getRepository(Category::class);

        $category = $categoryRepository->find($id);

        if(!$category) {
            throw $this->createNotFoundException(
                "Pas de Catégorie trouvée avec l'id ".$id
            );
        }

        $entityManager->remove($category);

        $entityManager->flush();

        return $this->redirectToRoute('category.list');
    }

    public function categories(): Response
    {
        $categoryRepository = $this->getDoctrine()->getRepository(Category::class);

        /** @var CategoryRepository $categoryRepository */

        $categories = $categoryRepository->findAllWithPost();

        return $this->render('user/comment/_recent_comments.html.twig', [
            'categories' => $categories
        ]);
    }
}
