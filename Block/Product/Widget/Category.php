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
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Render;
use Magento\Catalog\Model\Product;

/**
 * Class HotSalesWidget
 * @package Magenest\Widget\Block\Product\Widget
 */
class Category extends HotSalesProduct implements BlockInterface
{
    /**
     * Display products type - all products
     */
    const DISPLAY_TYPE_ALL_PRODUCTS = 'all_products';

    /**
     * Display products type - new products
     */
    const DISPLAY_TYPE_NEW_PRODUCTS = 'hot_sales_products';

    /**
     * Default value whether show pager or not
     */
    const DEFAULT_SHOW_PAGER = false;

    /**
     * Default value for products per page
     */
    const DEFAULT_PRODUCTS_PER_PAGE = 5;

    protected $_template = 'Magenest_Widget::product/widget/sales/content/display_with_category.phtml';

    /**
     * Instance of pager block
     *
     * @var \Magento\Catalog\Block\Product\Widget\Html\Pager
     */
    protected $_pager;

    /**
     * @var int
     */
    protected $_categoryId;

    /**
     * @param $id
     * @return $this
     */
    public function setCategoryId($id)
    {
        $this->_categoryId = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->_categoryId;
    }

    /**
     * @return $this|\Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductCollection()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
            ->joinField('category_id', 'catalog_category_product', 'category_id','product_id=entity_id', null,'left')
            ->addFieldToFilter('category_id', ['in' => [$this->getCategoryId()]])
            ->setOrder('created_at', 'DESC');

        $collection = $this->_addProductAttributesAndPrices(
            $collection
        )->addStoreFilter();

        if ($this->isShowNew()) {
            $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
            $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');
            $collection->addAttributeToFilter(
                'news_from_date',
                [
                    'or' => [
                        0 => ['date' => true, 'to' => $todayEndOfDayDate],
                        1 => ['is' => new \Zend_Db_Expr('null')],
                    ]
                ],
                'left'
            )->addAttributeToFilter(
                'news_to_date',
                [
                    'or' => [
                        0 => ['date' => true, 'from' => $todayStartOfDayDate],
                        1 => ['is' => new \Zend_Db_Expr('null')],
                    ]
                ],
                'left'
            )->addAttributeToFilter(
                [
                    ['attribute' => 'news_from_date', 'is' => new \Zend_Db_Expr('not null')],
                    ['attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('not null')],
                ]
            )->addAttributeToSort(
                'news_from_date',
                'desc');
        }

        $collection->setPageSize(
            $this->getPageSize()
        )->groupByAttribute('created_at');
        return $collection;
    }

    /**
     * Product collection initialize process
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    protected function _getProductCollection()
    {
        $collection = parent::_getProductCollection();
        return $collection;
    }

    /**
     * Retrieve how many products should be displayed
     *
     * @return int
     */
    public function getProductsPerPage()
    {
        if (!$this->hasData('products_per_page')) {
            $this->setData('products_per_page', self::DEFAULT_PRODUCTS_PER_PAGE);
        }
        return $this->getData('products_per_page');
    }

    /**
     * Retrieve how many products should be displayed
     *
     * @return bool
     */
    public function isShowNew()
    {
        if ($this->getData('only_show_new')) {
            return true;
        }

        return false;
    }


    /**
     * Return flag whether pager need to be shown or not
     *
     * @return bool
     */
    public function showPager()
    {
        if (!$this->hasData('show_pager')) {
            $this->setData('show_pager', self::DEFAULT_SHOW_PAGER);
        }
        return (bool)$this->getData('show_pager');
    }

    /**
     * Retrieve how many products should be displayed on page
     *
     * @return int
     */
    protected function getPageSize()
    {
        return $this->showPager() ? $this->getProductsPerPage() : $this->getProductsCount();
    }

    /**
     * @return int|mixed
     */
    public function getProductsCount()
    {
        if (!$this->hasData('products_count')) {
            $this->_productsCount = self::DEFAULT_PRODUCTS_COUNT;
        } else {
            $this->_productsCount = $this->getData('products_count');
        }

        return $this->_productsCount;
    }
    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        if ($this->showPager()) {
            if (!$this->_pager) {
                $this->_pager = $this->getLayout()->createBlock(
                    'Magento\Catalog\Block\Product\Widget\Html\Pager',
                    'widget.hotsales.product.list.pager'
                );

                $this->_pager->setUseContainer(true)
                    ->setShowAmounts(true)
                    ->setShowPerPage(false)
                    ->setPageVarName(self::PAGE_VAR_NAME)
                    ->setLimit($this->getProductsPerPage())
                    ->setTotalLimit($this->getProductsCount())
                    ->setCollection($this->getProductCollection());
            }
            if ($this->_pager instanceof AbstractBlock) {
                return $this->_pager->toHtml();
            }
        }
        return '';
    }

    /**
     * Return HTML block with price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getProductPriceHtml(
        Product $product,
        $priceType = null,
        $renderZone = Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['zone'] = isset($arguments['zone'])
            ? $arguments['zone']
            : $renderZone;
        $arguments['price_id'] = isset($arguments['price_id'])
            ? $arguments['price_id']
            : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = isset($arguments['include_container'])
            ? $arguments['include_container']
            : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
            ? $arguments['display_minimal_price']
            : true;

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                FinalPrice::PRICE_CODE,
                $product,
                $arguments
            );
        }
        return $price;
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
