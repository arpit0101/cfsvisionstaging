<?php
namespace Webkul\Marketplace\Block\Adminhtml\Customer\Edit;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
/**
 * Customer account form block
 */
class Tabs extends Generic implements TabInterface
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
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_systemStore = $systemStore;
        $this->_objectManager = $objectManager;
        $this->_country = $country;
		
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
        return __('Seller Account Information');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Seller Account Information');
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
	?>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker.css" />	
	<?php
        if (!$this->canShowTab()) {
            return $this;
        }
        /**@var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('marketplace_');
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $storeid = $this->_storeManager->getStore()->getId();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Seller Profile Information')]);
         
        $partner = $this->_objectManager->create('Webkul\Marketplace\Block\Adminhtml\Customer\Edit')->getSellerInfoCollection();
		//echo "<pre>"; print_r($partner); exit;
        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
						->getCollection()
						->addFieldToFilter('seller_id',$partner['seller_id']);
			foreach($collection as  $partnerData){
				
			}
			
			$partner['country_id']			=		$partnerData->getCountryId();	
			$partner['region_id']			=		$partnerData->getRegionId();	
			$partner['area_id']				=		$partnerData->getAreaId();	
			$partner['sequence']			=		$partnerData->getSequence();	
			$partner['backgroundcolor']		=		$partnerData->getBackgroundcolor();	
			$partner['off_dates']			=		$partnerData->getOffDates();	
			
            $tw_active = '';
            $fb_active = '';
            $gplus_active = '';
            $instagram_active = '';
            $youtube_active = '';
            $vimeo_active = '';
            $pinterest_active = '';
            $moleskine_active = '';

            $tw_id = '';
            $fb_id = '';
            $gplus_id = '';
            $instagram_id = '';
            $youtube_id = '';
            $vimeo_id = '';
            $pinterest_id = '';
            $moleskine_id = '';
            if($partner['tw_active'] == 1){ 
                $tw_active = "checked='checked'";
            }
            if($partner['fb_active'] == 1){ 
                $fb_active = "checked='checked'";
            }
            if($partner['gplus_active'] == 1){ 
                $gplus_active = "checked='checked'";
            }
            if($partner['instagram_active'] == 1){ 
                $instagram_active = "checked='checked'";
            }
            if($partner['youtube_active'] == 1){ 
                $youtube_active = "checked='checked'";
            }
            if($partner['vimeo_active'] == 1){ 
                $vimeo_active = "checked='checked'";
            }
            if($partner['pinterest_active'] == 1){ 
                $pinterest_active = "checked='checked'";
            }
            if($partner['moleskine_active'] == 1){ 
                $moleskine_active = "checked='checked'";
            }
			
            $fieldset->addField(
                'twitter_id',
                'text',
                [
                    'name' => 'twitter_id',
                    'data-form-part' => $this->getData('target_form'),
                    'label' => __('Twitter ID'),
                    'title' => __('Twitter ID'),
                    'value' => $partner['twitter_id'],
                    'after_element_html' => '<input type="checkbox" name="tw_active" data-form-part="customer_form" value="1" title="'.__('Allow to Display Twitter Icon in Profile Page').'" '.$tw_active.'>'
                ]
            );
            $fieldset->addField(
                'facebook_id',
                'text',
                [
                    'name' => 'facebook_id',
                    'data-form-part' => $this->getData('target_form'),
                    'label' => __('Facebook ID'),
                    'title' => __('Facebook ID'),
                    'value' => $partner['facebook_id'],
                    'after_element_html' => '<input type="checkbox" name="fb_active" data-form-part="customer_form" value="1" title="'.__('Allow to Display Facebook Icon in Profile Page').'" '.$fb_active.'>'
                ]
            );
            $fieldset->addField(
                'instagram_id',
                'text',
                [
                    'name' => 'instagram_id',
                    'data-form-part' => $this->getData('target_form'),
                    'label' => __('Instagram ID'),
                    'title' => __('Instagram ID'),
                    'value' => $partner['instagram_id'],
                    'after_element_html' => '<input type="checkbox" name="instagram_active" data-form-part="customer_form" value="1" title="'.__('Allow to Display Instagram Icon in Profile Page').'" '.$instagram_active.'>'
                ]
            );
            $fieldset->addField(
                'gplus_id',
                'text',
                [
                    'name' => 'gplus_id',
                    'data-form-part' => $this->getData('target_form'),
                    'label' => __('Google+ ID'),
                    'title' => __('Google+ ID'),
                    'value' => $partner['gplus_id'],
                    'after_element_html' => '<input type="checkbox" name="gplus_active" data-form-part="customer_form" value="1" title="'.__('Allow to Display Google+ Icon in Profile Page').'" '.$gplus_active.'>'
                ]
            );
            $fieldset->addField(
                'youtube_id',
                'text',
                [
                    'name' => 'youtube_id',
                    'data-form-part' => $this->getData('target_form'),
                    'label' => __('Youtube ID'),
                    'title' => __('Youtube ID'),
                    'value' => $partner['youtube_id'],
                    'after_element_html' => '<input type="checkbox" name="youtube_active" data-form-part="customer_form" value="1" title="'.__('Allow to Display Youtube Icon in Profile Page').'" '.$youtube_active.'>'
                ]
            );
            $fieldset->addField(
                'vimeo_id',
                'text',
                [
                    'name' => 'vimeo_id',
                    'data-form-part' => $this->getData('target_form'),
                    'label' => __('Vimeo ID'),
                    'title' => __('Vimeo ID'),
                    'value' => $partner['vimeo_id'],
                    'after_element_html' => '<input type="checkbox" name="vimeo_active" data-form-part="customer_form" value="1" title="'.__('Allow to Display Vimeo Icon in Profile Page').'" '.$vimeo_active.'>'
                ]
            );
            $fieldset->addField(
                'pinterest_id',
                'text',
                [
                    'name' => 'pinterest_id',
                    'data-form-part' => $this->getData('target_form'),
                    'label' => __('Pinterest ID'),
                    'title' => __('Pinterest ID'),
                    'value' => $partner['pinterest_id'],
                    'after_element_html' => '<input type="checkbox" name="pinterest_active" data-form-part="customer_form" value="1" title="'.__('Allow to Display Pinterest Icon in Profile Page').'" '.$pinterest_active.'>'
                ]
            );
            $fieldset->addField(
                'moleskine_id',
                'text',
                [
                    'name' => 'moleskine_id',
                    'data-form-part' => $this->getData('target_form'),
                    'label' => __('Moleskine ID'),
                    'title' => __('Moleskine ID'),
                    'value' => $partner['moleskine_id'],
                    'after_element_html' => '<input type="checkbox" name="moleskine_active" data-form-part="customer_form" value="1" title="'.__('Allow to Display Moleskine Icon in Profile Page').'" '.$moleskine_active.'>'
                ]
            );
            $fieldset->addField(
                'contact_number',
                'text',
                [
                    'name' => 'contact_number',
                    'data-form-part' => $this->getData('target_form'),
                    'label' => __('Contact Number'),
                    'title' => __('Contact Number'),
                    'value' => $partner['contact_number'],
                ]
            );
			$fieldset->addField(
                'shop_url',
                'text',
                [
                    'name' => 'shop_url',
                    'data-form-part' => $this->getData('target_form'),
                    'label' => __('Shop URL'),
                    'title' => __('Shop URL'),
                    'value' => $partner['shop_url'],
                ]
            );
            $fieldset->addField(
                'shop_title',
                'text',
                [
                    'name' => 'shop_title',
                    'data-form-part' => $this->getData('target_form'),
                    'label' => __('Shop Title'),
                    'title' => __('Shop Title'),
                    'value' => $partner['shop_title'],
                ]
            );
            $fieldset->addField(
                'company_locality',
                'text',
                [
                    'name' => 'company_locality',
                    'data-form-part' => $this->getData('target_form'),
                    'label' => __('Company Locality'),
                    'title' => __('Company Locality'),
                    'value' => $partner['company_locality'],
                ]
            );
            $fieldset->addField(
                'session_region_id',
                'text',
                [
                    'name' => 'session_region_id',
                    'label' => __('Custom area id'),
                    'title' => __('Custom area id'),
                    'value' => 0,
                ]
            );
			
			$countries 	= $this->_country;
			
            $country = $fieldset->addField(
                'country_id',
                'select',
                [
                    'name' => 'country_id',
					'required' => true,
					'data-form-part' => $this->getData('target_form'),
                    'label' => __('Country'),
                    'title' => __('Country'),
                    'values' => $countries->toOptionArray(),
                    'value' => isset($partner['country_id'])?$partner['country_id']:0,
                ]
            );
			$partner['region_id']	=	isset($partner['region_id'])?$partner['region_id']:0;
			$region_ids				=	isset($partner['region_id'])?explode(",", $partner['region_id']):[0=>['label'=>'--Please Select Area--', 'value' => '0']];
			
            $fieldset->addField(
                'region_id',
                'multiselect',
                [
                    'name' => 'region_id',
					'required' => true,
					'data-form-part' => $this->getData('target_form'),
                    'label' => __('Cities'),
                    'title' => __('Cities'),
                    'value' => ['-1'=> array( 'label' => '--Please Select Cities--', 'value' => '-1')],
					'after_element_html' => "<input type='hidden' id='old_region_id' value='".$partner['region_id']."' />",
                ]
            );
			$partner['area_id']	=	isset($partner['area_id'])?$partner['area_id']:0;
			$fieldset->addField(
                'area_id',
                'multiselect',
                [
                    'name' => 'area_id',
                    'required' => true,
					'data-form-part' => $this->getData('target_form'),
                    'label' => __('Area'),
                    'title' => __('Area'),
                    'values' =>  ['-1'=> array( 'label' => '--Please Select Area--', 'value' => '-1')],
					'after_element_html' => "<input type='hidden' id='old_area_id' value='".$partner['area_id']."' />",
                ]
            );
			
			 /*
            * Add Ajax to the Country select box html output
            */
			$country->setAfterElementHtml(" 
					
					<script type=\"text/javascript\">
							require([
							'jquery',
							'mage/template',
							'jquery/ui',
							'mage/translate'
						],
						
						function($, mageTemplate) {
							$(document).ready(function(){
								$.getScript('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.js' ).done(function() {
									$('.datepicker').datepicker({
										multidate: true,
										format: 'dd-mm-yyyy'
									});
								})
								.fail(function() {
									console.log( 'faild' );
			
								});
								var citieslist 	=	[];
								$(document).on('change', '#marketplace_country_id', function(event){
									$.ajax({
										url : '". $this->getUrl('region/lists/regionlist'). "',
										type: 'POST',
										data:{
											form_key: window.FORM_KEY,
											type: 'region',
											country_id:$('#marketplace_country_id').val()
										},
										dataType: 'json',
										showLoader:true,
										success: function(data){
											
											$('#marketplace_region_id').empty();
											var option = $('<option/>');
											option.attr({ 'value': '' }).text('--Please select Cities--');
											$('#marketplace_region_id').append(option);
											var old_region_id 	=	$('#old_region_id').val();
											var old_regions 		=	old_region_id.split(',');
											$.each(data, function(index, region){
												var option = $('<option/>');
												
												citieslist[region.region_id]	=	region.default_name;
												option.attr({ 'value': region.region_id }).text(region.default_name);
												$('#marketplace_region_id').append(option);
												jQuery('#marketplace_region_id').val(old_regions);
											});
										}
									});
								});
							   
								$(document).on('change', '#marketplace_region_id', function(event){
									region_id	=	$('#marketplace_region_id').val()
									$.ajax({
										url : '". $this->getUrl('region/lists/regionlist'). "',
										type: 'POST',
										data:{
											form_key: window.FORM_KEY,
											type: 'area',
											region_id:$('#marketplace_region_id').val()
										},
										dataType: 'json',
										showLoader:true,
										success: function(data){
											
											$('#marketplace_area_id').empty();
											var option = $('<option/>');
											option.attr({ 'value': '' }).text('--Please select Area--');
											$('#marketplace_area_id').append(option);
											var old_area_id 	=	$('#old_area_id').val();
											var old_areas 		=	old_area_id.split(',');
										
											$.each(data, function(index, optiondata){
												console.log(index);
												console.log(optiondata);
												var optiongroup = $('<optgroup label= '+citieslist[index]+' />');
												$.each(optiondata, function(){
													var option = $('<option/>');
													option.attr({ 'value': this.area_id }).text(this.default_name);
													option.appendTo(optiongroup);
												});
												optiongroup.appendTo($('#marketplace_area_id'));
											});
											jQuery('#marketplace_area_id').val(old_areas);
										}
									});
								});
						   
								if($('#marketplace_country_id').val()!=''){ 
							   
									$.ajax({
										url : '". $this->getUrl('region/lists/regionlist'). "',
										type: 'POST',
										data:{
											form_key: window.FORM_KEY,
											type: 'region',
											country_id:$('#marketplace_country_id').val()
										},
										dataType: 'json',
										showLoader:true,
										success: function(data){
											
											$('#marketplace_region_id').empty();
											var option = $('<option/>');
											option.attr({ 'value': '' }).text('--Please select Cities--');
											$('#marketplace_region_id').append(option);
											var old_region_id 	=	$('#old_region_id').val();
											var old_regions 		=	old_region_id.split(',');
											$.each(data, function(index, region){
												var option = $('<option/>');
												
												citieslist[region.region_id]	=	region.default_name;
												option.attr({ 'value': region.region_id }).text(region.default_name);
												$('#marketplace_region_id').append(option);
												jQuery('#marketplace_region_id').val(old_regions);
											});
											if($('#marketplace_region_id').val()!=''){
											
												$.ajax({
													url : '". $this->getUrl('region/lists/regionlist'). "',
													type: 'POST',
													data:{
														form_key: window.FORM_KEY,
														type: 'area',
														region_id:$('#marketplace_region_id').val()
													},
													dataType: 'json',
													showLoader:true,
													success: function(data){
														$('#marketplace_area_id').empty();
														var option = $('<option/>');
														option.attr({ 'value': '' }).text('--Please select Area--');
														$('#marketplace_area_id').append(option);
														var old_area_id 	=	$('#old_area_id').val();
														var old_areas 		=	old_area_id.split(',');
													
														$.each(data, function(index, optiondata){
															console.log(index);
															console.log(optiondata);
															var optiongroup = $('<optgroup label= '+citieslist[index]+' />');
															$.each(optiondata, function(){
																var option = $('<option/>');
																option.attr({ 'value': this.area_id }).text(this.default_name);
																option.appendTo(optiongroup);
															});
															optiongroup.appendTo($('#marketplace_area_id'));
														});
														jQuery('#marketplace_area_id').val(old_areas);
													}
												});
											}
										}
									});
								}
						   });
						}

					);
					</script>"
			);
			
            $fieldset->addField(
                'company_description',
                'textarea',
                [
                    'name' => 'company_description',
                    'data-form-part' => $this->getData('target_form'),
                    'label' => __('Company Description'),
                    'title' => __('Company Description'),
                    'value' => $partner['company_description'],
                ]
            );
            $fieldset->addField(
                'return_policy',
                'textarea',
                [
                    'name' => 'return_policy',
                    'data-form-part' => $this->getData('target_form'),
                    'label' => __('Return Policy'),
                    'title' => __('Return Policy'),
                    'value' => $partner['return_policy'],
                ]
            );
            $fieldset->addField(
                'shipping_policy',
                'textarea',
                [
                    'name' => 'shipping_policy',
                    'data-form-part' => $this->getData('target_form'),
                    'label' => __('Shipping Policy'),
                    'title' => __('Shipping Policy'),
                    'value' => $partner['shipping_policy'],
                ]
            );
            $fieldset->addField(
                'meta_keyword',
                'textarea',
                [
                    'name' => 'meta_keyword',
                    'data-form-part' => $this->getData('target_form'),
                    'label' => __('Meta Keywords'),
                    'title' => __('Meta Keywords'),
                    'value' => $partner['meta_keyword'],
                ]
            );
            $fieldset->addField(
                'meta_description',
                'textarea',
                [
                    'name' => 'meta_description',
                    'data-form-part' => $this->getData('target_form'),
                    'label' => __('Meta Description'),
                    'title' => __('Meta Description'),
                    'value' => $partner['meta_description'],
                ]
            );
            $fieldset->addField(
                'banner_pic',
                'file',
                [
                    'name' => 'banner_pic',
                    'data-form-part' => $this->getData('target_form'),
                    'label' => __('Company Banner'),
                    'title' => __('Company Banner'),
                    'value' => $partner['banner_pic'],
                    'after_element_html' => '<img style="margin:5px 0;width:700px;height:150px;" src="/pub/media/avatar/'.$partner['banner_pic'].'"/>'
                ]
            );
            $fieldset->addField(
                'logo_pic',
                'file',
                [
                    'name' => 'logo_pic',
                    'data-form-part' => $this->getData('target_form'),
                    'label' => __('Company Logo'),
                    'title' => __('Company Logo'),
                    'value' => $partner['logo_pic'],
                    'after_element_html' => '<img style="margin:5px 0;width:700px;height:150px;" src="/pub/media/avatar/'.$partner['logo_pic'].'"/>'
                ]
            );
			
			$dayoption	=	['-1'=> array( 'label' => '--Please Select Weekdays--', 'value' => '-1'),'1'=>array('label'=>'Sunday','value'=>'sunday'),'2'=>array('label'=>'Monday','value'=>'monday'),'3'=>array('label'=>'Tuesday','value'=>'tuesday'),'4'=>array('label'=>'Wednesday','value'=>'wednesday'),'5'=>array('label'=>'Thursday','value'=>'thursday'),'6'=>array('label'=>'Friday','value'=>'friday'),'7'=>array('label'=>'Saturday','value'=>'saturday')];
			$offweekdays	=	array();
			if(isset($partner['off_week_days'])){
				$offweekdays 	=	explode(",", $partner['off_week_days']);
			}
			$fieldset->addField(
                'offweekdays',
                'multiselect',
                [
                    'name' => 'offweekdays',
					'class' => 'offtimeselector',
					'data-form-part' => $this->getData('target_form'),
                    'label' => __('Week Days'),
                    'title' => __('Week Days'),
					'value' => $offweekdays,
                    'values' =>  $dayoption,
				]
            );
			
			$monthoption	=	['-1'=> array( 'label' => '--Please Select Month--', 'value' => '-1'),'1'=>array('label'=>'January','value'=>'january'),'2'=>array('label'=>'Feburary','value'=>'feburary'),'3'=>array('label'=>'March','value'=>'march'),'4'=>array('label'=>'April','value'=>'april'),'5'=>array('label'=>'May','value'=>'may'),'6'=>array('label'=>'June','value'=>'june'),'7'=>array('label'=>'July','value'=>'july'),'8'=>array('label'=>'Augest','value'=>'augest'),'9'=>array('label'=>'September','value'=>'september'),'10'=>array('label'=>'October','value'=>'october'),'11'=>array('label'=>'November','value'=>'november'),'12'=>array('label'=>'December','value'=>'december')];
			
			$offmonths	=	array();
			if(isset($partner['off_months'])){
				$offmonths 	=	explode(",", $partner['off_months']);
			}
			$fieldset->addField(
                'offmonths',
                'multiselect',
                [
                    'name' => 'offmonths',
                    'label' => __('Month'),
					'data-form-part' => $this->getData('target_form'),
                    'title' => __('Month'),
                  	'value' =>  $offmonths,
                  	'values' =>  $monthoption,
					
                ]
            );
			$offdates	=	'';
			if(isset($partner['off_dates'])){
				$offdates 	=	$partner['off_dates'];
			}
			$fieldset->addField(
                'offdates',
                'text',
                [
                    'name' => 'offdates',
                    'class' =>'datepicker',
					'data-form-part' => $this->getData('target_form'),
                    'label' => __('Date'),
                    'title' => __('Date'),
                    'value' => $offdates,
                    
                ]
            );
			$backgroundcolor	=	'';
			if(isset($partner['backgroundcolor'])){
				$backgroundcolor 	=	$partner['backgroundcolor'];
			}
			$fieldset->addField(
                'backgroundcolor',
                'text',
                [
                    'name' => 'backgroundcolor',
                    'label' => __('Background Color'),
					'data-form-part' => $this->getData('target_form'),
                    'title' => __('Background Color'),
                    'value' => $backgroundcolor,
                    
                ]
            );
			
			$sequence	=	'';
			if(isset($partner['sequence'])){
				$sequence 	=	$partner['sequence'];
			}
			$fieldset->addField(
                'sequence',
                'text',
                [
                    'name' => 'sequence',
                    'label' => __('Display Order'),
					'data-form-part' => $this->getData('target_form'),
                    'title' => __('Display Order'),
                    'value' => $sequence,
                    
                ]
            );

        $this->setForm($form);
        return $this;
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
        $html .= $this->setTemplate('Webkul_Marketplace::customer/image.phtml')->toHtml(); 
        return $html;
    }
    /* public function getSelectOptions($selectoption){
        $options = [];
        foreach ($selectoption as $option) {
            $opt = explode("=>",$option);
            $options[] = ['label'=>$opt[1],'value'=>$opt[0]];
        }
        return $options;
    }
    public function getCustomFieldModel()
    {
        return $this->_objectManager->create('Webkul\CustomRegistration\Model\Customfields')->getCollection()->addFieldToFilter('status',1)->setOrder("setorder","ASC");
    }*/
}
?>

