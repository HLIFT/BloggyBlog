<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelloController extends AbstractController
{
    /**
     * @Route("/hello")
     */
    public function hello()
    {
        return new Response('Hello !');
    }

    /**
     * @Route("/hello/{name}")
     */
    public function helloname($name)
    {
        //return new Response('Hello '.$name.' !');
        return $this->render('hello/hello.html.twig', ['name' => $name]);
    }
}