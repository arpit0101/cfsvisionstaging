<?php
namespace Webkul\Marketplace\Model;

use Webkul\Marketplace\Api\Data\SellertransactionInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Marketplace Sellertransaction Model
 *
 * @method \Webkul\Marketplace\Model\ResourceModel\Sellertransaction _getResource()
 * @method \Webkul\Marketplace\Model\ResourceModel\Sellertransaction getResource()
 */
class Sellertransaction extends \Magento\Framework\Model\AbstractModel implements SellertransactionInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**#@+
     * Sellertransaction's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * Marketplace Sellertransaction cache tag
     */
    const CACHE_TAG = 'marketplace_sellertransaction';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_sellertransaction';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_sellertransaction';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\Marketplace\Model\ResourceModel\Sellertransaction');
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
            return $this->noRouteSellertransaction();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Sellertransaction
     *
     * @return \Webkul\Marketplace\Model\Sellertransaction
     */
    public function noRouteSellertransaction()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
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
     * @return \Webkul\Marketplace\Api\Data\SellertransactionInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}