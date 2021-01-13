<?php

namespace App\Twig;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use DateTime;
use phpDocumentor\Reflection\Types\Integer;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class SidebarExtension extends AbstractExtension
{
    /**
     * @var CommentRepository
     */
    private $commentRepository;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    public function __construct(CommentRepository $commentRepository, CategoryRepository $categoryRepository, Environment $twig)
    {
        $this->commentRepository = $commentRepository;
        $this->categoryRepository = $categoryRepository;
        $this->twig = $twig;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sidebar', [$this, 'getSidebar'], ['is_safe' => ['html']]),
            new TwigFunction('getNbPosts', [$this, 'getNbPosts'])
        ];
    }

    public function getSidebar(): string
    {
        $comments = $this->commentRepository->findAllRecentValid(5);

        $categories = $this->categoryRepository->findAllWithPost();

        return $this->twig->render('user/post/sidebar.html.twig', [
            'comments' => $comments,
            'categories' => $categories
        ]);
    }

    public function getNbPosts($id)
    {
        $category = $this->categoryRepository->find($id);
        $nbPosts = 0;
        $date = new DateTime();

        $posts = $category->getPosts();

        foreach($posts as $post)
        {
            if($post->getPublishedAt() < $date)
            {
                $nbPosts += 1;
            }
        }
        
        return $nbPosts;
    }

    
}