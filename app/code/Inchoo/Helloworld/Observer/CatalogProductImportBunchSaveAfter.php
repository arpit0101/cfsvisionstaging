<?php
/**
 * Inchoo Helloworld CatalogProductImportBunchSaveAfter Observer Model
 *
 * @category    Inchoo
 * @package     Inchoo_Helloworld
 * @author      Inchoo Software
 *
 */
namespace Inchoo\Helloworld\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class CatalogProductImportBunchSaveAfter implements ObserverInterface
{
    /**
     * File Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;
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
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    protected $_mediaDirectory;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Filesystem $filesystem,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    ) {
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_productRepository = $productRepository;
        $this->_objectManager = $objectManager;
        $this->messageManager = $messageManager;
        $this->collectionFactory = $collectionFactory;
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
		$bunch		=	$observer->getBunch();
		foreach ($bunch as $rowNum => $rowData) {
			$seller_url 	=	trim($rowData['shop_url']);
			$product_sku 	=	trim($rowData['sku']);
			$seller_data 	=	$this->getSellerIDByUrl($seller_url);
			if(!empty($seller_data) && isset($seller_data['seller_id'])){				
				$seller_id 		=	$seller_data['seller_id'];
				$product_id 	=	$this->getProductIDBySku($product_sku);
				$this->assignProduct($seller_id, $product_id);
			}
		}
        return true;
    }
	
	public function getSellerIDByUrl($seller_url)
    {
        $seller_status = 0;
        $seller_data = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                    ->getCollection()
                    ->addFieldToFilter('shop_url',$seller_url);
        return $seller_data->getFirstItem()->getData();
    }
	
	public function getProductIDBySku($product_sku)
    {
		$productId 		= 	$this->_objectManager->get('Magento\Catalog\Model\Product')->getIdBySku($product_sku);
		return $productId;
    }
	
	
    public function assignProduct($seller_id,$proid){
		
		$userid		=	'';
		$product 	= 	$this->_objectManager->get('Magento\Catalog\Model\Product')->load($proid);
		if($product->getname()){   
			$collection = $this->_objectManager->create('Webkul\Marketplace\Model\Product')->getCollection()
			->addFieldToFilter('mageproduct_id',array('eq'=>$proid));
			foreach($collection as $coll){
			//echo "<pre>"; print_r($coll->getSellerId()); exit;
			   $userid = $coll->getSellerId();
			   if($userid){
					if($userid!=$seller_id){ 
						$coll->setSellerId($seller_id);
						$coll->setAdminassign(1);
						$coll->setUpdatedAt($this->_date->gmtDate());
						$coll->save();	
					}
			    }
			}
			if($userid){
				//echo $seller_id.'===='.$userid; die("tgtgtg");
				/* if($userid!=$seller_id){ 
					$collection->setSellerId($seller_id);
					$collection->setAdminassign(1);
					$collection->setUpdatedAt($this->_date->gmtDate());
					$collection->save();	
				} */
			} else{
				$collection1 = $this->_objectManager->create('Webkul\Marketplace\Model\Product');
				$collection1->setMageproductId($proid);
				$collection1->setSellerId($seller_id);
				$collection1->setStatus($product->getStatus());
				$collection1->setAdminassign(1);
				$collection1->setStoreId(array($this->_storeManager->getStore()->getId()));
				$collection1->setCreatedAt($this->_date->gmtDate());
				$collection1->setUpdatedAt($this->_date->gmtDate());
				$collection1->save();
			}
		}
    }
}
