<?php

namespace IWD\CheckoutConnector\Model\Api;

/**
 * Class FormatData
 *
 * @package IWD\CheckoutConnector\Model\Api
 */
class FormatData
{
    /**
     * @param null $data
     * @return array|null
     */
    public function format($data = null)
    {
        if ($data == null) {
            return null;
        }

        $formatData = [];
        $email = $data['email'];
        $same_as_billing = $data['ship_to_different_address'];

        unset($data['email']);
        unset($data['ship_to_different_address']);

        foreach ($data as $key => $item) {
            $formatData[$key] = [
                "region_id"       => isset($item["region_id"]) ? $item["region_id"] : null,
                "region"          => isset($item["state"]) ? $item["state"] : null,
                "country_id"      => $item['country'],
                "street"          => $item['address'],
                "postcode"        => $item['postcode'],
                "city"            => $item['city'],
                "firstname"       => $item['first_name'],
                "lastname"        => $item['last_name'],
                "telephone"       => isset($item["phone"]) ? $item["phone"] : null,
                "email"           => $email,
                "same_as_billing" => $same_as_billing ? $same_as_billing : 0,
            ];
        }

        return $formatData;
    }
}