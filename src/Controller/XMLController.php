<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\Section;
use App\Service\XMLUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class XMLController extends AbstractController
{
    /**
     * @Route("/x/m/l", name="x_m_l")
     */
    public function index(): Response
    {
        $folder = 'xml/';
        $file = $folder . 'catalog.xml';

        $fileSystem = new Filesystem();

        if (!$fileSystem->exists($folder)) {
            $fileSystem->mkdir($folder);
        }

        $fileSystem->touch($file);

        $fileSystem->dumpFile($file, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        $fileSystem->appendToFile($file, '<yml_catalog date="' . date('Y-m-d H:i:s') . '">' . "\n");

        $fileSystem->appendToFile($file, "\t" . '<shop>' . "\n");
        $fileSystem->appendToFile($file, "\t\t" . '<name>Интернет-магазин одежды</name>' . "\n");
        $fileSystem->appendToFile($file, "\t\t" . '<company>Интернет-магазин одежды</company>' . "\n");
        $fileSystem->appendToFile($file, "\t\t" . '<categories>' . "\n");

        $categoryRepository = $this->getDoctrine()->getRepository(Section::class);
        $categories = $categoryRepository->findBy(
            [],
            ['id' => 'ASC']
        );

        foreach ($categories as $category) {
            $fileSystem->appendToFile($file, "\t\t\t" . '<category id="' . $category->getId() . '"');
            if ($category->getParent()) {
                $fileSystem->appendToFile($file, ' parentId="' . $category->getParent()->getId() . '"');
            }
            $fileSystem->appendToFile($file, '>' . $category->getName() . '</category>' . "\n");
        }

        $fileSystem->appendToFile($file, "\t\t" . '</categories>' . "\n");

        $fileSystem->appendToFile($file, "\t\t" . '<offers>' . "\n");

        $offerRepository = $this->getDoctrine()->getRepository(Offer::class);
        $offers = $offerRepository->findAll();

        foreach ($offers as $offer) {
            $fileSystem->appendToFile($file, "\t\t\t" . '<offer id="' . $offer->getId() . '" productId="' . $offer->getProduct()->getId() . '" quantity="' . $offer->getQuantity() . '">' . "\n");
            $fileSystem->appendToFile($file, "\t\t\t\t" . '<url>' . '</url>' . "\n");
            $fileSystem->appendToFile($file, "\t\t\t\t" . '<price>' . $offer->getPrice() . '</price>' . "\n");
            $fileSystem->appendToFile($file, "\t\t\t\t" . '<categoryId>' . $offer->getProduct()->getSections()[0]->getId() . '</categoryId>' . "\n");
            $fileSystem->appendToFile($file, "\t\t\t\t" . '<picture>' . $offer->getPicture() . '</picture>' . "\n");
            $fileSystem->appendToFile($file, "\t\t\t\t" . '<name>' . $offer->getName() . '</name>' . "\n");
            $fileSystem->appendToFile($file, "\t\t\t\t" . '<xmlId>' . $offer->getXmlId() . '</xmlId>' . "\n");
            foreach ($offer->getPropertyValues() as $propertyValue) {
                $fileSystem->appendToFile($file, "\t\t\t\t" . '<param name="' . $propertyValue->getProperty()->getName() . '" code="' . $propertyValue->getProperty()->getCode() . '">' . $propertyValue->getValue() . '</param>' . "\n");
            }
            $fileSystem->appendToFile($file, "\t\t\t\t" . '<vendor>' . $offer->getProduct()->getVendor() . '</vendor>' . "\n");
            $fileSystem->appendToFile($file, "\t\t\t\t" . '<unit code="pcs" name="Штука" sym="Шт." />' . "\n");
            $fileSystem->appendToFile($file, "\t\t\t\t" . '<vatRate>' . $offer->getProduct()->getVatRate() . '</vatRate>' . "\n");
            $fileSystem->appendToFile($file, "\t\t\t\t" . '<dimensions></dimensions>' . "\n");
            $fileSystem->appendToFile($file, "\t\t\t\t" . '<weight></weight>' . "\n");
            $fileSystem->appendToFile($file, "\t\t\t\t" . '<barcode></barcode>' . "\n");
            $fileSystem->appendToFile($file, "\t\t\t\t" . '<markable>N</markable>' . "\n");
            $fileSystem->appendToFile($file, "\t\t\t" . '</offer>' . "\n");
        }

        $fileSystem->appendToFile($file, "\t\t" . '</offers>' . "\n");
        $fileSystem->appendToFile($file, "\t" . '</shop>' . "\n");
        $fileSystem->appendToFile($file, '</yml_catalog>');

        return $this->render('xml/index.html.twig', [
            'controller_name' => 'XMLController',
        ]);
    }

    /**
     * @Route("/xml_export", name="xml_export")
     */
    public function exportXml(XMLUploader $uploader): Response
    {
        $uploader->export();
        return $this->redirectToRoute('admin');
    }
}
