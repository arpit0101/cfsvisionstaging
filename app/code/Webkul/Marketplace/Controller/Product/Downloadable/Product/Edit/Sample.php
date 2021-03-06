<?php
namespace Webkul\Marketplace\Controller\Product\Downloadable\Product\Edit;

use Magento\Downloadable\Helper\Download as DownloadHelper;

class Sample extends \Webkul\Marketplace\Controller\Product\Downloadable\Product\Edit\Link
{
    /**
     * @return \Magento\Downloadable\Model\Sample
     */
    protected function _createLink()
    {
        return $this->_objectManager->create('Magento\Downloadable\Model\Sample');
    }

    /**
     * @return \Magento\Downloadable\Model\Sample
     */
    protected function _getLink()
    {
        return $this->_objectManager->get('Magento\Downloadable\Model\Sample');
    }

    /**
     * Download sample action
     *
     * @return void
     */
    public function execute()
    {
        $sampleId = $this->getRequest()->getParam('id', 0);
        /** @var \Magento\Downloadable\Model\Sample $sample */
        $sample = $this->_createLink()->load($sampleId);
        if ($sample->getId()) {
            $resource = '';
            $resourceType = '';
            if ($sample->getSampleType() == DownloadHelper::LINK_TYPE_URL) {
                $resource = $sample->getSampleUrl();
                $resourceType = DownloadHelper::LINK_TYPE_URL;
            } elseif ($sample->getSampleType() == DownloadHelper::LINK_TYPE_FILE) {
                $resource = $this->_objectManager->get(
                    'Magento\Downloadable\Helper\File'
                )->getFilePath(
                    $this->_getLink()->getBasePath(),
                    $sample->getSampleFile()
                );
                $resourceType = DownloadHelper::LINK_TYPE_FILE;
            }
            try {
                $this->_processDownload($resource, $resourceType);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError(__('Something went wrong while getting the requested content.'));
            }
        }
    }
}
