<?php

namespace IWD\CheckoutConnector\Model\Address;

/**
 * Class SaveToQuote
 *
 * @package IWD\CheckoutConnector\Model\Address
 */
class SaveToQuote
{
    /**
     * @param $quote
     * @param $data
     */
    public function saveAddress($quote, $data)
    {
        $quote->getBillingAddress()->addData($data['billing']);
        $quote->getShippingAddress()->addData($data['shipping']);

        $quote->save();
    }

    /**
     * @param $quote
     * @param $shippingMethodCode
     */
    public function saveShipping($quote, $shippingMethodCode)
    {
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setShippingMethod($shippingMethodCode)->setCollectShippingRates(true);

        $quote->collectTotals();
        $quote->save();
    }
}