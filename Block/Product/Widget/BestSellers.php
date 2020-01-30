<?php
/**
 * Created by Magenest
 * User: Luu Thanh Thuy
 * Date: 09/01/2016
 * Time: 10:56
 */
namespace Magenest\Widget\Block\Product\Widget;

use Magento\Widget\Block\BlockInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Render;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ResourceConnection;

/**
 * Class BestSellers
 * @package Magenest\Widget\Block\Product\Widget
 */
class BestSellers extends AbstractProduct implements BlockInterface
{
    const AGGREGATION_DAILY = 'daily';

    const AGGREGATION_MONTHLY = 'monthly';

    const AGGREGATION_YEARLY = 'yearly';

    /**
     * Default value for products per page
     */
    const DEFAULT_PRODUCTS_PER_PAGE = 5;

    /**
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param ResourceConnection $resource
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        Visibility $catalogProductVisibility,
        \Magento\Framework\App\Http\Context $httpContext,
        ResourceConnection $resource,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->resource = $resource;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|\Magento\Framework\Data\Collection|Object
     */
    public function getProductCollection()
    {
        return $this->_getProductCollection();
    }

    /**
     * Prepare and return product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    protected function _getProductCollection()
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
            ->getSelect()
            ->join(['sellers' => $this->getTablename()],
                'e.entity_id = sellers.product_id', 'qty_ordered')
            ->group('e.entity_id');

        $collection = $this->_addProductAttributesAndPrices(
            $collection
        )->addStoreFilter()
            ->setOrder('qty_ordered', 'desc')
            ->setPageSize(
            $this->getProductsCount()
        )->setCurPage(
            1
        );

        return $collection;
    }

    /**
     * @return array
     */
    public function getTitle()
    {
        if (!$this->hasData('title')) {
            $this->setData('title', __('BestSellers Product'));
        }

        return $this->getData('title');
    }


    /**
     * Retrieve display type for products
     *
     * @return string
     */
    public function getBestSellersType()
    {
        if (!$this->hasData('best_sellers_type')) {
            $this->setData('best_sellers_type', self::AGGREGATION_MONTHLY);
        }

        $type = $this->getData('best_sellers_type');
        if ($type != self::AGGREGATION_MONTHLY || $type != self::AGGREGATION_DAILY || $type != self::AGGREGATION_YEARLY) {
            $this->setData('best_sellers_type', self::AGGREGATION_MONTHLY);
        }

        return $this->getData('best_sellers_type');
    }

    /**
     * @return string
     */
    public function getTablename()
    {
        $type = $this->getBestSellersType();
        $table = 'sales_bestsellers_aggregated_'.$type;

        return $this->resource->getTableName($table);
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
     * Retrieve how many products should be displayed
     *
     * @return int
     */
    public function getProductsCount()
    {
        if (!$this->hasData('products_count')) {
            $this->setData('product_count', 10);
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
