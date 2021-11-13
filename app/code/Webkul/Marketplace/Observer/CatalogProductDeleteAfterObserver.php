<?php
/**
 * Webkul Marketplace CatalogProductDeleteAfterObserver Observer Model
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software
 *
 */
namespace Webkul\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;

class CatalogProductDeleteAfterObserver implements ObserverInterface
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
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        CollectionFactory $collectionFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->collectionFactory = $collectionFactory;
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
            $productId = $observer->getProduct()->getId();
            $productCollection = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
                                    ->getCollection()
                                    ->addFieldToFilter('mageproduct_id',$productId);
            foreach ($productCollection as $product) {
                $this->_objectManager->get('Webkul\Marketplace\Model\Product')->load($product->getEntityId())->delete();
             } 
        }catch(Exception $e){
            $this->messageManager->addError($e->getMessage());
        }
    }
}