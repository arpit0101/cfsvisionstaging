<?php
namespace Webkul\Marketplace\Api\Data;

/**
 * Marketplace Sellertransaction Interface.
 * @api
 */
interface SellertransactionInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID    = 'entity_id';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\Marketplace\Api\Data\SellertransactionInterface
     */
    public function setId($id);
}
