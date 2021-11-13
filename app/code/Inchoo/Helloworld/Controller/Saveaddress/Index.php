<?php
 
namespace Inchoo\Helloworld\Controller\Saveaddress;
 
use Magento\Framework\App\Action\Context;
 
class Index extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
 
    public function __construct(Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
 
    public function execute()
    {
		//$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$customerSession = $this->_objectManager->get('\Magento\Customer\Model\Session'); 
		$custId = $customerSession->getCustomer()->getId();
		$firstName = $customerSession->getCustomer()->getFirstname();
		$lastName = $customerSession->getCustomer()->getLastname();
		$street = $this->getRequest()->getParam('street', false);
		$city = $this->getRequest()->getParam('city', false);
		$state = $this->getRequest()->getParam('state', false);
		$postcode = $this->getRequest()->getParam('postcode', false);
		$phone = $this->getRequest()->getParam('phone', false);
		$street = implode(PHP_EOL, $street);
		//$street = preg_split('/\n|\r\n?/', $street);
		$addresss = $this->_objectManager->get('\Magento\Customer\Model\AddressFactory');
        $address = $addresss->create();
        $address->setCustomerId($custId)
            ->setFirstname($firstName)
            ->setLastname($lastName)
            ->setCountryId('AE')
            ->setPostcode($postcode)
            ->setCity($city)
            ->setTelephone($phone)
            ->setStreet($street)
            ->setRegion($state)
			->setIsDefaultBilling('1')
		    ->setIsDefaultShipping('1')
		    ->setSaveInAddressBook('1');
            //->setSaveInAddressBook('1');
        $address->save();
		$this->_redirect('onepage');
    }
}