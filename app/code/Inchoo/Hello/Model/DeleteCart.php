<?php
namespace Inchoo\Hello\Model;
use Inchoo\Hello\Api\DeleteCartInterface;
use Magento\Backend\App\Action;
use Magento\Webapi\Model\Authorization\TokenUserContext;
use Magento\Checkout\Model\Cart as CustomerCart;

class DeleteCart implements DeleteCartInterface
{
	protected $_context;
	protected $_quoteFactory;
	protected $cart;
	/**
	* @param \Magento\Framework\View\Element\Template\Context $context
	* @param \Magento\Catalog\Helper\Category $categoryHelper
	* @param array $data
	*/
	public function __construct(
		TokenUserContext $context,
		\Magento\Quote\Model\QuoteFactory $quoteFactory,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		CustomerCart $cart,
		array $data = []
	) {
		$this->_context	=	$context;
		$this->_quoteFactory 				=   $quoteFactory;
		$this->_objectManager 				=   $objectManager;
		$this->cart = $cart;
	}	
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @param string $area area
     * @return string Greeting message with users name.
     */
	 
    public function DeleteAllItems() {
		
		try {
			$customerId				=	$this->_context->getUserId(); /*get Login user detail*/
			$quote 					= 	$this->_objectManager->create('Magento\Quote\Model\Quote')->loadByCustomer($customerId);
			$quote->setIsActive(false);
			$quote->delete();
			return [['status'=>true]];
		} catch(Exception $e) {
			return [['status'=>false]];
		}
    }
}