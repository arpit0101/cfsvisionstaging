<?php
namespace Webkul\Marketplace\Controller\Account\Dashboard;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\View\Result\PageFactory;

class Tunnel extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        Context $context,
        Result\RawFactory $resultRawFactory,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Forward request for a graph image to the web-service
     *
     * This is done in order to include the image to a HTTPS-page regardless of web-service settings
     *
     * @return  \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $error = __('invalid request');
        $httpCode = 400;
        $gaData = $this->_request->getParam('ga');
        $gaHash = $this->_request->getParam('h');
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        if ($gaData && $gaHash) {
            /** @var $helper \Webkul\Marketplace\Helper\Dashboard\Data */
            $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Dashboard\Data');
            $newHash = $helper->getChartDataHash($gaData);
            if (Security::compareStrings($newHash, $gaHash)) {
                $params = null;
                $paramsJson = base64_decode(urldecode($gaData));
                if ($paramsJson) {
                    $params = json_decode($paramsJson, true);
                }
                if ($params) {
                    try {
                        /** @var $httpClient \Magento\Framework\HTTP\ZendClient */
                        $httpClient = $this->_objectManager->create('Magento\Framework\HTTP\ZendClient');
                        $response = $httpClient->setUri(
                            \Webkul\Marketplace\Block\Account\Dashboard\Diagrams::API_URL
                        )->setParameterGet(
                            $params
                        )->setConfig(
                            ['timeout' => 5]
                        )->request(
                            'GET'
                        );

                        $headers = $response->getHeaders();

                        $resultRaw->setHeader('Content-type', $headers['Content-type'])
                            ->setContents($response->getBody());
                        return $resultRaw;
                    } catch (\Exception $e) {
                        $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                        $error = __('see error log for details');
                        $httpCode = 503;
                    }
                }
            }
        }
        $resultRaw->setHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->setHttpResponseCode($httpCode)
            ->setContents(__('Service unavailable: %1', $error));
        return $resultRaw;
    }
}
