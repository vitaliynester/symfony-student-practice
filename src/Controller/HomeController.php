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
        $items = [];
        $categories = $repository->findBy(['parent' => null]);
        foreach ($categories as $category) {
            $subCategories = $repository->findBy(['parent' => $category->getId()]);
            $items[] = [$category, $subCategories];
        }
        return $this->render('home/index.html.twig', [
            'categories' => $items,
        ]);
    }
}
