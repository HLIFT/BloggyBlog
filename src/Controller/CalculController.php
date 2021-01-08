<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalculController extends AbstractController
{
    /**
     * @Route("/calcul/addition/{val1}/{val2}")
     */
    public function addition(int $val1, int $val2)
    {
        $result = $val1 + $val2;

        //return new Response('Le résultat de '.$val1.' + '.$val2.' est '.$result);
        return $this->render('calcul/addition.html.twig', ['val1' => $val1, 'val2' => $val2, 'resultat' => $result]);
    }

    /**
     * @Route("/calcul/carre/{val}")
     */
    public function carre(int $val)
    {
        $result = $val * $val;

        //return new Response('Le carré de '.$val.' est '.$result);
        return $this->render('calcul/carre.html.twig', ['val' => $val, 'resultat' => $result]);
    }
}