<?php

namespace Inchoo\Helloworld\Controller\Adminhtml\Lists;

use \Magento\Backend\App\Action\Context;

class Regionlist extends \Magento\Backend\App\Action
{
    protected $_resultPageFactory;
    protected $_resultJsonFactory;
    protected $_regionCollection;
    protected $_areaCollection;
 
    public function __construct(
		Context $context, 
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\PHPCuong\Region\Model\ResourceModel\Region\CollectionFactory $regionCollection,
		\PHPCuong\Region\Model\ResourceModel\Area\CollectionFactory $areaCollection,
        array $data = []
	)
    {
        $this->_resultPageFactory 			= 	$resultPageFactory;
		$this->_resultJsonFactory 			= 	$resultJsonFactory;
		$this->_regionCollection 			= 	$regionCollection;
		$this->_areaCollection 				= 	$areaCollection;
        parent::__construct($context);
    }
	
    public function execute()
    {
		$type 			= 	$this->getRequest()->getParam('type', false);
		if($type=="area"){
			$region_id 	= 	$this->getRequest()->getParam('region_id', false);
			$collection = $this->_areaCollection->create()->addFieldToSelect(
				['area_id','default_name','region_id']
			)
			->addFieldToFilter(
				'region_id',
				['in' => $region_id]
			)
			->setOrder(
				'default_name',
				'asc'
			);
			$area_data 		=	$collection->getData();
			$new_data 		=	[];
			if(!empty($area_data)){
				foreach($area_data as $cities_data){
					$new_data[$cities_data['region_id']][]	=	$cities_data;
				}
			}
			
		}else{
			$country_id 	= 	$this->getRequest()->getParam('country_id', false);
			$collection = $this->_regionCollection->create()->addFieldToSelect(
				['region_id','default_name']
			)
			->addFieldToFilter(
				'country_id',
				['eq' => $country_id]
			)
			->setOrder(
				'default_name',
				'asc'
			);
			$cities_data 		=	$collection->getData();
			$new_data 			=	[];
			foreach($cities_data as $city){
				$new_data[]	=	['region_id'=>$city['region_id'], 'default_name'=>$city['default_name']];
			}
		}
		return  $this->_resultJsonFactory->create()->setData($new_data);
    }
}