<?php
namespace Webkul\Marketplace\Controller\Adminhtml\Order;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory;

/**
 * Class Payseller
 */
class Payseller extends \Magento\Backend\App\Action
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
        CollectionFactory $collectionFactory
    )
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
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
        try {
            $wholedata=$this->getRequest()->getParams(); 
            $actparterprocost = 0;
            $totalamount = 0;
            $seller_id = $wholedata['seller_id'];

            $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
            $orderinfo = '';
            $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                                        ->getCollection()
                                        ->addFieldToFilter('entity_id',array('eq'=>$wholedata['autoorderid']))
                                        ->addFieldToFilter('order_id',array('neq'=>0))
                                        ->addFieldToFilter('paid_status',array('eq'=>0))
                                        ->addFieldToFilter('cpprostatus',array('neq'=>0));
            foreach ($collection as $row) {
                $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($row['order_id']);
                $tax_amount = $row['total_tax'];
                $vendor_tax_amount = 0;
                if($helper->getConfigTaxManage()){
                    $vendor_tax_amount = $tax_amount;
                }
                $cod_charges = 0;
                $shipping_charges = 0;
                if(!empty($row['cod_charges'])){
                    $cod_charges = $row->getCodCharges();
                }
                $shipping_charges = $row->getShippingCharges();
                $actparterprocost = $actparterprocost+$row->getActualSellerAmount()+$vendor_tax_amount+$cod_charges+$shipping_charges;
                $totalamount = $totalamount + $row->getTotalAmount()+$tax_amount+$cod_charges+$shipping_charges;
                $seller_id = $row->getSellerId();
                $orderinfo = $orderinfo."<tr>
                                <td class='item-info'>".$row['magerealorder_id']."</td>
                                <td class='item-info'>".$row['magepro_name']."</td>
                                <td class='item-qty'>".$row['magequantity']."</td>
                                <td class='item-price'>".$order->formatPrice($row['magepro_price'])."</td>
                                <td class='item-price'>".$order->formatPrice($row['total_commission'])."</td>
                                <td class='item-price'>".$order->formatPrice($row['actual_seller_amount'])."</td>
                            </tr>";
            }
            if($actparterprocost){      
                $collectionverifyread = $this->_objectManager->create('Webkul\Marketplace\Model\Saleperpartner')->getCollection();
                $collectionverifyread->addFieldToFilter('seller_id',array('eq'=>$seller_id));
                if(count($collectionverifyread)>=1){
                    foreach($collectionverifyread as $verifyrow){
                        if($verifyrow->getAmountRemain() >= $actparterprocost){
                            $totalremain=$verifyrow->getAmountRemain()-$actparterprocost;
                        }
                        else{
                            $totalremain=0;
                        }
                        $verifyrow->setAmountRemain($totalremain);
                        $verifyrow->save();
                        $totalremain;
                        $amountpaid=$verifyrow->getAmountReceived();
                        $totalrecived=$actparterprocost+$amountpaid;
                        $verifyrow->setLastAmountPaid($actparterprocost);
                        $verifyrow->setAmountReceived($totalrecived);
                        $verifyrow->setAmountRemain($totalremain);
                        $verifyrow->setUpdatedAt($this->_date->gmtDate());
                        $verifyrow->save();
                    }
                }
                else{
                    $percent = $helper->getConfigCommissionRate();          
                    $collectionf = $this->_objectManager->create('Webkul\Marketplace\Model\Saleperpartner');
                    $collectionf->setSellerId($seller_id);
                    $collectionf->setTotalSale($totalamount);
                    $collectionf->setLastAmountPaid($actparterprocost);
                    $collectionf->setAmountReceived($actparterprocost);
                    $collectionf->setAmountRemain(0);
                    $collectionf->setCommissionRate($percent);
                    $collectionf->setTotalCommission($totalamount-$actparterprocost);
                    $collectionf->setCreatedAt($this->_date->gmtDate());
                    $collectionf->setUpdatedAt($this->_date->gmtDate());
                    $collectionf->save();                       
                }

                $unique_id = $this->checktransid();
                $transid = '';
                $transaction_number = '';
                if($unique_id!=''){
                    $seller_trans = $this->_objectManager->create('Webkul\Marketplace\Model\Sellertransaction')->getCollection()
                            ->addFieldToFilter('transaction_id',array('eq'=>$unique_id));            
                    if(count($seller_trans)){
                        foreach ($seller_trans as $value) {
                            $id =$value->getId();
                            if($id){
                                $this->_objectManager->create('Webkul\Marketplace\Model\Sellertransaction')->load($id)->delete();
                            }
                        }
                    }
                    $seller_trans = $this->_objectManager->create('Webkul\Marketplace\Model\Sellertransaction');
                    $seller_trans->setTransactionId($unique_id);
                    $seller_trans->setTransactionAmount($actparterprocost);
                    $seller_trans->setType('Manual');
                    $seller_trans->setMethod('Manual');
                    $seller_trans->setSellerId($seller_id);
                    $seller_trans->setCustomNote($wholedata['seller_pay_reason']);
                    $seller_trans->setCreatedAt($this->_date->gmtDate());
                    $seller_trans->setUpdatedAt($this->_date->gmtDate());
                    $seller_trans = $seller_trans->save();
                    $transid = $seller_trans->getId();
                    $transaction_number = $seller_trans->getTransactionId();
                }

                
                $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                                ->getCollection()
                                ->addFieldToFilter('entity_id',array('eq'=>$wholedata['autoorderid']))
                                ->addFieldToFilter('cpprostatus',array('eq'=>1))
                                ->addFieldToFilter('paid_status',array('eq'=>0))
                                ->addFieldToFilter('order_id',array('neq'=>0));
                foreach ($collection as $row) {
                    $row->setPaidStatus(1);
                    $row->setTransId($transid)->save();
                    $data['id']=$row->getOrderId();
                    $data['seller_id']=$row->getSellerId();
                    $this->_eventManager->dispatch(
                        'mp_pay_seller',
                        [$data]
                    );
                }

                $seller = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($seller_id);
                
                $emailTempVariables = array();              

                $admin_storemail = $helper->getAdminEmailId();
                $adminEmail=$admin_storemail? $admin_storemail:$helper->getDefaultTransEmailId();
                $adminUsername = 'Admin';

                $senderInfo = array();
                $receiverInfo = array();
                
                $receiverInfo = [
                    'name' => $seller->getName(),
                    'email' => $seller->getEmail(),
                ];
                $senderInfo = [
                    'name' => $adminUsername,
                    'email' => $adminEmail,
                ];

                $emailTempVariables['myvar1'] = $seller->getName();
                $emailTempVariables['myvar2'] = $transaction_number;
                $emailTempVariables['myvar3'] = $this->_date->gmtDate();
                $emailTempVariables['myvar4'] = $actparterprocost;
                $emailTempVariables['myvar5'] = $orderinfo;
                $emailTempVariables['myvar6'] = $wholedata['seller_pay_reason'];

                $this->_objectManager->get('Webkul\Marketplace\Helper\Email')->sendSellerPaymentEmail(
                    $emailTempVariables,
                    $senderInfo,
                    $receiverInfo
                );

                $this->messageManager->addSuccess(__('Payment has been successfully done for this seller'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t pay the seller right now.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('marketplace/order/index',array('seller_id'=>$seller_id));
    }

    public function randString($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')
    {
        $str = 'tr-';
        $count = strlen($charset);
        while ($length--) {
            $str .= $charset[mt_rand(0, $count-1)];
        }

        return $str;
    }

    public function checktransid(){
        $unique_id=$this->randString(11);
        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Sellertransaction')
                    ->getCollection()
                    ->addFieldToFilter('transaction_id',array('eq'=>$unique_id));
        $i=0;
        foreach ($collection as $value) {
                $i++;
        }   
        if($i!=0){
            $this->checktransid();
        }else{
            return $unique_id;
        }       
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
