<?php
namespace Webkul\Marketplace\Model;

use Webkul\Marketplace\Api\Data\SellerInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Marketplace Seller Model
 *
 * @method \Webkul\Marketplace\Model\ResourceModel\Seller _getResource()
 * @method \Webkul\Marketplace\Model\ResourceModel\Seller getResource()
 */
class Follow extends \Magento\Framework\Model\AbstractModel
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**#@+
     * Seller's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * Marketplace Seller cache tag
     */
    const CACHE_TAG = 'marketplace_follow';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_follow';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_follow';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\Marketplace\Model\ResourceModel\Follow');
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
            return $this->noRouteSeller();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Seller
     *
     * @return \Webkul\Marketplace\Model\Seller
     */
    public function noRouteSeller()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Prepare seller's statuses.
     * Available event marketplace_seller_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Approved'), self::STATUS_DISABLED => __('Disapproved')];
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
     * @return \Webkul\Marketplace\Api\Data\SellerInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}