<?php
/**
 * Copyright © 2015 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 *
 * Magenest_Widget extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package  Magenest_Widget
 * @author   <ThaoPV> thaopw@gmail.com
 */
namespace Magenest\Widget\Model\Config\Source;

/**
 * Class BestSellers
 * @package Magenest\Widget\Model\Config\Source
 */
class BestSellers implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'daily', 'label' => __('Daily')],
            ['value' => 'monthly', 'label' => __('Monthly')],
            ['value' => 'yearly', 'label' => __('Yearly')],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }
}
