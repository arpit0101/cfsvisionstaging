<?php

/**
 *
 * @Author              Ngo Quang Cuong <bestearnmoney87@gmail.com>
 * @Date                2016-12-13 01:36:35
 * @Last modified by:   nquangcuong
 * @Last Modified time: 2016-12-13 14:29:04
 */

namespace PHPCuong\Region\Controller\Adminhtml\Area;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Inspection\Exception;

class Save extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $coreRegistry,
		\Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
		$this->_fileUploaderFactory = $fileUploaderFactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();
		
        if ($data) {

            $id = $this->getRequest()->getParam('area_id');
			
            /** @var \PHPCuong\Region\Model\Region $model */
            $model = $this->_objectManager->create('PHPCuong\Region\Model\Area')->load($id);
			
			try {
				$uploader = $this->_fileUploaderFactory->create(['fileId' => 'area_image']);
				$uploader->setAllowCreateFolders(true);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
                $path = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('area/'.$model->getId().'/');
                $results = $uploader->save($path);
				//echo "<pre>"; print_r($results); exit;
				$filename = $results['file'];
				if ($filename) {
					$model->setData('area_image', 'area/'.$model->getId().'/'.$filename);
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
			
            if (!$model->getAreaId() && $id) {
                $this->messageManager->addError(__('This area no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $model->setData($data);
			
			
            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved the area.'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['area_id' => $model->getAreaId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) { 
				echo $e;die;
                $this->messageManager->addException($e, __('Something went wrong while saving the area.'));
            }

            $this->_getSession()->setFormData($data);
            if ($this->getRequest()->getParam('area_id')) {
                return $resultRedirect->setPath('*/*/edit', ['area_id' => $this->getRequest()->getParam('area_id')]);
            }
            return $resultRedirect->setPath('*/*/new');
        }
        return $resultRedirect->setPath('*/*/');
    }
}
