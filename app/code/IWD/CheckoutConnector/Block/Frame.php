<?php

namespace IWD\CheckoutConnector\Block;

use Magento\Checkout\Block\Onepage as CheckoutOnepage;
use Magento\Checkout\Model\Session\Proxy as CheckoutSession;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Data\Form\FormKey;
use Magento\Checkout\Model\CompositeConfigProvider;
use IWD\CheckoutConnector\Helper\Data;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Integration\Model\Oauth\TokenFactory;

/**
 * Class Frame
 *
 * @package IWD\CheckoutConnector\Block
 */
class Frame extends CheckoutOnepage
{
    const CMS_TYPE = 'Magento2';

    /**
     * @var CheckoutSession
     */
    public $checkoutSession;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var TokenFactory
     */
    private $tokenModelFactory;

    /**
     * Frame constructor.
     *
     * @param Context $context
     * @param FormKey $formKey
     * @param CompositeConfigProvider $configProvider
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param TokenFactory $tokenModelFactory
     * @param Data $helper
     * @param array $layoutProcessors
     * @param array $data
     */
    public function __construct(
        Context $context,
        FormKey $formKey,
        CompositeConfigProvider $configProvider,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        TokenFactory $tokenModelFactory,
        Data $helper,
        array $layoutProcessors = [],
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->tokenModelFactory = $tokenModelFactory;
        $this->helper = $helper;
        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data);
    }

    /**
     * @return string
     */
    public function getFrameUrl()
    {
        $checkoutUrl = $this->helper->getCheckoutUrl();
        $integrationApiKey = $this->helper->getIntegrationApiKey();
        $quoteId = $this->checkoutSession->getQuote()->getId();

        $params = [
            'api_key' => $integrationApiKey,
            'quote_id' => $quoteId,
            'customer_token' => $this->getCustomerToken()
        ];

        if($this->customerSession->isLoggedIn()) {
            $params['customer_token'] = $this->getCustomerToken();
        }

        return $checkoutUrl . '?' . http_build_query($params);
    }

    /**
     * @return string
     */
    public function getSuccessActionUrl()
    {
        return $this->getUrl('iwd_checkout/index/success');
    }

    /**
     * @return string
     */
    public function getEditCartUrl()
    {
        return $this->getUrl('checkout/cart');
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->getUrl('customer/account/login');
    }

    /**
     * @return mixed
     */
    private function getCustomerToken()
    {
        if($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomer()->getId();
            $customerToken = $this->tokenModelFactory->create();

            return $customerToken->createCustomerToken($customerId)->getToken();
        }

        return 'empty';
    }
}
