<?php
/**
 * Marketplace Seller Newfeedback controller.
 *
 */
namespace Webkul\Marketplace\Controller\Seller;

use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\Action;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\View\Result\PageFactory;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Newfeedback extends Action implements AccountInterface
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
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

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
        Filesystem $filesystem,
        PageFactory $resultPageFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_date = $date;
        $this->dateTime = $dateTime;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
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
     * save feeback entry in db
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $wholedata = $this->getRequest()->getParams();

        if($this->getRequest()->isPost()){
            try{
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath('*/*/feedback',array('shop'=>$wholedata['shop_url']));
                }             
                $seller_id = $wholedata['seller_id'];
                $buyer_id = $this->_getSession()->getCustomerId();
                $buyer_email = $this->_getSession()->getEmail();
                $wholedata['buyer_id'] = $buyer_id;
                $wholedata['buyer_email'] = $buyer_email; 
                $wholedata['created_at'] = $this->_date->gmtDate();
                $feedbackcount = 0;
                $collectionfeed=$this->_objectManager->create('Webkul\Marketplace\Model\Feedbackcount')
                                    ->getCollection()
                                    ->addFieldToFilter('seller_id',$seller_id)
                                    ->addFieldToFilter('buyer_id',array('eq'=>$buyer_id));
                foreach ($collectionfeed as $value) {
                    $feedbackcount = $value->getFeedbackCount();
                    $value->setFeedbackcount($feedbackcount+1);
                    $value->save();
                }
                $collection=$this->_objectManager->create('Webkul\Marketplace\Model\Feedback');
                $collection->setData($wholedata);
                $collection->save();

                $this->messageManager->addSuccess(__('Your Review was successfully saved, Will be show here in list after reviewed by admin.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/feedback',array('shop'=>$wholedata['shop_url']));
                
            } catch (Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('*/*/feedback',array('shop'=>$wholedata['shop_url']));
            }
        }
        else{
            return $this->resultRedirectFactory->create()->setPath('*/*/feedback',array('shop'=>$wholedata['shop_url']));
        }
    }
}