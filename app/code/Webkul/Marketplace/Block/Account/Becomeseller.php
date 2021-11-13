<?php
namespace Webkul\Marketplace\Block\Account;
/**
 * Webkul Marketplace Account Becomeseller Block
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software Private Limited
 *
 */

class Becomeseller extends \Magento\Framework\View\Element\Template
{
    /**
    * @param Context $context
    * @param array $data
    */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}
