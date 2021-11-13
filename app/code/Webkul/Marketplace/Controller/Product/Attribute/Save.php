<?php
namespace Webkul\Marketplace\Controller\Product\Attribute;

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
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

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
        PageFactory $resultPageFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_date = $date;
        $this->dateTime = $dateTime;
        $this->resultPageFactory = $resultPageFactory;
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
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try{
            if($this->getRequest()->isPost()){
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath('*/*/new', ['_secure'=>$this->getRequest()->isSecure()]);
                }

                $wholedata=$this->getRequest()->getParams();

                $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');

                $attributes = $this->_objectManager->get('Magento\Catalog\Model\Product')->getAttributes();
                $allattrcodes = array();

                foreach($attributes as $a){
                    $allattrcodes = $a->getEntityType()->getAttributeCodes();
                }
                if(count($allattrcodes) && in_array($wholedata['attribute_code'], $allattrcodes)){
                    $this->messageManager->addError(__('Attribute Code already exists'));
                    return $this->resultRedirectFactory->create()->setPath('*/*/new', ['_secure'=>$this->getRequest()->isSecure()]);
                }else{
                    if(array_key_exists('attroptions', $wholedata)){
                        foreach( $wholedata['attroptions'] as $c){
                            $data1['.'.$c['admin'].'.'] = array($c['admin'],$c['store']);   
                        }
                    }else{
                        $data1=array();
                    }
                    
                    $_attribute_data = array(
                                        'attribute_code' => $wholedata['attribute_code'],
                                        'is_global' => '1',
                                        'frontend_input' => $wholedata['frontend_input'],
                                        'default_value_text' => '',
                                        'default_value_yesno' => '0',
                                        'default_value_date' => '',
                                        'default_value_textarea' => '',
                                        'is_unique' => '0',
                                        'is_required' => $wholedata['val_required'],
                                        'apply_to' => '0',
                                        'is_configurable' => '1',
                                        'is_searchable' => '0',
                                        'is_visible_in_advanced_search' => '1',
                                        'is_comparable' => '0',
                                        'is_used_for_price_rules' => '0',
                                        'is_wysiwyg_enabled' => '0',
                                        'is_html_allowed_on_front' => '1',
                                        'is_visible_on_front' => '0',
                                        'used_in_product_listing' => '0',
                                        'used_for_sort_by' => '0',
                                        'frontend_label' => $wholedata['attribute_label']
                                    );
                    $model = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute');
                    if (!isset($_attribute_data['is_configurable'])) {
                        $_attribute_data['is_configurable'] = 0;
                    }
                    if (!isset($_attribute_data['is_filterable'])) {
                        $_attribute_data['is_filterable'] = 0;
                    }
                    if (!isset($_attribute_data['is_filterable_in_search'])) {
                        $_attribute_data['is_filterable_in_search'] = 0;
                    }
                    if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
                        $_attribute_data['backend_type'] = $model->getBackendTypeByInput($_attribute_data['frontend_input']);
                    }
                    $defaultValueField = $model->getDefaultValueByInput($_attribute_data['frontend_input']);
                    if ($defaultValueField) {
                        $_attribute_data['default_value'] = $this->getRequest()->getParam($defaultValueField);
                    }
                    $model->addData($_attribute_data);
                    $data['option']['value'] = $data1;
                    if($wholedata['frontend_input'] == 'select'){
                        $model->addData($data);                    
                    }
                    $entityTypeID = $this->_objectManager->create('Magento\Eav\Model\Entity')->setType('catalog_product')->getTypeId();
                    $model->setEntityTypeId($entityTypeID);
                    $model->setIsUserDefined(1);
                    $model->save();
                    $this->messageManager->addSuccess(__('Attribute Created Successfully'));
                    return $this->resultRedirectFactory->create()->setPath('*/*/new', ['_secure'=>$this->getRequest()->isSecure()]);
                }
            }
            return $this->resultRedirectFactory->create()->setPath('*/*/new', ['_secure'=>$this->getRequest()->isSecure()]);
        }catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('*/*/new', ['_secure'=>$this->getRequest()->isSecure()]);
        }  catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('*/*/new', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
}