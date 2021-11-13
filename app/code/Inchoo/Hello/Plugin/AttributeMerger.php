<?php namespace Inchoo\Hello\Plugin;

use Magento\Quote\Api\Data\CartInterface;

class AttributeMerger
{
    public function afterMerge(
        \Magento\Checkout\Block\Checkout\AttributeMerger $subject,
        array $fields
    ) {
        if (!isset($fields['street'])) {
            return $fields;
        }
        $lines = $fields['street']['children'];
        for ($lineIndex = 0; $lineIndex < count($lines); $lineIndex++) {
            if ($lineIndex !== 0) {
                $lines[$lineIndex]['validation'] = ['required-entry' => false];
            }
        }
        $fields['street']['children'] = $lines;
        return $fields;
    }
}