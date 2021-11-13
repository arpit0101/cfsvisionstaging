<?php
namespace Webkul\Marketplace\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;

class Add extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = ['edit'];

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Webkul\Marketplace\Controller\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Webkul\Marketplace\Controller\Product\Builder $productBuilder,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory, 
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
        parent::__construct(
            $context
        );
        $this->productBuilder = $productBuilder;
        $this->resultPageFactory = $resultPageFactory;
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
        try{
            $set=$this->getRequest()->getParam('set');
            $type=$this->getRequest()->getParam('type');
            if(isset($set) && isset($type)){
                $allowedsets=explode(',',$this->_objectManager->get('Webkul\Marketplace\Helper\Data')->getAllowedAttributesetIds());
                $allowedtypes=explode(',',$this->_objectManager->get('Webkul\Marketplace\Helper\Data')->getAllowedProductType());
                if(!in_array($type,$allowedtypes) || !in_array($set,$allowedsets)){
                    $this->messageManager->addError('Product Type Invalid or Not Allowed');
                    return $this->resultRedirectFactory->create()->setPath('*/*/create', ['_secure'=>$this->getRequest()->isSecure()]);
                }                    
                $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
                $product = $this->productBuilder->build($this->getRequest()->getParams(),$helper->getCurrentStoreId());
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->set(__('Add Product'));
                return $resultPage;
            }else{
                $this->messageManager->addError(__('Please select attribute set and product type.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/create', ['_secure'=>$this->getRequest()->isSecure()]);
            }
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('*/*/create', ['_secure'=>$this->getRequest()->isSecure()]);
        }   
    }
}
