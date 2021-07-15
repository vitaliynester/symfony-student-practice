<?php

namespace App\Controller;

use App\Repository\SectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, SectionRepository $repository): Response
    {
        if (null !== $this->getUser()) {
            return new RedirectResponse($this->generateUrl('home'));
        }
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $items = [];
        $categories = $repository->findBy(['parent' => null]);
        foreach ($categories as $category) {
            $subCategories = $repository->findBy(['parent' => $category->getId()]);
            $items[] = [$category, $subCategories];
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'categories' => $items,
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
    }
}
