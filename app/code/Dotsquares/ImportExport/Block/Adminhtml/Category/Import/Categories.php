<?php

namespace Dotsquares\ImportExport\Block\Adminhtml\Category\Import;

use Magento\Framework\Exception\StateException;

class Categories extends \Dotsquares\ImportExport\Block\Adminhtml\Category\Import
{

	protected $dataHelper;
  
  public function __construct(
    \Magento\Backend\Block\Template\Context $context,
    \Magento\Framework\Json\Helper\Data $jsonHelper,  
    \Magento\Framework\ObjectManagerInterface $objectManager,
	\Dotsquares\ImportExport\Helper\Data $dataHelper,
    array $data = []
 ) {
	$this->dataHelper = $dataHelper;
    parent::__construct($context,$jsonHelper,$objectManager,$data);
  }
	
	public function _toHtml() {
		$type = $this->getRequest()->getParam('type');

		$title = $this->getTitle($type);
		
		$this->getHeadInfo($title);
		$this->getImportJavascripts($this->getUrl('*/index/'.$type));
		$this->getStartUpInfo();
		if ($title) {
			try {
				$this->openToImport($this->getFilePath(), $this->getFile());
				$i = 0;		
				while($csvData = $this->readCsvData() ) {
					if ($i == 0) {
						$this->addHeaderData($csvData);
					} else {
						$this->addContentData($csvData, 'add');
					}
					$i++;
				}
				$this->closeFile('send');

			} catch (Exception $e) {
				$this->showError($e->getMessage());
			}
		}
		$this->getEndInfo();
	}

	public function getFile() {
	
		$uploaddir = $this->getFilePath(). '/';
		
		if(!file_exists($uploaddir))
		{
			@mkdir( $uploaddir );			
		}
		try
		{
		$uploadfile = $uploaddir . basename($_FILES['import_filecat']['name']);
		
			if (move_uploaded_file($_FILES['import_filecat']['tmp_name'], $uploadfile)) {
			}
		}
		catch(\Exception $e){
			$message = __('File does not exist: "%s".', $e->getMessage());
			$this->writeErrorLog($message);
		}
		//$retval = $this->getRequest()->getPost('filename');
		$retval = basename($_FILES['import_filecat']['name']);
	
		if (!$retval) $retval = 'categories.csv';
		return $retval;
		
	}

	protected $_headerCodes;
	public function getHeaderField($header) {
		if (is_null($this->_headerCodes)) {
			$this->_headerCodes = array();
			$ID = $this->getRequest()->getParam('cat_id');
			$attributes = $this->dataHelper->getAttributes($ID);
			foreach($attributes as $attribute) {
				$this->_headerCodes[$attribute['label']] = $attribute['field'];
			}
		}
		if (isset($this->_headerCodes[$header])) return $this->_headerCodes[$header];
		else {
			$this->showError('The header field '.$header.' is not a valid one.');
			exit;
			//throw new StateException('The header field %s is not a valid one.', $header);

		}
	}

}