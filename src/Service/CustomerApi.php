<?php

namespace App\Service;

use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Interfaces\ApiExceptionInterface;
use RetailCrm\Api\Interfaces\ClientExceptionInterface;
use RetailCrm\Api\Model\Entity\Customers\Customer;
use RetailCrm\Api\Model\Entity\Customers\CustomerAddress;
use RetailCrm\Api\Model\Entity\Customers\CustomerPhone;
use RetailCrm\Api\Model\Request\Customers\CustomersCreateRequest;

class CustomerApi
{
    public function createCustomer($id, $form, $url, $api)
    {
        $client = SimpleClientFactory::createClient($url, $api);
        $requestUser = new CustomersCreateRequest();
        $requestUser->customer = new Customer();

        $requestUser->customer->externalId = $id;
        $requestUser->customer->sex = $form->get('gender')->getData();
        $requestUser->customer->address = new CustomerAddress();
        $requestUser->customer->address->text = $form->get('address')->getData();
        $requestUser->customer->phones = [new CustomerPhone($form->get('phoneNumber')->getData())];
        $requestUser->customer->email = $form->get('email')->getData();
        $requestUser->customer->firstName = $form->get('name')->getData();
        $requestUser->customer->lastName = $form->get('surname')->getData();
        $requestUser->customer->patronymic = $form->get('patronymic')->getData();

        try {
            $client->customers->create($requestUser);

            return true;
        } catch (ApiExceptionInterface | ClientExceptionInterface $exception) {
            return $exception;
        }
    }
}