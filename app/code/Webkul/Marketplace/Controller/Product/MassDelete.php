<?php
namespace Webkul\Marketplace\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

class MassDelete extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        \Magento\Framework\Registry $coreRegistry,
        PageFactory $resultPageFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_coreRegistry = $coreRegistry;
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
            $wholedata = $this->getRequest()->getParams();

            $ids= $this->getRequest()->getParam('product_mass_delete');

            $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');

            $this->_eventManager->dispatch(
                'mp_delete_product',
                [$wholedata]
            );

            $seller_id=$this->_getSession()->getCustomerId();

            $deleteFlag = 0;

            $this->_coreRegistry->register("isSecureArea", 1);
            $unauth_ids = array();
            foreach ($ids as $id){
                $seller_products = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
                                    ->getCollection()
                                    ->addFieldToFilter('mageproduct_id',array('eq'=>$id))
                                    ->addFieldToFilter('seller_id',array('eq'=>$seller_id));
                if(count($seller_products)) {
                    foreach ($seller_products as $seller_product) {
                        $deleteFlag = 1;
                        $this->_objectManager->create('Magento\Catalog\Model\Product')->load($id)->delete();
                        $seller_product->delete();
                    }
                }else{
                    array_push($unauth_ids, $id);
                }
            }            
            $this->_coreRegistry->unregister('isSecureArea');
            if(count($unauth_ids)){
                $this->messageManager->addError(__('You are not authorized to delete products with id(s) %1 .',implode(",", $unauth_ids)));   
            }else{
                $this->messageManager->addSuccess(__('Your product has been successfully deleted from your account.'));
            }
            return $this->resultRedirectFactory->create()->setPath('*/*/productlist', ['_secure'=>$this->getRequest()->isSecure()]);
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('*/*/productlist', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
}
