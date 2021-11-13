<?php
namespace Webkul\Marketplace\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\View\Result\PageFactory;

class Savesalesproduct extends \Magento\Customer\Controller\AbstractAccount
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

      
        try
        {
            $productId = '';
            $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
            if($this->getRequest()->isPost()) {
                $allsaledata   = $this->getRequest()->getParams();
                $product_id    = $allsaledata['product_id'];
                $price         = $allsaledata['price'];
                $special_price = $allsaledata['special_price'];
                $from_date     = $allsaledata['from_date'];
                $to_date       = $allsaledata['to_date'];

                /*foreach( $this->getRequest()->getParams() as $code => $value){
                    echo '</pre>';
                    echo $value.'</br>';
                    echo $code.'</br>';
                }*/
                
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath('*/*/editproductsale', ['_secure'=>$this->getRequest()->isSecure()]);
                }
                $wholedata = $this->getRequest()->getParams();
                list($datacol, $errors) = $this->validatePost();
                if(empty($errors))
                {     
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $_product = $objectManager->create('\Magento\Catalog\Model\Product');
                    $_product->load($product_id);
                    $_product->setPrice($price);  // price
                    $_product->setSpecialPrice($special_price);  // special price
                    $_product->setSpecialFromDate($from_date); //special price from (MM/DD/YYYY)
                    $_product->setSpecialToDate($to_date); //special price to (MM/DD/YYYY)
                    $_product->save();
                }
                else
                {
                    foreach ($errors as $message) 
                    {
                        $this->messageManager->addError($message);
                    }
                }
            }
            if($productId!='')
            {
                if (empty($errors))
                {
                    $this->messageManager->addSuccess(__('Your product has been successfully saved'));
                }
                return $this->resultRedirectFactory->create()->setPath('*/*/editproductsale', ['id'=>$productId, '_secure'=>$this->getRequest()->isSecure()]);
            }
            else 
            {
                if (isset($returnArr) && $returnArr['error'] && $returnArr['message'] != '')
                {
                    $this->messageManager->addError($returnArr['message']);
                }
                return $this->resultRedirectFactory->create()->setPath('*/*/editproductsale', ['_secure'=>$this->getRequest()->isSecure()]);
            }
        }
        catch (\Magento\Framework\Exception\LocalizedException $e) 
        {
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('*/*/editproductsale', ['_secure'=>$this->getRequest()->isSecure()]);
        } 
        catch (Exception $e) 
        {
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('*/*/editproductsale', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }

    private function validatePost()
    {
        $errors = array();
        $data = array();
        $date_regex = '/(0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])[- \/.](19|20)\d\d/';
        foreach( $this->getRequest()->getParams() as $code => $value){
                        switch ($code) :

                            case 'price':
                                if(!preg_match("/^([0-9])+?[0-9.]*$/",$value))
                                {
                                    $errors[] = __('Price should only in decimal numbers');
                                }
                                else
                                {
                                    $data[$code] = $value;
                                }
                                break;

                            case 'special_price':
                                if(!preg_match("/^([0-9])+?[0-9.]*$/",$value))
                                {
                                    $errors[] = __('Special Price should only in decimal numbers');
                                }
                                else
                                {
                                    $data[$code] = $value;
                                }
                                break;
                            
                            case 'from_date':
                                if(!preg_match($date_regex,$value)){
                                    $errors[] = __('From date is required.');
                                }
                                else
                                {
                                    $data[$code] = $value;
                                }
                                break;

                            case 'to_date':
                                if(!preg_match($date_regex,$value))
                                {
                                    $errors[] = __('To date is required.');
                                }
                                else
                                {
                                    $data[$code] = $value;
                                }
                                break;
                            
                        endswitch;
        }
        return array($data, $errors);
    }
}
