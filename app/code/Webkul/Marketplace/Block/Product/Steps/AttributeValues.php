<?php
/**
 * Marketplace block for fieldset of configurable product
 */
namespace Webkul\Marketplace\Block\Product\Steps;

class AttributeValues extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        return __('Attribute Values');
    }
}
