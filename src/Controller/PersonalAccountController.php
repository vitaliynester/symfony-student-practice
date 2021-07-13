<?php

namespace App\Controller;

use App\Form\PersonalAccountType;
use RetailCrm\Api\Enum\ByIdentifier;
use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Interfaces\ApiExceptionInterface;
use RetailCrm\Api\Interfaces\ClientExceptionInterface;
use RetailCrm\Api\Model\Entity\Customers\Customer;
use RetailCrm\Api\Model\Entity\Customers\CustomerAddress;
use RetailCrm\Api\Model\Entity\Customers\CustomerPhone;
use RetailCrm\Api\Model\Filter\Orders\OrderFilter;
use RetailCrm\Api\Model\Request\Customers\CustomersEditRequest;
use RetailCrm\Api\Model\Request\Orders\OrdersRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PersonalAccountController extends AbstractController
{
    /**
     * @Route("/personalAccount", name="personal_account")
     */
    public function index(): Response
    {
        $user_ = $this->getUser();
        $client = SimpleClientFactory::createClient('https://popova.retailcrm.ru', 'eVsrX4drzsw35chftqiSbTbGgbLtaPbN');
        $user = $client->customers->get($user_->getId())->customer;
        return $this->render('personal_account/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/personalAccount/edit", name="personal_account_edit")
     */
    public function edit(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $this->getUser();
        $client = SimpleClientFactory::createClient('https://popova.retailcrm.ru', 'eVsrX4drzsw35chftqiSbTbGgbLtaPbN');
        $form = $this->createForm(PersonalAccountType::class,null);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $requestUser = new CustomersEditRequest();
            $requestUser->customer = new Customer();
            $requestUser->site = 'b12-skillum-ru';
            if (!is_null($form->get('phoneNumber')->getData())){
                $requestUser->customer->phones= [new CustomerPhone($form->get('phoneNumber')->getData())];
            }
            if (!is_null($form->get('gender')->getData())){
                $requestUser->customer->sex=$form->get('gender')->getData();
            }
            if (!is_null($form->get('address')->getData())){
                $requestUser->customer->address= new CustomerAddress();
                $requestUser->customer->address->text=$form->get('address')->getData();
            }
            if ($form->get('plainPassword')->getData()){
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
            }

            $response_ = $client->customers->edit($user->getId(), $requestUser);
        }
        return $this->render('personal_account/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/personalAccount/ordersHystory", name="personal_account_orders_history")
     */
    public function ordersHistory(): Response
    {
        $user_ = $this->getUser();
        $client = SimpleClientFactory::createClient('https://popova.retailcrm.ru', 'eVsrX4drzsw35chftqiSbTbGgbLtaPbN');
        $requestOrders = new OrdersRequest();
        $requestOrders->filter = new OrderFilter();
        $requestOrders->filter->customerExternalId = $user_->getId();
        $orders = $client->orders->list($requestOrders)->orders;
        return $this->render('personal_account/historyOrders.html.twig', [
            'orders' => $orders,
        ]);
    }
}
