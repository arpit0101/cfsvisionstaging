<?php
namespace Webkul\Marketplace\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use \Magento\Sales\Model\Order\CreditmemoFactory;

use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;

abstract class Order extends Action
{
    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @var ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var CreditmemoSender
     */
    protected $creditmemoSender;
    
    /**
     * @var CreditmemoRepositoryInterface;
     */
    protected $creditmemoRepository;

    /**
     * @var CreditmemoFactory;
     */
    protected $creditmemoFactory;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param InvoiceSender $invoiceSender
     * @param ShipmentSender $shipmentSender
     * @param ShipmentFactory $shipmentFactory
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param CreditmemoFactory $creditmemoFactory
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     * @param OrderManagementInterface $orderManagement
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory, 
        InvoiceSender $invoiceSender,
        ShipmentSender $shipmentSender,
        ShipmentFactory $shipmentFactory,
        CreditmemoSender $creditmemoSender,
        CreditmemoRepositoryInterface $creditmemoRepository,
        CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        OrderRepositoryInterface $orderRepository,
        OrderManagementInterface $orderManagement,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->invoiceSender = $invoiceSender;
        $this->shipmentSender = $shipmentSender;
        $this->shipmentFactory = $shipmentFactory;
        $this->creditmemoSender = $creditmemoSender;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->invoiceRepository = $invoiceRepository;
        $this->stockConfiguration = $stockConfiguration;
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
        $this->_customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_objectManager->get('Magento\Customer\Model\Url')->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Initialize order model instance
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|false
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('id');
        try {
			$objectManager 	=	\Magento\Framework\App\ObjectManager::getInstance();
			$customerSession = $objectManager->create('Magento\Customer\Model\Session');
			$groupId = $customerSession->getCustomer()->getGroupId();
			$order = $this->orderRepository->get($id);
			if($groupId==4){
				return $order;	
			}
            $tracking = $this->_objectManager->create('Webkul\Marketplace\Helper\Orders')->getOrderinfo($id);
			//echo '<pre>'; echo $tracking->getsize(); print_r($tracking->getData()); die("testt");
            if(!empty($tracking->getData())){
                if ($tracking->getOrderId() == $id) {
                    if (!$id) {
                        $this->messageManager->addError(__('This order no longer exists.'));
                        $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                        return false;
                    }
                }else{
                    $this->messageManager->addError(__('You are not authorize to manage this order.'));
                    $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                    return false;
                }
            }else{
                $this->messageManager->addError(__('You are not authorize to manage this order.'));
                $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                return false;
            }
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addError(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        } catch (InputException $e) {
            $this->messageManager->addError(__('This order no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        $this->_coreRegistry->register('sales_order', $order);
        $this->_coreRegistry->register('current_order', $order);
        return $order;
    }  

    
    /**
     * Initialize invoice model instance
     *
     * @return \Magento\Sales\Api\InvoiceRepositoryInterface|false
     */
    protected function _initInvoice()
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        $orderId = $this->getRequest()->getParam('order_id');        
        if (!$invoiceId) {
            return false;
        }
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $this->_objectManager->create('Magento\Sales\Api\InvoiceRepositoryInterface')->get($invoiceId);
        if (!$invoice) {
            return false;
        }        
        try {
            $order = $this->orderRepository->get($orderId);
            $tracking = $this->_objectManager->create('Webkul\Marketplace\Helper\Orders')->getOrderinfo($orderId);
			
            if(count($tracking)){
                if ($tracking->getInvoiceId() == $invoiceId) {
                    if (!$invoiceId) {
                        $this->messageManager->addError(__('The invoice no longer exists.'));
                        $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                        return false;
                    }
                }else{
                    $this->messageManager->addError(__('You are not authorize to view this invoice.'));
                    $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                    return false;
                }
            }else{
                $this->messageManager->addError(__('You are not authorize to view this invoice.'));
                $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                return false;
            }
        } catch (NoSuchEntityException $e) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        } catch (InputException $e) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        $this->_coreRegistry->register('sales_order', $order);
        $this->_coreRegistry->register('current_order', $order);
        $this->_coreRegistry->register('current_invoice', $invoice);
        return $invoice;
    }
	
	/**
     * Initialize invoice model instance
     *
     * @return \Magento\Sales\Api\InvoiceRepositoryInterface|false
     */
    protected function _initInvoiceForManager()
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        $orderId = $this->getRequest()->getParam('order_id');        
        if (!$invoiceId) {
            return false;
        }
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $this->_objectManager->create('Magento\Sales\Api\InvoiceRepositoryInterface')->get($invoiceId);
        if (!$invoice) {
            return false;
        }        
        try {
            $order = $this->orderRepository->get($orderId);
            $tracking = $this->_objectManager->create('Webkul\Marketplace\Helper\Orders')->getOrderinfo($orderId);
			// print_r($tracking);die;
            /* if(count($tracking)){
                if ($tracking->getInvoiceId() == $invoiceId) {
                    if (!$invoiceId) {
                        $this->messageManager->addError(__('The invoice no longer exists.'));
                        $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                        return false;
                    }
                }else{
                    $this->messageManager->addError(__('You are not authorize to view this invoice.'));
                    $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                    return false;
                }
            }else{
                $this->messageManager->addError(__('You are not authorize to view this invoice.'));
                $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                return false;
            } */
        } catch (NoSuchEntityException $e) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        } catch (InputException $e) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        $this->_coreRegistry->register('sales_order', $order);
        $this->_coreRegistry->register('current_order', $order);
        $this->_coreRegistry->register('current_invoice', $invoice);
        return $invoice;
    }
    
    /**
     * Initialize shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment|false
     */
    protected function _initShipment()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $orderId = $this->getRequest()->getParam('order_id');        
        if (!$shipmentId) {
            return false;
        }
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipmentId);
        if (!$shipment) {
            return false;
        }        
        try {
            $order = $this->orderRepository->get($orderId);
            $tracking = $this->_objectManager->create('Webkul\Marketplace\Helper\Orders')->getOrderinfo($orderId);
            if(count($tracking)){
                if ($tracking->getShipmentId() == $shipmentId) {
                    if (!$shipmentId) {
                        $this->messageManager->addError(__('The shipment no longer exists.'));
                        $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                        return false;
                    }
                }else{
                    $this->messageManager->addError(__('You are not authorize to view this shipment.'));
                    $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                    return false;
                }
            }else{
                $this->messageManager->addError(__('You are not authorize to view this shipment.'));
                $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                return false;
            }
        } catch (NoSuchEntityException $e) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        } catch (InputException $e) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        $this->_coreRegistry->register('sales_order', $order);
        $this->_coreRegistry->register('current_order', $order);
        $this->_coreRegistry->register('current_shipment', $shipment);
        return $shipment;
    } 
	
	/**
     * Initialize shipment model instance for managers
     *
     * @return \Magento\Sales\Model\Order\Shipment|false
     */
    protected function _initShipmentForManager()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $orderId = $this->getRequest()->getParam('order_id');        
        if (!$shipmentId) {
            return false;
        }
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipmentId);
        if (!$shipment) {
            return false;
        }        
        try {
            $order = $this->orderRepository->get($orderId);
            $tracking = $this->_objectManager->create('Webkul\Marketplace\Helper\Orders')->getOrderinfo($orderId);
            
        } catch (NoSuchEntityException $e) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        } catch (InputException $e) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        $this->_coreRegistry->register('sales_order', $order);
        $this->_coreRegistry->register('current_order', $order);
        $this->_coreRegistry->register('current_shipment', $shipment);
        return $shipment;
    } 

    /**
     * Initialize invoice model instance
     *
     * @return \Magento\Sales\Api\InvoiceRepositoryInterface|false
     */
    protected function _initCreditmemo()
    {
        $creditmemo = false;
        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($orderId);

        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $this->_objectManager->create('Magento\Sales\Api\CreditmemoRepositoryInterface')->get($creditmemoId);
        if (!$creditmemo) {
            return false;
        }        
        try {
            $tracking = $this->_objectManager->create('Webkul\Marketplace\Helper\Orders')->getOrderinfo($orderId);            
            if(count($tracking)){
                $creditmemo_arr = explode(',', $tracking->getCreditmemoId());
                if (in_array($creditmemoId, $creditmemo_arr)) {
                    if (!$creditmemoId) {
                        $this->messageManager->addError(__('The creditmemo no longer exists.'));
                        $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                        return false;
                    }
                } else{
                    $this->messageManager->addError(__('You are not authorize to view this creditmemo.'));
                    $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                        return false;
                }
            }else{
                $this->messageManager->addError(__('You are not authorize to view this creditmemo.'));
                $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                return false;
            }
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        } catch (InputException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        $this->_coreRegistry->register('sales_order', $order);
        $this->_coreRegistry->register('current_order', $order);
        $this->_coreRegistry->register('current_creditmemo', $creditmemo);
        return $creditmemo;
    }    

    protected function _getItemQtys($order,$items){
        $data=array();
        $subtotal = 0;
        $baseSubtotal = 0;
        foreach($order->getAllItems() as $item){
            if(in_array($item->getItemId(),$items)){

                $data[$item->getItemId()]=intval($item->getQtyOrdered()-$item->getQtyInvoiced());

                $_item = $item;
                
                // for bundle product
                $bundleitems = array_merge(array($_item), $_item->getChildrenItems());

                if ($_item->getParentItem()) continue;

                if($_item->getProductType()=='bundle'){
                    foreach ($bundleitems as $_bundleitem){ 
                        if ($_bundleitem->getParentItem()){
                            $data[$_bundleitem->getItemId()]=intval($_bundleitem->getQtyOrdered()-$item->getQtyInvoiced());
                        }
                    }
                }
                $subtotal+=$_item->getRowTotal();
                $baseSubtotal+=$_item->getBaseRowTotal();
            }else{
                if(!$item->getParentItemId()){
                    $data[$item->getItemId()]=0;
                }
            }   
        }
        return array('data'=>$data,'subtotal'=>$subtotal,'baseSubtotal'=>$baseSubtotal);
    }

    protected function _getShippingItemQtys($order,$items){
        $data=array();
        $subtotal = 0;
        $baseSubtotal = 0;
        foreach($order->getAllItems() as $item){
            if(in_array($item->getItemId(),$items)){

                $data[$item->getItemId()]=intval($item->getQtyOrdered()-$item->getQtyShipped());

                $_item = $item;
                
                // for bundle product
                $bundleitems = array_merge(array($_item), $_item->getChildrenItems());

                if ($_item->getParentItem()) continue;

                if($_item->getProductType()=='bundle'){
                    foreach ($bundleitems as $_bundleitem){ 
                        if ($_bundleitem->getParentItem()){
                            $data[$_bundleitem->getItemId()]=intval($_bundleitem->getQtyOrdered()-$item->getQtyShipped());
                        }
                    }
                }
                $subtotal+=$_item->getRowTotal();
                $baseSubtotal+=$_item->getBaseRowTotal();
            }else{
                if(!$item->getParentItemId()){
                    $data[$item->getItemId()]=0;
                }
            }   
        }
        return array('data'=>$data,'subtotal'=>$subtotal,'baseSubtotal'=>$baseSubtotal);
    }
}
