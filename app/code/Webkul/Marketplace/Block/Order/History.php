<?php
namespace Webkul\Marketplace\Block\Order;
/**
 * Webkul Marketplace Order History Block
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software Private Limited
 *
 */
use Magento\Sales\Model\Order;
use Magento\Customer\Model\Customer;

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
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /** @var \Magento\Catalog\Model\Product */
    protected $salesOrderLists;

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
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory $orderCollectionFactory,
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->Customer = $customer;
        $this->Order = $order;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
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
	
	public function getOrderDeliveryDateHistory($order_id){
		
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
    

    /**
     * @return bool|\Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection
     */

    public function getManagerOrders()
	{	
	    $seller_id = $this->getCustomerId();
		$resource 		= 	$this->_objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection 	= 	$resource->getConnection();
		$tableName 		= 	$resource->getTableName('sales_order_address');
		$filter_orderstatus =	"";
		if(isset($_GET['orderstatus']) && $_GET['orderstatus']!=""){
			$filter_orderstatus = $_GET['orderstatus'] != ""?$_GET['orderstatus']:"";
			$sql = 	"SELECT marketplace_saleslist.entity_id as id FROM " . $tableName ." JOIN marketplace_saleslist ON (marketplace_saleslist.order_id=" . $tableName .".parent_id) JOIN sales_order ON (marketplace_saleslist.order_id=sales_order.entity_id AND status LIKE '".$filter_orderstatus."' ) WHERE seller_id =".$seller_id." AND date(marketplace_saleslist.created_at) > CURDATE() - interval 100 day  group by " . $tableName .".parent_id";
		}else{
			
			$sql = 	"SELECT marketplace_saleslist.entity_id as id FROM " . $tableName ." JOIN marketplace_saleslist ON (marketplace_saleslist.order_id=" . $tableName .".parent_id) WHERE seller_id =".$seller_id." AND date(marketplace_saleslist.created_at) > CURDATE() - interval 100 day  group by " . $tableName .".parent_id";
		}
		$result = $connection->fetchAll($sql);
		$orders =	array(); 		
		if(!empty($result)){
			foreach($result as $row){
				$orders[]	=	$row['id'];
			}
		}
		return $orders;	
	}
	
    public function getAllSalesOrder()
    {
        if (!($customerId = $this->getCustomerId())) {
            return false;
        }
        if (!$this->salesOrderLists) {
            $ids = array();
            $orderids = array();
            $filter_orderid = '';
            $filter_orderstatus = '';
            $filter_data_to = '';
            $filter_data_frm = '';
            $from = null;
            $to = null;

            if(isset($_GET['s'])){
                $filter_orderid = $_GET['s'] != ""?$_GET['s']:"";
            }
            if(isset($_GET['orderstatus'])){
                $filter_orderstatus = $_GET['orderstatus'] != ""?$_GET['orderstatus']:"";
            }
            if(isset($_GET['from_date'])){
                $filter_data_frm = $_GET['from_date'] != ""?$_GET['from_date']:"";
            }
            if(isset($_GET['to_date'])){
                $filter_data_to = $_GET['to_date'] != ""?$_GET['to_date']:"";
            }
            $orderids = $this->getManagerOrders();
            $collection = $this->_orderCollectionFactory->create()->addFieldToSelect(
                '*'
            )
            ->addFieldToFilter(
                'entity_id',
                ['in' => $orderids]
            );
			
			/* $joinConditions = "so.entity_id = main_table.order_id and so.status!='canceled'";
            $collection->getSelect()->join(
                ['so' => $collection->getTable('sales_order')],
                $joinConditions,
                []
            ); */

            if($filter_data_to){
                $todate = date_create($filter_data_to);
                $to = date_format($todate, 'Y-m-d 23:59:59');
            }
            if($filter_data_frm){
                $fromdate = date_create($filter_data_frm);
                $from = date_format($fromdate, 'Y-m-d H:i:s');
            }

            if($filter_orderid){
                $collection->addFieldToFilter(
                    'main_table.magerealorder_id',
                    ['eq' => $filter_orderid]
                );
            }

            $collection->addFieldToFilter(
                'main_table.created_at',
                ['datetime' => true,'from' => $from,'to' =>  $to]
            ); 
			
			
            $collection->setOrder(
                'created_at',
                'desc'
            );
			//$collection->getSellerOrderCollection();
            $this->salesOrderLists = $collection;
        }
        return $this->salesOrderLists;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllSalesOrder()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'marketplace.dashboard.pager'
            )
            ->setCollection(
                $this->getAllSalesOrder()
            );
            $this->setChild('pager', $pager);
            $this->getAllSalesOrder()->load();
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

    
    public function getpronamebyorder($order_id)
    {
        $seller_id = $this->getCustomerId();
        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                            ->getCollection()
                            ->addFieldToFilter(
                                'seller_id',
                                ['eq' => $seller_id]
                            )
                            ->addFieldToFilter(
                                'order_id',
                                ['eq' => $order_id]
                            );
        $name='';
        foreach($collection as $res){
            $products = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($res['mageproduct_id']);
            $name .= "<p style='float:left;'><a href='javascript:void(0)' target='blank'>".$res['magepro_name']."</a> X ".intval($res['magequantity'])."&nbsp;</p>";
        }   
        return $name;       
    }

    public function getPricebyorder($order_id)
    {
        $seller_id = $this->getCustomerId();
        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                    ->getCollection();
        $name='';
        $collection->getSelect()
                    ->where('seller_id ='.$seller_id)
                    ->columns('SUM(actual_seller_amount) AS qty')
                    ->group('order_id');     
        foreach($collection as $coll){
            if($coll->getOrderId() == $order_id){
                return $coll->getQty();
            }
        }
    }
	
	public function getInvoiceDetails($order_id)
    {
		
		/** @var \Magento\Sales\Model\Order $order */
		$order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($order_id); 
		if($order->hasInvoices()){
			return $order->getInvoiceCollection()->getData();
		}
		return false;
    }
	
	public function getShipmentsDetails($order_id)
    {
		
		/** @var \Magento\Sales\Model\Order $order */
		$order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($order_id); 
		if($order->hasShipments()){
			
			return $order->getShipmentsCollection()->getData();
		}
		return false;
    }
}
