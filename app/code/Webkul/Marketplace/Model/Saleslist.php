<?php
namespace Webkul\Marketplace\Model;

use Webkul\Marketplace\Api\Data\SaleslistInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Marketplace Saleslist Model
 *
 * @method \Webkul\Marketplace\Model\ResourceModel\Saleslist _getResource()
 * @method \Webkul\Marketplace\Model\ResourceModel\Saleslist getResource()
 */
class Saleslist extends \Magento\Framework\Model\AbstractModel implements SaleslistInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Marketplace Saleslist cache tag
     */
    const CACHE_TAG = 'marketplace_saleslist';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_saleslist';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_saleslist';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\Marketplace\Model\ResourceModel\Saleslist');
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
            return $this->noRouteSaleslist();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Saleslist
     *
     * @return \Webkul\Marketplace\Model\Saleslist
     */
    public function noRouteSaleslist()
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
     * @return \Webkul\Marketplace\Api\Data\SaleslistInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}