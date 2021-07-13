<?php

namespace App\Controller;

use App\Entity\CartItem;
use App\Entity\Offer;
use App\Form\CartCheckoutFormType;
use App\Form\CartItemType;
use App\Repository\CartItemRepository;
use App\Repository\OfferRepository;
use App\Service\OrderApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/cart")
 */
class CartController extends AbstractController
{
    /**
     * @Route("/", name="cart_index", methods={"GET"})
     */
    public function index(CartItemRepository $cartItemRepository): Response
    {
        return $this->render('cart/index.html.twig', [
            'cart_items' => $cartItemRepository->findBy(['customer' => $this->getUser()]),
            'payment_amount' => $this->getPaymentAmount($this->getUser()),
        ]);
    }

    /**
     * @Route("/new/", name="cart_new", methods={"POST"})
     */
    public function new(Request $request, CartItemRepository $cartItemRepository): Response
    {
        $offer = $this->getDoctrine()->getRepository(Offer::class)->findOneBy(['id' => $request->request->get('offer')]);
        $cartItem = $cartItemRepository->findOneBy(['customer' => $this->getUser(), 'offer' => $offer]);
        if ($cartItem == null) {
            $cartItem = new CartItem();
            $cartItem->setCustomer($this->getUser());
            $cartItem->setOffer($offer);
            $cartItem->setQuantity($request->request->get('quantity'));

        } else {
            $cartItem->setQuantity($cartItem->getQuantity()+$request->request->get('quantity'));
        }


        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($cartItem);
        $entityManager->flush();

        return $this->redirectToRoute('cart_index', [], Response::HTTP_SEE_OTHER);
    }


    /**
     * @Route("/{id}/edit", name="cart_edit", methods={"POST"})
     */
    public function edit(Request $request, CartItem $cartItem): Response
    {
        $cartItem->setQuantity($request->get('quantity'));

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('cart_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{id}/delete", name="cart_delete", methods={"GET","POST"})
     */
    public function delete(Request $request, CartItem $cartItem): Response
    {
        if ($this->isCsrfTokenValid('delete' . $cartItem->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($cartItem);
            $entityManager->flush();
        }

        return $this->redirectToRoute('cart_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/checkout", name="cart_checkout", methods={"GET","POST"})
     */
    public function checkout(Request $request): Response
    {
        $form = $this->createForm(CartCheckoutFormType::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $orderApi = new OrderApi($this->getParameter('url'), $this->getParameter('apiKey'));

            $apiResponse = $orderApi->createOrder($this->getUser(), $form);
            //$this->getDoctrine()->getRepository(CartItem::class)->deleteCustomerCart($this->getUser());

            return $this->redirectToRoute('cart_thanks', ['id' => $apiResponse->order->id]);
        }

        return $this->render('cart/checkout.html.twig', [
            'form' => $form->createView(),
            'payment_amount' => $this->getPaymentAmount($this->getUser()),
        ]);
    }

    /**
     * @Route("/thanks", name="cart_thanks", methods={"GET"})
     */
    public function thanks(Request $request)
    {
        $orderApi = new OrderApi($this->getParameter('url'), $this->getParameter('apiKey'));
        $order = $orderApi->getOrderById($request->get('id'))->order;
        return $this->render('cart/thanks.html.twig', ['order' => $order]);
    }

    private function getPaymentAmount($customer)
    {
        $cartItems = $customer->getCartItems();

        $amount = 0;
        foreach ($cartItems as $item) {
            $amount += $item->getOffer()->getPrice() * $item->getQuantity();
        }

        return $amount;
    }

    /**
     * @Route("/quantityChange", name="quantityChange", methods={"POST"})
     */
    public function quantityChange(Request $request, CartItemRepository $cartItemRepository)
    {
        if ($request->request->get('operation')) {
            $cartItem = $cartItemRepository->findOneBy(['id' => $request->request->get('cartItemId')]);
            if ($request->request->get('operation') == 'reduce')
                $cartItem->setQuantity($cartItem->getQuantity() - 1);
            elseif ($request->request->get('operation') == 'increase')
                $cartItem->setQuantity($cartItem->getQuantity() + 1);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($cartItem);
            $entityManager->flush();
            $data = ['output' => $cartItem->getQuantity(), 'payment_amount' => $this->getPaymentAmount($this->getUser()).' руб.'];
            return new JsonResponse($data);
        }
        return $this->redirectToRoute('cart_index', [], Response::HTTP_SEE_OTHER);
    }
}
