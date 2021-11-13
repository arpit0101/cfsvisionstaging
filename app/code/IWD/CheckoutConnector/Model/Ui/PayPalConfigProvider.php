<?php

namespace IWD\CheckoutConnector\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Gateway\Config\Config;

/**
 * Class PayPalConfigProvider
 */
class PayPalConfigProvider implements ConfigProviderInterface
{
    const CODE = 'iwd_checkout_paypal';

    /**
     * @var Config
     */
    private $config;

    /**
     * Constructor
     *
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'label' => $this->config->getValue('label'),
                    'description' => $this->config->getValue('description'),
                ],
            ]
        ];
    }
}
