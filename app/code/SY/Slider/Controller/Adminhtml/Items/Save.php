<?php
/**
 * Slider
 * 
 * @author Slava Yurthev
 */
namespace SY\Slider\Controller\Adminhtml\Items;

use \Magento\Backend\App\Action;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;

class Save extends Action {
	protected $_resultPageFactory;
	protected $_resultPage;
	public function __construct(
			Context $context, 
			PageFactory $resultPageFactory,
			\Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
		){
		parent::__construct($context);
		$this->_fileUploaderFactory = $fileUploaderFactory;
		$this->_resultPageFactory = $resultPageFactory;
	}
	public function execute(){
		$object_manager = $this->_objectManager;
		$directory = $object_manager->get('\Magento\Framework\Filesystem\DirectoryList');
		$data = $this->getRequest()->getPostValue();
		$resultRedirect = $this->resultRedirectFactory->create();
		$id = $this->getRequest()->getParam('id');
		$model = $this->_objectManager->create('SY\Slider\Model\Item');
		$image = false;
		if($id) {
			$model->load($id);
			if($model->getData('image')){
				$image = $model->getData('image');
			}
		}
		$model->setData($data);
		try {
			$model->save();
			try {
				$uploader = $this->_fileUploaderFactory->create(['fileId' => 'image']);
				$uploader->setAllowCreateFolders(true);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'jfif']);
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
                $path = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('slider/'.$model->getId().'/');
                $results = $uploader->save($path);
				$filename = $results['file'];
				if ($filename) {
					$model->setData('image', '/slider/'.$model->getId().'/'.$filename);
					try {
						$model->save();
						/* if($image){
							@unlink($directory->getRoot().$image);
						} */
					} catch (\Exception $e) {
						$this->messageManager->addException($e, $e->getMessage());
					}
				}
			} catch (\Exception $e) {
				if ($e->getCode() != \Magento\Framework\File\Uploader::TMP_NAME_EMPTY) {
					$this->messageManager->addException($e, $e->getMessage());
				}
			}
			$this->messageManager->addSuccess(__('Saved.'));
			if ($this->getRequest()->getParam('back')) {
				return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
			}
			$this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
			return $resultRedirect->setPath('*/*/');
		} catch (\Exception $e) {
			$this->messageManager->addException($e, __('Something went wrong.'));
		}
		$this->_getSession()->setFormData($data);
		return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
	}
	protected function _isAllowed(){
		return $this->_authorization->isAllowed('SY_Slider::items');
	}
}