<?php

namespace Dotsquares\ImportExport\Block\Adminhtml\Category;

class Import extends \Dotsquares\ImportExport\Block\Adminhtml\Import
{

	public function getTitle($type) {
		$types = array(
			'category' => __('Import Categories'),
			'attributes' => __('Import Category Attributes')
		);
		if (isset($types[$type])) return $types[$type];
		return '';
	}

	public function getFilePath() {
		
	
	$uploaddir = 'var/import/';
	
		if(!file_exists($uploaddir))
		{
			@mkdir( $uploaddir );			
		}
	
		return 'var/import/category';
	
	}

	public function getFileName($type) {
		$types = array(
			'category' => 'categories.csv',
			'attributes' => 'attributes.csv'
		);
		if (isset($types[$type])) return $types[$type];
		return '';
	}

	public function showExportInfo($type) {
		return $this->getLayout()->createBlock('dotsquares_import/adminhtml_category_import')
				->setTemplate('import/category/'.$type.'.phtml')
				->toHtml();
	}

	public function getAttributes() {
		return $this->helper('dotsquares_core/category')->getAttributes();
	}

}
