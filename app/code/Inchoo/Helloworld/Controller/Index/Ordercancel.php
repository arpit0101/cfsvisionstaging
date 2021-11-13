<?php namespace Inchoo\Helloworld\Controller\Index;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
class Ordercancel extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory; 
    public function __construct(
	    Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Sales\Model\OrderFactory $orderFactory,
		\Magento\Sales\Api\OrderManagementInterface $orderManagement,
		\Magento\Framework\Message\ManagerInterface $messageManager
	){
		$this->_orderFactory = $orderFactory;
		$this->_resultPageFactory = $resultPageFactory;
		$this->_messageManager = $messageManager;
		$this->orderManagement = $orderManagement;
        parent::__construct($context);
    }
 
    public function execute()
    {
		$orderId = $this->getRequest()->getParam('order_id');
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
		try{
		    //$order->cancel()->save();
			$this->orderManagement->cancel($orderId);
			$order = $this->_orderFactory->create()->load($orderId);
			$emailSender = $objectManager->create('\Magento\Sales\Model\Order\Email\Sender\OrderSender');
            $emailSender->send($order);
			$this->_messageManager->addSuccess(__("Order Successfully Cancel."));
		}catch (\Exception $e) {
			$this->logger->critical($e->getMessage());
			$this->_messageManager->addError(__("Something wrong."));
		}
		$resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
	}
}