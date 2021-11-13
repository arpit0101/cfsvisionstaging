<?php
/**
 * Marketplace block for fieldset of configurable product
 */
namespace Webkul\Marketplace\Block\Product\Steps;

class Summary extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        return __('Summary');
    }

    /**
     * Get url to upload files
     *
     * @return string
     */
    public function getImageUploadUrl()
    {
        return $this->getUrl('marketplace/product_gallery/upload', ['_secure' => $this->getRequest()->isSecure()]);
    }
}
