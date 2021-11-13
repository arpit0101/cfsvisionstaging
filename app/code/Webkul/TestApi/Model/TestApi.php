<?php
 
namespace Webkul\TestApi\Model;
 
/**
 * Marketplace Product Model.
 *
 * @method \Webkul\Marketplace\Model\ResourceModel\Product _getResource()
 * @method \Webkul\Marketplace\Model\ResourceModel\Product getResource()
 */
class TestApi  implements \Webkul\TestApi\Api\Data\TestApiInterface
{
    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return 10;
    }
 
    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\Marketplace\Api\Data\ProductInterface
     */
    public function setId($id)
    {
    }
 
    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return 'this is test title';
    }
 
    /**
     * Set title.
     *
     * @param string $title
     *
     * @return \Webkul\Marketplace\Api\Data\ProductInterface
     */
    public function setTitle($title)
    {
    }
 
    /**
     * Get desc.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return 'this is test api description';
    }
 
    /**
     * Set Desc.
     *
     * @param string $desc
     *
     * @return \Webkul\Marketplace\Api\Data\ProductInterface
     */
    public function setDescription($desc)
    {
    }
}