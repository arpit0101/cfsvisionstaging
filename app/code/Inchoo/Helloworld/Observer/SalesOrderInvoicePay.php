<?php
namespace Inchoo\HelloWorld\Observer\Sales;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
class SalesOrderInvoicePay implements ObserverInterface
{   
/**
* @param EventObserver $observer
* @return $this
*/
public function execute(EventObserver $observer)
{
     $invoice = $observer->getEvent()->getInvoice();
     $order = $invoice->getOrder();
   
     /* reset total_paid & base_total_paid of order */
     $order->setTotalPaid($order->getTotalPaid() - $invoice->getGrandTotal());
     $order->setBaseTotalPaid($order->getBaseTotalPaid() - $invoice->getBaseGrandTotal());
}    
}