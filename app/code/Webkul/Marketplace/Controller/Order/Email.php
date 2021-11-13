<?php
namespace Webkul\Marketplace\Controller\Order;

class Email extends \Webkul\Marketplace\Controller\Order
{
    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($order = $this->_initOrder()) {
            try {
                $this->orderManagement->notify($order->getEntityId());
                $this->messageManager->addSuccess(__('You sent the order email.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We can\'t send the email order right now.'));
            }
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/view',
                [
                    'id' => $order->getEntityId(),
                    '_secure'=>$this->getRequest()->isSecure()
                ]
            );
        }else{
            return $this->resultRedirectFactory->create()->setPath('*/*/history', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
}
