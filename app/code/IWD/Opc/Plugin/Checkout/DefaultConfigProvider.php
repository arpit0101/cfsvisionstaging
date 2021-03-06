<?php

namespace IWD\Opc\Plugin\Checkout;

use IWD\Opc\Helper\Data as OpcHelper;
use Magento\Framework\UrlInterface;

class DefaultConfigProvider
{
    public $opcHelper;
    public $url;

    public function __construct(
        OpcHelper $opcHelper,
        UrlInterface $url
    ) {
        $this->opcHelper = $opcHelper;
        $this->url = $url;
    }

    public function afterGetCheckoutUrl($subject, $result)
    {
        return $result;
    }
}
