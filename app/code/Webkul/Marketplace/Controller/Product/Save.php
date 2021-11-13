<?php
namespace Webkul\Marketplace\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\View\Result\PageFactory;

class Save extends \Magento\Customer\Controller\AbstractAccount
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
     * @param Context $context
     * @param Session $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param PageFactory $resultPageFactory
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        SaveProduct $saveProduct,
        PageFactory $resultPageFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->resultPageFactory = $resultPageFactory;
        $this->saveProduct = $saveProduct;
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
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try{
            $productId = '';
            $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
            if($this->getRequest()->isPost()){
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath('*/*/create', ['_secure'=>$this->getRequest()->isSecure()]);
                }

                $wholedata = $this->getRequest()->getParams();

                list($datacol, $errors) = $this->validatePost();

                if(empty($errors)){                    
                    $returnArr = $this->saveProduct->saveProductData($this->_getSession()->getCustomerId(),$wholedata);
                    $productId = $returnArr['product_id'];
                }else{
                    foreach ($errors as $message) {
                        $this->messageManager->addError($message);
                    }
                }
            }
            if($productId!=''){
                if (empty($errors)){
                    $this->messageManager->addSuccess(__('Your product has been successfully saved'));
                }
				return $this->resultRedirectFactory->create()->setPath('*/*/productlist', ['_secure'=>$this->getRequest()->isSecure()]);
                #return $this->resultRedirectFactory->create()->setPath('*/*/edit', ['id'=>$productId, '_secure'=>$this->getRequest()->isSecure()]);
            }else {
                if (isset($returnArr) && $returnArr['error'] && $returnArr['message'] != ''){
                    $this->messageManager->addError($returnArr['message']);
                }
                return $this->resultRedirectFactory->create()->setPath('*/*/create', ['_secure'=>$this->getRequest()->isSecure()]);
            }
        }catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('*/*/create', ['_secure'=>$this->getRequest()->isSecure()]);
        }  catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('*/*/create', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }

    private function validatePost()
    {
        $errors = array();
        $data = array();
        foreach( $this->getRequest()->getParams() as $code => $value){
            switch ($code) :
                case 'name':
                    if(trim($value) == '' ){$errors[] = __('Name has to be completed');} 
                    else{$data[$code] = $value;}
                    break;
                case 'description':
                    if(trim($value) == '' ){$errors[] = __('Description has to be completed');} 
                    else{$data[$code] = $value;}
                    break;
                case 'short_description':
                    if(trim($value) == ''){$errors[] = __('Short description has to be completed');} 
                    else{$data[$code] = $value;}
                    break;
                case 'price':
                    if(!preg_match("/^([0-9])+?[0-9.]*$/",$value)){
                        $errors[] = __('Price should contain only decimal numbers');
                    }else{$data[$code] = $value;}
                    break;
                case 'weight':
                    if(!preg_match("/^([0-9])+?[0-9.]*$/",$value)){
                        $errors[] = __('Weight should contain only decimal numbers');
                    }else{$data[$code] = $value;}
                    break;
                case 'stock':
                    if(!preg_match("/^([0-9])+?[0-9.]*$/",$value)){
                        $errors[] = __('Product stock should contain only an integer number');
                    }else{$data[$code] = $value;}
                    break;
                case 'sku_type':
                    if(trim($value) == '' ){$errors[] = __('Sku Type has to be selected');} 
                    else{$data[$code] = $value;}
                    break;
                case 'price_type':
                    if(trim($value) == '' ){$errors[] = __('Price Type has to be selected');} 
                    else{$data[$code] = $value;}
                    break;
                case 'weight_type':
                    if(trim($value) == ''){$errors[] = __('Weight Type has to be selected');} 
                    else{$data[$code] = $value;}
                    break;
                case 'bundle_options':
                    if(trim($value) == ''){$errors[] = __('Default Title has to be completed');} 
                    else{$data[$code] = $value;}
                    break;  
            endswitch;
        }
        return array($data, $errors);
    }
}
