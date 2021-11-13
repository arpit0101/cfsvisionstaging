<?php
namespace Webkul\Marketplace\Controller\Product;
use Magento\Framework\App\Action\Action;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\View\Result\PageFactory;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\RequestInterface;

class Create extends Action
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
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct(
            $context
        );
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
     * Retrieve customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }


    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {        
        try{
            if($this->getRequest()->isPost()){
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath('*/*/create', ['_secure'=>$this->getRequest()->isSecure()]);
                }
                $set=$this->getRequest()->getParam('set');
                $type=$this->getRequest()->getParam('type');
                if(isset($set) && isset($type)){
                    $allowedsets=explode(',',$this->_objectManager->get('Webkul\Marketplace\Helper\Data')->getAllowedAttributesetIds());
                    $allowedtypes=explode(',',$this->_objectManager->get('Webkul\Marketplace\Helper\Data')->getAllowedProductType());
                    if(!in_array($type,$allowedtypes) || !in_array($set,$allowedsets)){
                        $this->messageManager->addError('Product Type Invalid Or Not Allowed');
                        return $this->resultRedirectFactory->create()->setPath('*/*/create', ['_secure'=>$this->getRequest()->isSecure()]);
                    }
                    $this->_getSession()->setAttributeSet($set);
                    return $this->resultRedirectFactory->create()->setPath('*/*/add',['set'=>$set,'type'=>$type, '_secure'=>$this->getRequest()->isSecure()]);
                }else{
                    $this->messageManager->addError(__('Please select attribute set and product type.'));
                    return $this->resultRedirectFactory->create()->setPath('*/*/create', ['_secure'=>$this->getRequest()->isSecure()]);
                }
            }else{
                /** @var \Magento\Framework\View\Result\Page $resultPage */
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->set(__('Add New Product'));
                return $resultPage;
            }
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('*/*/create', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
}
