<?php
namespace Webkul\Marketplace\Block\Adminhtml\Customer\Edit;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
/**
 * Customer account form block
 */
class AddProductTab extends Generic implements TabInterface
{
    /**
     * @var string
     */
    /*protected $_template = 'customfields/customer/button.phtml';*/

     /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    protected $_dob = null;
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_country;
    protected $_productCollectionFactory;
	
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Directory\Model\ResourceModel\Country\Collection $country,
        \Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_systemStore = $systemStore;
        $this->_objectManager = $objectManager;
        $this->_country = $country;
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Add Product');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Add Product');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        $coll = $this->_objectManager->create('Webkul\Marketplace\Block\Adminhtml\Customer\Edit')->getMarketplaceUserCollection();
        $isSeller = false;
        foreach($coll as $row){
            $isSeller=$row->getIsSeller();
        }
        if ($this->getCustomerId() && $isSeller) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        $coll = $this->_objectManager->create('Webkul\Marketplace\Block\Adminhtml\Customer\Edit')->getMarketplaceUserCollection();
        $isSeller = false;
        foreach($coll as $row){
            $isSeller=$row->getIsSeller();
        }
        if ($this->getCustomerId() && $isSeller) {
            return false;
        }
        return true;
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }
    public function initForm()
    {
        if (!$this->canShowTab()) {
            return $this;
        }
        /**@var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('marketplace_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Assign Product To Seller')]);
        
		$partner = $this->_objectManager->create('Webkul\Marketplace\Block\Adminhtml\Customer\Edit')->getSellerInfoCollection();
		
		//echo "<pre>"; print_r($partner); exit;
		/* $seller_name 	=	$partner['shop_url'];
		// select other products
		$products = $this->_productCollectionFactory->create()->addAttributeToSelect(
			['*']
		)-> addAttributeToFilter('vendor', $seller_name); */
		
		// select other products
		/* $products = $this->_productCollectionFactory->create()->addAttributeToSelect(
			['*']
		)-> addAttributeToFilter('visibility', array('in' => array(2, 4) ));
		$products->getSelect()
				->joinLeft(array("marketplace_product" => 'marketplace_product'),"`marketplace_product`.`mageproduct_id` = `e`.`entity_id`",array("seller_id" => "seller_id"))->where('`marketplace_product`.`seller_id` IS NULL OR `marketplace_product`.`seller_id`='.$partner['seller_id']); */
		
		 
		/* $all_products 		=	['-1'=> array( 'label' => '--Please Select Product--', 'value' => '')];
		$seller_products 	=	[];
		foreach($products as $_product){
			$all_products[] 	=	['label' => $_product->getName(), 'value' => $_product->getId()];
			$seller_id 			=	$_product->getSellerId();
			if($seller_id){
				$seller_products[] 	=	$_product->getId();
			}
			$vendor_name 	=	$_product->getData('vendor');
			$this->assignProduct($vendor_name, $_product->getId());
		} 
		
		echo "<pre>";
		 print_r($seller_products);die; */
		$all_products 		=	['-1'=> array( 'label' => '--Please Select Product--', 'value' => '')];
		$seller_products 	=	[];
		
		$fieldset->addField(
			'sellerassignproid',
			'multiselect',
			[
				'name' => 'sellerassignproid',
				'required' => true,
				'label' => __('Seller Products'),
				'title' => __('Seller Products'),
				'values' =>  $all_products,
				'value' =>  $seller_products,
			]
		);	
		
       /*  $fieldset->addField(
            'sellerassignproid',
            'multiselect',
            [
                'name' => 'sellerassignproid',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Select Product'),
                'title' => __('Select Product'),
                'after_element_html' => '<p class="notice">'.__('Notice: Enter Only Integer value by comma (,)').'</p>'
            ]
        ); */
        $this->setForm($form);
        return $this;
    }
	
	public function assignProduct($vendor_name,$proid){
		
		/* $_date = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
		$seller_data = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                    ->getCollection()
                    ->addFieldToFilter('shop_url',$vendor_name);
        $seller_id	=	$seller_data->getFirstItem()->getSellerId();
		$userid		=	'';
		$product 	= 	$this->_objectManager->get('Magento\Catalog\Model\Product')->load($proid);
		if($product->getname() && $seller_id !=""){
			$collection = $this->_objectManager->create('Webkul\Marketplace\Model\Product')->getCollection()
			->addFieldToFilter('mageproduct_id',array('eq'=>$proid));
			
			foreach($collection as $coll){
			   $userid = $coll['seller_id'];
			}
			if($userid){
				if($userid!=$seller_id){ 
					$collection->setSellerId($seller_id);
					$collection->setAdminassign(1);
					$collection->setUpdatedAt(time());
					$collection->save();	
				}
			} else{
				$collection1 = $this->_objectManager->create('Webkul\Marketplace\Model\Product');
				$collection1->setMageproductId($proid);
				$collection1->setSellerId($seller_id);
				$collection1->setStatus($product->getStatus());
				$collection1->setAdminassign(1);
				$collection1->setStoreId(array($this->_storeManager->getStore()->getId()));
				$collection1->setCreatedAt(time());
				$collection1->setUpdatedAt(time());
				$collection1->save();
			} 
		} */  
    }
	
    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->canShowTab()) {
            $this->initForm();
            return parent::_toHtml();
        } else {
            return '';
        }
    }
    /**
     * Prepare the layout.
     *
     * @return $this
     */
   public function getFormHtml()
    {
        $html = parent::getFormHtml();
        $html .= $this->getLayout()->createBlock(
            'Webkul\Marketplace\Block\Adminhtml\Customer\Edit\Tab\AddProduct'
        )->toHtml();
        return $html;
    }
}
