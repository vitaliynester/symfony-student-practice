<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthentificatorAuthenticator;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use App\Service\CustomerApi;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthentificatorAuthenticator $authenticator): Response
    {
        if($this->getUser() !== null) {
            return new RedirectResponse($this->generateUrl('home'));
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class,null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setEmail($form->get('email')->getData());
            if($this->getDoctrine()->getRepository(User::class)->findOneBy(array('email'=>$user->getEmail()))!=null) {
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                    'error'=>'Пользователь с введенным email уже существует',
                    'link_img_logo'=>'',
                    'alt_text_logo'=>'',
                    'store_title'=>'',
                    'link_log_in'=>'',
                    'link_sign_up'=>'',
                    'categories'=>[],
                    'store_name'=>''
                ]);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $Customer=new CustomerApi();
            $resultApi=$Customer->createCustomer(
                $user->getId(),
                $form,
                $this->getParameter('url'),
                $this->getParameter('apiKey')
            );

            if($resultApi!==true){
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($user);
                $entityManager->flush();
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                    'error'=>$resultApi,
                    'link_img_logo'=>'',
                    'alt_text_logo'=>'',
                    'store_title'=>'',
                    'link_log_in'=>'',
                    'link_sign_up'=>'',
                    'categories'=>[],
                    'store_name'=>''
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
            'link_img_logo'=>'',
            'alt_text_logo'=>'',
            'store_title'=>'',
            'link_log_in'=>'',
            'link_sign_up'=>'',
            'categories'=>[],
            'store_name'=>''
        ]);
    }
}
