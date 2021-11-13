<?php

namespace IWD\CheckoutConnector\Model;

use IWD\CheckoutConnector\Helper\Data as Helper;

/**
 * Class AddressStep
 *
 * @package IWD\CheckoutConnector\Model
 */
class AccessValidator
{
    const AUTH_API_KEY = 'IWD_Checkout_SaaS';
    const AUTH_API_CIPHER = 'AES-128-CBC';

    /**
     * @var Helper
     */
    private $helper;

    /**
     * AccessValidator constructor.
     *
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param $tokens
     * @return bool
     */
    public function checkAccess($tokens)
    {
        $integrationKey = $this->helper->getIntegrationApiKey();
        $phrase = $tokens['phrase'];
        $iv = $tokens['iv'];

        $decryptedPhrase = $this->decryptPhrase($phrase, $iv);

        if($decryptedPhrase === $integrationKey) {
            return true;
        }
        return false;
    }

    /**
     * @param $phrase
     * @param $iv
     * @return string
     */
    private function decryptPhrase($phrase, $iv) {
        $key = self::AUTH_API_KEY;
        $cipher = self::AUTH_API_CIPHER;

        return openssl_decrypt($phrase, $cipher, $key, $options=0, $iv);
    }
}