<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
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
        $categories = $categoryRepository->findAll();
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
            //...
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
     * @Route("/admin/category/{id}/edit", name="category.edit", requirements={"id"="\d+"})
     */
    public function update(int $id, HttpFoundationRequest $request): Response
    {
        // On récupère le manager des entities
        $entityManager = $this->getDoctrine()->getManager();
        // On récupère le `repository` en rapport avec l'entity `Post`
        $categoryRepository = $entityManager->getRepository(Category::class);
        // On fait appel à la méthode générique `find` qui permet de SELECT en fonction d'un Id
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
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();
            
            return $this->redirectToRoute('category.list', ['id' => $id]);
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
        // On dit au manager que l'on veux supprimer cet objet en base de données
        $entityManager->remove($category);
        // On met à jour en base de données en supprimant la ligne correspondante (i.e. la requête DELETE)
        $entityManager->flush();

        return $this->redirectToRoute('category.list');
    }
}
