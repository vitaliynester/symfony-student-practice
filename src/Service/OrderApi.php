<?php
namespace App\Service;

use App\Entity\User;
use DateInterval;
use DateTime;
use RetailCrm\Api\Enum\ByIdentifier;
use RetailCrm\Api\Enum\CountryCodeIso3166;
use RetailCrm\Api\Enum\Customers\CustomerType;
use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Interfaces\ApiExceptionInterface;
use RetailCrm\Api\Interfaces\ClientExceptionInterface;
use RetailCrm\Api\Model\Entity\Orders\Delivery\OrderDeliveryAddress;
use RetailCrm\Api\Model\Entity\Orders\Delivery\SerializedOrderDelivery;
use RetailCrm\Api\Model\Entity\Orders\Items\Offer;
use RetailCrm\Api\Model\Entity\Orders\Items\OrderProduct;
use RetailCrm\Api\Model\Entity\Orders\Items\PriceType;
use RetailCrm\Api\Model\Entity\Orders\Items\Unit;
use RetailCrm\Api\Model\Entity\Orders\Order;
use RetailCrm\Api\Model\Entity\Orders\Payment;
use RetailCrm\Api\Model\Entity\Orders\SerializedRelationCustomer;
use RetailCrm\Api\Model\Entity\References\DeliveryService;
use RetailCrm\Api\Model\Request\BySiteRequest;
use RetailCrm\Api\Model\Request\Orders\OrdersCreateRequest;
use RetailCrm\Api\Model\Response\Orders\OrdersGetResponse;
use RetailCrm\Api\ResourceGroup\References;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class OrderApi
{
    private $client;

    public function __construct($url, $apiKey)
    {
        $this->client = SimpleClientFactory::createClient($url, $apiKey);
    }

    public function createOrder(User $customer, $form)
    {
        $request         = new OrdersCreateRequest();
        $order           = new Order();
        $payment         = new Payment();
        $delivery        = new SerializedOrderDelivery();
        $deliveryAddress = new OrderDeliveryAddress();
        $items            = [];

        $payment->type   = $form->get('payment_type')->getData();
        $payment->status = 'paid';
        $payment->amount = $this->getPaymentAmount($customer);
        $payment->paidAt = new DateTime();

        $deliveryAddress->text = $form->get('address')->getData();

        $delivery->code = $form->get('delivery_type')->getData();
        $delivery->address = $deliveryAddress;
        $delivery->cost    = 0;
        $delivery->netCost = 0;

        foreach ($customer->getCartItems() as $cartItem) {
            $offer = $cartItem->getOffer();
            $crmOffer = new Offer();

            $crmOffer->name = $offer->getName();
            $crmOffer->article =  $offer->getName();
            $crmOffer->xmlId =  $offer->getXmlId();
            $crmOffer->unit = new Unit($offer->getQuantity(), $offer->getUnit(), $offer->getUnit());

            $item = new OrderProduct();
            $item->offer = $crmOffer;
            $item->priceType = new PriceType('base');
            $item->quantity = $cartItem->getQuantity();
            $item->purchasePrice = $offer->getPrice();

            $items[] = $item;
        }

        $order->delivery      = $delivery;
        $order->items         = $items;
        $order->payments      = [$payment];
        $order->orderMethod   = 'phone';
        $order->countryIso    = CountryCodeIso3166::RUSSIAN_FEDERATION;
        $order->firstName     = $form->get('name')->getData();
        $order->lastName      = $form->get('surname')->getData();
        $order->patronymic    = $form->get('patronymic')->getData();
        $order->phone         = $form->get('phone')->getData();
        $order->email         = $form->get('email')->getData();
        $order->customer      = SerializedRelationCustomer::withExternalIdAndType(
            $customer->getId(),
            CustomerType::CUSTOMER,
            "b12-skillum-ru"
        );
        $order->status        = 'assembling';
        $order->shipmentDate  = (new DateTime())->add(new DateInterval('P7D'));
        $order->shipped       = false;

        $request->order = $order;

        try {
            $response = $this->client->orders->create($request);
            return $response;
        } catch (ApiExceptionInterface | ClientExceptionInterface $exception) {
            return $exception;
        }
    }

    public function getOrderById($orderId) {
        try {
            $response = $this->client->orders->get($orderId, new BySiteRequest(ByIdentifier::ID));
            return $response;
        } catch (ApiExceptionInterface | ClientExceptionInterface $exception) {
            return $exception;
        }
    }

    private function getPaymentAmount(User $customer)
    {
        $cartItems = $customer->getCartItems();

        $amount = 0;
        foreach ($cartItems as $item) {
            $amount += $item->getOffer()->getPrice() * $item->getQuantity();
        }

        return $amount;
    }
}
