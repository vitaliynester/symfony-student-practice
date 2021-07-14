<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'text_info' => "About",
            'text_order' => "Shipping",
            'text_basket' => "Contact Us",
            'store_title' => "Store Name",
            'text_search' => "Search",
            'store_name' => "© 2021, storename.com", 
            'category' => "",

        ]);
    }
}
