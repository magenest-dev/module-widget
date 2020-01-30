<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magenest\Widget\Block\Product;

use Magento\CatalogWidget\Block\Product\ProductsList as ProductListBase;
use Magento\Widget\Block\BlockInterface;

/**
 * Catalog Products List widget block
 * Class ProductsList
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductsList extends ProductListBase implements BlockInterface
{
    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $category;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder
     * @param \Magento\CatalogWidget\Model\Rule $rule
     * @param \Magento\Widget\Helper\Conditions $conditionsHelper
     * @param \Magento\Catalog\Model\Category $category
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder,
        \Magento\CatalogWidget\Model\Rule $rule,
        \Magento\Widget\Helper\Conditions $conditionsHelper,
        \Magento\Catalog\Model\Category $category,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $productCollectionFactory,
            $catalogProductVisibility,
            $httpContext,
            $sqlBuilder,
            $rule,
            $conditionsHelper,
            $data
        );

        $this->_category = $category;
        $this->setData('conditions', []);
    }
    /**
     * @return array
     */
    public function getCategorySelected()
    {
        $data = $this->getCategoriesList();
        $result = [];
        foreach ($data as $key) {
            $name = $this->_category->load($key)->getName();
            $result[$key] = $name;
        }
        return $result;
    }

    /**
     * @return array
     */
    protected function getCategoriesList()
    {
        if ($this->hasData('category_ids'))
        {
            $list = $this->getData('category_ids');
            $list = explode(',', $list);

            return $list;
        }

        return [];
    }

    /**
     * @return bool
     */
    protected function isShowProductNew()
    {
        if($this->hasData('only_show_new')) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getTitle()
    {
        if (!$this->hasData('title')) {
            $this->setData('title', __('Category Lists'));
        }

        return $this->getData('title');
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWidgetCategory()
    {
        $block = $this->getLayout()->createBlock('Magenest\Widget\Block\Product\Widget\Category');
        $block->setData(
            [
                'products_count' => $this->getProductsCount(),
                'only_show_new' => $this->isShowProductNew(),
            ]
        );

        return $block;
    }

    /**
     * Retrieve how many products should be displayed
     *
     * @return int
     */
    public function getProductsCount()
    {
        if (!$this->hasData('products_count')) {
            return parent::getProductsCount();
        }
        return $this->getData('products_count');
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
