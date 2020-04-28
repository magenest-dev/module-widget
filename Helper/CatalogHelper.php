<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_Widget extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_Widget
 */

namespace Magenest\Widget\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Pricing\SaleableInterface;

class CatalogHelper extends AbstractHelper
{
    /**
     * @param Product $_product
     * @return bool|float
     */
    public static function getSalesPercent($_product)
    {
        if( ($_product instanceof Product) &&
            ($_product->getTypeId() !== 'bundle') &&
            ($_product->getTypeId() !== 'configurable')
        ) {
            $regularPrice = $_product->getPriceInfo()->getPrice("regular_price")->getAmount();
            $finalPrice = $_product->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)->getAmount();
            $specialPrice = $finalPrice->getValue();
            $price = $regularPrice->getValue();
            if ($specialPrice && $price && (float)$specialPrice < (float)$price) {
                $sale = floor((($price - $specialPrice) / $price) * 100);
                return $sale;
            }
        }
        return false;
    }

    public static function getSalesAmount($_product)
    {
        if( ($_product instanceof Product) &&
            ($_product->getTypeId() !== 'bundle') &&
            ($_product->getTypeId() !== 'configurable')
        ) {
            $regularPrice = $_product->getPriceInfo()->getPrice("regular_price")->getAmount();
            $finalPrice = $_product->getPriceInfo()->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)->getAmount();

            $specialPrice = $finalPrice->getValue();
            $price = $regularPrice->getValue();

            return $price-$specialPrice;
        }
        return false;
    }

    public static function compareDateRange($today, $from, $to)
    {
        return ($today >= strtotime($from) && $today <= strtotime($to)) || ($today >= strtotime($from) && is_null($to)) || (is_null($from) && $today <= strtotime($to)) || (is_null($from) && is_null($to));
    }

    /**
     * @param Product|SaleableInterface $_product
     * @return bool
     */
    public static function isProductNew($_product)
    {
        if (!$_product instanceof Product) {
            return false;
        }
        //Display new when turn on is_new or set new from date, new to date.
        if ($_product->getIsNew()) {
            return true;
        }else {
            $newFromDate = $_product->getNewsFromDate()?:0;
            $newToDate = $_product->getNewsToDate()?:0;
            if (static::compareDateRange(time(), $newFromDate, $newToDate)) {
                return true;
            }
        }

        return false;
    }
}
