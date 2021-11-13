<?php
namespace Webkul\Marketplace\Block\Order;
/**
 * Webkul Marketplace Order View Block
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software Private Limited
 *
 */
use Magento\Sales\Model\Order;
use Magento\Customer\Model\Customer;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Link\Purchased;
use Magento\Store\Model\ScopeInterface;

class View extends \Magento\Framework\View\Element\Template
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
     * @var AddressRenderer
     */
    protected $addressRenderer;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    protected $_links = array();

    /**
     * @var Purchased
     */
    protected $_purchasedLinks;

    /**
     * @var \Magento\Downloadable\Model\Link\PurchasedFactory
     */
    protected $_purchasedFactory;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory
     */
    protected $_itemsFactory;

    /**
     * @param Context $context
     * @param array $data
     * @param Customer $customer
     * @param Order $order
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param AddressRenderer $addressRenderer
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory
     * @param \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $itemsFactory
    */
    public function __construct(
        Order $order,
        Customer $customer,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Element\Template\Context $context,
        AddressRenderer $addressRenderer,
        \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $itemsFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->addressRenderer = $addressRenderer;
        $this->Customer = $customer;
        $this->Order = $order;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_purchasedFactory = $purchasedFactory;
        $this->_itemsFactory = $itemsFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('View Order Detail'));
    }

    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }
	
	public function getOrderDeliveryDate($order_id){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('sales_order'); //gives table name with prefix
		$sql = "Select `delivery_date` FROM " . $tableName . " where `entity_id`= ".$order_id;
		//return $sql;
		$result = $connection->fetchAll($sql); // gives associated array, table fields as key in array.
		if(isset($result[0]['delivery_date'])){
		return $result[0]['delivery_date'];
			
		}else{
			return false;
		}
	}
	
	public function getProductBarCodeByID($product_id){
		
		
		$objectManager 	= 	\Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
		$resource 		= 	$objectManager->get('Magento\Catalog\Model\Product');
		$product 		= 	$objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
		return $product->getData('barcode');
	}
	
	public function getSellerDataByCustomerID($customer_id){
		
		$sellerdata=$this->_objectManager->create('Webkul\Marketplace\Model\Seller')
				->getCollection()
				->addFieldToFilter('seller_id',array('eq'=>$customer_id))->addFieldToSelect('shop_url');
			
		foreach($sellerdata as $seller){ 
			return $seller->getData('shop_url'); 
		}
	}

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {        
        return parent::_prepareLayout();
    }

    public function getSellerOrderInfo($order_id='')
    {
		
		$objectManager 	=	\Magento\Framework\App\ObjectManager::getInstance();
		$customerSession = $objectManager->create('Magento\Customer\Model\Session');
		$groupId = $customerSession->getCustomer()->getGroupId();
		if($groupId==4){
			$collection = $this->_objectManager->create('Webkul\Marketplace\Model\Orders')
			->getCollection()
			->addFieldToFilter(
				'order_id',
				['eq' => $order_id]
			);
			
		}else{
			$collection = $this->_objectManager->create('Webkul\Marketplace\Model\Orders')
			->getCollection()
			->addFieldToFilter(
				'order_id',
				['eq' => $order_id]
			)
			->addFieldToFilter(
				'seller_id',
				['eq' => $this->getCustomerId()]
			);
		}
        return $collection;
    }

    public function getOrderCreditmemo($creditmemo_ids='')
    {
        $collection = $this->_objectManager->create('Magento\Sales\Model\Order\Creditmemo')
        ->getCollection()
        ->addFieldToFilter(
            'entity_id',
            ['in' => $creditmemo_ids]
        );
        return $collection;
    }

    public function getCreditmemoItemsCollection($creditmemo_id)
    {
        $collection = $this->_objectManager->create('Magento\Sales\Model\Order\Creditmemo\Item')
        ->getCollection()
        ->addFieldToFilter(
            'parent_id',
            ['eq' => $creditmemo_id]
        );
        return $collection;
    }

    public function getOrderInvoice($invoice_id='')
    {
        $collection = $this->_objectManager->create('Magento\Sales\Model\Order\Invoice')->load($invoice_id);
        return $collection;
    }

    public function getDebug(){
		$seller_product_coll = 	$this->_objectManager->create('Webkul\Marketplace\Model\Product')
								->getCollection()
								->addFieldToFilter(
									'mageproduct_id',
									['eq' => 2076]
								)
								->addFieldToSelect('seller_id')
								->distinct(true);
		
		
		foreach ($seller_product_coll as $value) {
			return $value;
			$seller_id	=	$value['seller_id'];
		}
		return $customer_id 	=	$seller_id;
	}
	public function getSellerOrdersList($order_id='',$pro_id,$item_id)
    {
		$customer_id 		=	$this->getCustomerId();
		$seller_id 			=	$this->getCustomerId();
		
		$objectManager 		=	\Magento\Framework\App\ObjectManager::getInstance();
		$customerSession 	= 	$this->_objectManager->create('Magento\Customer\Model\Session');
		$groupId 			= 	$customerSession->getCustomer()->getGroupId();
		if($groupId==4){
			$seller_product_coll = 	$this->_objectManager->create('Webkul\Marketplace\Model\Product')
									->getCollection()
									->addFieldToFilter(
										'mageproduct_id',
										['eq' => $pro_id]
									)->addFieldToFilter(
										'status',
										['eq' => 1]
									)
									->addFieldToSelect('seller_id')
									->distinct(true);
			
			
			if($seller_product_coll){
				foreach ($seller_product_coll as $value) {
					$seller_id	=	$value['seller_id'];
					break;
				}
				$customer_id 	=	$seller_id;
			} 
		}
		
        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
        ->getCollection()
        ->addFieldToFilter(
            'order_id',
            ['eq' => $order_id]
        )
        ->addFieldToFilter(
            'seller_id',
            ['eq' =>$customer_id]
        )
        ->addFieldToFilter(
            'mageproduct_id',
            ['eq' => $pro_id]
        )
        ->addFieldToFilter(
            'order_item_id',
            ['eq' => $item_id]
        )
        ->setOrder('order_id','DESC');
        return $collection;
    }

    public function getAdminPayStatus($order_id)
    {
        $admin_pay_status = 0;
        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
        ->getCollection()
        ->addFieldToFilter(
            'order_id',
            ['eq' => $order_id]
        )
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $this->getCustomerId()]
        );
        foreach($collection as $saleproduct){
            $admin_pay_status = $saleproduct->getAdminPayStatus();
        }
        return $admin_pay_status;
    }

    public function getQtyToRefundCollection($order_id)
    {
        $qty_to_refund_collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
        ->getCollection()
        ->addFieldToFilter(
            'order_id',
            ['eq' => $order_id]
        )
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $this->getCustomerId()]
        )
        ->addFieldToFilter(
            'magequantity',
            ['neq' => 0]
        );
        return count($qty_to_refund_collection);
    }

    /**
     * @return Purchased
     */
    public function getDownloadableLinks($item_id)
    {
        $this->_purchasedLinks = $this->_purchasedFactory->create()->load(
            $item_id,
            'order_item_id'
        );
        $purchasedItems = $this->_itemsFactory->create()->addFieldToFilter(
            'order_item_id',
            $item_id
        );
        $this->_purchasedLinks->setPurchasedItems($purchasedItems);

        return $this->_purchasedLinks;
    }

    /**
     * @return string
     */
    public function getLinksTitle($item_id)
    {
        return $this->getDownloadableLinks($item_id)->getLinkSectionTitle() ?: $this->_scopeConfig->getValue(
            Link::XML_PATH_LINKS_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl(); // Give the current url of recently viewed page
    }

    /**
     * Returns string with formatted address
     *
     * @param Address $address
     * @return null|string
     */
    public function getFormattedAddress(Address $address)
    {
        return $this->addressRenderer->format($address, 'html');
    }

    public function getLinks()
    {
        $this->checkLinks();
        return $this->_links;
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
     * Retrieve current invoice model instance
     *
     */
    public function getInvoice()
    {
        return $this->_coreRegistry->registry('current_invoice');
    }

    /**
     * Retrieve current shipment model instance
     *
     */
    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
    }

    /**
     * Retrieve current creditmemo model instance
     *
     */
    public function getCreditmemo()
    {
        return $this->_coreRegistry->registry('current_creditmemo');
    }

    private function checkLinks()
    {
        $order = $this->getOrder();
        $order_id = $order->getId();        
        $shipmentId = '';
        $invoiceId='';
        $creditmemo_id='';
        $tracking = $this->_objectManager->create('Webkul\Marketplace\Helper\Orders')->getOrderinfo($order_id);
        if(count($tracking)){
            $shipmentId = $tracking->getShipmentId();
            $invoiceId=$tracking->getInvoiceId();
            $creditmemo_id=$tracking->getCreditmemoId();
        }
        $this->_links['order'] = array(
            'name' => 'order',
            'label' => __('Items Ordered'),
            'url' => $this->_urlBuilder->getUrl(
                        'marketplace/order/view',
                        ['order_id' => $order_id, '_secure' => $this->getRequest()->isSecure()]
                    )
        );
        if (!$order->hasInvoices()) {
            unset($this->_links['invoice']);
        }else{
            if($invoiceId){
                $this->_links['invoice'] = array(
                    'name' => 'invoice',
                    'label' => __('Invoices'),
                    'url' => $this->_urlBuilder->getUrl(
                        'marketplace/order_invoice/view',
                        ['order_id' => $order_id, 'invoice_id' => $invoiceId, '_secure' => $this->getRequest()->isSecure()]
                    )
                );
            }
        }
        if (!$order->hasShipments()) {
            unset($this->_links['shipment']);
        }else{
            if($shipmentId){
                $this->_links['shipment'] = array(
                    'name' => 'shipment',
                    'label' => __('Shipments'),
                    'url' => $this->_urlBuilder->getUrl(
                        'marketplace/order_shipment/view',
                        ['order_id' => $order_id, 'shipment_id' => $shipmentId, '_secure' => $this->getRequest()->isSecure()]
                    )
                );
            }
        }
        if (!$order->hasCreditmemos()) {
            unset($this->_links['creditmemo']);
        }else{
            if($creditmemo_id){
                $this->_links['creditmemo'] = array(
                    'name' => 'creditmemo',
                    'label' => __('Refunds'),
                    'url' => $this->_urlBuilder->getUrl(
                        'marketplace/order_creditmemo/viewlist',
                        ['order_id' => $order_id, '_secure' => $this->getRequest()->isSecure()]
                    )
                );
            }
        }
    }

    /**
     * @param mixed $item
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isShipmentSeparately($item = null)
    {
        if ($item) {
            $parentItem = $item->getParentItem();
            if ($parentItem) {
                $options = $parentItem->getProductOptions();
                if ($options) {
                    return (isset($options['shipment_type'])
                        && $options['shipment_type'] == 1);
                }
            } else {
                $options = $item->getProductOptions();
                if ($options) {
                    return !(isset($options['shipment_type'])
                        && $options['shipment_type'] == 1);
                }
            }
        }
        return false;
    }

    /**
     * @param mixed $item
     * @return mixed|null
     */
    public function getSelectionAttributes($item)
    {
        $options = $item->getProductOptions();
        if (isset($options['bundle_selection_attributes'])) {
            return unserialize($options['bundle_selection_attributes']);
        }
        return null;
    }

    /**
     * @param mixed $item
     * @return string
     */
    public function getValueHtml($item)
    {
        if ($attributes = $this->getSelectionAttributes($item)) {
            return sprintf('%d', $attributes['qty']) . ' x ' . $this->escapeHtml($item->getName()) . " "
                . $this->getOrder()->formatPrice($attributes['price']);
        } else {
            return $this->escapeHtml($item->getName());
        }
    }

    /**
     * @param mixed $item
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isChildCalculated($item = null)
    {
        if ($item) {
            $parentItem = $item->getParentItem();
            if ($parentItem) {
                $options = $parentItem->getProductOptions();
                if ($options) {
                    return (isset($options['product_calculations'])
                        && $options['product_calculations'] == 0);
                }
            } else {
                $options = $item->getProductOptions();
                if ($options) {
                    return !(isset($options['product_calculations'])
                        && $options['product_calculations'] == 0);
                }
            }
        }
        return false;
    }
}
