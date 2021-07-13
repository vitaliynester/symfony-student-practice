<?php

namespace App\Service;

use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Interfaces\ApiExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ReferenceApi
{

    public static function getDeliveryTypes($url, $apiKey)
    {
        $client = SimpleClientFactory::createClient($url, $apiKey);

        try {
            $response = $client->references->deliveryTypes();
            $deliveryTypes = $response->deliveryTypes;
        } catch (ApiExceptionInterface $exception) {
            return [];
        }

        $deliveryTypesList = [];
        foreach ($deliveryTypes as $type) {
            $deliveryTypesList[] = [$type->name => $type->code];
        }

        return $deliveryTypesList;
    }

    public static function getPaymentsTypes($url, $apiKey)
    {
        $client = SimpleClientFactory::createClient($url, $apiKey);

        try {
            $response = $client->references->paymentTypes();
            $paymentTypes = $response->paymentTypes;
        } catch (ApiExceptionInterface $exception) {
            return [];
        }

        $paymentTypesList = [];
        foreach ($paymentTypes as $type) {
            $paymentTypesList[] = [$type->name => $type->code];
        }

        return $paymentTypesList;
    }
}