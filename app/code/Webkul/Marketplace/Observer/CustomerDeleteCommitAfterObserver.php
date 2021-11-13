<?php
/**
 * Webkul Marketplace CustomerDeleteCommitAfterObserver Observer Model
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software
 *
 */
namespace Webkul\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;

class CustomerDeleteCommitAfterObserver implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

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
     * 
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;
     /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->collectionFactory = $collectionFactory;
        $this->_productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        $this->_date = $date;
        $this->dateTime = $dateTime;
    }

    /**
     * customer register event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try{
            $customer = $observer->getCustomer();
            $customerid=$customer->getId();
            $seller_id = '';
            $entity_id = '';
            $sellerCollection = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                                        ->getCollection()
                                        ->addFieldToFilter('seller_id',$customerid);
            foreach ($sellerCollection as $seller) {
                $seller_id = $seller->getSellerId();
                $entity_id = $seller->getId();
                $seller->delete();
            }
            if($seller_id){
                $model = $this->_objectManager->get('Webkul\Marketplace\Model\Seller')->load($entity_id);
                $productCollection = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
                ->getCollection()->addFieldToFilter('seller_id',$seller_id);
                foreach ($productCollection as $productData) {
                    $product = $this->_productRepository->getById($productData->getMageproductId());
                    if($product->getId()){
                        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                        $this->_productRepository->save($product);
                    }
                    $productData->delete();
                }
            }
        }catch(Exception $e){
            $this->messageManager->addError($e->getMessage());
        }
    }
}