<?php
namespace Webkul\Marketplace\Api\Data;

/**
 * Marketplace Saleslist Interface.
 * @api
 */
interface SaleslistInterface
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
     * @return \Webkul\Marketplace\Api\Data\SaleslistInterface
     */
    public function setId($id);
}
