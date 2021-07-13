<?php

namespace App\Controller;

use App\Form\PersonalAccountType;
use App\Service\CustomerApi;
use Knp\Component\Pager\PaginatorInterface;
use RetailCrm\Api\Model\Entity\Customers\Customer;
use RetailCrm\Api\Model\Entity\Customers\CustomerAddress;
use RetailCrm\Api\Model\Entity\Customers\CustomerPhone;
use RetailCrm\Api\Model\Request\Customers\CustomersEditRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PersonalAccountController extends AbstractController
{
    /**
     * @Route("/personal_account", name="personal_account")
     */
    public function index(): Response
    {
        $user = $this->getUser();
        $Api = new CustomerApi();
        $customer = $Api->checkCustomer($user, $this->getParameter('url'), $this->getParameter('apiKey'));

        return $this->render('personal_account/index.html.twig', [
            'user' => $customer,
        ]);
    }

    /**
     * @Route("/personal_account/edit", name="personal_account_edit")
     */
    public function edit(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(PersonalAccountType::class, null);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $requestUser = new CustomersEditRequest();
            $requestUser->customer = new Customer();
            $requestUser->site = 'b12-skillum-ru';
            if (null !== $form->get('phoneNumber')->getData()) {
                $requestUser->customer->phones = [new CustomerPhone($form->get('phoneNumber')->getData())];
            }
            if (null !== $form->get('gender')->getData()) {
                $requestUser->customer->sex = $form->get('gender')->getData();
            }
            if (null !== $form->get('address')->getData()) {
                $requestUser->customer->address = new CustomerAddress();
                $requestUser->customer->address->text = $form->get('address')->getData();
            }
            if (null !== $form->get('plainPassword')->getData()) {
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
            $user = $this->getUser();
            $Api = new CustomerApi();
            $Api->changeCustomer($user, $this->getParameter('url'), $this->getParameter('apiKey'), $requestUser);

            return $this->redirectToRoute('personal_account');
        }

        return $this->render('personal_account/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/personal_account/ordersHystory/{page}", name="personal_account_orders_history", methods={"GET","POST"})
     */
    public function ordersHistory(PaginatorInterface $paginator, $page): Response
    {
        $user = $this->getUser();
        $Api = new CustomerApi();
        $order = $Api->getHistoryOrders($user, $this->getParameter('url'), $this->getParameter('apiKey'));
        $pagination = $paginator->paginate($order, $page, 1);

        return $this->render('personal_account/historyOrders.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}
