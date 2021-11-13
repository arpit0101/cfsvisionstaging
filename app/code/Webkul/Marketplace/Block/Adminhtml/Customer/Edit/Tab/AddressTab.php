<?php
namespace Webkul\Marketplace\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
/**
 * Customer account form block
 */
class AddressTab extends Generic implements TabInterface
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
        return __('Manager Locations');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Manager Locations');
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
	
<?php
        if (!$this->canShowTab()) {
            return $this;
        }
        /**@var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('marketplace_');
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $storeid = $this->_storeManager->getStore()->getId();

       
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
			
			$countries 	= $this->_country;
			
            $country = $fieldset->addField(
                'country_id',
                'select',
                [
                    'name' => 'country_id',
					'required' => true,
                    'label' => __('Country'),
                    'title' => __('Country'),
                    'values' => $countries->toOptionArray(),
                    'value' => isset($partner['country_id'])?$partner['country_id']:0,
                ]
            );
			$partner['region_id']	=	isset($partner['region_id'])?$partner['region_id']:0;
            $fieldset->addField(
                'region_id',
                'select',
                [
                    'name' => 'region_id',
					'required' => true,
                    'label' => __('Region'),
                    'title' => __('Region'),
                    'values' =>  ['--Please Select Country--'],
					'value' => $partner['region_id'],
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
                    'label' => __('Area'),
                    'title' => __('Area'),
                    'values' =>  ['-1'=> array( 'label' => '--Please Select Region--', 'value' => '-1')],
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
									$('.date').datepicker({
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
						   
								jQuery('#marketplace_type').change(function(){
									thisval = jQuery(this).val();
									jQuery('.field-type').nextAll().hide();
									jQuery('.field-add_more').show();
									jQuery('.field-'+thisval).show();
								});	
								
								jQuery('#marketplace_add_more').click(function(){
									var html = '<div class=admin__field field field-type  data-ui-id=seller-edit-addseller-tab-view-fieldset-element-form-field-type> <label class=label admin__field-label for=marketplace_type data-ui-id=seller-edit-addseller-tab-view-fieldset-element-select-type-label><span>Off Duration</span></label><div class=admin__field-control control><select id=marketplace_type name=type title=Type class= select admin__control-select data-ui-id=seller-edit-addseller-tab-view-fieldset-element-select-type><option value=0 selected=selected>Select Type</option><option value=day>Day</option><option value=date>Date</option><option value=month>Month</option></select>	</div></div><div class=admin__field field field-day  data-ui-id=seller-edit-addseller-tab-view-fieldset-element-form-field-day style=display: none;><label class=label admin__field-label for=marketplace_day data-ui-id=seller-edit-addseller-tab-view-fieldset-element-select-weekdays-label><span>Week Days</span></label><div class=admin__field-control control><select id=marketplace_day name=weekdays[] class=offtimeselector select multiselect admin__control-multiselect title=Week Days size=10 data-ui-id=seller-edit-addseller-tab-view-fieldset-element-select-weekdays multiple=multiple><option value=-1>--Please Select Weekdays--</option><option value=sunday>Sunday</option><option value=monday>Monday</option><option value=tuesday>Tuesday</option><option value=wednesday>Wednesday</option><option value=thursday>Thursday</option><option value=friday>Friday</option><option value=saturday>Saturday</option></select></div></div><div class=admin__field field field-month  data-ui-id=seller-edit-addseller-tab-view-fieldset-element-form-field-month style=display: block;><label class=label admin__field-label for=marketplace_month data-ui-id=seller-edit-addseller-tab-view-fieldset-element-select-month-label><span>Month</span></label><div class=admin__field-control control><select id=marketplace_month name=month[] class=offtimeselector select multiselect admin__control-multiselect title=Month size=10 data-ui-id=seller-edit-addseller-tab-view-fieldset-element-select-month multiple=multiple><option value=-1>--Please Select Month--</option><option value=january>January</option><option value=feburary>Feburary</option><option value=march>March</option><option value=april>April</option><option value=may>May</option><option value=june>June</option><option value=july>July</option><option value=augest>Augest</option><option value=september>September</option><option value=october>October</option><option value=november>November</option><option value=december>December</option></select></div></div><div class=admin__field field field-date  data-ui-id=seller-edit-addseller-tab-view-fieldset-element-form-field-date style=display: none;><label class=label admin__field-label for=marketplace_date data-ui-id=seller-edit-addseller-tab-view-fieldset-element-text-date-label><span>Date</span></label><div class=admin__field-control control><input id=marketplace_date name=date data-ui-id=seller-edit-addseller-tab-view-fieldset-element-text-date value= class=offtimeselector date datepicker input-text admin__control-text title=Date type=text></div></div>'
									jQuery('#marketplace_base_fieldset').append(html);
								})
								/* if($('#marketplace_country_id').val()!=''){ 
							   
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
											option.attr({ 'value': '' }).text('--Please select Region--');
											$('#marketplace_region_id').append(option);
											var old_region_id 	=	$('#old_region_id').val();
											$.each(data, function(index, region){
												var option = $('<option/>');
												if(old_region_id==region.region_id){
													option.attr({ 'selected': 'selected' });
												}
												option.attr({ 'value': region.region_id }).text(region.default_name);
												$('#marketplace_region_id').append(option);
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
														
														$.each(data, function(index, area){
															var option = $('<option/>');
															if(old_area_id==area.area_id){
																option.attr({ 'selected': 'selected' });
															}
															option.attr({ 'value': area.area_id }).text(area.default_name);
															$('#marketplace_area_id').append(option);
														});
														jQuery('#marketplace_area_id').val(old_areas);
													}
												});
											}
										}
									});
								} */
						   });
						}

					);
					</script>"
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
  /* public function getFormHtml()
    {
        $html = parent::getFormHtml();
        $html .= $this->getLayout()->createBlock(
            'Webkul\CustomRegistration\Block\Adminhtml\Customer\Edit\Button'
        )->toHtml();
        return $html;
    }
    public function getSelectOptions($selectoption){
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

