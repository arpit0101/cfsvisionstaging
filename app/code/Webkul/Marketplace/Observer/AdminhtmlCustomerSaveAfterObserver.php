<?php
/**
 * Webkul Marketplace AdminhtmlCustomerSaveAfterObserver Observer Model
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software
 *
 */
namespace Webkul\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class AdminhtmlCustomerSaveAfterObserver implements ObserverInterface
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
    protected $_urlInterFace;

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
		\Magento\Framework\UrlInterface $urlInterFace,
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
		$this->_urlInterFace = $urlInterFace;
    }

    /**
     * customer register event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    { 
		//echo $this->_storeManager->getStore()->getBaseUrl() . '<br />'; die;
        $customer=$observer->getCustomer();
        $customerid=$customer->getId();
		
        if($this->isSeller($customerid)){
			
			
            list($data, $errors) = $this->validateprofiledata($observer);
			
            $fields = $observer->getRequest()->getPostValue();
			//echo '<pre>'; print_r($fields); die("testtt");
            $productIds = $observer->getRequest()->getParam('sellerassignproid');
			if(is_array($productIds)){
				$observer->getRequest()->setParam('sellerassignproid', implode(",", $productIds));
				$productIds 					= 	$observer->getRequest()->getParam('sellerassignproid');
			}else{
				$productIds		=	0;
			}
			if(isset($fields['area_id'])){
				$fields['area_id'] 			 	= 	implode(",", $fields['area_id']);
			}
			if(isset($fields['region_id'])){
				$fields['region_id'] 			 	= 	implode(",", $fields['region_id']);
			}
			if(isset($fields['offweekdays'])){
				$fields['offweekdays'] 			 	= 	implode(",", $fields['offweekdays']);
			}
			if(isset($fields['offmonths'])){
				$fields['offmonths'] 			 	= 	implode(",", $fields['offmonths']);
			}
			
			// if(isset($fields['sellerassignproid'])){
				// $fields['sellerassignproid'] 			 	= 	implode(",", $fields['sellerassignproid']);
			// }
			
            $unassignproid = $observer->getRequest()->getParam('sellerunassignproid');
            $seller_id						=	$customerid;
            $partner_type = $observer->getRequest()->getParam('partnertype');
           
			if($partner_type==2)
            {
                $this->removePartner($seller_id);
                $this->messageManager->addSuccess(__("You removed the customer from seller."));
                return $this;
            }
			
            /* if($productIds !=''||$productIds!= 0){
                $this->assignProduct($seller_id,$productIds);
            }
            if($unassignproid !=''||$unassignproid!= 0){
                $this->unassignProduct($seller_id,$unassignproid);
            } */
            $collectionselect = $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleperpartner')->getCollection()->addFieldToFilter('seller_id',$seller_id);
            if(count($collectionselect)==1){
                foreach($collectionselect as $verifyrow){
                $autoid=$verifyrow->getEntityId();
                }
                
                $collectionupdate = $this->_objectManager->get('Webkul\Marketplace\Model\Saleperpartner')->load($autoid);
                if(!isset($fields['commision'])){
                    $fields['commision'] = $collectionupdate->getCommissionRate();
                }
                $collectionupdate->setCommissionRate($fields['commision']);
                $collectionupdate->save();
                }
            else{
                if(!isset($fields['commision'])){
                    $fields['commision'] = 0;
                }
                $collectioninsert=$this->_objectManager->create('Webkul\Marketplace\Model\Saleperpartner');
                $collectioninsert->setSellerId($seller_id);
                $collectioninsert->setCommissionRate($fields['commision']);
                $collectioninsert->save();
            }
			
            if(empty($errors)){ 
                $auto_id = ''; 
                $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                                ->getCollection()
                                ->addFieldToFilter('seller_id',$seller_id);
                foreach($collection as  $value){
                    $auto_id = $value->getId();
                }
				$logo_pic 	=	$value->getData('logo_pic');
				
                if(!isset($fields['tw_active'])){
                    $fields['tw_active']=0;
                }
                if(!isset($fields['fb_active'])){
                    $fields['fb_active']=0;
                }
                if(!isset($fields['gplus_active'])){
                    $fields['gplus_active']=0;
                }
                if(!isset($fields['youtube_active'])){
                    $fields['youtube_active']=0;
                }
                if(!isset($fields['vimeo_active'])){
                    $fields['vimeo_active']=0;
                }
                if(!isset($fields['instagram_active'])){
                    $fields['instagram_active']=0;
                }
                if(!isset($fields['pinterest_active'])){
                    $fields['pinterest_active']=0;
                }
                if(!isset($fields['moleskine_active'])){
                    $fields['moleskine_active']=0;
                }
				
                $value = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')->load($auto_id);
                
				if(isset($fields['country_id'])){
                    $value->setCountryId($fields['country_id']);
                }
				if(isset($fields['region_id'])){
                    $value->setRegionId($fields['region_id']);
                }
				if(isset($fields['area_id'])){
					$value->setAreaId($fields['area_id']);
                } 
				if(isset($fields['offweekdays'])){
					$value->setOffWeekDays($fields['offweekdays']);
                } 
				if(isset($fields['offmonths'])){
					$value->setOffMonths($fields['offmonths']);
                } 
				if(isset($fields['offdates'])){
					$value->setOffDates($fields['offdates']);
                }
				if(isset($fields['backgroundcolor'])){
					$value->setBackgroundcolor($fields['backgroundcolor']);
                }
				
				if(isset($fields['sequence'])){
					$value->setSequence($fields['sequence']);
                }
				
				$value->addData($fields);
                $value->setIsSeller(1);
                $value->setUpdatedAt($this->_date->gmtDate());
                // $value->save();
				
                if(isset($fields['company_description'])){
                    $fields['company_description'] = str_replace('script', '', $fields['company_description']);
                    $value->setCompanyDescription($fields['company_description']);
                }

                if(isset($fields['return_policy'])){
                    $fields['return_policy'] = str_replace('script', '', $fields['return_policy']);
                    $value->setReturnPolicy($fields['return_policy']);
                }               

                if(isset($fields['shipping_policy'])){
                    $fields['shipping_policy'] = str_replace('script', '', $fields['shipping_policy']);
                    $value->setShippingPolicy($fields['shipping_policy']);
                }               
                if(isset($fields['meta_description'])){
                    $value->setMetaDescription($fields['meta_description']);
                }
				
                $target = $this->_mediaDirectory->getAbsolutePath('avatar/');
                if( isset($_FILES['banner_pic']) && !empty($_FILES['banner_pic']['name'])){
					if(strlen($_FILES['banner_pic']['name'])>0){
                        $image = getimagesize($_FILES['banner_pic']['tmp_name']);
                        if($image['mime']) {
                            $img1 = rand(1,99999).$_FILES["banner_pic"]["name"];
                            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                            $uploader = $this->_fileUploaderFactory->create(['fileId' => 'banner_pic']);
                            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                            $uploader->setAllowRenameFiles(true);
                            $result = $uploader->save($target,$img1);
                            if($result['file']){                         
                                $value->setBannerPic($result['file']);
                            }
                        }else{
                            $this->messageManager->addError(__("Disallowed file type."));
                        }
                    }
                }
				
                if( isset($_FILES['logo_pic']) && !empty($_FILES['logo_pic']['name'])){   
					
                    if(strlen($_FILES['logo_pic']['name'])>0){
                        $image = getimagesize($_FILES['logo_pic']['tmp_name']);
                        if($image['mime']) {
                            $img2 = rand(1,99999).$_FILES["logo_pic"]["name"];
                            /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                            $uploader = $this->_fileUploaderFactory->create(['fileId' => 'logo_pic']);
                            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                            $uploader->setAllowRenameFiles(true);
                            $result = $uploader->save($target);
							
                            if($result['file']){                         
                                $value->setLogoPic($result['file']);
                            }
                        }else{
                            $this->messageManager->addError(__("Disallowed file type."));
                        }
                    }
                } else{
					$value->setLogoPic($logo_pic);
				}   
                if (array_key_exists('country_pic', $fields)) {
                    $value->setCountryPic($fields['country_pic']);
                }
				
                $value->save();
            }    
        }else{
            $partner_type = $observer->getRequest()->getParam('partnertype');
            $profileurl = $observer->getRequest()->getParam('profileurl');
			
            if($partner_type==1)
            {
                if($profileurl!=''){
                    $profileurlcount =$this->_objectManager->create('Webkul\Marketplace\Model\Seller')->getCollection();
                    $profileurlcount->addFieldToFilter('shop_url',$profileurl);
                    $seller_profile_id = 0;
                    $seller_profileurl = '';
                    $collectionselect = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')->getCollection();
                    $collectionselect->addFieldToFilter('seller_id',$customerid);
                    foreach($collectionselect as $coll){
                        $seller_profile_id = $coll->getEntityId();
                        $seller_profileurl = $coll->getShopUrl();
                    }
                    if(count($profileurlcount) && ($profileurl!=$seller_profileurl)){
                        $this->messageManager->addError(__('This Shop URL already Exists.'));
                    }else{
                        $collection=$this->_objectManager->get('Webkul\Marketplace\Model\Seller')->load($seller_profile_id);
                        $collection->setIsSeller(1);
                        $collection->setShopUrl($profileurl);
                        $collection->setSellerId($customerid);
                        $collection->setCreatedAt($this->_date->gmtDate());
                        $collection->setUpdatedAt($this->_date->gmtDate());
                        $collection->save();

                        $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
                        $admin_storemail = $helper->getAdminEmailId();
                        $adminEmail=$admin_storemail? $admin_storemail:$helper->getDefaultTransEmailId();
                        $adminUsername = 'Admin';

                        $seller = $this->_objectManager->get('Magento\Customer\Model\Customer')->load($customerid);

                        $emailTempVariables['myvar1'] = $seller->getName();
						
                        $emailTempVariables['myvar2'] = $this->_storeManager->getStore()->getBaseUrl().'customer/account/login';
                        $senderInfo = [
                            'name' => $adminUsername,
                            'email' => $adminEmail,
                        ];
                        $receiverInfo = [
                            'name' => $seller->getName(),
                            'email' => $seller->getEmail(),
                        ];
                        $this->_objectManager->create('Webkul\Marketplace\Helper\Email')->sendSellerApproveMail($emailTempVariables,$senderInfo,$receiverInfo);
                        $this->messageManager->addSuccess(__("You created the customer as seller."));
                    }
                }else{
                    $this->messageManager->addError(__("Enter Shop Name of Customer."));
                }
            }
			
			$fields 	= 	$observer->getRequest()->getPostValue();
			/*$fields1 	= 	$observer->getRequest()->getPost();
			$fields2 	= 	$observer->getRequest()->getParams();
			//echo "<pre>"; print_r(get_class_methods($observer->getRequest()));
			 echo "<pre>"; print_r($fields1);
			echo "<br/>";
			echo "<pre>"; print_r($fields2);
			echo "<br/>";
			//$fields['customer']['area_id'] 			 	= 	@implode(",", $fields['customer']['area_id']);*/
			//echo "<pre>"; print_r($fields); exit;
			
			$resource		=	 $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection		=	 $resource->getConnection();
			$tableName		=	 $resource->getTableName('customer_entity');
			if(isset($fields['customer']['group_id']) && $fields['customer']['group_id']==4){
				/* if(isset($fields['customer']['area_id'])){
					$fields['customer']['area_id'] 			 	= 	@implode(",", $fields['customer']['area_id']);
				} */
				if(isset($fields['customer']['country_id'])){
					$fields['customer']['area_id'] 			 	= 	@implode(",", $fields['customer']['area_id']);
					if(isset($fields['customer']['country_id'])){
						$country_id	=	$fields['customer']['country_id'];
					}
					if(isset($fields['customer']['region_id'])){
						$region_id	=	$fields['customer']['region_id'];
					}
					else
					{
						$region_id	=	$fields['region_id'];
					}
					//echo "<hr/>";
					//echo $fields['customer']['area_id'];
					if(isset($fields['customer']['area_id'])){
						$area_id	=	$fields['customer']['area_id'];
					}
					else
					{
						$area_id	=	@implode(",", $fields['area_id']);
					}
				}
				else
				{
					if($fields['country_id'] != '')
					{
						$country_id	=	$fields['country_id'];
						$region_id	=	$fields['region_id'];
						$area_id	=	@implode(",", $fields['area_id']);
					}
					//echo @implode(",", $fields['area_id']);
					//exit;
				}
				$sql = "UPDATE " . $tableName . " SET area_id = '".$area_id."', region_id = '".$region_id."', country_id = '".$country_id."' WHERE `entity_id` = " . $customerid;
				//echo "<hr/>";
				//echo $sql;  
				$connection->query($sql);
			}
        }
		
        return $this;
    }
    public function isSeller($customerid)
    {
        $seller_status = 0;
        $model = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                    ->getCollection()
                    ->addFieldToFilter('seller_id',$customerid);
        foreach ($model as $value) {
            $seller_status = $value->getIsSeller();
        }
        return $seller_status;
    }

    private function validateprofiledata($observer)
    {
        $errors = array();
        $data = array();
        foreach( $observer->getRequest()->getParams() as $code => $value){
            switch ($code) :
                case 'twitter_id':
                    if(trim($value) != '' && preg_match('/[\'^£$%&*()}{@#~?><>, |=_+¬-]/', $value)){$errors[] = __('Twitterid cannot contain space and special charecters');} 
                    else{$data[$code] = $value;}
                    break;
                case 'facebook_id':
                    if(trim($value) != '' &&  preg_match('/[\'^£$%&*()}{@#~?><>, |=_+¬-]/', $value)){$errors[] = __('Facebookid cannot contain space and special charecters');} 
                    else{$data[$code] = $value;}
                    break;
            endswitch;
        }
        return array($data, $errors);
    }
    private function removePartner($seller_id)
    {
        $collectionselectdelete = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')->getCollection();
        $collectionselectdelete->addFieldToFilter('seller_id',$seller_id);
        foreach($collectionselectdelete as $delete){
            $autoid=$delete->getEntityId();
        }
        $collectiondelete = $this->_objectManager->get('Webkul\Marketplace\Model\Seller')->load($autoid);
        $collectiondelete->delete();
        //Set Produt status disabled
        $sellerProduct = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
                                    ->getCollection()
                                    ->addFieldToFilter('seller_id',$seller_id);

        foreach ($sellerProduct as $productInfo) {
            $allStores = $this->_storeManager->getStores();
            foreach ($allStores as $_eachStoreId => $val)
            {
                $product = $this->_productRepository->getById($productInfo->getMageproductId());
                $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                $this->_productRepository->save($product);
            }

            $productInfo->setStatus(0);
            $productInfo->save();
        }

        $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
        $admin_storemail = $helper->getAdminEmailId();
        $adminEmail=$admin_storemail? $admin_storemail:$helper->getDefaultTransEmailId();
        $adminUsername = 'Admin';

        $seller = $this->_objectManager->get('Magento\Customer\Model\Customer')->load($seller_id);

        $emailTempVariables['myvar1'] = $seller->getName();
        $emailTempVariables['myvar2'] = $this->_storeManager->getStore()->getBaseUrl().'customer/account/login';
        $senderInfo = [
            'name' => $adminUsername,
            'email' => $adminEmail,
        ];
        $receiverInfo = [
            'name' => $seller->getName(),
            'email' => $seller->getEmail(),
        ];
        $this->_objectManager->create('Webkul\Marketplace\Helper\Email')->sendSellerDisapproveMail($emailTempVariables,$senderInfo,$receiverInfo);
    }
	
    public function assignProduct($seller_id,$productIds){
		$productids	=	explode(',',$productIds);
       
		foreach($productids as $proid){
            $userid='';
            $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($proid);
            if($product->getname()){   
                $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Product')->getCollection()
				->addFieldToFilter('mageproduct_id',array('eq'=>$proid));
				
				
                foreach($collection as $coll){
                   $userid = $coll['seller_id'];
                }
                if($userid){
                    if($userid!=$seller_id)
						$this->messageManager->addError(__("The product is already assigned to other seller."));
                }
                else{
                    $collection1 = $this->_objectManager->create('Webkul\Marketplace\Model\Product');
                    $collection1->setMageproductId($proid);
                    $collection1->setSellerId($seller_id);
                    $collection1->setStatus($product->getStatus());
                    $collection1->setAdminassign(1);
                    $collection1->setStoreId(array($this->_storeManager->getStore()->getId()));
                    $collection1->setCreatedAt($this->_date->gmtDate());
                    $collection1->setUpdatedAt($this->_date->gmtDate());
                    $collection1->save();
                    $this->messageManager->addSuccess(__("Products has been successfully assigned to seller."));
                }
            } else {
                $this->messageManager->addError(__("Product with id %s doesn't exist.",$proid));
            } 
        }
		// remove all products not selected
		$this->unassignUnselectedProduct($seller_id,$productIds);
    }
	
    public function unassignUnselectedProduct($seller_id,$productIds){
        $productids=explode(',',$productIds);
		
		$collection = $this->_objectManager->create('Webkul\Marketplace\Model\Product')->getCollection()->addFieldToFilter('mageproduct_id',['nin'=>$productIds])->addFieldToFilter('seller_id',['eq'=>$seller_id]);
		foreach($collection as $coll){
			$coll->delete();
		}
    }
	
    public function unassignProduct($seller_id,$productIds){
        $productids=explode(',',$productIds);
        foreach($productids as $proid){
            $userid='';
            $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($proid);
            if($product->getname()){   
                $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Product')->getCollection()->addFieldToFilter('mageproduct_id',$proid);
                foreach($collection as $coll){
                    $coll->delete();
                }
                $this->messageManager->addSuccess(__("Products has been successfully unassigned to seller."));
            } else {
                $this->messageManager->addSuccess(__("Product with id %s doesn't exist.",$proid));
            } 
        }
    }
}
