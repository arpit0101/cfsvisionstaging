<?php
namespace Webkul\Marketplace\Block\Account;
/**
 * Webkul Marketplace Account Dashboard Block
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software Private Limited
 *
 */
use Magento\Sales\Model\Order;
use Magento\Customer\Model\Customer;

class Dashboard extends \Magento\Framework\View\Element\Template
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
        array $data = []
    ) {
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
        $this->pageConfig->getTitle()->set(__('Seller Dashboard'));
    }

    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }

    /**
     * @return bool|\Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection
     */

    public function getCollection()
    {
        if (!($customerId = $this->getCustomerId())) {
            return false;
        }

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

        $collection_orders = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                            ->getCollection()
                            ->addFieldToFilter(
                                'seller_id',
                                ['eq' => $customerId]
                            )
							->setPageSize(5)
                            ->distinct(true);
		if($filter_data_to){
            $todate = date_create($filter_data_to);
            $to = date_format($todate, 'Y-m-d 23:59:59');
        }
        if($filter_data_frm){
            $fromdate = date_create($filter_data_frm);
            $from = date_format($fromdate, 'Y-m-d H:i:s');
        }
        if($filter_orderid){
            $collection_orders->addFieldToFilter(
                'magerealorder_id',
                ['eq' => $filter_orderid]
            );
        }
        $collection_orders->setOrder(
            'created_at',
            'desc'
        );
		$collection_orders->addFieldToFilter(
            'created_at',
            ['datetime' => true,'from' => $from,'to' =>  $to]
        );
        foreach ($collection_orders as $collection_order) {
			$autoid = $collection_order->getId();
            if($filter_orderstatus){
                $tracking=$this->_objectManager->create('Webkul\Marketplace\Helper\Orders')->getOrderinfo($collection_order->getOrderId());
                if($tracking){
                    if($tracking->getIsCanceled()){
                        if($filter_orderstatus=='canceled'){
                            array_push($orderids, $collection_order->getOrderId());
                        }
                    }else{
                        $tracking = $this->_objectManager->create('Magento\Sales\Model\Order')->load($collection_order->getOrderId());                
                        if($tracking->getStatus()==$filter_orderstatus){
                            array_push($orderids, $collection_order->getOrderId());
                        }
                    }
                }
				array_push($ids, $autoid);
            }else{
                array_push($orderids, $collection_order->getOrderId());
				array_push($ids, $autoid);
            }
        }
		//echo '<pre>'; print_r($collection_orders->getData()); die("test");
		return $collection_orders;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    public function getDateDetail()
    { 
        $seller_id = $this->getCustomerId();
		$canceledorders 	= 	$this->_objectManager->create('Magento\Sales\Model\Order')->getCollection()->addAttributeToFilter('state', 'canceled');
		
		$all_canceled_order	=	[];
		if($canceledorders){
			foreach($canceledorders as $orderdata){
				$all_canceled_order[]	=	$orderdata->getId();
			}
		}
        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                            ->getCollection()
                            ->addFieldToFilter(
                                'seller_id',
                                ['eq' => $seller_id]
                            )
                            ->addFieldToFilter(
                                'order_id',
                                ['neq' => 0]
                            )->addFieldToFilter(
                                'order_id',
                                ['nin' => $all_canceled_order]
                            );
        $collection1 = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                            ->getCollection()
                            ->addFieldToFilter(
                                'seller_id',
                                ['eq' => $seller_id]
                            )
                            ->addFieldToFilter(
                                'order_id',
                                ['neq' => 0]
                            )->addFieldToFilter(
                                'order_id',
                                ['nin' => $all_canceled_order]
                            );
        $collection2 = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                            ->getCollection()
                            ->addFieldToFilter(
                                'seller_id',
                                ['eq' => $seller_id]
                            )
                            ->addFieldToFilter(
                                'order_id',
                                ['neq' => 0]
                            )->addFieldToFilter(
                                'order_id',
                                ['nin' => $all_canceled_order]
                            );
        $collection3 = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                            ->getCollection()
                            ->addFieldToFilter(
                                'seller_id',
                                ['eq' => $seller_id]
                            )
                            ->addFieldToFilter(
                                'order_id',
                                ['neq' => 0]
                            )->addFieldToFilter(
                                'order_id',
                                ['nin' => $all_canceled_order]
                            );

        $first_day_of_week = date('Y-m-d', strtotime('Last Monday', time()));

        $last_day_of_week = date('Y-m-d', strtotime('Next Sunday', time()));

        $month=$collection1->addFieldToFilter('created_at', array('datetime' => true,'from' =>  date('Y-m').'-01 00:00:00','to' =>  date('Y-m').'-31 23:59:59'));

        $week=$collection2->addFieldToFilter('created_at', array('datetime' => true,'from' =>  $first_day_of_week.' 00:00:00','to' =>  $last_day_of_week.' 23:59:59'));

        $day=$collection3->addFieldToFilter('created_at', array('datetime' => true,'from' =>  date('Y-m-d').' 00:00:00','to' =>  date('Y-m-d').' 23:59:59'));

        $sale=0;

        $data1['year']=$sale;

        $sale1=0;
        foreach($day as $record1) {
            $sale1=$sale1+$record1->getActualSellerAmount();
        }
        $data1['day']=$sale1;

        $sale2=0;
        foreach($month as $record2) {
            $sale2=$sale2+$record2->getActualSellerAmount();
        }
        $data1['month']=$sale2;

        $sale3=0;
        foreach($week as $record3) {
            $sale3=$sale3+$record3->getActualSellerAmount();
        }
        $data1['week']=$sale3;

        $temp=0;
        foreach ($collection as $record) {
            $temp = $temp+$record->getActualSellerAmount();
        }
        $data1['totalamount']=$temp;
        return $data1;
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
            $name .= "<p style='float:left;'><a href='javascript:void(0);' target='blank'>".$res['magepro_name']."</a> X ".intval($res['magequantity'])."&nbsp;</p>";
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
    

    public function getTotalSaleColl($value='')
    {
		$seller_id = $this->getCustomerId();
        //echo $seller_id; die("tgtgt");
        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleperpartner')
        ->getCollection()
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $seller_id]
        );
		return $collection;
    }

    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl(); // Give the current url of recently viewed page
    }

    public function getReviewcollection($value='')
    {
        $seller_id = $this->getCustomerId();

        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Feedback')
        ->getCollection()
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $seller_id]
        )
        ->addFieldToFilter(
            'status',
            ['eq' => 1]
        )
        ->setOrder(
            'created_at',
            'desc'
        )
        ->setPageSize(5)
        ->setCurPage(1);

        return $collection;
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
