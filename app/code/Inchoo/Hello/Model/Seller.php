<?php
namespace Inchoo\Hello\Model;
use Inchoo\Hello\Api\SellerInterface;
 
class Seller implements SellerInterface
{
	protected $_objectManager;
	protected $_sellerlistCollectionFactory;
	protected $sellerList;
	protected $_productCollectionFactory;
	protected $_categoryFactory;
	
/**
	* @param \Magento\Framework\View\Element\Template\Context $context
	* @param \Magento\Catalog\Helper\Category $categoryHelper
	* @param array $data
	*/
	public function __construct(
		\Magento\Cms\Model\Template\FilterProvider $filterProvider,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Magento\Framework\View\Element\Template\Context $context,
		\Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $sellerlistCollectionFactory,
		\PHPCuong\Region\Model\ResourceModel\Region\CollectionFactory $regionCollection,
		\PHPCuong\Region\Model\ResourceModel\Area\CollectionFactory $areaCollection,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		array $data = []
	) {
		
		$this->_objectManager 				=   $objectManager;
		$this->_regionCollection 			= 	$regionCollection;
		$this->_areaCollection 				= 	$areaCollection;
		$this->_sellerlistCollectionFactory = $sellerlistCollectionFactory;
		$this->_productCollectionFactory = $productCollectionFactory;
		$this->_categoryFactory 			= 	$categoryFactory;
		
		
	
	}	

    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $region_id region_id.
     * @param string $area_id area_id
     * @param string $cat_id cat_id
     * @return string Greeting message with users name.
     */
    public function getSeller($region_id, $area_id, $cat_id) {
		
		$seller_arr 	= 	array();
		if($cat_id!=0){
			$category 	= 	$this->_categoryFactory->create()->load($cat_id);
			$collection = 	$this->_productCollectionFactory->create()->addAttributeToSelect(
				'entity_id'
			);
			$collection->addCategoryFilter($category);
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
				'area_id',
				['finset' => [$area_id]]
			)
			->addFieldToFilter(
				'region_id',
				['finset' => [$region_id]]
			)
			->addFieldToFilter(
				'is_seller',
				['eq' => 1]
			)
			->setOrder(
				'sequence',
				'asc'
			);
		}else{
			
			$collection = 	$this->_productCollectionFactory->create()->addAttributeToSelect(
				'entity_id'
			);
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
				'area_id',
				['finset' => [$area_id]]
			)
			->addFieldToFilter(
				'region_id',
				['finset' => [$region_id]]
			)
			->setOrder(
				'sequence',
				'asc'
			);
		}
		//echo "<pre>"; print_r($$collection->getdata()); exit;
		
		$store 				= 	$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
		$avtarImageUrl 		= 	$store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'avatar/';
		$this->sellerList 	= 	$collection->getdata();
		if(!empty($this->sellerList)){
			foreach($this->sellerList as &$seller){
				
				$seller['avtarurl']			=	$avtarImageUrl;
				if(!$seller['shop_title']){
					$customer_data 				= 	$this->_objectManager->create('Magento\Customer\Model\Customer')->load($seller['seller_id']);
					$seller['shop_title']		=	trim($customer_data->getData('firstname')." ".$customer_data->getData('lastname'));
				}
				if(isset($seller['backgroundcolor']) && strpos($seller['backgroundcolor'], "rgb") >-1 ){
					$seller['backgroundcolor']	=	$this->timberpress_rgb_to_hex($seller['backgroundcolor']);
				}
			}
		}
		return $this->sellerList;
    }
	
	/**
     * Returns greeting message to user
     *
     * @api
     * @param string $region_id region_id.
     * @param string $area_id area_id
     * @param string $cat_id cat_id
     * @return string Greeting message with users name.
     */
	function timberpress_rgb_to_hex( $color ) {
		$pattern = "/(\d{1,3})\,?\s?(\d{1,3})\,?\s?(\d{1,3})/";
		// Only if it's RGB
		if ( preg_match( $pattern, $color, $matches ) ) {
			$r[] = $matches[1];
			$r[] = $matches[2];
			$r[] = $matches[3];
			// $color = sprintf("#%02x%02x%02x", $r, $g, $b);
			if (is_array($r) && sizeof($r) == 3)
				list($r, $g, $b) = $r;

			$r = intval($r); $g = intval($g);
			$b = intval($b);

			$r = dechex($r<0?0:($r>255?255:$r));
			$g = dechex($g<0?0:($g>255?255:$g));
			$b = dechex($b<0?0:($b>255?255:$b));

			$color = (strlen($r) < 2?'0':'').$r;
			$color .= (strlen($g) < 2?'0':'').$g;
			$color .= (strlen($b) < 2?'0':'').$b;
			return '#'.$color;
		}
		return $color;
	}
}