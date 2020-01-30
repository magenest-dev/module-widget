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
 * Class PageLayout
 * @package Magenest\Widget\Model\Config\Source
 */
class PageLayout implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '1column', 'label' => __('1 column')],
            ['value' => '2columns-left', 'label' => __('2 columns with left bar')],
            ['value' => '2columns-right', 'label' => __('2 columns with right bar')],
            ['value' => '3columns', 'label' => __('3 columns')],
            ['value' => 'empty', 'label' => __('Empty')],
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
