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

class RewriteUrlPost extends Action
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
        PageFactory $resultPageFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_date = $date;
        $this->dateTime = $dateTime;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
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
                $fields = $this->getRequest()->getParams();             
                $seller_id=$this->_getSession()->getCustomerId();
                $auto_id = ''; 
                $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                                ->getCollection()
                                ->addFieldToFilter('seller_id',$seller_id);
                foreach($collection as  $value){
                    $auto_id = $value->getId();
                    $profileurl = $value->getShopUrl();
                }

                $getCurrentStoreId = $this->_objectManager->create('Webkul\Marketplace\Helper\Data')->getCurrentStoreId();

                if($fields['profile_request_url']){
                    $source_url = 'marketplace/seller/profile/shop/'.$profileurl;
                    /*
                    * Check if already rexist in url rewrite model
                    */
                    $url_id = '';
                    $profile_request_url = '';
                    $url_coll = $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')
                                    ->getCollection()
                                    ->addFieldToFilter('target_path',$source_url)
                                    ->addFieldToFilter('store_id',$getCurrentStoreId);
                    foreach ($url_coll as $value) {
                        $url_id = $value->getId();
                        $profile_request_url = $value->getRequestPath();
                    }
                    if($profile_request_url != $fields['profile_request_url']){
                        $id_path= rand(1,100000);
                        $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')->load($url_id)
                            ->setStoreId($getCurrentStoreId)
                            ->setIsSystem(0)
                            ->setIdPath($id_path)
                            ->setTargetPath($source_url)
                            ->setRequestPath($fields['profile_request_url'])
                            ->save();
                    }
                }
                if($fields['collection_request_url']){
                    $source_url = 'marketplace/seller/collection/shop/'.$profileurl;
                    /*
                    * Check if already rexist in url rewrite model
                    */
                    $url_id = '';
                    $collection_request_url = '';
                    $url_coll = $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')
                                    ->getCollection()
                                    ->addFieldToFilter('target_path',$source_url)
                                    ->addFieldToFilter('store_id',$getCurrentStoreId);
                    foreach ($url_coll as $value) {
                        $url_id = $value->getId();
                        $collection_request_url = $value->getRequestPath();
                    }
                    if($collection_request_url != $fields['collection_request_url']){
                        $id_path= rand(1,100000);
                        $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')->load($url_id)
                            ->setStoreId($getCurrentStoreId)
                            ->setIsSystem(0)
                            ->setIdPath($id_path)
                            ->setTargetPath($source_url)
                            ->setRequestPath($fields['collection_request_url'])
                            ->save();
                    }
                }
                if($fields['review_request_url']){
                    $source_url = 'marketplace/seller/feedback/shop/'.$profileurl;
                    /*
                    * Check if already rexist in url rewrite model
                    */
                    $url_id = '';
                    $review_request_url = '';
                    $url_coll = $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')
                                    ->getCollection()
                                    ->addFieldToFilter('target_path',array('eq'=>$source_url))
                                    ->addFieldToFilter('store_id',$getCurrentStoreId);
                    foreach ($url_coll as $value) {
                        $url_id = $value->getId();
                        $review_request_url = $value->getRequestPath();
                    }
                    if($review_request_url != $fields['review_request_url']){
                        $id_path= rand(1,100000);
                        $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')->load($url_id)
                            ->setStoreId($getCurrentStoreId)
                            ->setIsSystem(0)
                            ->setIdPath($id_path)
                            ->setTargetPath($source_url)
                            ->setRequestPath($fields['review_request_url'])
                            ->save();
                    }
                }
                if($fields['location_request_url']){
                    $source_url = 'marketplace/seller/location/shop/'.$profileurl;
                    /*
                    * Check if already rexist in url rewrite model
                    */
                    $url_id = '';
                    $location_request_url = '';
                    $url_coll = $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')
                                    ->getCollection()
                                    ->addFieldToFilter('target_path',array('eq'=>$source_url))
                                    ->addFieldToFilter('store_id',$getCurrentStoreId);
                    foreach ($url_coll as $value) {
                        $url_id = $value->getId();
                        $location_request_url = $value->getRequestPath();
                    }
                    if($location_request_url != $fields['location_request_url']){
                        $id_path= rand(1,100000);
                        $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')->load($url_id)
                            ->setStoreId($getCurrentStoreId)
                            ->setIsSystem(0)
                            ->setIdPath($id_path)
                            ->setTargetPath($source_url)
                            ->setRequestPath($fields['location_request_url'])
                            ->save();
                    }
                }
                $this->messageManager->addSuccess(__('The URL Rewrite has been saved.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/editProfile', ['_secure'=>$this->getRequest()->isSecure()]);
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('*/*/editProfile', ['_secure'=>$this->getRequest()->isSecure()]);
            }
        }
        else{
            return $this->resultRedirectFactory->create()->setPath('*/*/editProfile', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
}
