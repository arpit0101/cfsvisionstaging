<?php
namespace Inchoo\Helloworld\Block;
//use Inchoo\HelloWorld\Model\Data;
class ProductSearch extends \Magento\Framework\View\Element\Template
{
	protected $_productCollectionFactory;
	protected $_objectManager;
	protected $_customerSession;
	protected $_categoryCollectionFactory;
	public $imageHelperFactory;
	/**
	* @param \Magento\Framework\View\Element\Template\Context $context
	* @param \Magento\Catalog\Helper\Category $categoryHelper
	* @param array $data
	*/
	public function __construct(
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
		\Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
		\Magento\Customer\Model\Session $customerSession,
		array $data = []
	) {
		$this->_productCollectionFactory 	= 	$productCollectionFactory;
		$this->_objectManager 				= 	$objectManager;
		$this->_categoryCollectionFactory 	= 	$categoryCollectionFactory;
		$this->imageHelperFactory 	= 	$imageHelperFactory;
		$this->_customerSession 			= 	$customerSession;
		parent::__construct($context, $data);
	
	}
	
	/**
	* @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
	*/
	public function getSearchData()
	{
			if($this->_customerSession->getCityId()){
				$city_id 	= 	$this->_customerSession->getCityId();
			}
			if($this->_customerSession->getAreaId()){
				$area_id 	= 	$this->_customerSession->getAreaId();
			}
			$search_term 	= 	$this->getRequest()->getParam('search_term', false);
			$seller_id 		= 	$this->getRequest()->getParam('seller_id', false);
			$querydata 		= 	$this->_objectManager->create('Webkul\Marketplace\Model\Product')->getCollection()
								->addFieldToFilter(
									'seller_id',
									['eq' => $seller_id]
								)
								->addFieldToFilter(
									'status',
									['eq' =>1]
								)
								->addFieldToSelect('mageproduct_id')
								->setOrder('mageproduct_id');
			
			$collection = $this->_productCollectionFactory->create()->addAttributeToSelect('*');
			$collection->addAttributeToFilter('entity_id', array('in' => $querydata->getData()));
			$collection->addFieldToFilter('visibility', array('in', [2, 4]));
			$collection->addAttributeToFilter('name', array('like' => '%'.$search_term.'%'));
			//echo $collection->getSize(); die("testt");
			if($collection->getSize() != 0){
			    $all_products	=	$collection;
				return $all_products;	
			}else{
				$collection = $this->_productCollectionFactory->create()->addAttributeToSelect('*');
			    $collection->addAttributeToFilter('entity_id', array('in' => $querydata->getData()));
			    $collection->addFieldToFilter('visibility', array('in', [2, 4]));
			    $collection->addAttributeToFilter('sku', array('like' => '%'.$search_term.'%'));
				$all_products	=	$collection;
				return $all_products;
			}
			
	}
}