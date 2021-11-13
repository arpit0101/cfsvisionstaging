<?php
namespace Inchoo\Helloworld\Block\Email\Order;

//use Inchoo\HelloWorld\Model\Data;
class DeliveryDate extends \Magento\Framework\View\Element\Template
{
	protected $_categoryHelper;
	protected $categoryFlatConfig;
	protected $topMenu;
	protected $categoryView;
	protected $_filterProvider;
	protected $_objectManager;
	protected $_sellerlistCollectionFactory;
	protected $sellerList;

	protected $_customerSession;
	/**
	* @param \Magento\Framework\View\Element\Template\Context $context
	* @param \Magento\Catalog\Helper\Category $categoryHelper
	* @param array $data
	*/
	public function __construct(
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\View\Element\Template\Context $context,	
		array $data = []
	) {
		$this->_objectManager = $objectManager;
		parent::__construct($context, $data);
	}
	
	/**
	* @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
	*/
	public function getDeliveryDate($order)
	{
		return $this->getOrderDeliveryDateHistory($order->getId());
	}
	
	public function getOrderDeliveryDateHistory($order_id){
		
		$resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('sales_order'); //gives table name with prefix
		$sql = "Select `delivery_date` FROM " . $tableName . " where `entity_id`= ".$order_id;
		
		$result = $connection->fetchAll($sql); // gives associated array, table fields as key in array.
		if(isset($result[0]['delivery_date'])){
			return $result[0]['delivery_date'];
		}else{
			return false;
		}
	}
}
