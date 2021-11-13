<?php
namespace Inchoo\Hello\Model;
use Inchoo\Hello\Api\AddAddressInterface;
 use Magento\Webapi\Model\Authorization\TokenUserContext;
 
class AddAddress implements AddAddressInterface
{
	protected $_objectManager;
	protected $_context;
	protected $request;
	
/**
	* @param \Magento\Framework\View\Element\Template\Context $context
	* @param \Magento\Catalog\Helper\Category $categoryHelper
	* @param array $data
	*/
	public function __construct(
		TokenUserContext $context,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\App\Request\Http $request,
		array $data = []
		
	) {
		$this->_context	=	$context;
		$this->_objectManager 				=   $objectManager;
		$this->request						=	$request;
	}	

    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @param string $area area
     * @return string Greeting message with users name.
     */
    public function AddCustomerAddress() {
		
		try{
			$user_data 		=	json_decode($this->request->getContent(), true);
			$customerId		=	$this->_context->getUserId();
			if(empty($customerId)){
				return [["status"=>false,"msg"=>"Invalid user token."]];
			}
			$addresss = $this->_objectManager->get('\Magento\Customer\Model\AddressFactory');
			if(isset($user_data['id']) && $user_data['id']!=""){
				$address = $addresss->create()->load($user_data['id']);
			}else{
				$address = $addresss->create();
			}
			$address->setCustomerId($customerId)
			->setFirstname($user_data['firstname'])
			->setLastname($user_data['lastname'])
			->setCountryId($user_data['country_id'])
			->setRegion()->setRegion($user_data['region']['region'])->setRegionCode($user_data['region']['region_code'])
			->setPostcode($user_data['postcode'])
			->setCity($user_data['city'])
			->setTelephone($user_data['telephone'])
			->setStreet($user_data['street'])
			->setIsDefaultBilling($user_data['default_billing'])
			->setIsDefaultShipping($user_data['default_shipping'])
			->setSaveInAddressBook(0);
			$address->save();
			return [$address->getData()];
		}
		catch (Exception $e) {
			Zend_Debug::dump($e->getMessage());
			return false;
		}			
    }
}