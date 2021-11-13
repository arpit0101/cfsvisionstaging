<?php
namespace Inchoo\Hello\Model;
use Inchoo\Hello\Api\HyperPayInterface;
use Magento\Backend\App\Action;
use Magento\Webapi\Model\Authorization\TokenUserContext;
use Magento\Checkout\Model\Cart as CustomerCart;

class HyperPay implements HyperPayInterface
{
	protected $_store;
	protected $_context;
	protected $_objectManager;
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
        \Magento\Framework\Registry $coreRegistry,
        \Hyperpay\Extension\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Hyperpay\Extension\Model\Adapter $adapter,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remote,
		array $data = []
	) {
		$this->_context	=	$context;
		$this->_quoteFactory 				=   $quoteFactory;
		$this->_objectManager 				=   $objectManager;
		$this->_store 						= 	$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
		$this->_helper						=	$helper;
        $this->_pageFactory 				= 	$pageFactory;
        $this->_adapter						=	$adapter;
        $this->_storeManager 				= 	$storeManager;
        $this->_remote						=	$remote;
	}	
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    public function checkouts() {
		
    }
}