<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 09/01/2016
 * Time: 10:56
 */
namespace Magenest\Widget\Block\Product\Widget;

use Magento\Widget\Block\BlockInterface;
use Magenest\Widget\Block\Product\HotSalesProduct;
use Magento\Catalog\Pricing\Price;
use Magento\Framework\Pricing\Render;
use Magento\Catalog\Model\Product;

/**
 * Class SalesWithPercent
 * @package Magenest\Widget\Block\Product\Widget
 */
class SalesWithPercent extends HotSalesProduct implements BlockInterface
{

    const DEFAULT_PERCENT_SALES = 50;

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|\Magento\Framework\Data\Collection|Object
     */
    public function getProductCollection()
    {
        return $this->_getProductCollection()->load();
    }

    /**
     * Product collection initialize process
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    protected function _getProductCollection()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection|Object $collections */
        $collection = $this->_productCollectionFactory->create();

        $customerGroupId = (int)$this->_customerSession->getCustomerGroupId();
        $websiteId = (int)$this->_storeManager->getStore($this->getStoreId())->getWebsiteId();

        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $collection = $this->_addProductAttributesAndPrices(
            $collection
        )->addStoreFilter();

        $date = $this->_localeDate->date()->format('Y-m-d');
        $joinCond = join(
            ' AND ',
            [
                '(e.entity_id = rule_price.product_id OR child_product.product_id = rule_price.product_id)',
                'rule_price.rule_date = "'.$date.'"',
                'rule_price.customer_group_id = '.$customerGroupId,
                'rule_price.website_id = '.$websiteId
            ]
        );
        $displayCond = join(
            ' OR ',
            [
                'price_index.final_price = price_index.price*'.$this->getPercentSales(),
                'rule_price.rule_price = price_index.price*'.$this->getPercentSales()
            ]
        );
        $collection->getSelect()->where($displayCond)
            ->joinLeft(
                ['child_product' => $collection->getTable('catalog_product_super_link')],
                'child_product.parent_id = e.entity_id', 'product_id'
            )
            ->joinLeft(
                ['rule_price' => $collection->getTable('catalogrule_product_price')],
                $joinCond, 'rule_price'
            )->group('e.entity_id');

        $collection->addAttributeToSort('created_at', 'desc')
            ->setPageSize($this->getProductsCount())
            ->setCurPage(1);

        return $collection;
    }

    /**
     *
     */
    public function getPercentSales()
    {
        if (!$this->hasData('percent_sales')) {
            $this->setData('percent_sales', self::DEFAULT_PERCENT_SALES);
        }

        return (float)$this->getData('percent_sales')/100;
    }

    /**
     * Add data to the widget.
     * Retains previous data in the widget.
     *
     * @param array $arr
     * @return $this
     */
    public function addData(array $arr)
    {
        return parent::addData($arr);
    }

    /**
     * Overwrite data in the widget.
     *
     * Param $key can be string or array.
     * If $key is string, the attribute value will be overwritten by $value.
     * If $key is an array, it will overwrite all the data in the widget.
     *
     * @param string|array $key
     * @param mixed $value
     * @return \Magento\Framework\DataObject
     */
    public function setData($key, $value = null)
    {
        return parent::setData($key, $value);
    }
}
