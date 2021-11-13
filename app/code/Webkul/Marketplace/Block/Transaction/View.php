<?php
namespace Webkul\Marketplace\Block\Transaction;
/**
 * Webkul Marketplace Transaction Create Block
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software Private Limited
 *
 */

use Magento\Catalog\Model\Product;

use Magento\Sales\Model\Order;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $order;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $customerSession;
    /**
    * @param Context $context
    * @param array $data
    * @param \Magento\Framework\ObjectManagerInterface $objectManager
    * @param \Magento\Customer\Model\Session $customerSession
    * @param \Magento\Store\Model\StoreManagerInterface $storeManager
    * @param Product $product
    */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Block\Product\Context $context,
        Product $product,
        Order $order,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->customerSession = $customerSession;
        $this->Product = $product;
        $this->Order = $order;
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function sellertransactionDetails($id)
    {
        return $this->_objectManager->create('Webkul\Marketplace\Model\Sellertransaction')->load($id);
    }

    public function sellertransactionOrderDetails($id)
    {
        return $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')->getCollection()
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $this->customerSession->getCustomerId()]
        )
        ->addFieldToFilter(
            'trans_id',
            ['eq' => $id]
        )
        ->addFieldToFilter(
            'order_id',
            ['neq' => 0]
        );
    }
}
