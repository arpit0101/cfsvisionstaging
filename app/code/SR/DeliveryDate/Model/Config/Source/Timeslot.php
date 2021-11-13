<?php
namespace SR\DeliveryDate\Model\Config\Source;

class Timeslot
{
    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $localeLists;

    /**
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     */
    public function __construct(\Magento\Framework\Locale\ListsInterface $localeLists)
    {
        $this->localeLists = $localeLists;
    }
	
	protected function _getDateColumnRenderer() {
        if( !$this->_dateRenderer ) {
            $this->_dateRenderer = $this->getLayout()->createBlock(
                'Namespace\Module\Block\Adminhtml\System\Config\DateField',
                '',
                [
                    'data' => [
                        'is_render_to_js_template' => true,
                        'date_format'              => 'dd/mm/Y'
                    ]
                ]
            );
        }

        return $this->_dateRenderer;
    }

    protected function _prepareToRender() {
        $this->addColumn( 'from', [ 'label' => __( 'From' ), 'renderer' => $this->_getDateColumnRenderer() ] );
        $this->addColumn( 'to', [ 'label' => __( 'To' ), 'renderer' => $this->_getDateColumnRenderer() ] );
        $this->addColumn( 'holiday', [ 'label' => __( 'Holiday' ), 'renderer' => false ] );

        $this->_addAfter       = false;
        $this->_addButtonLabel = __( 'Add' );
    }


    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return void
     */
    protected function _prepareArrayRow( \Magento\Framework\DataObject $row ) {

        $data = $row->getData();
        if( count( $data['column_values'] ) ) {
            $new_data = [
                'from'          => $data[0],
                'to'            => $data[1],
                'holiday'       => $data[2],
                '_id'           => $data['_id'],
                'column_values' => [
                    $data['_id'] . '_from'    => $data[0],
                    $data['_id'] . '_to'      => $data[1],
                    $data['_id'] . '_holiday' => $data[2]
                ]
            ];
        } else {
            $new_data = [
                'from'          => '',
                'to'            => '',
                'holiday'       => '',
                '_id'           => $data['_id'],
                'column_values' => []
            ];
        }
        $row->setData( $new_data );

    }
}