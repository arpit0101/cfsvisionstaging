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

class EditprofilePost extends Action
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
                    return $this->resultRedirectFactory->create()->setPath('*/*/editProfile', ['_secure'=>$this->getRequest()->isSecure()]);
                }
                list($data, $errors) = $this->validateprofiledata();
                $fields = $this->getRequest()->getParams();             
                $seller_id=$this->_getSession()->getCustomerId();
                $img1='';
                $img2='';
                if(empty($errors)){ 
                    $auto_id = 0;
                    $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                                    ->getCollection()
                                    ->addFieldToFilter('seller_id',$seller_id);
                    foreach($collection as  $value){
                        $auto_id = $value->getId();
                    }
                    if(!isset($fields['tw_active'])){
                        $fields['tw_active']=0;
                    }
                    if(!isset($fields['fb_active'])){
                        $fields['fb_active']=0;
                    }
                    if(!isset($fields['gplus_active'])){
                        $fields['gplus_active']=0;
                    }
                    if(!isset($fields['youtube_active'])){
                        $fields['youtube_active']=0;
                    }
                    if(!isset($fields['vimeo_active'])){
                        $fields['vimeo_active']=0;
                    }
                    if(!isset($fields['instagram_active'])){
                        $fields['instagram_active']=0;
                    }
                    if(!isset($fields['pinterest_active'])){
                        $fields['pinterest_active']=0;
                    }
                    if(!isset($fields['moleskine_active'])){
                        $fields['moleskine_active']=0;
                    }
                            
                    $value = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')->load($auto_id);
                    //print_r($fields); die;
                    $value->addData($fields);
                    $value->setUpdatedAt($this->_date->gmtDate());
                    $value->save();

                    if($fields['company_description']){
                        $fields['company_description'] = str_replace('script', '', $fields['company_description']);
                    }
                    $value->setCompanyDescription($fields['company_description']);

                    if(isset($fields['return_policy'])){
                        $fields['return_policy'] = str_replace('script', '', $fields['return_policy']);
                        $value->setReturnPolicy($fields['return_policy']);
                    }               

                    if(isset($fields['shipping_policy'])){
                        $fields['shipping_policy'] = str_replace('script', '', $fields['shipping_policy']);
                        $value->setShippingPolicy($fields['shipping_policy']);
                    }               
                    // var_dump(get_class_methods($value)); die;
                    $value->setMetaDescription($fields['meta_description']);

                    $target = $this->_mediaDirectory->getAbsolutePath('avatar/');
                    
                    @chmod($target, 0755);

                    //echo "<pre>";print_r($_FILES);exit;
                    if(strlen($_FILES['banner_pic']['name'])>0){
                        if(strlen($_FILES['banner_pic']['tmp_name'])>0){
                            $image = getimagesize($_FILES['banner_pic']['tmp_name']);
                            if($image['mime']) {
                                $img1 = rand(1,99999).$_FILES["banner_pic"]["name"];
                                /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                                $uploader = $this->_fileUploaderFactory->create(['fileId' => 'banner_pic']);
                                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                                $uploader->setAllowRenameFiles(true);
                                $result = $uploader->save($target,$img1);
                                if($result['file']){                         
                                    $value->setBannerPic($result['file']);
                                }
                                
                                @chmod($target.$img1, 0644);
                            
                            }else{
                                $this->messageManager->addError(__("Disallowed file type."));
                            }
                        }
                        else{
                            $this->messageManager->addError(__("Banner File size is exceeded."));
                        }
                    }

                    /* Slider Images*/

                    if(strlen($_FILES['slider_pic1']['name'])>0){
                        if(strlen($_FILES['slider_pic1']['tmp_name'])>0){ 
                            $image = getimagesize($_FILES['slider_pic1']['tmp_name']);
                            if($image['mime']) {
                                $img1 = rand(1,99999).$_FILES["slider_pic1"]["name"];
                                /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                                $uploader = $this->_fileUploaderFactory->create(['fileId' => 'slider_pic1']);
                                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                                $uploader->setAllowRenameFiles(true);
                                $result = $uploader->save($target,$img1);
                                if($result['file']){                         
                                    //$value->setSliderPic1($result['file']);
                                    $value->addData(array("slider_pic1"=>$result['file']));
                                }
                                
                                @chmod($target.$img1, 0644);
                            
                            }else{
                                $this->messageManager->addError(__("Disallowed file type."));
                            }
                        }
                        else{
                            $this->messageManager->addError(__("Slider 1 File size is exceeded."));
                        }

                    }

                    if(strlen($_FILES['slider_pic2']['name'])>0){
                        if(strlen($_FILES['slider_pic2']['tmp_name'])>0){ 
                            $image = getimagesize($_FILES['slider_pic2']['tmp_name']);
                            if($image['mime']) {
                                $img1 = rand(1,99999).$_FILES["slider_pic2"]["name"];
                                /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                                $uploader = $this->_fileUploaderFactory->create(['fileId' => 'slider_pic2']);
                                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                                $uploader->setAllowRenameFiles(true);
                                $result = $uploader->save($target,$img1);
                                if($result['file']){                         
                                    // $value->setSliderPic2($result['file']);
                                    $value->addData(array("slider_pic2"=>$result['file']));
                                }

                                @chmod($target.$img1, 0644);
                            
                            }else{
                                $this->messageManager->addError(__("Disallowed file type."));
                            }
                        }
                        else{
                            $this->messageManager->addError(__("Slider 2 File size is exceeded."));
                        }
                    }

                    if(strlen($_FILES['slider_pic3']['name'])>0){
                        if(strlen($_FILES['slider_pic3']['tmp_name'])>0){ 
                            $image = getimagesize($_FILES['slider_pic3']['tmp_name']);
                            if($image['mime']) {
                                $img1 = rand(1,99999).$_FILES["slider_pic3"]["name"];
                                /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                                $uploader = $this->_fileUploaderFactory->create(['fileId' => 'slider_pic3']);
                                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                                $uploader->setAllowRenameFiles(true);
                                $result = $uploader->save($target,$img1);
                                if($result['file']){                         
                                    // $value->setSliderPic3($result['file']);
                                    $value->addData(array("slider_pic3"=>$result['file']));
                                }

                                @chmod($target.$img1, 0644);

                            }else{
                                $this->messageManager->addError(__("Disallowed file type."));
                            }
                        }
                        else{
                            $this->messageManager->addError(__("Slider 3 File size is exceeded."));
                        }
                    }

                    if(strlen($_FILES['logo_pic']['name'])>0){
						
                        if(strlen($_FILES['logo_pic']['tmp_name'])>0){ 
                            $image = getimagesize($_FILES['logo_pic']['tmp_name']);
							
                            if($image['mime']) {
                                $img2 = rand(1,99999).$_FILES["logo_pic"]["name"];
                                /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                                $uploader = $this->_fileUploaderFactory->create(['fileId' => 'logo_pic']);
                                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                                $uploader->setAllowRenameFiles(true);
                                $result = $uploader->save($target,$img2);
                                if($result['file']){                         
                                    $value->setLogoPic($result['file']);
                                }

                                @chmod($target.$img2, 0777);
                            
                            }else{
                                $this->messageManager->addError(__("Disallowed file type."));
                            }
                        }
                        else{
                            $this->messageManager->addError(__("Logo File size is exceeded."));
                        }

                    }
                    if (array_key_exists('country_pic', $fields)) {
                        $value->setCountryPic($fields['country_pic']);
                    }
                    $value->save();
                    try{
                        if(!empty($errors)){
                            foreach ($errors as $message){
                                $this->messageManager->addError($message);
                            }
                        }else{
                            $this->messageManager->addSuccess(__('Profile information was successfully saved'));
                        }
                        return $this->resultRedirectFactory->create()->setPath('*/*/editProfile', ['_secure'=>$this->getRequest()->isSecure()]);
                    }catch (Mage_Core_Exception $e){
                        $this->messageManager->addError($e->getMessage());
                    }catch (Exception $e){
                        $this->messageManager->addException($e, __('We can\'t save the customer.'));
                    }
                    return $this->resultRedirectFactory->create()->setPath('*/*/editProfile', ['_secure'=>$this->getRequest()->isSecure()]);
                }else{
                    foreach ($errors as $message) {
                        $this->messageManager->addError($message);
                    }
                    
                    return $this->resultRedirectFactory->create()->setPath('*/*/editProfile', ['_secure'=>$this->getRequest()->isSecure()]);
                }
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('*/*/editProfile', ['_secure'=>$this->getRequest()->isSecure()]);
            }
        }
        else{
            return $this->resultRedirectFactory->create()->setPath('*/*/editProfile', ['_secure'=>$this->getRequest()->isSecure()]);
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
