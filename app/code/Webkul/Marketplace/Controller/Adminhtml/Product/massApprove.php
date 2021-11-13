<?php
namespace Webkul\Marketplace\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class massApprove
 */
class massApprove extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;
     /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * 
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context, 
        Filter $filter, 
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory
    )
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_productRepository = $productRepository;
        $this->_date = $date;
        $this->dateTime = $dateTime;
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        foreach ($collection as $item) {
            $item->setStatus(1);
            $item->setUpdatedAt($this->_date->gmtDate());
            $item->save();
            $collectionItem = $item->getId();
            $pro = $this->_objectManager->create('Webkul\Marketplace\Model\Product')->load($collectionItem);
            $this->_objectManager->create('Magento\Catalog\Model\Product')->load($item->getMageproductId())->setStatus(1)->save();
            $magentoProductModel = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($item->getMageproductId());
            $catarray=$magentoProductModel->getCategoryIds();
            $categoryname='';
            $catagory_model = $this->_objectManager->get('Magento\Catalog\Model\Category');
            foreach($catarray as $keycat){
            $categoriesy = $catagory_model->load($keycat);
                if($categoryname ==''){
                    $categoryname=$categoriesy->getName();
                }else{
                    $categoryname=$categoryname.",".$categoriesy->getName();
                }
            }
            $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
            $admin_storemail = $helper->getAdminEmailId();
            $adminEmail=$admin_storemail? $admin_storemail:$helper->getDefaultTransEmailId();
            $adminUsername = 'Admin';

            $seller = $this->_objectManager->get('Magento\Customer\Model\Customer')->load($item->getSellerId());

            $emailTemplateVariables = array();
            $emailTemplateVariables['myvar1'] =$magentoProductModel->getName();
            $emailTemplateVariables['myvar2'] =$magentoProductModel->getDescription();
            $emailTemplateVariables['myvar3'] =$magentoProductModel->getPrice();
            $emailTemplateVariables['myvar4'] =$categoryname;
            $emailTemplateVariables['myvar5'] =$seller->getname();
            $emailTemplateVariables['myvar6'] ='I would like to inform you that your product has been approved.';

            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $receiverInfo = [
                'name' => $seller->getName(),
                'email' => $seller->getEmail(),
            ];
            $this->_objectManager->create('Webkul\Marketplace\Helper\Email')->sendProductStatusMail($emailTemplateVariables,$senderInfo,$receiverInfo);

            $this->_eventManager->dispatch(
                'mp_approve_product',
                ['product'=>$pro,'seller'=>$seller]
            );
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been approved.', $collection->getSize()));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Marketplace::product');
    }
}
