<?php

namespace App\Service;

use App\Entity\Offer;
use App\Entity\Section;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use Symfony\Component\HttpFoundation\RequestStack;

class XMLUploader
{
    private string $shopName = 'Интернет-магазин одежды';
    private string $fileNameToSave = 'catalog.xml';
    private string $pathPictures = 'upload/pictures/';

    private EntityManagerInterface $entityManager;
    private RequestStack $requestStack;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    /**
     * Экспорт данных из БД в XML файл
     */
    public function export(): void
    {
        // Получаем все категории и предложения
        $allCategories = $this->entityManager->getRepository(Section::class)->findAll();
        $allOffers = $this->entityManager->getRepository(Offer::class)->findAll();

        // Получаем данные для формировании ссылки доступа
        $request = $this->requestStack->getCurrentRequest();
        $domainSite = $request->server->get('HTTP_HOST');
        $protocolSite = $request->server->get('REQUEST_SCHEME');

        // Создаем новый XML документ
        $xml = new domDocument('1.0', 'utf-8');

        // Создаем корневой элемент
        $root = $xml->createElement('yml_catalog');
        $root->setAttribute('date', date('Y-m-d H:i:s'));
        $xml->appendChild($root);

        // Создаем элемент магазина
        $shop = $xml->createElement('shop');
        $root->appendChild($shop);

        // Добавляем название магазина
        $name = $xml->createElement('name', $this->shopName);
        $shop->appendChild($name);

        // Добавим компанию в магазин
        $company = $xml->createElement('company', $this->shopName);
        $shop->appendChild($company);

        // Добавляем элемент с категориями
        $categories = $xml->createElement('categories');
        // Проходимся по всем категориям
        foreach ($allCategories as $category) {
            // Добавляем каждую категорию в XML документ
            /** @var Section $category */
            $categoryElement = $xml->createElement('category', $category->getName());
            $categoryElement->setAttribute('id', $category->getXmlId());
            if (null !== $category->getParent()) {
                $categoryElement->setAttribute('parentId', $category->getParent()->getXmlId());
            }
            $categories->appendChild($categoryElement);
        }
        // Добавляем в магазин все сформированные категории
        $shop->appendChild($categories);

        // Добавляем элемент с предложениями
        $offers = $xml->createElement('offers');
        foreach ($allOffers as $offer) {
            // Добавляем каждое предложение в XML документ
            /** @var Offer $offer */
            $offerElement = $xml->createElement('offer');
            $offerElement->setAttribute('id', $offer->getXmlId());
            $offerElement->setAttribute('productId', $offer->getProduct()->getXmlId());
            $offerElement->setAttribute('quantity', $offer->getQuantity());

            // Добавляем фотографию предложения
            if (null !== $offer->getPicture()) {
                $pictureUrl = "$protocolSite://$domainSite/" . $this->pathPictures . $offer->getPicture();
                $pictureElement = $xml->createElement('picture', $pictureUrl);
                $offerElement->appendChild($pictureElement);
            }
            // Добавляем ссылку на предоставляемое предложение
            $offerUrl = "$protocolSite://$domainSite/product/" . $offer->getProduct()->getId();
            $offerUrlElement = $xml->createElement('url', $offerUrl);
            $offerElement->appendChild($offerUrlElement);

            // Добавляем цену предложения
            $priceElement = $xml->createElement('price', $offer->getPrice());
            $offerElement->appendChild($priceElement);

            // Добавляем категорию предложения
            $categoryId = $offer->getProduct()->getSections()[0]->getXmlId();
            $categoryIdElement = $xml->createElement('categoryId', $categoryId);
            $offerElement->appendChild($categoryIdElement);

            // Добавляем название предложения
            $nameElement = $xml->createElement('name', $offer->getName());
            $offerElement->appendChild($nameElement);

            // Добавляем название продукта
            $productName = $offer->getProduct()->getName();
            $productNameElement = $xml->createElement('productName', $productName);
            $offerElement->appendChild($productNameElement);

            // Если у продукта есть производитель, то добавляем его
            if ($offer->getProduct()->getVendor()) {
                // Сохраняем HTML структуру
                $vendor = htmlspecialchars($offer->getProduct()->getVendor(), ENT_QUOTES);
                $vendorElement = $xml->createElement('vendor', $vendor);
                $offerElement->appendChild($vendorElement);
            }

            // Проходимся по каждой характеристике
            foreach ($offer->getPropertyValues() as $propertyValue) {
                $paramElement = $xml->createElement('param', $propertyValue->getValue());
                $paramElement->setAttribute('name', $propertyValue->getProperty()->getName());
                $paramElement->setAttribute('code', $propertyValue->getProperty()->getCode());
                $offerElement->appendChild($paramElement);
            }

            // Добавляем сформированную структуру предложения в XML элемент предложений
            $offers->appendChild($offerElement);
        }
        // Добавляем сформированный список предложений в XML документ
        $shop->appendChild($offers);

        $xml->save('xml/' . $this->fileNameToSave);
    }
}
