<?php
 
namespace Inchoo\Helloworld\Controller\Deleteaddress;
 
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
		$this->_objectManager 		=	 \Magento\Framework\App\ObjectManager::getInstance();
		$this->_addressRepository 	=	 $this->_objectManager->get('\Magento\Customer\Model\AddressFactory');
		$messageManager 			=    $this->_objectManager->get('\Magento\Framework\Message\ManagerInterface');
		$addressId = $this->getRequest()->getPost("address_id");
		$returnArray = [];
		try{

			 
			$this->_addressRepository->deleteById($addressId);
			 
			$messageManager->addSuccess("Successfully deleted customer address");
			 
		} catch(\Exception $e) {
			$messageManager->addError($e->getMessage());

		}
			
		$this->_redirect('onepage');
    }
}