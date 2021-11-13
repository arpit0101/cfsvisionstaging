<?php

/**
 *
 * @Author              Ngo Quang Cuong <bestearnmoney87@gmail.com>
 * @Date                2016-12-13 00:49:46
 * @Last modified by:   nquangcuong
 * @Last Modified time: 2016-12-13 15:45:47
 */

namespace PHPCuong\Region\Block\Adminhtml\Area\Edit;

use \Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{
    protected $_coreRegistry = null;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('area_form');
        $this->setTitle(__('Area Information'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post',
					'enctype' => 'multipart/form-data'
                ]
            ]
        );

        $form->setHtmlIdPrefix('area_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('General Information'),
                'class' => 'fieldset-wide'
            ]
        );

	
		$country_list = $this->_coreRegistry->registry('phpcuong_region_country_list');

        $fieldset->addField(
            'country_id',
            'select',
            [
                'name' => 'country_id',
                'label' => __('Country'),
                'title' => __('Country'),
                'required' => true,
                'values' => $country_list,
                'selected' => 'AE'
            ]
        );
        $city_list = $this->_coreRegistry->registry('phpcuong_region_list');
       
	   $fieldset->addField(
            'region_id',
            'select',
            [
                'name' => 'region_id',
                'label' => __('City'),
                'title' => __('City'),
               
                'values' => $city_list
                
            ]
        );

       

        $fieldset->addField(
            'default_name',
            'text',
            [
                'name' => 'default_name',
                'label' => __('Area Name'),
                'title' => __('Area Name'),
                'required' => true
            ]
        );
		
		$fieldset->addField(
			'area_image',
			 'file',
			 [
				'name' => 'area_image',
				'label' => __('Image'),
				'title' => __('Image'),
				'data-form-part' => $this->getData('target_form'),
				'note' => __('Allowed image types: jpg,png')
			  ]
		);

        $formData = $this->_coreRegistry->registry('phpcuong_area');
		
        if ($formData) {
            if ($formData->getAreaId()) {
                $fieldset->addField(
                    'area_id',
                    'hidden',
                    ['name' => 'area_id']
                );
            }
            $form->setValues($formData->getData());
        }

        $form->setUseContainer(true);
        $form->setMethod('post');
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
