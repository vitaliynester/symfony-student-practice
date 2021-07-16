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
use Symfony\Component\Security\Core\Security;

class CatalogController extends AbstractController
{
    /**
     * @Route("/catalog/{parId}/", name="catalog", methods={"GET"})
     */
    public function index(SectionRepository $sectRep, PaginatorInterface $paginator, Request $request, $parId): Response
    {
        $items = [];
        $categories = $sectRep->findBy(['parent' => null]);
        foreach ($categories as $category) 
        {
            $subCategories = $sectRep->findBy(['parent' => $category->getId()]);
            $items[] = [$category, $subCategories];
        }
        $pageRequest = $request->query->getInt('page', 1);
        if ($pageRequest <= 0) 
        {
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
            'subSections' => $childSections, 'pagination' => $pagination, 'categories' => $items,
        ]);
    }

    /**
     * @Route("/offer/{offerId}", name="offer", methods={"GET","POST"})
     */
    public function offer(OfferRepository $offerRep,Security $security, SectionRepository $sectRep, $offerId, Request $request): Response
    {
        $items = [];
        $categories = $sectRep->findBy(['parent' => null]);
        foreach ($categories as $category) 
        {
            $subCategories = $sectRep->findBy(['parent' => $category->getId()]);
            $items[] = [$category, $subCategories];
        }
        $pageRequest = $request->query->getInt('page', 1);
        if ($pageRequest <= 0) 
        {
            $pageRequest = 1;
        }
        $offerData = $offerRep->findOneBy(['id' => $offerId]);
        $form = $this->createForm(CartItemType::class, null);
        $form->handleRequest($request);
        $product = $offerData->getProduct();
        $offers = $product->getOffers();
        if ($form->isSubmitted() && $form->isValid() ) 
        {
            if(($form->get('quantity')->getData()<=0) and ($security->getUser()!= NULL))
            {
                return $this->render('catalog/offer.html.twig', ['mainOffer' => $offerData, 'similarOffers' => $offers,
                    'categories' => $items, 'form' => $form->createView(),
                    ]);
            }
            $response = $this->forward('App\Controller\CartController::new',
                [
                    'quantity' => $form->get('quantity')->getData(),
                    'offer' => $offerId,
                ]);
            return $response;
        }
        return $this->render('catalog/offer.html.twig', ['mainOffer' => $offerData, 'similarOffers' => $offers,
        'categories' => $items, 'form' => $form->createView(),
        ]);
    }
}
