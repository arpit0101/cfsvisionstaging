<?php
namespace Webkul\Marketplace\Api\Data;

/**
 * Marketplace Room Interface
 * @api
 */
interface RoomInterface
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
     * @return \Webkul\Marketplace\Api\Data\RoomInterface
     */
    public function setId($id);
}
