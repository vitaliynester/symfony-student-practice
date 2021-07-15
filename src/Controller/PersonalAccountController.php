<?php

namespace App\Controller;

use App\Form\PersonalAccountType;
use App\Repository\SectionRepository;
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

class PersonalAccountController extends AbstractController
{
    /**
     * @Route("/personal_account", name="personal_account")
     */
    public function index(SectionRepository $sectionRepository): Response
    {
        $user = $this->getUser();
        $Api = new CustomerApi();
        $customer = $Api->checkCustomer($user, $this->getParameter('url'), $this->getParameter('apiKey'));

        $items = [];
        $categories = $sectionRepository->findBy(['parent' => null]);
        foreach ($categories as $category) {
            $subCategories = $sectionRepository->findBy(['parent' => $category->getId()]);
            $items[] = [$category, $subCategories];
        }

        return $this->render('personal_account/index.html.twig', [
            'user' => $customer,
            'categories' => $items,
        ]);
    }

    /**
     * @Route("/personal_account/edit", name="personal_account_edit")
     */
    public function edit(Request $request, SectionRepository $sectionRepository): Response
    {
        $form = $this->createForm(PersonalAccountType::class, null);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $requestUser = new CustomersEditRequest();
            $requestUser->customer = new Customer();
            $requestUser->site = 'b12-skillum-ru';
            if (null !== $form->get('phoneNumber')->getData()) {
                $requestUser->customer->phones = [new CustomerPhone($form->get('phoneNumber')->getData())];
            }
            if (null !== $form->get('address')->getData()) {
                $requestUser->customer->address = new CustomerAddress();
                $requestUser->customer->address->text = $form->get('address')->getData();
            }
            $user = $this->getUser();
            $Api = new CustomerApi();
            $Api->changeCustomer($user, $this->getParameter('url'), $this->getParameter('apiKey'), $requestUser);
            return $this->redirectToRoute('personal_account');
        }

        $items = [];
        $categories = $sectionRepository->findBy(['parent' => null]);
        foreach ($categories as $category) {
            $subCategories = $sectionRepository->findBy(['parent' => $category->getId()]);
            $items[] = [$category, $subCategories];
        }

        return $this->render('personal_account/edit.html.twig', [
            'form' => $form->createView(),
            'categories' => $items,
        ]);
    }

    /**
     * @Route("/personal_account/ordersHystory/{page}", name="personal_account_orders_history", methods={"GET","POST"})
     */
    public function ordersHistory(PaginatorInterface $paginator, SectionRepository $sectionRepository, $page): Response
    {
        $user = $this->getUser();
        $Api = new CustomerApi();
        $order = $Api->getHistoryOrders($user, $this->getParameter('url'), $this->getParameter('apiKey'));
        $pagination = $paginator->paginate($order, $page, 1);

        $items = [];
        $categories = $sectionRepository->findBy(['parent' => null]);
        foreach ($categories as $category) {
            $subCategories = $sectionRepository->findBy(['parent' => $category->getId()]);
            $items[] = [$category, $subCategories];
        }

        return $this->render('personal_account/historyOrders.html.twig', [
            'pagination' => $pagination,
            'categories' => $items,
        ]);
    }
}
