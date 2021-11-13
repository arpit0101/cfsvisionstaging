<?php
namespace Webkul\Marketplace\Controller\Order\Creditmemo;

class Email extends \Webkul\Marketplace\Controller\Order
{
    public function execute()
    {
        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        if ($creditmemo = $this->_initCreditmemo()) {
            try {
                $this->_objectManager->create('Magento\Sales\Api\CreditmemoManagementInterface')->notify($creditmemo->getEntityId());
                $this->messageManager->addSuccess(__('The message has been sent.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Failed to send the creditmemo email.'));
            }
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/view',
                ['order_id' => $creditmemo->getOrder()->getId(), 'creditmemo_id' => $creditmemoId, '_secure'=>$this->getRequest()->isSecure()]
            );
        }else{
            return $this->resultRedirectFactory->create()->setPath('*/*/history', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
}
