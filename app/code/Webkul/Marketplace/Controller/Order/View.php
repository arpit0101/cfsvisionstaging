<?php
namespace Webkul\Marketplace\Controller\Order;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\Filesystem\DirectoryList;

class View extends \Webkul\Marketplace\Controller\Order
{	
	protected $_formKeyValidator;
	protected $_fileUploaderFactory;
	protected $directory_list ;
	protected $_filesystem ;
	/**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
		$this->_formKeyValidator 	= $this->_objectManager->get('Magento\Framework\Data\Form\FormKey\Validator');
		$this->_fileUploaderFactory = $this->_objectManager->get('Magento\MediaStorage\Model\File\UploaderFactory');
		$this->_filesystem  		= $this->_objectManager->get('Magento\Framework\Filesystem');
		
        if ($order = $this->_initOrder()) {
			
			try{
				if($this->getRequest()->isPost()){
					list($datacol, $errors) = $this->validatePost();
					if(!empty($errors)){
						$this->messageManager->addError(__('Only PDF, DOC and DOCX files allowed.'));
						return $this->resultRedirectFactory->create()->setPath('*/*/view/id/'.$order->getId().'/', ['_secure'=>$this->getRequest()->isSecure()]);
					}else{
						
						$uploader = $this->_fileUploaderFactory->create(['fileId' => 'deliveryproof']);
		  
						$uploader->setAllowedExtensions(['doc', 'docx', 'pdf']);
						  
						$uploader->setAllowRenameFiles(true);
						  
						$uploader->setFilesDispersion(true);
					 
						$path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('documents/');
						
						$result = $uploader->save($path);
						
						$collection1	=	$this->_objectManager->create('Webkul\Marketplace\Model\Orders')->load($order->getId());
						$collection1->setDeliveryProofFile($result['file']);
						$collection1->save();
						
						$this->messageManager->addSuccess(__('You proof has been successfully uploaded.'));
						return $this->resultRedirectFactory->create()->setPath('*/*/view/id/'.$order->getId().'/', ['_secure'=>$this->getRequest()->isSecure()]);
					}
				}
			} catch (Exception $e) {
				$this->messageManager->addError($e->getMessage());
				return $this->resultRedirectFactory->create()->setPath('*/*/create', ['_secure'=>$this->getRequest()->isSecure()]);
			}
			
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Order #%1', $order->getRealOrderId()));
            return $resultPage;
        }else{
            return $this->resultRedirectFactory->create()->setPath('*/*/history', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
	
	private function validatePost()
    {
		$errors = array();
        $data = array();
        foreach( $this->getRequest()->getFiles() as $code => $value){
            switch ($code) :
                case 'deliveryproof':
					$exten 	= 	pathinfo(basename($value['name']), PATHINFO_EXTENSION);
					$mime 	= 	mime_content_type($value['tmp_name']);
					
					if (false === $ext = array_search(
						$mime,
						array(
							'pdf' => 'application/pdf',
							'doc' => 'application/msword',
							'docx' => 'application/msword',
						),
						true
					)){
						$errors[] = __('Name has to be completed');
					}
					break;
            endswitch;
        }
        return array($data, $errors);
    }
}
