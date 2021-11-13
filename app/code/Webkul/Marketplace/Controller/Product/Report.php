<?php
namespace Webkul\Marketplace\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\View\Result\PageFactory;

class Report extends \Magento\Customer\Controller\AbstractAccount
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

                $wholedata = $this->getRequest()->getParams();

                list($datacol, $errors) = $this->validatePost();

                if(empty($errors)){            

                    $seller_id = $wholedata['seller_id'];
                    $buyer_id = $this->_getSession()->getCustomerId();
                    $buyer_email = $this->_getSession()->getEmail();
                    $wholedata['user_id'] = $buyer_id;
                    $wholedata['email'] = $buyer_email; 
                    $dateTime = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime');
                    $wholedata['created_at'] = $dateTime->date();
                   
                    $collection =   $this->_objectManager->create('Webkul\Marketplace\Model\Report');
                    $collection->setData($wholedata);
                    $collection->save();

                    //$returnArr = $this->saveProduct->saveProductData($this->_getSession()->getCustomerId(),$wholedata);
                    //$productId = $returnArr['product_id'];
                    return $this->getResponse()->representJson(
                        $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode(array("success"=>true,"mesage"=>__("Your Request successfully posted.")))
                    );

                }else{
                    return $this->getResponse()->representJson(
                        $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode(array("success"=>false,"mesage"=>$errors))
                    );
                    
                }
            }
        }catch (\Magento\Framework\Exception\LocalizedException $e) {
            
            return $this->getResponse()->representJson(
                        $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode(array("success"=>false,"mesage"=>$e->getMessage()))
                    );
        }  catch (Exception $e) {
            return $this->getResponse()->representJson(
                        $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode(array("success"=>false,"mesage"=>$e->getMessage()))
                    );
        }
    }

    private function validatePost()
    {
        $errors = array();
        $data = array();
        foreach( $this->getRequest()->getParams() as $code => $value){
            switch ($code) :
                case 'user_name':
                    if(trim($value) == '' ){$errors[] = __('Name has to be completed');} 
                    else{$data[$code] = $value;}
                    break;
                case 'email':
                    if(trim($value) == '' ){$errors[] = __('Email has to be completed');} 
                    else{$data[$code] = $value;}
                    break;
                case 'reason':
                    if(trim($value) == '' ){$errors[] = __('Reason has to be completed');} 
                    else{$data[$code] = $value;}
                    break;
                case 'seller-id':
                    if(trim($value) == '' ){$errors[] = __('Reason has to be completed');} 
                    else{$data[$code] = $value;}
                    break;
                case 'product-id':
                    if(trim($value) == '' ){$errors[] = __('Reason has to be completed');} 
                    else{$data[$code] = $value;}
                    break;
                
            endswitch;
        }
        return array($data, $errors);
    }
}
