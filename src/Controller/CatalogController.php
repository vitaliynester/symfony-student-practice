<?php

namespace App\Controller;

use App\Repository\OfferRepository;
use App\Repository\ProductRepository;
use App\Repository\SectionRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CatalogController extends AbstractController
{
    /**
     * @Route("/catalog/{parId}/{page}", name="catalog")
     */
    public function index(SectionRepository $sectRep, PaginatorInterface $paginator, $parId, $page): Response
    {
        $sectData = $sectRep->findBy(['parent' => $parId]);
        $tmp = $sectRep->findBy(['id' => $parId]);
        array_unshift($sectData, $tmp[0]);
        $offerData = [];
        foreach ($sectData as $section) {
            $products = $section->getProducts();
            foreach ($products as $product) {
                $offers = $product->getOffers();
                foreach ($offers as $offer) {
                    $offerData[] = $offer;
                }
            }
        }
        $pagination = $paginator->paginate($offerData, $page, 9);

        return $this->render('catalog/index.html.twig', [
            'sections' => $sectData,
            'pagination' => $pagination,
            'categories' => $sectRep->findBy(['parent' => null]),
        ]);
    }

    /**
     * @Route("/offer/{offerId}", name="offer")
     */
    public function offer(OfferRepository $offerRep, SectionRepository $sectionRepository, ProductRepository $prodRep, $offerId): Response
    {
        $offerData = $offerRep->findBy(['id' => $offerId]);

        return $this->render('catalog/offer.html.twig', [
            'offer' => $offerData,
            'categories' => $sectionRepository->findBy(['parent' => null]),
        ]);
    }
}
