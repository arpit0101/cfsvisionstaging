<?php
namespace Webkul\Marketplace\Block\Account;
/**
 * Webkul Marketplace Account Becomeseller Block
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software Private Limited
 *
 */
use Magento\Customer\Model\Customer;

class Chatroom extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;


    /**
    * @param Context $context
    * @param array $data
    */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        Customer $customer,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {

        $this->Customer = $customer;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;

        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }

    public function getChatrooms(){

        if (!($customerId = $this->getCustomerId())) {
            return false;
        }
        //echo $customerId;

        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Room')
                    ->getCollection();
        $name='';
        $collection->getSelect()
                    ->where('seller_id ='.$customerId.' OR customer_id ='.$customerId)
                    ->joinLeft(
                   ['customer'=>$collection->getTable('customer_entity')],
                   'main_table.customer_id = customer.entity_id',
                   ['customer_firstname'=>'customer.firstname','customer_lastname'=>'customer.lastname'])
                    //->columns('SUM(actual_seller_amount) AS qty')
                    ->group('created_at');     
        return $collection;
    }

    
    public function getCurrentRoomId(){

        if (!($customerId = $this->getCustomerId())) {
            return false;
        }

        $room_id = $this->getRequest()->getParam('room');       

        
        return $room_id;
    }

    public function getCurrentRoom(){

        if (!($customerId = $this->getCustomerId())) {
            return false;
        }
        //echo $customerId;

        $room_id = $this->getRequest()->getParam('room');       

        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Room')
                    ->getCollection();
        $name='';
        $collection->getSelect()
                    ->where('seller_id ='.$customerId)
                    ->joinLeft(
                   ['customer'=>$collection->getTable('customer_entity')],
                   'main_table.customer_id = customer.entity_id',
                   ['customer_firstname'=>'customer.firstname','customer_lastname'=>'customer.lastname'])
                    //->columns('SUM(actual_seller_amount) AS qty')
                    ->group('created_at');     
        return $collection;
    }

    public function getRoomMessages($room_id){
         

        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Message')
                    ->getCollection();
        $name='';
        $collection->getSelect()
                    ->where('room_id ='.$room_id)
                    /*->joinLeft(
                   ['customer'=>$collection->getTable('customer_entity')],
                   'main_table.customer_id = customer.entity_id',
                   ['customer_firstname'=>'customer.firstname','customer_lastname'=>'customer.lastname'])*/
                    //->columns('SUM(actual_seller_amount) AS qty')
                    ->order('created_at');     
        return $collection;
    }

}
