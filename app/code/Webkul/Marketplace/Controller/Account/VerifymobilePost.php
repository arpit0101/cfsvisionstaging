<?php
namespace Webkul\Marketplace\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\View\Result\PageFactory;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\RequestInterface;

class VerifymobilePost extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * File Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param PageFactory $resultPageFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        Filesystem $filesystem,
        PageFactory $resultPageFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_date = $date;
        $this->dateTime = $dateTime;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        parent::__construct(
            $context
        );
    }

    /**
     * Retrieve customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }
    
    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        /*$loginUrl = $this->_objectManager->get('Magento\Customer\Model\Url')->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }*/
        return parent::dispatch($request);
    }   

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $session = $this->_objectManager->create('Magento\Customer\Model\Session');
        if($this->getRequest()->isPost()){
            try{
              
                $fields = $this->getRequest()->getParams();
                //print_r($fields);
                //$session->setData("current_customer_id",100);
                $current_customer_id = $session->getData("current_customer_id");

                $collection =$this->_objectManager->create('Webkul\Marketplace\Model\Verifymobile')->getCollection();
                $collection->addFieldToFilter('otp', $fields['otp']);
                $collection->addFieldToFilter('customer_id', $current_customer_id); 
                if(count($collection)){
                   
                           
                            foreach($collection as  $value){
                                $auto_id = $value->getId();
                            }     
                                $value = $this->_objectManager->create('Webkul\Marketplace\Model\Verifymobile')->load($auto_id);
                                $value->setData('status', 1);
                                $value->setUpdatedAt($this->_date->gmtDate());
                                $value->save();
                            $this->messageManager->addSuccess(__('Successfully verified'));
                            return $this->resultRedirectFactory->create()->setPath('marketplace/account/choosemembership', ['_secure'=>$this->getRequest()->isSecure()]);
                        }else{
                            $this->messageManager->addError(__('Invalid OTP.'));
                            return $this->resultRedirectFactory->create()->setPath('marketplace/account/verifymobile', ['_secure'=>$this->getRequest()->isSecure()]);
                        }
                    
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('marketplace/account/verifymobile', ['_secure'=>$this->getRequest()->isSecure()]);
            }
        }
        else{
            return $this->resultRedirectFactory->create()->setPath('marketplace/account/verifymobile', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
}
