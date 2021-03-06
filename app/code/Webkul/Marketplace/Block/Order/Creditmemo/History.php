<?php
namespace Webkul\Marketplace\Block\Order\Creditmemo;
/**
 * Webkul Marketplace Order Creditmemo History Block
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software Private Limited
 *
 */
use Magento\Sales\Model\Order;
use Magento\Customer\Model\Customer;
use Magento\Framework\UrlInterface;

class History extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
    * @param Context $context
    * @param array $data
    * @param Customer $customer
    * @param Order $order
    * @param \Magento\Framework\ObjectManagerInterface $objectManager
    * @param \Magento\Customer\Model\Session $customerSession
    */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Order $order,
        Customer $customer,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->Customer = $customer;
        $this->Order = $order;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current order model instance
     *
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('sales_order');
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Orders'));
    }

    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }    

    /**
     * @return bool|\Magento\Sales\Model\Order\Creditmemo\Collection
     */

    public function getCollection()
    {
        $order_id = $this->getRequest()->getParam('order_id');
        $tracking=$this->_objectManager->create('Webkul\Marketplace\Helper\Orders')->getOrderinfo($order_id);
        $creditmemo = array();
        if($tracking){
            $creditmemo_ids=array();        
            $creditmemo_ids = explode(',', $tracking->getCreditmemoId());
            $creditmemo = $this->_objectManager->create('Magento\Sales\Model\Order\Creditmemo')
            ->getCollection()
            ->addFieldToFilter(
                'entity_id',
                ['in' => $creditmemo_ids]
            );
        }
        return $creditmemo;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'marketplace.order.creditmemo.pager'
            )->setCollection(
                $this->getCollection()
            );
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl(); // Give the current url of recently viewed page
    }
}
