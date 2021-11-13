<?php
namespace Webkul\Marketplace\Controller\Product;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;
use Magento\Framework\Event\Manager;

class SaveProduct
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    protected $productTypeManager;

    /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable */
    protected $productType;

    /** @var \Magento\ConfigurableProduct\Model\Product\VariationHandler */
    protected $variationHandler;

    /** @var \Magento\Catalog\Api\ProductRepositoryInterface  */
    protected $productRepository;

    /**
     * @var eventManager
     */
    protected $_eventManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager
     * @param \Magento\ConfigurableProduct\Model\Product\VariationHandler $variationHandler
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param Filesystem $filesystem
     * @param Initialization\Helper $initializationHelper
     * @param Builder $productBuilder
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager,
        \Magento\ConfigurableProduct\Model\Product\VariationHandler $variationHandler,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        Filesystem $filesystem,
        Initialization\Helper $initializationHelper,
        Builder $productBuilder
    ) {
        $this->_eventManager = $eventManager;
        $this->_objectManager = $objectManager;
        $this->_date = $date;
        $this->dateTime = $dateTime;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->initializationHelper = $initializationHelper;
        $this->productBuilder = $productBuilder;
        $this->productTypeManager = $productTypeManager;        
        $this->variationHandler = $variationHandler;
        $this->productType = $productType;
        $this->productRepository = $productRepository;
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function saveProductData($sellerId,$wholedata)
    { 
        //echo $sellerId;
        
		//echo "<pre>"; print_r($wholedata); exit;
        $returnArr = array();
        $returnArr['error'] = 0;
        $returnArr['product_id'] = '';
        $returnArr['message'] = '';
        $wholedata['new-variations-attribute-set-id'] = $wholedata['set'];

        $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');

        $seller_id=$sellerId;

        if (!empty($wholedata['id'])) {
            $productId = $wholedata['id'];
            $editFlag = 1;
            $storeId=$helper->getCurrentStoreId();
            $this->_eventManager->dispatch(
                'mp_customattribute_deletetierpricedata',
                [$wholedata]
            );
        }else{
            $productId = '';
            $editFlag = 0;
            $storeId = 0;
            $wholedata['product']['website_ids'][] = 1;
            $wholedata['product']['url_key'] = '';
            $wholedata['product']['meta_title'] = $wholedata['product']['name'];
            $wholedata['product']['meta_keyword'] = $wholedata['product']['name'];
            $wholedata['product']['meta_description'] = $wholedata['product']['description'];
        }

        if(isset($wholedata['status']) && $wholedata['status'] && $productId){
            $status=$wholedata['status']; 
            if($helper->getIsProductEditApproval()){
                $status=$helper->getIsProductEditApproval()? 2:1;
            }
        }
        else{   
            $status=$helper->getIsProductApproval()? 2:1;
        }
        $wholedata['status'] = $status;

        $wholedata['store'] = $storeId;

        /*
        * Marketplace Product save before Observer
        */
        $this->_eventManager->dispatch(
            'mp_product_save_before',
            [$wholedata]
        );
        
        $productTypeId = $wholedata['type'];

        $product = $this->initializationHelper->initialize($this->productBuilder->build($wholedata,$storeId),$wholedata);

        /*for downloadable products start*/

        if (!empty($wholedata['downloadable']) && $downloadable = $wholedata['downloadable']) {
            $product->setDownloadableData($downloadable);
        }

        /*for downloadable products end*/


        /*for configurable products start*/

        $associatedProductIds = array();

        if (!empty($wholedata['attributes'])) {
            $attributes = $wholedata['attributes'];
            $setId = $wholedata['set'];
            $product->setAttributeSetId($setId);
            $this->productType->setUsedProductAttributeIds($attributes, $product);

            $product->setNewVariationsAttributeSetId($setId);
            $associatedProductIds = array();
            if(!empty($wholedata['associated_product_ids'])){
                $associatedProductIds = $wholedata['associated_product_ids'];
            }
            $variationsMatrix = array();
            if(!empty($wholedata['variations-matrix'])){
                $variationsMatrix = $wholedata['variations-matrix'];
            }
            if (!empty($variationsMatrix)) {
                $generatedProductIds = $this->variationHandler->generateSimpleProducts($product, $variationsMatrix);
                $associatedProductIds = array_merge($associatedProductIds, $generatedProductIds);
            }
            $product->setAssociatedProductIds(array_filter($associatedProductIds));

            $product->setCanSaveConfigurableAttributes(
                (bool)$wholedata['affect_configurable_product_attributes']
            );
        }

        /*for configurable products end*/

        $this->productTypeManager->processProduct($product);
        $set = $product->getAttributeSetId();

        $type=$product->getTypeId();
        //$type=$productTypeId;
        
        if(isset($set) && isset($type)){
            $allowedsets=explode(',',$this->_objectManager->get('Webkul\Marketplace\Helper\Data')->getAllowedAttributesetIds());
            //$allowedtypes=explode(',',$this->_objectManager->get('Webkul\Marketplace\Helper\Data')->getAllowedProductTypes());
            $allowedtypes=explode(',',$this->_objectManager->get('Webkul\Marketplace\Helper\Data')->getAllowedProductType());

            if(!in_array($type,$allowedtypes) || !in_array($set,$allowedsets)){
                $returnArr['error'] = 1;
                $returnArr['message'] = __('Product Type Invalid Or Not Allowed');
                return $returnArr;
            }
        }else{
            $returnArr['error'] = 1;
            $returnArr['message'] = __('Product Type Invalid Or Not Allowed');
            return $returnArr;
        }

        if (isset($data[$product->getIdFieldName()])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to save product'));
        }

        $originalSku = $product->getSku();
		
        $product->save();
        $productId = $product->getId();

		$mainproduct = $this->productRepository->getById($productId, false, $storeId);
		//$mainproduct->setCustomAttribute($attributeCode, $attributeValue);
		
		
		/* barcode and vendorshopurl  */
		$barcode = '';
		$shopurl = '';

		$barcode = $wholedata['product']['barcode'];
		$mainproduct->setBarcode($barcode);
		$shopurl = $wholedata['product']['shopurl'];
		$mainproduct->setVendor($shopurl);
		$mainproduct->setShopUrl($shopurl);
		
		$mainproduct->setTax(1);
		$this->productRepository->save($mainproduct);
		
        /*for configurable associated products save start*/

        $configurations = array();
        if(!empty($wholedata['configurations'])){
            $configurations = $wholedata['configurations'];
        }

        if(!empty($configurations)){
            $configurations = $this->variationHandler->duplicateImagesForVariations($configurations);
            foreach ($configurations as $associtedProductId => $productData) {
                /** @var \Magento\Catalog\Model\Product $product */
                $associtedProduct = $this->productRepository->getById($associtedProductId, false, $storeId);
                $productData = $this->variationHandler->processMediaGallery($associtedProduct, $productData);
                $associtedProduct->addData($productData);
                if ($associtedProduct->hasDataChanges()) {
                    $associtedProduct->save();
                }
            }
        }

        /*for configurable associated products save end*/

        $wholedata['id'] = $productId;
        $this->_eventManager->dispatch(
            'mp_customoption_setdata',
            [$wholedata]
        );
        $seller_product_id = 0;
        
        $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId)->setStatus($status)->save();

        if(!$editFlag || ($editFlag && $helper->getIsProductEditApproval())){
            $seller_products = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
                                ->getCollection()
                                ->addFieldToFilter('mageproduct_id',array('eq'=>$productId))
                                ->addFieldToFilter('seller_id',array('eq'=>$seller_id));
            foreach ($seller_products as $seller_product) {
                $seller_product_id = $seller_product->getId();
            }
            $collection1=$this->_objectManager->create('Webkul\Marketplace\Model\Product')->load($seller_product_id);
            $collection1->setMageproductId($productId);
            $collection1->setSellerId($seller_id);
            $collection1->setStatus($status);
            if(!$editFlag){
                $collection1->setCreatedAt($this->_date->gmtDate());
            }
            $collection1->setUpdatedAt($this->_date->gmtDate());
            $collection1->save();

            foreach ($associatedProductIds as $key => $value) {
                $seller_product_id = 0;
                $seller_products = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
                                    ->getCollection()
                                    ->addFieldToFilter('mageproduct_id',array('eq'=>$value))
                                    ->addFieldToFilter('seller_id',array('eq'=>$seller_id));
                foreach ($seller_products as $seller_product) {
                    $seller_product_id = $seller_product->getId();
                }
                $collection1=$this->_objectManager->create('Webkul\Marketplace\Model\Product')->load($seller_product_id);
                $collection1->setMageproductId($value);
                if(!$editFlag){
                    $collection1->setStatus(1);
                }
                if(!$editFlag){
                    $collection1->setCreatedAt($this->_date->gmtDate());
                }
                $collection1->setUpdatedAt($this->_date->gmtDate());
                $collection1->setSellerId($seller_id);
                $collection1->save();
            }
        }
		/* code by Arpit */
		
		$mobjectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
		$mcartObject = $mobjectManager->create('Magento\Checkout\Model\Cart')->truncate(); 
		$mcartObject->saveQuote();

		$cacheTypeList = $mobjectManager->create('Magento\Framework\App\Cache\TypeListInterface');
		$_cacheFrontendPool = $mobjectManager->create('Magento\Framework\App\Cache\Frontend\Pool');
		$types = array('config','layout','block_html','collections','reflection','db_ddl','eav','config_integration','config_integration_api','full_page','translate','config_webservice');
		foreach ($types as $type) {
			$cacheTypeList->cleanType($type);
		}
		foreach ($_cacheFrontendPool as $cacheFrontend) {
			$cacheFrontend->getBackend()->clean();
		}

		/* code by Arpit */
		
		
        $this->_eventManager->dispatch(
            'mp_customattribute_settierpricedata',
            [$wholedata]
        );
        
        /*
        * Marketplace Product save before Observer
        */
        $this->_eventManager->dispatch(
            'mp_product_save_after',
            [$wholedata]
        ); 
        if(!$editFlag){
            $this->sendProductMail($wholedata,$sellerId,'');
        }else{
            if($helper->getIsProductEditApproval()){
                $this->sendProductMail($wholedata,$sellerId,$editFlag);
            }
        }

        $returnArr['product_id'] = $productId;
        return $returnArr;
    }

    /**
     * @param  Mixed $data Product Information
     * @return 
     */
    private function sendProductMail($data,$sellerId,$editFlag = null)
    {

        $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
        
        $seller_id = $sellerId;
        $customer = $this->_objectManager->get('Magento\Customer\Model\Customer')->load($seller_id);
        $seller_name = $customer->getFirstname()." ".$customer->getLastname();
        $seller_email = $customer->getEmail();
        $catagory_model = $this->_objectManager->get('Magento\Catalog\Model\Category');
        if (isset($data['product']) && !empty($data['product']['category_ids'])) {
            $categoriesy = $catagory_model->load($data['product']['category_ids'][0]);
            $categoryname=$categoriesy->getName();
        }else{
            $categoryname='';
        }
        $emailTempVariables = array();
        $admin_storemail = $helper->getAdminEmailId();
        $adminEmail=$admin_storemail? $admin_storemail:$helper->getDefaultTransEmailId();
        $adminUsername = 'Admin';

        $emailTempVariables['myvar1'] = $data['product']['name'];
        $emailTempVariables['myvar2'] =$categoryname;
        $emailTempVariables['myvar3'] = $adminUsername;
        if($editFlag == null)
            $emailTempVariables['myvar4'] = __('I would like to inform you that recently I have added a new product in the store.');
        else
            $emailTempVariables['myvar4'] = __('I would like to inform you that recently I have updated a  product in the store.');
        $senderInfo = [
            'name' => $seller_name,
            'email' => $adminEmail
        ];
        $receiverInfo = [
            'name' => $adminUsername,
            'email' => $adminEmail,
        ];
        if($editFlag == null){
            $status=$helper->getIsProductApproval();
            if($status==1){
                $this->_objectManager->create('Webkul\Marketplace\Helper\Email')->sendNewProductMail($emailTempVariables, $senderInfo, $receiverInfo, null);
            }
        }else{
            $status = $helper->getIsProductEditApproval();
            if($status==1){
                $this->_objectManager->create('Webkul\Marketplace\Helper\Email')->sendNewProductMail($emailTempVariables, $senderInfo, $receiverInfo, $editFlag);
            }
        }
    }
}
