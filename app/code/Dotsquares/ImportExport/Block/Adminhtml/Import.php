<?php

namespace Dotsquares\ImportExport\Block\Adminhtml;
use Magento\Backend\Block\Template;

class Import extends Template
{
	protected $jsonHelper;
	
	protected $objectManager;
	
	protected $_fileread;
	
	
	public function __construct(
    \Magento\Backend\Block\Template\Context $context,
    \Magento\Framework\Json\Helper\Data $jsonHelper,  
    \Magento\Framework\ObjectManagerInterface $objectManager,
    array $data = []
 ) {
    parent::__construct($context, $data);
    $this->jsonHelper = $jsonHelper;
    $this->_objectManager = $objectManager;
  }
	
	public function getImportUrl($action, $type) {
		return $this->getUrl('*/*/'.$action, array('type' => $type));
	}

	public function getHeadInfo($title) {
		
		echo '<html><head>';
		echo '<title>'.$title.'</title>';
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
		echo '<script type="text/javascript">var FORM_KEY = "'.$this->getFormKey().'";</script>';
		
		$js_asset = $this->getViewFileUrl('Dotsquares_ImportExport::js/prototype.js');
		echo '<script src='.$js_asset.'></script>';
		echo '<style type="text/css">';
		echo 'ul { list-style-type:none; padding:0; margin:0; }';
		echo 'li { margin-left:0; border:1px solid #ccc; margin:2px; padding:2px 2px 2px 2px; font:normal 12px sans-serif; }';
		echo 'img { margin-right:5px; }';
		echo 'a.back {color: #ED6502; font-weight: bold; text-decoration: none; }';
		echo '</style>';
		echo '</head>';
	}

	public function getImportJavascripts($url) {
		$batchConfig = array(
			'styles' => array(
				'error' => array(
					'icon' => $this->getViewFileUrl('Dotsquares_ImportExport::images/error_msg_icon.gif'),
					'bg'   => '#FDD'
				),
				'message' => array(
					'icon' => $this->getViewFileUrl('Dotsquares_ImportExport::images/fam_bullet_success.gif'),
					'bg'   => '#DDF'
				),
				'loader'  => $this->getViewFileUrl('Dotsquares_ImportExport::images/eajax-loader.gif')
			),
			'template' => '<li style="#{style}" id="#{id}">'
						. '<img id="#{id}_img" src="#{image}" class="v-middle" style="margin-right:5px"/>'
						. '<span id="#{id}_status" class="text">#{text}</span>'
						. '</li>'
		);
		echo '<script>';
		echo 'var importData = [];';
		echo 'var config= '. $this->jsonHelper->jsonEncode($batchConfig).';';
		echo '
		function addImportData(data) {
			importData.push(data);
		}

		function sendAllData() {
			sendImportData();
		}';
		echo
		'var importCount = 0;
		function sendImportData(data) {
			var cImport = "async";
			if (!data) {
				if (importData.length == 0) return;
				data = importData.shift();
				cImport = "sync";
			}
			if (!data.form_key) {
				data.form_key = FORM_KEY;
			}

			new Ajax.Request("'.$url.'", {
				method: "post",
				parameters: data,
				onSuccess: function(transport) {
					importCount++;
					if (transport.responseText.isJSON()) {
						var retdata = transport.responseText.evalJSON();
						if (retdata.error) {
							$("row_content_"+retdata.count+"").style.backgroundColor = config.styles.error.bg;
							$("row_content_"+retdata.count+"_img").src = config.styles.error.icon;
							$("row_content_"+retdata.count+"_text").innerHTML = retdata.error;
						} else {
							$("row_content_"+retdata.count+"").style.backgroundColor = config.styles.message.bg;
							$("row_content_"+retdata.count+"_img").src = config.styles.message.icon;
							$("row_content_"+retdata.count+"_text").innerHTML = retdata.message;
						}
					} else {
						$("row_content_"+data.count+"").style.backgroundColor = config.styles.error.bg;
						$("row_content_"+data.count+"_img").src = config.styles.error.icon;
						$("row_content_"+data.count+"_text").innerHTML = transport.responseText;
					}
					if (importCount == totalCount) {
						$("finished_exec").style.display = "";
					}
					if (cImport == "sync") {
						sendImportData();
					}
				}
			});
		}';
		echo '</script>';
	}

	public function getStartUpInfo() {
		echo '<body>';
		echo '<ul id="profileRowStart">';
		$this->showNote(__("Starting profile execution, please wait..."));
		$this->showWarning(__("Warning: Please do not close the window during importing data"));
		echo '</ul>';
		echo '<ul id="profileRows">';
	}

	public function getEndInfo() {
		echo '</ul>';
		echo '<ul id="finished_exec" style="display:none;">';
		$this->showNote(__("Finished profile execution."));
		$this->showNote('<a href="'.$this->getUrl('*/cache').'">'.__('Please refresh the cache.').'</a>');
		echo "</ul>";
		echo '<script>var totalCount = '.$this->_count.';</script>';
		echo '</body></html>';
	}

	public function showError($text, $id = '') {
		echo '<li style="background-color:#FDD; " id="'.$id.'">';
		echo '<img src="'.$this->getViewFileUrl('Dotsquares_ImportExport::images/error_msg_icon.gif').'" class="v-middle"/>';
		echo $text;
		echo "</li>";
	}

	public function showWarning($text, $id = '') {
		echo '<li id="'.$id.'" style="background-color:#FFD;">';
		echo '<img src="'.$this->getViewFileUrl('Dotsquares_ImportExport::images/fam_bullet_error.gif').'" class="v-middle" style="margin-right:5px"/>';
		echo $text;
		echo '</li>';
	}

	public function showNote($text, $id = '', $style = '') {
		echo '<li id="'.$id.'" style="'.$style.'">';
		echo '<img src="'.$this->getViewFileUrl('Dotsquares_ImportExport::images/note_msg_icon.gif').'" class="v-middle" style="margin-right:5px"/>';
		echo $text;
		echo '</li>';
	}

	public function showSuccess($text, $id = '', $style = '') {
		echo '<li id="'.$id.'" style="background-color:#DDF; '.$style.'">';
		echo '<img src="'.$this->getViewFileUrl('Dotsquares_ImportExport::images/fam_bullet_success.gif').'" class="v-middle" style="margin-right:5px"/>';
		echo $text;
		echo '</li>';
	}

	public function showProgress($text, $id = '', $style = '') {
		echo '<li id="'.$id.'" style="background-color:#DDF; '.$style.'">';
		echo '<img id="'.$id.'_img" src="'.$this->getViewFileUrl('Dotsquares_ImportExport::images/ajax-loader.gif').'" class="v-middle" style="margin-right:5px"/>';
		echo '<span id="'.$id.'_text">'.$text.'<span>';
		echo '</li>';
	}

	protected $_resource;
	public function openToImport($path, $file) {
		$baseDir = $this->getBaseDir();
		//$this->_resource = new Varien_Io_File();
		$filesystem = $this->_objectManager->get('Magento\Framework\Filesystem');
		$filepath = $baseDir . '/' . trim($path, '/');
		//echo $realPath = realpath($filepath);
		
		if ($filepath === false) {
			$message = __('The destination folder "%s" does not exist.', $path);
			//Mage::throwException($message);
			echo $message;
		}
		
		try {
			$writer = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
			$this->_fileread = $writer->openFile('import/category/'.$file, 'r');
			//$csvData = $fileread->readCsv();
			//$this->_resource->open(array('path' => $filepath));
			//$this->_resource->streamOpen($file, 'r+');
		} catch (Exception $e) {
			$message = __('An error occurred while opening file: "%s".', $e->getMessage());
			//Mage::throwException($message);
			echo $message;
		}
		$this->showSuccess(__("Starting import profile execution."));
		$this->showSuccess(__('Found <span id="row_count">0</span> rows.'));
	}

	public function readCsvData() {
		$csvData = $this->_fileread->readCsv();
		return $csvData;
	}

	public function closeFile($opt = '') {
		$this->_fileread->close();
		if ($opt == 'send') {
			echo '<script>sendAllData();</script>';
		}
	}

	protected $_csvHeader = array();
	public function addHeaderData($data) {
		foreach ($data as $val) {
			$this->_csvHeader[] = $this->getHeaderField($val);
		}
	}

	protected $_count = 0;
	public function addContentData($data, $opt = 'send') {
		$this->_count++;

		$contentData = array('count' => $this->_count);
		foreach($data as $key => $val) {
			if (isset($this->_csvHeader[$key])) $contentData[$this->_csvHeader[$key]] = $val;
		}
		$text = sprintf(__("Saving row no %d."), $this->_count); 
		$this->showProgress($text, 'row_content_'.$this->_count);
		echo '<script>';
		echo '$("row_count").innerHTML = "'.$this->_count.'";';
		if ($opt == 'add') {
			echo 'addImportData('. $this->jsonHelper->jsonEncode($contentData).');';
		} else {
			echo 'sendImportData('. $this->jsonHelper->jsonEncode($contentData).');';
		}
		
		echo '</script>';
	}


}
