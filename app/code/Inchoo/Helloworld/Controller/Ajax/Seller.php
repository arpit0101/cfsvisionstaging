<?php
namespace Inchoo\Helloworld\Controller\Ajax;
use Magento\Framework\App\Action\Context;
class Seller extends \Magento\Framework\App\Action\Action
{
	protected $_resultPageFactory;
	protected $_sellerlistCollectionFactory;
	protected $_resultJsonFactory;
	protected $_productCollectionFactory;
	public function __construct(
	Context $context, 
	\Magento\Framework\View\Result\PageFactory $resultPageFactory,
	\Magento\Catalog\Model\CategoryFactory $categoryFactory,
	\Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $sellerlistCollectionFactory,
	\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
	\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
	array $data = []
	)
	{
		$this->_resultPageFactory 			= 	$resultPageFactory;
		$this->_categoryFactory 			= 	$categoryFactory;
		$this->_sellerlistCollectionFactory = 	$sellerlistCollectionFactory;
		$this->_resultJsonFactory 			= 	$resultJsonFactory;
		$this->_productCollectionFactory = $productCollectionFactory;
		parent::__construct($context);
	}
	public function getCategory($categoryfield,$type=null) 
	{
		$collection = $this->_categoryFactory->create()->getCollection()->addAttributeToFilter($type,$categoryfield)->setPageSize(1);
		if ($collection->getSize()) {
			$category = $collection->getFirstItem();	
		}
		return $category;
	}
	public function execute()
	{
		$seller_arr 	= 	array();
		$categoryId 	= 	$this->getRequest()->getParam('category', false);
		
		if($categoryId !=''){
			$category 	= 	$this->_categoryFactory->create()->load($categoryId);
			$collection = 	$this->_productCollectionFactory->create()->addAttributeToSelect(
				'entity_id'
			);
			$collection->addCategoryFilter($category);
			// $products 	= 	$collection->getData();
			$products 	= 	$collection;
			$product_ids	=	[];
			foreach ($products as $product) {
				array_push($product_ids, $product->getId());
			}
			$seller_product_coll = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
			->getCollection()
			->addFieldToFilter(
			'mageproduct_id',
				['in' => $product_ids]
			)
			->addFieldToSelect('seller_id')
			->distinct(true);
			
			foreach ($seller_product_coll as $product) {
				array_push($seller_arr, $product->getSellerId());
			}
			$collection = $this->_sellerlistCollectionFactory->create()->addFieldToSelect(
				'*'
			) 
			->addFieldToFilter(
				'seller_id',
				['in' => $seller_arr]
			)
			->addFieldToFilter(
				'is_seller',
				['eq' => 1]
			)
			->setOrder(
				'entity_id',
				'desc'
			);
			
		}else{	
		
			$seller_product_coll = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
			->getCollection()
			->addFieldToFilter(
				'status',
				['eq' => 1]
			)
			->addFieldToSelect('seller_id')
			->distinct(true);
			
			foreach ($seller_product_coll as $product) {
				array_push($seller_arr, $product->getSellerId());
			}
			
			$collection = $this->_sellerlistCollectionFactory->create()->addFieldToSelect(
				'*'
			)
			->addFieldToFilter(
				'seller_id',
				['in' => $seller_arr]
			)
			->addFieldToFilter(
				'is_seller',
				['eq' => 1]
			)
			->setOrder(
				'entity_id',
				'desc'
			);
			
		} 
		return  $this->_resultJsonFactory->create()->setData($collection->getData());
	}
}