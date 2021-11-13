<?php
 
namespace Webkul\TestApi\Model\Api;
 
class TestApiManagement implements \Webkul\TestApi\Api\TestApiManagementInterface
{
    const SEVERE_ERROR = 0;
    const SUCCESS = 1;
    const LOCAL_ERROR = 2;
 
    protected $_testApiFactory;
 
    public function __construct(
        \Webkul\TestApi\Model\TestApiFactory $testApiFactory
 
    ) {
        $this->_testApiFactory = $testApiFactory;
    }
 
    /**
     * get test Api data.
     *
     * @api
     *
     * @param int $id
     *
     * @return \Webkul\TestApi\Api\Data\TestApiInterface
     */
    public function getApiData($id)
    {
        try {
            $model = $this->_testApiFactory
                ->create();
 
            if (!$model->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('no data found')
                );
            }
 
            return $model;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = 0;
            $this->getJsonResponse(
                $returnArray
            );
        } catch (\Exception $e) {
            $this->createLog($e);
            $returnArray['error'] = __('unable to process request');
            $returnArray['status'] = 2;
            $this->getJsonResponse(
                $returnArray
            );
        }
    }
}