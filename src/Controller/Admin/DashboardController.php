<?php

namespace App\Controller\Admin;

use App\Entity\Offer;
use App\Entity\Product;
use App\Entity\Property;
use App\Entity\PropertyValue;
use App\Entity\Section;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        $routeBuilder = $this->get(CrudUrlGenerator::class)->build();
        return $this->redirect($routeBuilder->setController(OfferCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('AdminPanel магазина');
    }

    public function configureMenuItems(): iterable
    {
//         TODO: update when completed home controller
//        yield MenuItem::linkToUrl(
//            'Вернуться на сайт',
//            'fas fa-angle-double-left',
//            $this->generateUrl('app', [], true)
//        );
        yield MenuItem::linkToCrud('Предложения', 'fas fa-user-circle', Offer::class);
        yield MenuItem::linkToCrud('Товары', 'fas fa-chart-bar', Product::class);
        yield MenuItem::linkToCrud('Список доступных свойств', 'fas fa-chart-bar', Property::class);
        yield MenuItem::linkToCrud('Значение свойств', 'fas fa-chart-bar', PropertyValue::class);
        yield MenuItem::linkToCrud('Разделы', 'fas fa-chart-bar', Section::class);
    }
}
