<?php
/**
 * Attribute add/edit form options tab
 *
 */

namespace Clarion\CustomerAttribute\Block\Adminhtml\Attribute\Edit\Options;

use Magento\Store\Model\ResourceModel\Store\Collection;

class Options extends \Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Options
{
    /**
     * @var string
     */
    protected $_template = 'Clarion_CustomerAttribute::customer/attribute/options.phtml';
}
