<?php
namespace Webkul\Marketplace\Model;

use Webkul\Marketplace\Api\Data\FeedbackInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Marketplace Room Model
 *
 * @method \Webkul\Marketplace\Model\ResourceModel\Room _getResource()
 * @method \Webkul\Marketplace\Model\ResourceModel\Room getResource()
 */
class Room extends \Magento\Framework\Model\AbstractModel implements FeedbackInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**#@+
     * Room's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * Marketplace Room cache tag
     */
    const CACHE_TAG = 'marketplace_room';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_room';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_room';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\Marketplace\Model\ResourceModel\Room');
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
            return $this->noRouteFeedback();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Room
     *
     * @return \Webkul\Marketplace\Model\Room
     */
    public function noRouteRoom()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Prepare Room's statuses.
     * Available event marketplace_feedback_get_available_statuses to customize statuses.
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
     * @return \Webkul\Marketplace\Api\Data\RoomInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}