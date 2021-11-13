<?php
namespace Inchoo\Hello\Model;
use Inchoo\Hello\Api\AddressInterface;
use Magento\Webapi\Model\Authorization\TokenUserContext;
use Magento\Framework\Controller\ResultFactory;

use Magento\Framework\App\Action\Action; 
class CustomerAddress implements AddressInterface
{
	/**
     * $_addressRepository
     * @var \Magento\Customer\Api\AddressRepository
     */
    protected $_addressRepository;
	protected $_objectManager;
	protected $_context;
	protected $request;
    /**
     * @param TokenUserContext     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        TokenUserContext $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
		\Magento\Framework\App\Request\Http $request
    ) 
    {
        $this->_addressRepository 			= 	$addressRepository;
		$this->_context						=	$context;
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
    public function AddressDelete($address_id) {
		
		$returnArray = [];
		try{ 
			$customerId		=	$this->_context->getUserId();
			$addresss = $this->_objectManager->get('\Magento\Customer\Model\AddressFactory');
			$address = $addresss->create()->load($address_id);
			$addresscustomerid 	=	$address->getCustomerId();
			if($customerId==$addresscustomerid){
				$this->_addressRepository->deleteById($address_id);
				return json_encode(['error'=>false, 'msg'=>'This address successfully deleted.']);
			}else{
				return json_encode(['error'=>true, 'msg'=>'This address does not belongs to you.']);
			}
		} catch(\Exception $e) {
			return false;
		}
    }
}