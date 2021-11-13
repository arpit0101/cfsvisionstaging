<?php

namespace IWD\CheckoutConnector\Helper;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 *
 * @package IWD\CheckoutConnector\Helper
 */
final class Data extends AbstractHelper
{
    const PLATFORM = 'Magento2';
    const XML_PATH_ENABLE = 'iwd_checkout_connector/general/enable';
    const XML_PATH_INTEGRATION_API_KEY = 'iwd_checkout_connector/general/integration_api_key';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $resourceConfig;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var null
     */
    private $apiResponse = null;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Config $resourceConfig
     * @param Curl $curl
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Config $resourceConfig,
        Curl $curl
    ) {
        $this->storeManager = $storeManager;
        $this->resourceConfig = $resourceConfig;
        $this->curl = $curl;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isEnable()
    {
		return 1;
        $apiKey = $this->getIntegrationApiKey();
        if (!empty($apiKey)) {
            $status = $this->scopeConfig->getValue(self::XML_PATH_ENABLE);
            return (bool)$status;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function getIntegrationApiKey()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_INTEGRATION_API_KEY);
    }

    /**
     * @return string
     */
    public static function getAppUrl()
    {
        return 'https://checkout.iwdagency.com/';
    }

    /**
     * @return string
     */
    public function getCheckConnectionUrl()
    {
        return $this->getAppUrl() . 'checkout/check-connection';
    }

    /**
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getAppUrl() . 'checkout/address';
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isCurrentlySecure()
    {
        return (bool)$this->storeManager->getStore()->isCurrentlySecure();
    }

    /**
     * @return bool|string
     */
    public function checkIsAllow()
    {
        if ($this->apiResponse === null) {
            try {
                $params = $this->prepareParams();
                $url = $this->getCheckConnectionUrl().'?'.http_build_query($params);

                $this->curl->get($url);

                $response = $this->curl->getBody();

                $this->apiResponse = $this->parseResponse($response);
            } catch (\Exception $e) {
                $this->apiResponse = [
                    'Error' => 1,
                    'ErrorMessage' => $e->getMessage(),
                    'ErrorCode' => ($e->getCode() == 111)
                        ? 'api_key_empty'
                        : (($e->getCode() == 222) ? 'connect_error' : $e->getCode())
                ];
            }
        }
        $status = (bool)$this->apiResponse['Error'] === false;

        return $status;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function prepareParams()
    {
        $apiKey = $this->getIntegrationApiKey();
        if (empty($apiKey)) {
            throw new \Exception('Integration API Key is empty. Please, enter valid API Key.', 111);
        }

        return [
            'api_key'     => $apiKey,
            'platform'    => self::PLATFORM,
            'website_url' => $this->getCleanStoreUrl()
        ];
    }

    /**
     * @param $response
     * @return array|bool|mixed|string
     */
    private function parseResponse($response)
    {
        if (empty($response)) {
            return ['Error' => 'connect_error'];
        }

        $response = json_decode($response, true);

        return $response;
    }

    /**
     * Strip Base Url from protocol prefixes and ending slash
     *
     * @return string|string[]|null
     * @throws NoSuchEntityException
     */
    private function getCleanStoreUrl()
    {
        $storeUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);

        return preg_replace('#^https?://#', '', rtrim($storeUrl,'/'));
    }

    /**
     * @return null
     */
    public function getLastResponse()
    {
        return $this->apiResponse;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        $response = $this->getLastResponse();

        if (isset($response['ErrorCode'])) {
            switch ($response['ErrorCode']) {
                case 'wrong_api_key':
                    return 'Wrong Integration API Key';
                case 'not_configured_payments':
                    return 'Not Configured Payments';
                case 'wrong_website_url':
                    return 'Wrong Integration Website URL';
                case 'wrong_platform':
                    return 'Wrong Integration Platform Type';
                case 'api_key_empty':
                    return 'Empty API Key Field';
                case 'connect_error':
                    return 'Connection error';
            }
        }

        return isset($response['ErrorMessage']) ? $response['ErrorMessage'] : 'API Error!';
    }

    /**
     * @return string
     */
    public function getHelpText()
    {
        $response = $this->getLastResponse();

        if (isset($response['ErrorCode'])) {
            $platform = self::PLATFORM;
            $iwdSiteUrl = '<a href="https://www.iwdagency.com">IWD Agency Site</a>';
            $checkoutAdminUrl = '<a href="https://www.iwdagency.com/account?checkout-saas">Account > IWD Checkout > Integrations</a>';

            switch ($response['ErrorCode']) {
                case 'wrong_api_key':
                    return "We were unable to locate an Integration with your Api Key. Please enter API Key from your $checkoutAdminUrl on our $iwdSiteUrl.";
                case 'not_configured_payments':
                    return "Your Payment Methods are not configured. Please go to your $checkoutAdminUrl on our $iwdSiteUrl and configure at least one Payment Method.";
                case 'wrong_website_url':
                    return "Your current Store URL differs from the Website URL saved for your Integration. Please go to your $checkoutAdminUrl on our $iwdSiteUrl and change Website URL value for your Integration";
                case 'wrong_platform':
                    return "Your current Platform Type differs from the Platform saved for your Integration. Please go to your $checkoutAdminUrl on our $iwdSiteUrl, change Platform to '$platform' and Save.";
                case 'api_key_empty':
                    return "Please enter the Integration API Key. You can find it after purchasing IWD Checkout SaaS in $checkoutAdminUrl on our $iwdSiteUrl";
                case 'connect_error':
                    return "Could not connect to server API. Please contact our $iwdSiteUrl support";
            }
        }

        return '';
    }
}
