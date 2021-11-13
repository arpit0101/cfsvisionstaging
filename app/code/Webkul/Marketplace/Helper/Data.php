<?php
namespace Webkul\Marketplace\Helper;
/**
 * Marketplace data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var null|array
     */
    protected $options;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    protected $_resourceFactory;

    /**
    * @param Magento\Framework\App\Helper\Context $context
    * @param Magento\Directory\Model\Currency $currency
    * @param Magento\Customer\Model\Session $customerSession
    * @param Magento\Framework\UrlInterface $url
    * @param Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $collectionFactory
    * @param Magento\Catalog\Model\ResourceModel\Product $product
    * @param Magento\Store\Model\StoreManagerInterface $_storeManager
    * @param Magento\Directory\Model\Currency $currency
    * @param Magento\Framework\Locale\CurrencyInterface $localeCurrency,
    */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $product,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $resourceFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->_resourceFactory = $resourceFactory;
        $this->customerSession = $customerSession;
        $this->collectionFactory = $collectionFactory;
        $this->product = $product;
        parent::__construct($context);
        $this->_currency = $currency;
        $this->_localeCurrency = $localeCurrency;
        $this->_storeManager = $storeManager;
    }

    /**
     * get featured product collection
     */
    public function getBestsellerProducts(){
        
        $resourceCollection = $this->_resourceFactory->create('Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection');
        
        return $resourceCollection;
    }
    /**
     * Return the Customer seller status
     *
     * @return \Webkul\Marketplace\Api\Data\SellerInterface
     */    
    public function isSeller()
    {
        $seller_status = 0;
        $model = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                    ->getCollection()
                    ->addFieldToFilter('seller_id',$this->customerSession->getCustomerId());
        foreach ($model as $value) {
            $seller_status = $value->getIsSeller();
        }
        
        //return $seller_status;
        return $model->getSize();
    }

    public function isSellerPresent()
    {
        $seller_status = 0;
        $model = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                    ->getCollection()
                    ->addFieldToFilter('seller_id',$this->customerSession->getCustomerId());
        
        return $model->getSize();
    }


    public function isRightSeller($product_id='')
    {
        $data = 0;
        $model = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
                    ->getCollection()
                    ->addFieldToFilter('mageproduct_id',$product_id)
                    ->addFieldToFilter('seller_id',$this->customerSession->getCustomerId());
        foreach ($model as $value) {
            $data = 1;
        }
        return $data;
    }

    /**
     * Return the Customer seller status
     *
     * @return \Webkul\Marketplace\Api\Data\SellerInterface
     */    
    public function getSellerData()
    {
        $model = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                    ->getCollection()
                    ->addFieldToFilter('seller_id',$this->customerSession->getCustomerId());
        return $model;
    }

    /**
     * Return the Customer seller status
     *
     * @return \Webkul\Marketplace\Api\Data\SellerInterface
     */    
    public function getSellerProductData()
    {
        $model = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
                    ->getCollection()
                    ->addFieldToFilter('seller_id',$this->customerSession->getCustomerId());
        return $model;
    }

    /**
     * Return the Customer seller status
     *
     * @return \Webkul\Marketplace\Api\Data\SellerInterface
     */    
    public function getSellerProductDataByProductId($product_id='')
    {
        $model = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
                    ->getCollection()
                    ->addFieldToFilter('mageproduct_id',$product_id);
        return $model;
    }

    /**
     * Return the Customer seller data by seller id
     *
     * @return \Webkul\Marketplace\Api\Data\SellerInterface
     */    
    public function getSellerDataBySellerId($seller_id='')
    {
        $model = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                    ->getCollection()
                    ->addFieldToFilter('seller_id',$seller_id);
        return $model;
    }

    public function getRootCategoryIdByStoreId($storeId='')
    {
        return $this->_storeManager->getStore($storeId)->getRootCategoryId(); 
    }

    public function getAllStores()
    {
        return $this->_storeManager->getStores();
    }

    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getStoreId(); // give the current stor id
    }

    public function setCurrentStore($storeId)
    {
        return $this->_storeManager->setCurrentStore($storeId);
    }

    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode(); // give the currency code
    }

    public function getBaseCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }

    public function getConfigAllowCurrencies()
    {
        return $this->_currency->getConfigAllowCurrencies();
    }    

    /**
     * Retrieve currency rates to other currencies
     *
     * @param string $currency
     * @param array|null $toCurrencies
     * @return array
     */
    public function getCurrencyRates($currency, $toCurrencies = null)
    {
        return $this->_currency->getCurrencyRates($currency, $toCurrencies); // give the currency rate
    }

    public function getCurrencySymbol()
    {
        return $this->_localeCurrency->getCurrency($this->getBaseCurrencyCode())->getSymbol();
    }

    /**
     * @return array|null
     */
    public function getAllowedSets()
    {
        if (null == $this->options) {
            $this->options = $this->collectionFactory->create()
                ->addFieldToFilter('attribute_set_id',array('in'=>explode(',',$this->getAllowedAttributesetIds())))
                ->setEntityTypeFilter($this->product->getTypeId())
                ->toOptionArray();
        }
        return $this->options;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function getAllowedProductTypes()
    {        
        $alloweds=explode(',',$this->getAllowedProductType());
        $data =  array('simple'=>__('Simple'),
                        'downloadable'=>__('Downloadable'),
                        'virtual'=>__('Virtual'),
                        'configurable'=>__('Configurable'),
                        'grouped'=>__('Grouped Product'),
                        'bundle'=>__('Bundle Product')
            );
        $allowedproducts=array();
        //$allowedproducts='';
        if(isset($alloweds)){
            foreach($alloweds as $allowed){
                if(!empty($data[$allowed])){
                    array_push($allowedproducts,array('value'=>$allowed, 'label'=>$data[$allowed]));
                    //array_push($allowedproducts,$allowed);

                }
            }
        }
        //echo "<pre>";print_r($allowedproducts);exit;
        return $allowedproducts;
    }

    /**
     * Return the product visibilty options
     *
     * @return \Magento\Tax\Model\ClassModel
     */    
    public function getTaxClassModel()
    {
        return $this->_objectManager->create('Magento\Tax\Model\ClassModel')->getCollection()
                    ->addFieldToFilter('class_type',array('eq'=>'PRODUCT'));
    }

    /**
     * Return the product visibilty options
     *
     * @return \Magento\Catalog\Model\Product\Visibility
     */    
    public function getVisibilityOptionArray()
    {
        return $this->_objectManager->create('Magento\Catalog\Model\Product\Visibility')->getOptionArray();
    }

    /**
     * Return the Seller existing status
     *
     * @return \Webkul\Marketplace\Api\Data\SellerInterface
     */    
    public function isSellerExist()
    {
        $model = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                    ->getCollection()
                    ->addFieldToFilter('seller_id',$this->customerSession->getCustomerId());       
        return count($model);
    }


    /**
     * Return the Customer given the customer Id stored in the session.
     *
     * @return \Webkul\Marketplace\Api\Data\SellerInterface
     */
    public function getSeller()
    {
        $data = array();
        $bannerpic='';
        $logopic='';
        $countrylogopic='';
        $model = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                    ->getCollection()
                    ->addFieldToFilter('seller_id',$this->customerSession->getCustomerId());
        foreach ($model as $value) {
            $data = $value->getData();
            $bannerpic=$value->getBannerPic();
            $logopic=$value->getLogoPic();
            $countrylogopic=$value->getCountryPic();
            if(strlen($bannerpic)<=0){$bannerpic='banner-image.png';}
            if(strlen($logopic)<=0){$logopic='noimage.png';}
            if(strlen($countrylogopic)<=0){$countrylogopic='';}
        }
        $data['banner_pic'] = $bannerpic;
        $data['logo_pic'] = $logopic;
        $data['country_pic'] = $countrylogopic;
        return $data;
    }

    public function getFeedTotal($seller_id)
    {
        $data = array();
        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Feedback')
                        ->getCollection()
                        ->addFieldToFilter('seller_id',array('eq'=>$seller_id));
        $collection->addFieldToFilter('status',array('neq'=>0));
        $price=0;
        $value=0;
        $quality=0;
        $totalfeed=0;
        $feed_count = 0;
        $collection_count = 1;
        foreach($collection as $record) {
            $price+=$record->getFeedPrice();
            $value+=$record->getFeedValue();
            $quality+=$record->getFeedQuality();
        }
        if(count($collection)!=0)
        {
            $feed_count = count($collection);
            $collection_count = count($collection);
            $totalfeed=ceil(($price+$value+$quality)/(3*$collection_count));
        }
        
        $data=array('price'=>$price/$collection_count,'value'=>$value/$collection_count,'quality'=>$quality/$collection_count,'totalfeed'=>$totalfeed, 'feedcount'=>$feed_count);
        return $data;
    }

    public function getSelleRating($seller_id)
    {
        $feeds = $this->getFeedTotal($seller_id);
        $total_rating = ($feeds['price']+$feeds['value']+$feeds['quality'])/60;     
        return round($total_rating, 1, PHP_ROUND_HALF_UP);
    }

    public function getCatatlogGridPerPageValues(){
        return $this->scopeConfig->getValue('catalog/frontend/grid_per_page_values', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);     
    }
    
    public function getCaptchaEnable()
    {
        return $this->scopeConfig->getValue('marketplace/general_settings/captcha', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getDefaultTransEmailId()
    {
        return $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getAdminEmailId()
    {
        return $this->scopeConfig->getValue('marketplace/general_settings/adminemail', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getAllowedCategoryIds()
    {
        return $this->scopeConfig->getValue('marketplace/product_settings/categoryids', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getIsProductEditApproval()
    {
        return $this->scopeConfig->getValue('marketplace/product_settings/product_edit_approval', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getIsPartnerApproval(){
        return $this->scopeConfig->getValue('marketplace/general_settings/seller_approval', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getIsProductApproval()
    {
        return $this->scopeConfig->getValue('marketplace/product_settings/product_approval', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getAllowedAttributesetIds(){
        return $this->scopeConfig->getValue('marketplace/product_settings/attributesetid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getAllowedProductType()
    {
        return $this->scopeConfig->getValue('marketplace/product_settings/allow_for_seller', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getUseCommissionRule(){
        return $this->scopeConfig->getValue('mpadvancecommission/mpadvancecommission_options/usecommissionrule', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }   

    public function getCommissionType(){
        return $this->scopeConfig->getValue('mpadvancecommission/mpadvancecommission_options/commissiontype', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);       
    }

    public function getIsOrderManage()
    {
        return $this->scopeConfig->getValue('marketplace/general_settings/order_manage', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getConfigCommissionRate()
    {
        return $this->scopeConfig->getValue('marketplace/general_settings/percent', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getConfigTaxManage()
    {
        return $this->scopeConfig->getValue('marketplace/general_settings/tax_manage', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getlowStockNotification()
    {
        return $this->scopeConfig->getValue('marketplace/inventory_settings/low_stock_notification', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getlowStockQty()
    {
        return $this->scopeConfig->getValue('marketplace/inventory_settings/low_stock_amount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getActiveColorPicker()
    {
		//echo 123;	
        return $this->scopeConfig->getValue('marketplace/profile_settings/activecolorpicker', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSellerPolicyApproval()
    {
        return $this->scopeConfig->getValue('marketplace/profile_settings/seller_policy_approval', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getUrlRewrite()
    {
        return $this->scopeConfig->getValue('marketplace/profile_settings/url_rewrite', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getReviewStatus()
    {
        return $this->scopeConfig->getValue('marketplace/review_settings/review_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getMarketplaceHeadLabel()
    {
        return $this->scopeConfig->getValue('marketplace/landingpage_settings/marketplacelabel', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getMarketplacelabel1()
    {
        return $this->scopeConfig->getValue('marketplace/landingpage_settings/marketplacelabel1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getMarketplacelabel2()
    {
        return $this->scopeConfig->getValue('marketplace/landingpage_settings/marketplacelabel2', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getMarketplacelabel3()
    {
        return $this->scopeConfig->getValue('marketplace/landingpage_settings/marketplacelabel3', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getMarketplacelabel4()
    {
        return $this->scopeConfig->getValue('marketplace/landingpage_settings/marketplacelabel4', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getDisplayBanner()
    {
        return $this->scopeConfig->getValue('marketplace/landingpage_settings/displaybanner', \Magento\Store\Model\ScopeInterface::SCOPE_STORE); 
    }
    
    public function getBannerImage()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)."marketplace/banner/".$this->scopeConfig->getValue("marketplace/landingpage_settings/banner", \Magento\Store\Model\ScopeInterface::SCOPE_STORE); 
    }
    
    public function getBannerContent()
    {
        return  $this->scopeConfig->getValue('marketplace/landingpage_settings/bannercontent', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getDisplayIcon()
    {
        return  $this->scopeConfig->getValue('marketplace/landingpage_settings/displayicons', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getIconImage1()
    {
        return  $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)."marketplace/icon/".$this->scopeConfig->getValue("marketplace/landingpage_settings/feature_icon1", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getIconImageLabel1()
    {
        return  $this->scopeConfig->getValue('marketplace/landingpage_settings/feature_icon1_label', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getIconImage2()
    {
        return  $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)."marketplace/icon/".$this->scopeConfig->getValue("marketplace/landingpage_settings/feature_icon2", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getIconImageLabel2()
    {
        return  $this->scopeConfig->getValue('marketplace/landingpage_settings/feature_icon2_label', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getIconImage3()
    {
        return  $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)."marketplace/icon/".$this->scopeConfig->getValue("marketplace/landingpage_settings/feature_icon3", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getIconImageLabel3()
    {
        return  $this->scopeConfig->getValue('marketplace/landingpage_settings/feature_icon3_label', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getIconImage4()
    {
        return  $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)."marketplace/icon/".$this->scopeConfig->getValue("marketplace/landingpage_settings/feature_icon4", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getIconImageLabel4()
    {
        return  $this->scopeConfig->getValue('marketplace/landingpage_settings/feature_icon4_label', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getMarketplacebutton()
    {
        return  $this->scopeConfig->getValue('marketplace/landingpage_settings/marketplacebutton', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getMarketplaceprofile()
    {
        return  $this->scopeConfig->getValue('marketplace/landingpage_settings/marketplaceprofile', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSellerlisttopLabel()
    {
        return  $this->scopeConfig->getValue('marketplace/landingpage_settings/sellerlisttop', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSellerlistbottomLabel()
    {
        return  $this->scopeConfig->getValue('marketplace/landingpage_settings/sellerlistbottom', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductHintStatus()
    {
        return  $this->scopeConfig->getValue('marketplace/producthint_settings/product_hint_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductHintCategory()
    {
        return  $this->scopeConfig->getValue('marketplace/producthint_settings/product_category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductHintName()
    {
        return  $this->scopeConfig->getValue('marketplace/producthint_settings/product_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductHintDesc()
    {
        return  $this->scopeConfig->getValue('marketplace/producthint_settings/product_des', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductHintShortDesc()
    {
        return  $this->scopeConfig->getValue('marketplace/producthint_settings/product_sdes', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductHintSku()
    {
        return  $this->scopeConfig->getValue('marketplace/producthint_settings/product_sku', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductHintPrice()
    {
        return  $this->scopeConfig->getValue('marketplace/producthint_settings/product_price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductHintSpecialPrice()
    {
        return  $this->scopeConfig->getValue('marketplace/producthint_settings/product_sprice', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductHintStartDate()
    {
        return  $this->scopeConfig->getValue('marketplace/producthint_settings/product_sdate', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductHintEndDate()
    {
        return  $this->scopeConfig->getValue('marketplace/producthint_settings/product_edate', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductHintQty()
    {
        return  $this->scopeConfig->getValue('marketplace/producthint_settings/product_qty', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductHintStock()
    {
        return  $this->scopeConfig->getValue('marketplace/producthint_settings/product_stock', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductHintTax()
    {
        return  $this->scopeConfig->getValue('marketplace/producthint_settings/product_tax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductHintWeight()
    {
        return  $this->scopeConfig->getValue('marketplace/producthint_settings/product_weight', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductHintImage()
    {
        return  $this->scopeConfig->getValue('marketplace/producthint_settings/product_image', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProductHintEnable()
    {
        return  $this->scopeConfig->getValue('marketplace/producthint_settings/product_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProfileHintStatus()
    {
        return  $this->scopeConfig->getValue('marketplace/profilehint_settings/profile_hint_status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProfileHintBecomeSeller()
    {
        return  $this->scopeConfig->getValue('marketplace/profilehint_settings/become_seller', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProfileHintShopurl()
    {
        return  $this->scopeConfig->getValue('marketplace/profilehint_settings/shopurl_seller', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProfileHintTw()
    {
        return  $this->scopeConfig->getValue('marketplace/profilehint_settings/profile_tw', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProfileHintFb()
    {
        return  $this->scopeConfig->getValue('marketplace/profilehint_settings/profile_fb', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProfileHintCn()
    {
        return  $this->scopeConfig->getValue('marketplace/profilehint_settings/profile_cn', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProfileHintBc()
    {
        return  $this->scopeConfig->getValue('marketplace/profilehint_settings/profile_bc', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProfileHintShop()
    {
        return  $this->scopeConfig->getValue('marketplace/profilehint_settings/profile_shop', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProfileHintBanner()
    {
        return  $this->scopeConfig->getValue('marketplace/profilehint_settings/profile_banner', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProfileHintLogo()
    {
        return  $this->scopeConfig->getValue('marketplace/profilehint_settings/profile_logo', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProfileHintLoc()
    {
        return  $this->scopeConfig->getValue('marketplace/profilehint_settings/profile_loc', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProfileHintDesc()
    {
        return  $this->scopeConfig->getValue('marketplace/profilehint_settings/profile_desciption', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProfileHintReturnPolicy()
    {
        return  $this->scopeConfig->getValue('marketplace/profilehint_settings/returnpolicy', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProfileHintShippingPolicy()
    {
        return  $this->scopeConfig->getValue('marketplace/profilehint_settings/shippingpolicy', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProfileHintCountry()
    {
        return  $this->scopeConfig->getValue('marketplace/profilehint_settings/profile_country', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProfileHintMeta()
    {
        return  $this->scopeConfig->getValue('marketplace/profilehint_settings/profile_meta', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProfileHintMetaDesc()
    {
        return  $this->scopeConfig->getValue('marketplace/profilehint_settings/profile_mdesc', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProfileHintBank()
    {
        return  $this->scopeConfig->getValue('marketplace/profilehint_settings/profile_bank', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getProfileUrl()
    {
        $target_url = $this->getTargetUrlPath();    
        if($target_url){   
            $temp=explode('/profile/shop',$target_url);
            if(!isset($temp[1])){
                $temp[1]='';
            }
            $temp=explode('/',$temp[1]);
            if($temp[1]!=''){
                $temp1 = explode('?', $temp[1]);
                return $temp1[0];
            }
        }
        return false;
    }

    public function getCollectionUrl()
    {
        $target_url = $this->getTargetUrlPath();
        if($target_url){
            $temp=explode('/collection/shop',$target_url);
            if(!isset($temp[1])){
                $temp[1]='';
            }
            $temp=explode('/',$temp[1]);
            if($temp[1]!=''){
                $temp1 = explode('?', $temp[1]);
                return $temp1[0];
            }
        }
        return false;
    }

    public function getLocationUrl()
    {
        $target_url = $this->getTargetUrlPath();    
        if($target_url){    
            $temp=explode('/location/shop',$target_url);
            if(!isset($temp[1])){
                $temp[1]='';
            }
            $temp=explode('/',$temp[1]);
            if($temp[1]!=''){
                $temp1 = explode('?', $temp[1]);
                return $temp1[0];
            }
        }
        return false;
    }

    public function getFeedbackUrl()
    {
        $target_url = $this->getTargetUrlPath();      
        if($target_url){  
            $temp=explode('/feedback/shop',$target_url);
            if(!isset($temp[1])){
                $temp[1]='';
            }
            $temp=explode('/',$temp[1]);
            if($temp[1]!=''){
                $temp1 = explode('?', $temp[1]);
                return $temp1[0];
            }
        }
        return false;
    }

    public function getRewriteUrl($target_url)
    {
        $request_url = $this->_urlBuilder->getUrl('', ['_direct' => $target_url, '_secure' => $this->_request->isSecure()]);
        $url_coll = $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')
                    ->getCollection()
                    ->addFieldToFilter('target_path',array('eq'=>$target_url))
                    ->addFieldToFilter('store_id',$this->getCurrentStoreId());
        foreach ($url_coll as $value) {
            $request_url = $this->_urlBuilder->getUrl('', ['_direct' => $value->getRequestPath(), '_secure' => $this->_request->isSecure()]);
        }
        return $request_url;
    }

    public function getRewriteUrlPath($target_url)
    {
        $request_path = '';
        $url_coll = $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')
                    ->getCollection()
                    ->addFieldToFilter(
                        'target_path',
                        ['eq' => $target_url]
                    )
                    ->addFieldToFilter(
                        'store_id',
                        ['eq' => $this->getCurrentStoreId()]
                    );
        foreach ($url_coll as $value) {
            $request_path = $value->getRequestPath();
        }
        return $request_path;
    }

    public function getTargetUrlPath(){
        $urls = explode($this->_urlBuilder->getUrl('', ['_secure' => $this->_request->isSecure()]),$this->_urlBuilder->getCurrentUrl());
        $target_url = '';
        $temp = explode('/?',$urls[1]);
        if(!isset($temp[1])){
            $temp[1]='';
        }
        if(!$temp[1]){
            $temp=explode('?',$temp[0]);
        }
        $request_path=$temp[0]; 
        $url_coll = $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')
                    ->getCollection()
                    ->addFieldToFilter(
                        'request_path',
                        ['eq' => $request_path]
                    )
                    ->addFieldToFilter(
                        'store_id',
                        ['eq' => $this->getCurrentStoreId()]
                    );
        foreach ($url_coll as $value) {
            $target_url = $value->getTargetPath();
        }
        return $target_url;
    }

    public function getPlaceholderImage()
    {
        return  $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)."marketplace/placeholder/image.jpg";
    }

    public function getSellerProCount($seller_id){

        $querydata = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
                            ->getCollection()
                            ->addFieldToFilter('seller_id', array('eq' => $seller_id))
                            ->addFieldToFilter('status', array('neq' => 2))
                            ->addFieldToSelect('mageproduct_id')
                            ->setOrder('mageproduct_id');
        $collection = $this->_objectManager->create('Magento\Catalog\Model\Product')
                            ->getCollection();
        $collection->addAttributeToSelect('*');        
        $collection->addAttributeToFilter('entity_id', array('in' => $querydata->getData()));
        $collection-> addAttributeToFilter('visibility', array('in' => array(4) )); 
        $collection->addStoreFilter();
        //Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
        //Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);

        $collectionConfigurable = $this->_objectManager->create('Magento\Catalog\Model\Product')
                                        ->getCollection()
                                        ->addAttributeToFilter('type_id', array('eq' => 'configurable'))
                                        ->addAttributeToFilter('entity_id', array('in' => $querydata->getData()));

        $outOfStockConfis = array();
        foreach ($collectionConfigurable as $_configurableproduct) {
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($_configurableproduct->getId());
            if (!$product->getData('is_salable')) {
               $outOfStockConfis[] = $product->getId();
            }
        }
        if(count($outOfStockConfis)){
            $collection->addAttributeToFilter('entity_id',array('nin' => $outOfStockConfis));
        }

        $collectionBundle = $this->_objectManager->create('Magento\Catalog\Model\Product')
                                        ->getCollection()
                                ->addAttributeToFilter('type_id', array('eq' => 'bundle'))
                                ->addAttributeToFilter('entity_id', array('in' => $querydata->getData()));
        $outOfStockConfis = array();
        foreach ($collectionBundle as $_bundleproduct) {
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($_bundleproduct->getId());
            if (!$product->getData('is_salable')) {
               $outOfStockConfis[] = $product->getId();
            }
        }
        if(count($outOfStockConfis)){
            $collection->addAttributeToFilter('entity_id',array('nin' => $outOfStockConfis));
        }

        $collectionGrouped = $this->_objectManager->create('Magento\Catalog\Model\Product')
                                ->getCollection()
                                ->addAttributeToFilter('type_id', array('eq' => 'grouped'))
                                ->addAttributeToFilter('entity_id', array('in' => $querydata->getData()));
        $outOfStockConfis = array();
        foreach ($collectionGrouped as $_groupedproduct) {
            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($_groupedproduct->getId());
            if (!$product->getData('is_salable')) {
               $outOfStockConfis[] = $product->getId();
            }
        }
        if(count($outOfStockConfis)){
            $collection->addAttributeToFilter('entity_id',array('nin' => $outOfStockConfis));
        }
               
        return count($collection);
    }
    public function getMediaUrl(){
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}