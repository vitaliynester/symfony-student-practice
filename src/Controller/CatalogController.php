<?php

namespace App\Controller;

use App\Entity\Section;
use App\Entity\Product;
use App\Entity\Offer;
use App\Repository\OfferRepository;
use App\Repository\SectionRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use Knp\Component\Pager\PaginatorInterface;
use App\Form\CartItemType;

class CatalogController extends AbstractController
{
    /**
     * @Route("/catalog/{parId}/", name="catalog", methods={"GET"})
     */
    public function index(SectionRepository $sectRep, PaginatorInterface $paginator, Request $request, $parId): Response
    {
        $pageRequest = $request->query->getInt('page', 1);
        if ($pageRequest <= 0) {
            $pageRequest = 1;
        }
        $qb = $sectRep->createQueryBuilder('s');
        $sectData = $qb->where('s.id = :parId')
            ->setParameter('parId', $parId)
            ->orWhere('s.parent = :parId')
            ->setParameter('parId', $parId)
            ->orderBy('s.id')
            ->getQuery()
            ->getResult();
        $offerData = [];
        foreach ($sectData as $section) {
            $products = $section->getProducts();
            foreach ($products as $product) {
                $offers = $product->getOffers();
                foreach ($offers as $offer) {
                    array_push($offerData, $offer);
                }
            }
        }
        $mainSecion = $sectData[0];
        $childSections = [];
        foreach ($sectData as $section) {
            if ($section->getId() != $parId) {
                array_push($childSections, $section);
            }
        }
        $pagination = $paginator->paginate($offerData, $pageRequest, 9);
        return $this->render('catalog/index.html.twig', ['parentSection' => $mainSecion,
            'subSections' => $childSections, 'pagination' => $pagination, 'categories' => $sectData
        ]);
    }

    /**
     * @Route("/offer/{offerId}", name="offer", methods={"GET","POST"})
     */
    public function offer(OfferRepository $offerRep, ProductRepository $prodRep, $offerId, Request $request): Response
    {
        $offerData = $offerRep->findOneBy(['id' => $offerId]);
        $form = $this->createForm(CartItemType::class, null);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $response = $this->forward('App\Controller\CartController::new',
                [
                    'quantity' => $form->get('quantity')->getData(),
                    'offer' => $offerId,
                ]);
            return $response;
        }
        $product = $offerData->getProduct();
        $offers = $product->getOffers();
        return $this->render('catalog/offer.html.twig', ['mainOffer' => $offerData, 'similarOffers' => $offers, 'form' => $form->createView(),
        ]);
    }
}
