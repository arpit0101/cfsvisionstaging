<?php

/**
 *
 * @Author              Ngo Quang Cuong <bestearnmoney87@gmail.com>
 * @Date                2016-12-12 21:47:12
 * @Last modified by:   nquangcuong
 * @Last Modified time: 2016-12-12 22:22:47
 */

namespace PHPCuong\Region\Controller\Adminhtml\Area;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'PHPCuong_Region::area_delete';
    /**
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        // check if we know what should be deleted
        $area_id = $this->getRequest()->getParam('area_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($area_id) {
            $area_name = '';
            try {
                // init model and delete
                $model = $this->_objectManager->create('PHPCuong\Region\Model\Area');
                $model->load($area_id);
                $area_name = $model->getDefaultName();
                $model->delete();
                $this->messageManager->addSuccess(__('The '.$area_name.' Area has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['area_id' => $area_id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('Area to delete was not found.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
