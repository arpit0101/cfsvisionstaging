<?php
namespace Webkul\Marketplace\Controller\Product\Initialization\Helper;

class HandlerFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create handler instance
     *
     * @param string $instance
     * @param array $arguments
     * @return object
     * @throws \InvalidArgumentException
     */
    public function create($instance, array $arguments = [])
    {
        if (!is_subclass_of(
            $instance,
            '\Webkul\Marketplace\Controller\Product\Initialization\Helper\HandlerInterface'
        )
        ) {
            throw new \InvalidArgumentException(
                $instance .
                ' does not implement ' .
                'Webkul\Marketplace\Controller\Product\Initialization\Helper\HandlerInterface'
            );
        }

        return $this->objectManager->create($instance, $arguments);
    }
}
