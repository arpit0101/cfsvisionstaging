<?php
namespace Webkul\Marketplace\Controller\Product;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;

class Edit extends Action
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
        $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
        $productId = (int) $this->getRequest()->getParam('id');
        $product = $this->productBuilder->build($this->getRequest()->getParams(),$helper->getCurrentStoreId());

        if ($productId && !$product->getId()) {
            $this->messageManager->addError(__('This product no longer exists.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/productlist', ['_secure'=>$this->getRequest()->isSecure()]);
        }
        if($this->getRequest()->getParam('id')){
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Edit Product'));
            return $resultPage;            
        }else{
            return $this->resultRedirectFactory->create()->setPath('*/*/add', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
}
