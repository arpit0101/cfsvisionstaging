<?php
/**
 * Marketplace block for fieldset of configurable product
 */
namespace Webkul\Marketplace\Block\Product\Steps;

class SelectAttributes extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->registry = $registry;
    }

    public function getAddNewAttributeButtonUrl()
    {
        return $this->getUrl('catalog/product_attribute/new', [
                            'store' => $this->registry->registry('current_product')->getStoreId(),
                            'product_tab' => 'variations',
                            'popup' => 1,
                            '_query' => [
                                'attribute' => [
                                    'is_global' => 1,
                                    'frontend_input' => 'select',
                                ],
                            ],
                        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        return __('Select Attributes');
    }
}
