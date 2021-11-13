<?php
namespace Inchoo\Helloworld\Setup;

use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Status;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\Status as StatusResource;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;


/**
 * Class InstallData
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Custom Processing Order-Status code
     */
    const ORDER_STATUS_PROCESSING_FULFILLMENT_CODE = 'delivered';

    /**
     * Custom Processing Order-Status label
     */
    const ORDER_STATUS_PROCESSING_FULFILLMENT_LABEL = 'Delivered';

    /**
     * Custom Order-State code
     */
    const ORDER_STATE_CUSTOM_CODE = 'delivered';

    /**
     * Custom Order-Status code
     */
    const ORDER_STATUS_CUSTOM_CODE = 'delivered';

    /**
     * Custom Order-Status label
     */
    const ORDER_STATUS_CUSTOM_LABEL = 'Delivered';

    /**
     * Status Factory
     *
     * @var StatusFactory
     */
    protected $statusFactory;

    /**
     * Status Resource Factory
     *
     * @var StatusResourceFactory
     */
    protected $statusResourceFactory;

	
	/**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;
     
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;
	
	
    /**
     * InstallData constructor
     *
     * @param StatusFactory $statusFactory
     * @param StatusResourceFactory $statusResourceFactory
     */
    public function __construct(
        StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory,
		CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
		
		$this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     *
     * @throws Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
		if (version_compare($context->getVersion(), '1.0.3') < 0) {
			$this->addNewOrderProcessingStatus();
			$this->addNewOrderStateAndStatus();
			
		}
		$this->addNewCustomerAttributes($setup, $context );
    }

    /**
     * Create new order processing status and assign it to the existent state
     *
     * @return void
     *
     * @throws Exception
     */
    protected function addNewOrderProcessingStatus()
    {
        /** @var StatusResource $statusResource */
        $statusResource = $this->statusResourceFactory->create();
        /** @var Status $status */
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => self::ORDER_STATUS_PROCESSING_FULFILLMENT_CODE,
            'label' => self::ORDER_STATUS_PROCESSING_FULFILLMENT_LABEL,
        ]);

        try {
            $statusResource->save($status);
        } catch (AlreadyExistsException $exception) {

            return;
        }

        $status->assignState(Order::STATE_PROCESSING, false, true);
    }

    /**
     * Create new custom order status and assign it to the new custom order state
     *
     * @return void
     *
     * @throws Exception
     */
    protected function addNewOrderStateAndStatus()
    {
        /** @var StatusResource $statusResource */
        $statusResource = $this->statusResourceFactory->create();
        /** @var Status $status */
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => self::ORDER_STATUS_CUSTOM_CODE,
            'label' => self::ORDER_STATUS_CUSTOM_LABEL,
        ]);

        try {
            $statusResource->save($status);
        } catch (AlreadyExistsException $exception) {

            return;
        }

        $status->assignState(self::ORDER_STATE_CUSTOM_CODE, true, true);
    }
	
	/**
     * Create new custom attribute to save customer location selection
     *
     * @return void
     *
     * @throws Exception
     */
    protected function addNewCustomerAttributes($setup, $context)
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
         
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
         
        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
         
        $customerSetup->addAttribute(Customer::ENTITY, 'session_area_id', [
            'type' => 'varchar',
            'label' => 'Area',
            'input' => 'text',
            'required' => false,
            'visible' => false,
            'user_defined' => true,
            'position' =>999,
            'system' => 0,
        ]);
         
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'session_area_id')
        ->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer'],//you can use other forms also ['adminhtml_customer_address', 'customer_address_edit', 'customer_register_address']
        ]);
         
        $attribute->save();
		
		$customerSetup->addAttribute(Customer::ENTITY, 'session_region_id', [
            'type' => 'varchar',
            'label' => 'Area',
            'input' => 'text',
            'required' => false,
            'visible' => false,
            'user_defined' => true,
            'position' =>999,
            'system' => 0,
        ]);
         
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'session_region_id')
        ->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer'],//you can use other forms also ['adminhtml_customer_address', 'customer_address_edit', 'customer_register_address']
        ]);
         
        $attribute->save();
    }
}