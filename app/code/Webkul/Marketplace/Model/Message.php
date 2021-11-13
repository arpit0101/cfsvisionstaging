<?php
namespace Webkul\Marketplace\Model;

use Webkul\Marketplace\Api\Data\FeedbackInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Marketplace Message Model
 *
 * @method \Webkul\Marketplace\Model\ResourceModel\Message _getResource()
 * @method \Webkul\Marketplace\Model\ResourceModel\Message getResource()
 */
class Message extends \Magento\Framework\Model\AbstractModel implements FeedbackInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**#@+
     * Message's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * Marketplace Message cache tag
     */
    const CACHE_TAG = 'marketplace_message';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_message';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_message';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\Marketplace\Model\ResourceModel\Message');
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
     * Load No-Route Message
     *
     * @return \Webkul\Marketplace\Model\Message
     */
    public function noRouteRoom()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Prepare Message's statuses.
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