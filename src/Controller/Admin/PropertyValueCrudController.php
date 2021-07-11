<?php

namespace App\Controller\Admin;

use App\Entity\PropertyValue;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PropertyValueCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PropertyValue::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Свойства')
            ->setEntityLabelInSingular('Свойство');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('offer')->setLabel('Предложение')->setRequired(true);
        yield AssociationField::new('property')->setLabel('Свойство')->setRequired(true);
        yield TextField::new('value')->setLabel('Описание');
    }
}
