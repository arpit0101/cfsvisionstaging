<?php
namespace Webkul\Marketplace\Model\ResourceModel\Report;

use \Webkul\Marketplace\Model\ResourceModel\AbstractCollection;

/**
 * Webkul Marketplace ResourceModel Report collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\Marketplace\Model\Report', 'Webkul\Marketplace\Model\ResourceModel\Report');
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }

    
    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }
}
