<?php namespace Inchoo\Helloworld\Block\Order\Info;

use Magento\Customer\Model\Context;

class Buttons extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->httpContext = $httpContext;
		$this->orderRepository = $orderRepository;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }
	
    public function getCancelUrl($order)
    {
        return $this->getUrl('region/index/ordercancel', ['order_id' => $order->getId()]);
    }
	public function getOrderStatus($orderid)
    {
        $order = $this->orderRepository->get($orderid);
        $state = $order->getStatus(); //Get Order State(Complete, Processing, ....)
        return $state;
    }
}
