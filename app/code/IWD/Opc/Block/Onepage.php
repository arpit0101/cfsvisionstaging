<?php

namespace IWD\Opc\Block;

use Magento\Checkout\Block\Onepage as CheckoutOnepage;
use Magento\Checkout\Model\Session\Proxy as CheckoutSession;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Data\Form\FormKey;
use Magento\Checkout\Model\CompositeConfigProvider;

class Onepage extends CheckoutOnepage
{

    public $checkoutSession;
    public $quote = null;

    public function __construct(
        Context $context,
        FormKey $formKey,
        CompositeConfigProvider $configProvider,
        CheckoutSession $checkoutSession,
        array $layoutProcessors = [],
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data);
    }

    public function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }
	
	/*Function to fetch customer city and state*/	
	
	public function findLocation(){
		$this->_objectManager 		=	 \Magento\Framework\App\ObjectManager::getInstance();
		$customerSession 			= 	 $this->_objectManager->get('\Magento\Customer\Model\Session'); 
		$regionCollectionFactory 	=    $this->_objectManager->get('\PHPCuong\Region\Model\ResourceModel\Region\CollectionFactory'); 
		$areaCollectionFactory 		=    $this->_objectManager->get('\PHPCuong\Region\Model\ResourceModel\Area\CollectionFactory'); 
		$response_object 			= 	 $this->_objectManager->get('\Magento\Framework\App\Response\Http');
		$redirect_interface 		=  	 $this->_objectManager->get('\Magento\Framework\App\Response\RedirectInterface');
		$messageManager 			=    $this->_objectManager->get('\Magento\Framework\Message\ManagerInterface');
		
		$city_id 	= $customerSession->getCityId();
		$area_id 	= $customerSession->getAreaId();
		if(trim($city_id)!=""  && trim($area_id)!=""){
			
			$collection = $regionCollectionFactory->create()->addFieldToSelect(
			['region_id','default_name']
			)
			->addFieldToFilter(
				'region_id',
				['eq' => $city_id]
			)
			->setOrder(
				'default_name',
				'asc'
			);
			
			$collectionarea = $areaCollectionFactory->create()->addFieldToSelect(
				['area_id','default_name']
			)
			->addFieldToFilter(
				'area_id',
				['eq' => $area_id]
			)
			->setOrder(
				'default_name',
				'asc'
			);
			$location_city_data 	=	$collection->getFirstItem()->getData();
			$location_area_data 	=	$collectionarea->getFirstItem()->getData();
			$location['city'] 		= 	isset($location_city_data['default_name']) ? $location_city_data['default_name'] : '';
			$location['area']		= 	isset($location_area_data['default_name']) ? $location_area_data['default_name'] : '';
			return $location;	
		}else{
			$messageManager->addError(__("Please select location"));
			$redirectUrl = $this->getUrl('/');
			$redirect_interface->redirect($response_object, $redirectUrl);
		}
		
		
		
		
	}
}
