<?php

namespace IWD\CheckoutConnector\Model;

use IWD\CheckoutConnector\Api\OrderInterface;
use IWD\CheckoutConnector\Model\Address\Addresses;
use IWD\CheckoutConnector\Model\Address\ShippingMethods;
use IWD\CheckoutConnector\Model\Cart\CartItems;
use IWD\CheckoutConnector\Model\Cart\CartTotals;
use IWD\CheckoutConnector\Helper\Order as OrderHelper;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\OrderFactory;

/**
 * Class Order
 *
 * @package IWD\CheckoutConnector\Model
 */
class Order implements OrderInterface
{
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var MagentoOrder
     */
    private $order;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var AccessValidator
     */
    private $accessValidator;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var Addresses
     */
    private $address;

    /**
     * @var ShippingMethods
     */
    private $shippingMethods;

    /**
     * @var CartItems
     */
    private $cartItems;

    /**
     * @var CartTotals
     */
    private $cartTotals;

    /**
     * @var OrderHelper
     */
    private $orderHelper;

    /**
     * Order constructor.
     *
     * @param QuoteFactory $quoteFactory
     * @param QuoteManagement $quoteManagement
     * @param OrderFactory $orderFactory
     * @param AccessValidator $accessValidator
     * @param OrderSender $orderSender
     * @param Addresses $address
     * @param ShippingMethods $shippingMethods
     * @param CartItems $cartItems
     * @param CartTotals $cartTotals
     * @param OrderHelper $orderHelper
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        QuoteManagement $quoteManagement,
        OrderFactory $orderFactory,
        AccessValidator $accessValidator,
        OrderSender $orderSender,
        Addresses $address,
        ShippingMethods $shippingMethods,
        CartItems $cartItems,
        CartTotals $cartTotals,
        OrderHelper $orderHelper
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->quoteManagement = $quoteManagement;
        $this->orderFactory = $orderFactory;
        $this->accessValidator = $accessValidator;
        $this->orderSender = $orderSender;
        $this->address = $address;
        $this->shippingMethods = $shippingMethods;
        $this->cartItems = $cartItems;
        $this->cartTotals = $cartTotals;
        $this->orderHelper = $orderHelper;
    }

    /**
     * @param string $quote_id
     * @param mixed $access_tokens
     * @param mixed $data
     * @return \IWD\CheckoutConnector\Api\array_iwd|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function create($quote_id, $access_tokens, $data)
    {
        if(!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        $quote = $this->quoteFactory->create()->load($quote_id);

        if (!$quote->getCustomer()->getId()) {
            $this->orderHelper->assignCustomerToQuote($quote);
        }

        // Set Payment Method
        $quote->setPaymentMethod($data['payment_method_code']);
        $quote->save();

        // Set Sales Order Payment
        $quote->getPayment()->importData(['method' => $data['payment_method_code']]);

        // Collect Totals & Save Quote
        $quote->collectTotals()->save();

        // Create Order From Quote
        $order = $this->quoteManagement->submit($quote);

        if($order->getEntityId()){
            //Set Transactions for order
            $paymentAction = $data['payment_action'];
            $transactions = $data['transactions'];

            if ($paymentAction == 'authorize') {
                $this->orderHelper->addTransactionToOrder($order, $transactions['authorization'], Transaction::TYPE_AUTH, 'authorized');
            }
            elseif ($paymentAction == 'auth_and_capture') {
                $this->orderHelper->addTransactionToOrder($order, $transactions['authorization'], Transaction::TYPE_AUTH, 'authorized');
                $this->orderHelper->addTransactionToOrder($order, $transactions['capture'], Transaction::TYPE_CAPTURE, 'captured');
                $this->orderHelper->addInvoiceToOrder($order, $transactions['capture']);
            }
            elseif ($paymentAction == 'capture') {
                $this->orderHelper->addTransactionToOrder($order, $transactions['capture'], Transaction::TYPE_CAPTURE, 'captured');
                $this->orderHelper->addInvoiceToOrder($order, $transactions['capture']);
            }

            if (!$order->getEmailSent()) {
                // Send order confirmation email to customer.
                $this->orderSender->send($order);
            }

            $result = [
                'order_id' => $order->getId(),
                'order_increment_id' => $order->getIncrementId(),
                'order_status' => $order->getStatus(),
                'quote_id' => $quote->getId(),
            ];
        }else{
            $result = [
                'error'=> 1,
                'order_status' => 'not_created'
            ];
        }

        return $result;
    }

    /**
     * @param mixed $access_tokens
     * @param mixed $data
     * @return \IWD\CheckoutConnector\Api\array_iwd|string
     * @throws LocalizedException
     */
    public function update($access_tokens, $data)
    {
        if(!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        $orderId = $data['order_id'];
        $order = $this->orderFactory->create()->loadByIncrementId($orderId);

        if($order->getEntityId()){
            //Set Transactions for order
            $paymentAction = $data['payment_action'];
            $transaction   = $data['transaction'];

            if ($paymentAction == 'capture') {
                $this->orderHelper->addTransactionToOrder($order, $transaction, Transaction::TYPE_CAPTURE, 'captured');
                $this->orderHelper->addInvoiceToOrder($order, $transaction);
            }
            elseif ($paymentAction == 'refund') {
                $this->orderHelper->addTransactionToOrder($order, $transaction, Transaction::TYPE_REFUND, 'refunded');
                $this->orderHelper->refundOrder($order, $transaction);
            }
            elseif ($paymentAction == 'void') {
                $order->getPayment()->void(new DataObject());
                $order->save();
            }

            $result = [
                'error'=> 0,
                'order_status' => $order->getStatus()
            ];
        }
        else {
            $result = [
                'error'=> 1,
                'order_status' => 'not_found'
            ];
        }

        return $result;
    }

    /**
     * @param string $quote_id
     * @param mixed $access_tokens
     * @return \IWD\CheckoutConnector\Api\array_iwd|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuoteData($quote_id, $access_tokens)
    {
        if(!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        $quote = $this->quoteFactory->create()->load($quote_id);

        $result['addresses']              = $this->address->formatAddress($quote);
        $result['chosen_delivery_method'] = $this->shippingMethods->getSelectedShippingMethod($quote);
        $result['cart_items']             = $this->cartItems->getItems($quote);
        $result['cart']                   = $this->cartTotals->getTotals($quote);

        return $result;
    }
}