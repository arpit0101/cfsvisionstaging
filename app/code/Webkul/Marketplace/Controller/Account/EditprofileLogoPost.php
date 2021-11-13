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

class EditprofileLogoPost extends Action
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
                // if (!$this->_formKeyValidator->validate($this->getRequest())) {
                //     return $this->resultRedirectFactory->create()->setPath('*/*/editProfile', ['_secure'=>$this->getRequest()->isSecure()]);
                // }
                //list($data, $errors) = $this->validateprofiledata();
                $fields = $this->getRequest()->getParams();             
                $seller_id=$this->_getSession()->getCustomerId();
                $img1='';
                $img2='';

                    $auto_id = 0;
                    $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                                    ->getCollection()
                                    ->addFieldToFilter('seller_id',$seller_id);
                    foreach($collection as  $value){
                        $auto_id = $value->getId();
                    }
                            
                    $value = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')->load($auto_id);
                    //print_r($fields); die;
                    //$value->addData($fields);
                    $value->setUpdatedAt($this->_date->gmtDate());
                    //$value->save();


                    $target = $this->_mediaDirectory->getAbsolutePath('avatar/');

                    
                   @chmod($target, 0755);
                   
                    
                    try{
                         if(strlen($_FILES['logo_pic']['name'])>0){
                            $image = getimagesize($_FILES['logo_pic']['tmp_name']);
                            if($image['mime']) {
                                $img2 = rand(1,99999).$_FILES["logo_pic"]["name"];
                                /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                                $uploader = $this->_fileUploaderFactory->create(['fileId' => 'logo_pic']);
                                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                                $uploader->setAllowRenameFiles(true);
                                $result = $uploader->save($target, $img2);
                                if($result['file']){                         
                                    $value->setLogoPic($result['file']);
                                }
                                
                                @chmod($target.$img2, 0644);
                            
                            }else{
                                $this->messageManager->addError(__("Disallowed file type."));
                            }

                        }

                        $value->save();

                            $this->messageManager->addSuccess(__('Logo was successfully saved'));
                        
                        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRefererUrl());
                    }catch (Mage_Core_Exception $e){
                        $this->messageManager->addError($e->getMessage());
                    }catch (Exception $e){
                        $this->messageManager->addException($e, __('We can\'t save the Logo.'));
                    }
                    return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRefererUrl());
               
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRefererUrl());
            }
        }
        else{
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRefererUrl());
            //return $this->resultRedirectFactory->create()->setPath('*/*/editProfile', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }

    private function validateprofiledata()
    {
        $errors = array();
        $data = array();
        foreach( $this->getRequest()->getParams() as $code => $value){
            switch ($code) :
                case 'twitter_id':
                    if(trim($value) != '' && preg_match('/[\'^£$%&*()}{@#~?><>, |=_+¬-]/', $value)){$errors[] = __('Twitterid cannot contain space and special charecters');} 
                    else{$data[$code] = $value;}
                    break;
                case 'facebook_id':
                    if(trim($value) != '' &&  preg_match('/[\'^£$%&*()}{@#~?><>, |=_+¬-]/', $value)){$errors[] = __('Facebookid cannot contain space and special charecters');} 
                    else{$data[$code] = $value;}
                    break;
                case 'background_width':
                    if(trim($value) != '' && strlen($value)!=6 && substr($value, 0, 1) != "#"){
                        $errors[] = __('Invalid Background Color');
                    } 
                    else{$data[$code] = $value;}
                    break;
            endswitch;
        }
        return array($data, $errors);
    }
}
