<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthentificatorAuthenticator;
use phpDocumentor\Reflection\Types\This;
use RetailCrm\Api\Client;
use RetailCrm\Api\Model\Entity\Customers\CustomerAddress;
use RetailCrm\Api\Model\Entity\Customers\CustomerPhone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use RetailCrm\Api\Interfaces\ClientExceptionInterface;
use RetailCrm\Api\Interfaces\ApiExceptionInterface;
use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Model\Entity\Customers\Customer;
use RetailCrm\Api\Model\Request\Customers\CustomersCreateRequest;



class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthentificatorAuthenticator $authenticator): Response
    {
        if($this->getUser() !== null)
        {
            return new RedirectResponse($this->generateUrl('about'));
        }
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class,null);//$user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setEmail($form->get('email')->getData());
            if($this->getDoctrine()->getRepository(User::class)->findOneBy(array('email'=>$user->getEmail()))!=null)
            {
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                    'error'=>'Пользователь с введенным email уже существует',
                ]);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();


            $client = SimpleClientFactory::createClient('https://popova.retailcrm.ru', 'eVsrX4drzsw35chftqiSbTbGgbLtaPbN');
            $requestUser = new CustomersCreateRequest();
            $requestUser->customer = new Customer();

            $requestUser->customer->externalId=$user->getId();
            $requestUser->customer->sex=$form->get('gender')->getData();
            $requestUser->customer->address= new CustomerAddress();
            $requestUser->customer->address->text=$form->get('address')->getData();
            $requestUser->customer->phones= [new CustomerPhone($form->get('phoneNumber')->getData())];
            $requestUser->customer->email = $form->get('email')->getData();
            $requestUser->customer->firstName = $form->get('name')->getData();
            $requestUser->customer->lastName = $form->get('surname')->getData();
            $requestUser->customer->patronymic=$form->get('patronymic')->getData();

            try {
                $response = $client->customers->create($requestUser);
            } catch (ApiExceptionInterface | ClientExceptionInterface $exception) {
                $entityManager=$this->getDoctrine()->getManager();
                $entityManager->remove($user);
                $entityManager->flush();
                echo $exception; // Every ApiExceptionInterface instance should implement __toString() method.
                exit(-1);
            }
            echo 'Customer ID: ' . $response->id;


            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'error'=>'',
        ]);
    }
}
