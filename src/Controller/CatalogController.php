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
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use Knp\Component\Pager\PaginatorInterface;

class CatalogController extends AbstractController
{
    /**
     * @Route("/catalog/{parId}/{page}", name="catalog")
     */
    public function index(SectionRepository $sectRep,PaginatorInterface $paginator, $parId,$page): Response 
    {
        $sectData = $sectRep->findBy(['parent' => $parId]);
        $tmp = $sectRep->findBy(['id' => $parId]);
        array_unshift($sectData, $tmp[0]);
        $offerData = [];
        foreach($sectData as $section)
        {
            $products = $section->getProducts();
            foreach($products as $product)
            {
                $offers = $product->getOffers();
                foreach($offers as $offer)
                {
                    array_push($offerData, $offer);
                }
            }
        }
        $pagination = $paginator->paginate($offerData,$page,9);
        return $this->render('catalog/index.html.twig', [
            'sections' => $sectData, 'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/offer/{offerId}", name="offer")
     */
    public function offer( OfferRepository $offerRep, ProductRepository $prodRep, $offerId): Response
    {
        $offerData = $offerRep->findBy(['id' => $offerId]);
        return $this->render('catalog/offer.html.twig', [ 'offer' => $offerData,
        ]);
    }
}
