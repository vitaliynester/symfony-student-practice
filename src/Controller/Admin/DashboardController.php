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
        yield MenuItem::linkToRoute('Вернуться на сайт', 'fas fa-angle-double-left', 'admin');
        yield MenuItem::section('Объекты');
        yield MenuItem::linkToCrud('Предложения', 'fas fa-tags', Offer::class);
        yield MenuItem::linkToCrud('Товары', 'fas fa-shopping-bag', Product::class);
        yield MenuItem::linkToCrud('Список доступных свойств', 'fas fa-chart-bar', Property::class);
        yield MenuItem::linkToCrud('Значение свойств', 'far fa-clipboard', PropertyValue::class);
        yield MenuItem::linkToCrud('Разделы', 'far fa-folder-open', Section::class);
        yield MenuItem::section('Каталог');
        yield MenuItem::linkToRoute('Экспортировать в XML', 'fas fa-file-export', 'xml_export');
        yield MenuItem::linkToRoute('Импортировать из XML', 'far fa-file-code', 'admin');
    }
}
