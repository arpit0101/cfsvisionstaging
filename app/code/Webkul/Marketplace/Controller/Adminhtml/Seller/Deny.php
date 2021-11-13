<?php
namespace Webkul\Marketplace\Controller\Adminhtml\Seller;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class massDisapprove
 */
class Deny extends \Magento\Backend\App\Action
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
        $this->_date = $date;
        $this->_storeManager = $storeManager;
        $this->_productRepository = $productRepository;
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
        $data = $this->getRequest()->getParams();
        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                            ->getCollection()
                            ->addFieldToFilter('seller_id',$data['seller_id']);

        foreach ($collection as $item) {
            $auto=$item->getEntityId();
            $collection1 = $this->_objectManager->get('Webkul\Marketplace\Model\Seller')->load($auto);
            $collection1->setIsSeller(0);
            $collection1->save();
        }
        $sellerProduct = $this->_objectManager->create('Webkul\Marketplace\Model\Product')->getCollection()->addFieldToFilter('seller_id',$data['seller_id']);
        foreach ($sellerProduct as $value) {
            $id = $value->getMageproductId();
            $model = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($id);
            $allStores = $this->_storeManager->getStores();
            foreach ($allStores as $_eachStoreId => $val)
            {
                $product = $this->_productRepository->getById($id);
                $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                $this->_productRepository->save($product);
            }
            $model->setStatus(0)->save();
            $value->setStatus(0);
            $value->save();
        }
        $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
        $admin_storemail = $helper->getAdminEmailId();
        $adminEmail=$admin_storemail? $admin_storemail:$helper->getDefaultTransEmailId();
        $adminUsername = 'Admin';

        $seller = $this->_objectManager->get('Magento\Customer\Model\Customer')->load($data['seller_id']);
        $emailTempVariables['myvar1'] = $seller->getName();
        $emailTempVariables['myvar2'] = $data['seller_deny_reason'];
        $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
        $receiverInfo = [
            'name' => $seller->getName(),
            'email' => $seller->getEmail(),
        ];
       $this->_objectManager->create('Webkul\Marketplace\Helper\Email')->sendSellerDenyMail($emailTempVariables,$senderInfo,$receiverInfo);
        
        $this->messageManager->addSuccess(__('Seller has been Denied.'));

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
        return $this->_authorization->isAllowed('Webkul_Marketplace::seller');
    }
}
