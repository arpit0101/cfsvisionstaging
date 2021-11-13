<?php
namespace Inchoo\Hello\Model;
use Inchoo\Hello\Api\WishlistInterface;
use Magento\Webapi\Model\Authorization\TokenUserContext;
use Magento\Wishlist\Controller\WishlistProvider;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Helper\ImageFactory as ProductImageHelper;
use Magento\Store\Model\App\Emulation as AppEmulation;
 
class Wishlist implements WishlistInterface
{
	/**
     * @var CollectionFactory
     */
    protected $_wishlistCollectionFactory;

    /**
     * Wishlist item collection
     *
     * @var \Magento\Wishlist\Model\ResourceModel\Item\Collection
     */
    protected $_itemCollection;

    /**
     * @var WishlistRepository
     */
    protected $_wishlistRepository;

    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    /**
     * @var WishlistFactory
     */
    protected $_wishlistFactory;

    /**
     * @var Item
     */
    protected $_itemFactory;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     *@var \Magento\Catalog\Helper\ImageFactory
     */
    protected $productImageHelper;

    /**
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storemanagerinterface;

    /**
     *@var \Magento\Store\Model\App\Emulation
     */
    protected $appEmulation;

    /**
     *@var \Magento\Catalog\Model\Product
     */
    protected $_productload;

    /**
     *@var \Magento\Directory\Model\CountryFactory
     */
    protected $countryfactory;
    protected $_context;

    /**
     * @param CollectionFactory $wishlistCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        CollectionFactory $wishlistCollectionFactory,
        WishlistFactory $wishlistFactory,
        \Magento\Customer\Model\Customer $customer,
        AppEmulation $appEmulation,
        \Magento\Directory\Model\CountryFactory $countryfactory,
        \Magento\Store\Model\StoreManagerInterface $storemanagerinterface,
        ProductImageHelper $productImageHelper,
        \Magento\Catalog\Model\Product $productload,
        \Magento\Wishlist\Model\WishlistFactory $wishlistRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Wishlist\Model\ItemFactory $itemFactory,
		TokenUserContext $context
    ) {
        $this->_wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->_wishlistRepository = $wishlistRepository;
        $this->_productRepository = $productRepository;
        $this->_wishlistFactory = $wishlistFactory;
        $this->countryfactory = $countryfactory;
        $this->storemanagerinterface = $storemanagerinterface;
        $this->_itemFactory = $itemFactory;
        $this->_customer = $customer;
        $this->_productload = $productload;
        $this->appEmulation = $appEmulation;
        $this->productImageHelper = $productImageHelper;
        $this->_customer = $customer;
		$this->_context	=	$context;
    }

    /**
     * Get wishlist collection
     * @deprecated
     * @param $customerId
     * @return WishlistData
     */
    public function getWishlistForCustomer()
    {
		
        $customerId		=	$this->_context->getUserId();
		if(empty($customerId)){
			return [["status"=>false,"msg"=>"Invalid user token.","msg_ar"=>"رمز المستخدم غير صالح."]];
		}else {
            $collection =
                $this->_wishlistCollectionFactory->create()
                    ->addCustomerIdFilter($customerId);
            $baseurl = $this->storemanagerinterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product';
            $wishlistData = [];
            foreach ($collection as $item) {
                $productInfo = $item->getProduct()->toArray();
				//echo "<pre>"; print_r($productInfo); 
				if(isset($productInfo['small_image']))
				{
					if ($productInfo['small_image'] == 'no_selection') {
					  $currentproduct = $this->_productload->load($productInfo['entity_id']);
					  $imageURL = $this->getImageUrl($currentproduct, 'product_base_image');
					  $productInfo['small_image'] = ltrim($imageURL, "/");
					  $productInfo['thumbnail'] = ltrim($imageURL, "/");
				}else{
                  $imageURL = $productInfo['small_image'];
                  $productInfo['small_image'] = ltrim($imageURL, "/");
                  $productInfo['thumbnail'] = ltrim($imageURL, "/");
                }
				}
                $data = [
                    "wishlist_item_id" => $item->getWishlistItemId(),
                    "wishlist_id"      => $item->getWishlistId(),
                    "product_id"       => $item->getProductId(),
                    "store_id"         => $item->getStoreId(),
                    "added_at"         => $item->getAddedAt(),
                    "description"      => $item->getDescription(),
                    "qty"              => round($item->getQty()),
                    "product"          => $productInfo
                ];
                $wishlistData[] = $data;
            }
			//exit;
            return $wishlistData;
        }
    }

    /**
     * Add wishlist item for the customer
     * @param int $productIdId
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addWishlistForCustomer($product_id)
    {
		$customerId		=	$this->_context->getUserId();
		if(empty($customerId)){
			return [["status"=>false,"msg"=>"Invalid user token.","msg_ar"=>"رمز المستخدم غير صالح."]];
		}
        if ($product_id == null) {
            throw new LocalizedException(__
            ('Invalid product, Please select a valid product'));
			return [["status"=>false,"msg"=>"Invalid product, Please select a valid product.","msg_ar"=>"منتج غير صالح ، يرجى تحديد منتج صالح."]];
        }
        try {
            $product = $this->_productRepository->getById($product_id);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }
        try {
            $wishlist = $this->_wishlistRepository->create()->loadByCustomerId
            ($customerId, true);
            $wishlist->addNewItem($product);
            $returnData = $wishlist->save();
        } catch (NoSuchEntityException $e) {

        }
        return [["status"=>true,"msg"=>"Successfully added in wishlist.","msg_ar"=>"تم الإضافة للأمنيات"]];
    }
	
	/**
     * Remove wishlist item for the customer
     * @param int $productIdId
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function removeWishlistForCustomer($product_id)
    {
		$customerId		=	$this->_context->getUserId();
		if(empty($customerId)){
			return [["status"=>false,"msg"=>"Invalid user token.","msg_ar"=>"رمز المستخدم غير صالح."]];
		}
        if ($product_id == null) {
            throw new LocalizedException(__
            ('Invalid product, Please select a valid product'));
			return [["status"=>false,"msg"=>"Invalid product, Please select a valid product.","msg_ar"=>"منتج غير صالح ، يرجى تحديد منتج صالح."]];
        }
        try {
            $product = $this->_productRepository->getById($product_id);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }
        try {
            $wishlist = $this->_wishlistRepository->create()->loadByCustomerId
            ($customerId, true);
            $items = $wishlist->getItemCollection();
			foreach ($items as $item) 
			{
				if ($item->getProductId() == $product_id) 
				{
					$item->delete();
					$wishlist->save();   
					return [["status"=>true,"msg"=>'You have successfully remove item from wishlist.', "msg_ar"=>'لقد نجحت في إزالة عنصر من قائمة الأمنيات.']];
				}
			}
        } catch (NoSuchEntityException $e) {
			return [["status"=>false,"msg"=>$e->getMessage(),"msg_ar"=>$e->getMessage()]];
        }
        
    }    

    /**
     * Helper function that provides full cache image url
     * @param \Magento\Catalog\Model\Product
     * @return string
     */
    public function getImageUrl($product, string $imageType = ''){
        $storeId = $this->storemanagerinterface->getStore()->getId();
        $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
        $imageUrl = $this->productImageHelper->create()->init($product, $imageType)->getUrl();
        $this->appEmulation->stopEnvironmentEmulation();

        return $imageUrl;
    }
	
	/**
     * Get wishlist collection
     * @deprecated
     * @param $user_id
     * @return WishlistData
     */
    public function getWishlistByUserId($user_id)
    {
		$collection =
			$this->_wishlistCollectionFactory->create()
				->addCustomerIdFilter($user_id);
		$baseurl = $this->storemanagerinterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product';
		$wishlistData = [];
		foreach ($collection as $item) {
			$wishlistData[] = $item->getProductId();
		}
		return $wishlistData;
    }
}