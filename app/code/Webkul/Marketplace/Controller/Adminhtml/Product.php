<?php
namespace Webkul\Marketplace\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Webkul Marketplace admin product controller
 */
abstract class Product extends \Magento\Backend\App\Action
{
    /**
     * @param Action\Context $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Marketplace::product');
    }
}
