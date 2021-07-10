<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class XMLController extends AbstractController
{
    /**
     * @Route("/x/m/l", name="x_m_l")
     */
    public function index(): Response
    {
        $filesystem = new Filesystem();
        $filesystem->mkdir(sys_get_temp_dir().'/'.'new');

        return $this->render('xml/index.html.twig', [
            'controller_name' => 'XMLController',
        ]);
    }
}
