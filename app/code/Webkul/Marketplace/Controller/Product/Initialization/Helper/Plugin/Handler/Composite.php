<?php
namespace Webkul\Marketplace\Controller\Product\Initialization\Helper\Plugin\Handler;

use Webkul\Marketplace\Controller\Product\Initialization\Helper\HandlerFactory;
use Webkul\Marketplace\Controller\Product\Initialization\Helper\HandlerInterface;
use Magento\Catalog\Model\Product;

class Composite implements HandlerInterface
{
    /**
     * Array of handler interface objects
     *
     * @var HandlerInterface[]
     */
    protected $handlers;

    /**
     * @param HandlerFactory $factory
     * @param array $handlers
     */
    public function __construct(HandlerFactory $factory, array $handlers = [])
    {
        foreach ($handlers as $instance) {
            $this->handlers[] = $factory->create($instance);
        }
    }

    /**
     * Process each of the handler objects
     *
     * @param Product $product
     * @return void
     */
    public function handle(Product $product)
    {
        foreach ($this->handlers as $handler) {
            $handler->handle($product);
        }
    }
}
