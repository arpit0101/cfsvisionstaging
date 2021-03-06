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

class BecomesellerPost extends Action
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
        $loginUrl = $this->_objectManager->get('Magento\Customer\Model\Url')->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
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

        if($this->getRequest()->isPost()){
            try{
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath('marketplace/account/becomeseller', ['_secure'=>$this->getRequest()->isSecure()]);
                }
                $fields = $this->getRequest()->getParams();

                $profileurlcount =$this->_objectManager->create('Webkul\Marketplace\Model\Seller')->getCollection();
                $profileurlcount->addFieldToFilter('shop_url',$fields['profileurl']);
                if(!count($profileurlcount)){
                    $seller_id=$this->_getSession()->getCustomerId();
                    $status=$this->_objectManager->get('Webkul\Marketplace\Helper\Data')->getIsPartnerApproval()? 0:1;
                    $model = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')->getCollection()->addFieldToFilter('shop_url', $fields['profileurl']);
                    if(!count($model)){
                        if (isset($fields['partnertype']) && $fields['partnertype']) {
                            $auto_id = 0; 
                            $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                                            ->getCollection()
                                            ->addFieldToFilter('seller_id',$seller_id);
                            foreach($collection as  $value){
                                $auto_id = $value->getId();
                            }     
                            $value = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')->load($auto_id);
                            $value->setData('is_seller', $status);
                            $value->setData('shop_url', $fields['profileurl']);
                            $value->setData('seller_id', $seller_id);  
                            $value->setCreatedAt($this->_date->gmtDate());  
                            $value->setUpdatedAt($this->_date->gmtDate());
                            $value->save();
                            try{
                                if(!empty($errors)){
                                    foreach ($errors as $message){
                                        $this->messageManager->addError($message);
                                    }
                                }else{
                                    $this->messageManager->addSuccess(__('Profile information was successfully saved'));
                                }
                                return $this->resultRedirectFactory->create()->setPath('marketplace/account/becomeseller', ['_secure'=>$this->getRequest()->isSecure()]);
                            }catch (Mage_Core_Exception $e){
                                $this->messageManager->addError($e->getMessage());
                            }catch (Exception $e){
                                $this->messageManager->addException($e, __('We can\'t save the customer.'));
                            }
                            return $this->resultRedirectFactory->create()->setPath('marketplace/account/becomeseller', ['_secure'=>$this->getRequest()->isSecure()]);
                        }else{
                            $this->messageManager->addError(__('Please confirm that you want to become seller.'));
                            return $this->resultRedirectFactory->create()->setPath('marketplace/account/becomeseller', ['_secure'=>$this->getRequest()->isSecure()]);
                        }
                    }else{
                        $this->messageManager->addError(__('Shop URL already exist please set another.'));
                        return $this->resultRedirectFactory->create()->setPath('marketplace/account/becomeseller', ['_secure'=>$this->getRequest()->isSecure()]);
                    }
                }else{
                    $this->messageManager->addError(__('Shop URL already exist please set another.'));
                    return $this->resultRedirectFactory->create()->setPath('marketplace/account/becomeseller', ['_secure'=>$this->getRequest()->isSecure()]);
                }
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('marketplace/account/becomeseller', ['_secure'=>$this->getRequest()->isSecure()]);
            }
        }
        else{
            return $this->resultRedirectFactory->create()->setPath('marketplace/account/becomeseller', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
}
