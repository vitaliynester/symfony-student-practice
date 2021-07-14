<?php

namespace App\Controller;

use App\Repository\SectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(SectionRepository $repository): Response
    {
        return $this->render('home/index.html.twig', [
            'categories' => $repository->findBy(['parent' => null]),
        ]);
    }
}
