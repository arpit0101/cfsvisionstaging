<?php
namespace Dotsquares\ImportExport\Controller\Adminhtml\Index;

class Importcat extends \Magento\Backend\App\Action
{

	public function execute() {
		if ($this->getRequest()->getParam('type')) {
			$this->getResponse()->setBody($this->_view->getLayout()->createBlock('Dotsquares\ImportExport\Block\Adminhtml\Category\Import\Categories')->toHtml());
		$this->getResponse()->sendResponse();
		} else {
			$this->_view->loadLayout();
			$this->_view->getLayout()->initMessages();
			$this->_view->renderLayout();
		}
	}

}