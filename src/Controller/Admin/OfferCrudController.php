<?php

namespace App\Controller\Admin;

use App\Entity\Offer;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FileUploadType;

class OfferCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Offer::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Предложения')
            ->setEntityLabelInSingular('Предложение');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm();
        yield AssociationField::new('product')
            ->setLabel('Товар')
            ->setRequired(true)
            ->hideOnIndex();
        yield TextField::new('name')
            ->setLabel('Название')
            ->setRequired(true);
        yield IdField::new('xmlId')
            ->setLabel('Идентификатор XML')
            ->hideOnIndex();
        yield NumberField::new('price')
            ->setLabel('Стоимость')
            ->setRequired(true);
        yield IntegerField::new('quantity')
            ->setLabel('Количество')
            ->setRequired(true);
        yield TextField::new('unit')
            ->setLabel('Единица измерения')
            ->setRequired(true)
            ->hideOnIndex();
        yield BooleanField::new('active')
            ->setLabel('Доступность')
            ->setRequired(true);
        yield ImageField::new('picture')
            ->setLabel('Изображение товара')
            ->setBasePath('uploads/')
            ->setUploadDir('uploads/')
            ->setFormType(FileUploadType::class)
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setRequired(false)
            ->hideOnIndex();
    }
}
