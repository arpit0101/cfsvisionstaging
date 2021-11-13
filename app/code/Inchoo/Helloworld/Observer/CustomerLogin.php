<?php

namespace Inchoo\Helloworld\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerLogin implements ObserverInterface
{
    protected $_customerRepositoryInterface;

    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
		\Magento\Framework\App\RequestInterface $request
    ) {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_customerFactory = $customerFactory;
		$this->_request = $request;
    }
	
	public function execute(\Magento\Framework\Event\Observer $observer)
    {
		$user_data 		=	json_decode($this->_request->getContent());
		if(empty($user_data)){
			$user_data	=	json_decode(json_encode($_POST));
		}
		// file_put_contents(__DIR__."/user_api_data.txt", print_R($user_data, true));
		//$params = $this->_request->getParams();
		$objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
		$customer = $observer->getEvent()->getCustomer();
		
		if(isset($user_data->device_token)):
		    $devicetoken = $user_data->device_token;
		    $devicetype = $user_data->device_type;
		    //$customer->setDeviceType($devicetype);
		    $customer_id = $customer->getId();
		    $loaded_customer = $this->_customerFactory->create()->load($customer_id)->getDataModel();
            $loaded_customer->setCustomAttribute('device_token', $devicetoken);
            $loaded_customer->setCustomAttribute('device_type', $devicetype);
            $this->_customerRepositoryInterface->save($loaded_customer);
		endif;
		if($customer->getGroupId()==4 || $customer->getGroupId()==3){
			$allItems 		= $objectManager->create('Magento\Checkout\Model\Session')->getQuote()->getAllVisibleItems();
			$cart 			= $objectManager->create('Magento\Checkout\Model\Cart');
			foreach ($allItems as $item) {
				$itemId = $item->getItemId();
				$cart->removeItem($itemId)->save();
			}
			$allItems 		= $objectManager->create('Magento\Checkout\Model\Session')->getQuote()->getAllVisibleItems();
		}
    }
}