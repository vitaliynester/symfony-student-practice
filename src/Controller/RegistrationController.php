<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\SectionRepository;
use App\Security\LoginFormAuthentificatorAuthenticator;
use App\Service\CustomerApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request,
                             UserPasswordEncoderInterface $passwordEncoder,
                             GuardAuthenticatorHandler $guardHandler,
                             LoginFormAuthentificatorAuthenticator $authenticator,
                             SectionRepository $repository
    ): Response {
        if (null !== $this->getUser()) {
            return new RedirectResponse($this->generateUrl('home'));
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setEmail($form->get('email')->getData());
            if (null != $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $user->getEmail()])) {
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                    'error' => 'Пользователь с введенным email уже существует',
                    'categories' => $repository->findBy(['parent' => null]),
                ]);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $Customer = new CustomerApi();
            $resultApi = $Customer->createCustomer(
                $user->getId(),
                $form,
                $this->getParameter('url'),
                $this->getParameter('apiKey')
            );

            if (true !== $resultApi) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($user);
                $entityManager->flush();

                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                    'error' => $resultApi,
                    'categories' => $repository->findBy(['parent' => null]),
                ]);
            }

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'categories' => $repository->findBy(['parent' => null]),
        ]);
    }
}
