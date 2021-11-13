<?php

namespace IWD\CheckoutConnector\Model\Address;

use Magento\Customer\Model\AddressFactory;

/**
 * Class Addresses
 * @package IWD\CheckoutConnector\Model\Address
 */
class Addresses
{
    /**
     * @var AddressFactory
     */
    private $addressRepository;

    /**
     * @var SaveToQuote
     */
    private $saveToQuote;

    /**
     * Addresses constructor.
     *
     * @param AddressFactory $addressRepository
     * @param SaveToQuote $saveToQuote
     */
    public function __construct(
        AddressFactory $addressRepository,
        SaveToQuote $saveToQuote
    )
    {
        $this->addressRepository = $addressRepository;
        $this->saveToQuote = $saveToQuote;
    }

    /**
     * @param $quote
     * @return array
     */
    public function getCustomerAddresses($quote)
    {
        $data = null;
        if ($this->isLoggedIn($quote)) {
            $this->getSavedAddress($quote);
        }
        $data = $this->formatAddress($quote);

        return $data;
    }

    /**
     * @param $quote
     */
    public function getSavedAddress($quote)
    {
        $billingAddressId = $quote->getCustomer()->getDefaultBilling();
        $shippingAddressId = $quote->getCustomer()->getDefaultShipping();
        $defaultAddress = null;

        if ($billingAddressId) {
            $defaultAddress['billing'] = $this->addressRepository->create()->load($billingAddressId)->getData();
        }
        if ($shippingAddressId) {
            $defaultAddress['shipping'] = $this->addressRepository->create()->load($shippingAddressId)->getData();
        }

        $quoteAddress = $this->getAddresses($quote);
        $address = $this->prepareForCustomer($quoteAddress, $defaultAddress);

        $this->saveToQuote->saveAddress($quote, $address);
    }

    /**
     * @param $savedAddress
     * @param null $address
     * @return array
     */
    public function prepareForCustomer($savedAddress, $address = null)
    {
        $address = $address ? $address : $savedAddress;
        $data = [];
        foreach ($address as $key => $item) {
            $data[$key] = [
                'firstname'  => $savedAddress[$key]['firstname'] ?? $item['firstname'],
                'lastname'   => $savedAddress[$key]['lastname'] ?? $item['lastname'],
                'street'     => $savedAddress[$key]['street'] ?? $item['street'],
                'country_id' => $savedAddress[$key]['country_id'] ?? $item['country_id'],
                'region'     => $savedAddress[$key]['region'] ?? $item['region'],
                'region_id'  => $savedAddress[$key]['region_id'] ?? $item['region_id'],
                'city'       => $savedAddress[$key]['city'] ?? $item['city'],
                'postcode'   => $savedAddress[$key]['postcode'] ?? $item['postcode'],
                'telephone'  => $savedAddress[$key]['telephone'] ?? $item['telephone']
            ];
        }

        return $data;
    }

    /**
     * @param $quote
     * @return array
     */
    public function formatAddress($quote)
    {
        $data = $this->getAddresses($quote);
        $address = [];
        foreach ($data as $key => $item) {
            $address[$key] = [
                'email'      => $item['email'],
                'first_name' => $item['firstname'],
                'last_name'  => $item['lastname'],
                'address'    => $item['street'],
                'country'    => $item['country_id'],
                'state'      => $item['region'],
                "region_id"  => $item['region_id'],
                'city'       => $item['city'],
                'postcode'   => $item['postcode'],
                'phone'      => $item['telephone']
            ];
        }
        $address['ship_to_different_address'] = $data['billing']['same_as_billing'];

        return $address;
    }

    /**
     * @param $quote
     * @return mixed
     */
    public function getAddresses($quote)
    {
        $result['billing']  = $quote->getBillingAddress()->getData();
        $result['shipping'] = $quote->getShippingAddress()->getData();

        return $result;
    }

    /**
     * @param $quote
     * @return bool
     */
    public function isLoggedIn($quote)
    {
        $data = $quote->getCustomer()->getId();

        return (bool)$data;
    }
}