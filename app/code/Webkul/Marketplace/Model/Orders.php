<?php
namespace Webkul\Marketplace\Model;

use Webkul\Marketplace\Api\Data\OrdersInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Marketplace Orders Model
 *
 * @method \Webkul\Marketplace\Model\ResourceModel\Orders _getResource()
 * @method \Webkul\Marketplace\Model\ResourceModel\Orders getResource()
 */
class Orders extends \Magento\Framework\Model\AbstractModel implements OrdersInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**#@-*/

    /**
     * Marketplace Orders cache tag
     */
    const CACHE_TAG = 'marketplace_orders';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_orders';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_orders';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\Marketplace\Model\ResourceModel\Orders');
    }

    /**
     * Load object data
     *
     * @param int|null $id
     * @param string $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteOrders();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Orders
     *
     * @return \Webkul\Marketplace\Model\Orders
     */
    public function noRouteOrders()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Prepare product's statuses.
     * Available event marketplace_product_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [0 => __('Pending'),1 => __('Paid'), 2 => __('Hold'), 3 => __('Refunded'), 4 => __('Voided')];
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\Marketplace\Api\Data\OrdersInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}