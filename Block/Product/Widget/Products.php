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

namespace Magenest\Widget\Block\Product\Widget;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Widget\Block\BlockInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magenest\Widget\Model\Product as ProductModel;

class Products extends AbstractProduct implements BlockInterface, IdentityInterface
{
	/**
     * @var ProductModel
     */
	protected $_productModel;

	/**
	 * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
	 */
	protected $_collection;

	public function __construct(
        ProductModel $productModel,
        Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_productModel = $productModel;
    }

    /**
     * Initialize block's cache
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addColumnCountLayoutDepend('empty', 6)
            ->addColumnCountLayoutDepend('1column', 5)
            ->addColumnCountLayoutDepend('2columns-left', 4)
            ->addColumnCountLayoutDepend('2columns-right', 4)
            ->addColumnCountLayoutDepend('3columns', 3);

        $this->addData(
            ['cache_lifetime' => 86400, 'cache_tags' => [\Magento\Catalog\Model\Product::CACHE_TAG]]
        );
    }

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
	public function getCacheKeyInfo()
	{
        $keyArr = parent::getCacheKeyInfo();
        $blockDataValue = array_values($this->getData());
        $keyArr[] = implode("|", $blockDataValue);
        return $keyArr;
	}

	public function getConfig($key, $default = '')
	{
		if($this->hasData($key) && $this->getData($key))
		{
			return $this->getData($key);
		}
		return $default;
	}

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
	public function getProductCollection(){
	    if(!$this->_collection){
            $catIds = [];
            $categories = $this->getConfig("categories");
            if($categories!=''){
                $catIds = explode(",", $categories);
            }
            $source_key = $this->getConfig("product_source");
            $config = [];
            $config['pagesize'] = $this->getConfig('products_count',12);
            $config['cats'] = $catIds;
            $collection = $this->_productModel->getProductBySource($source_key, $config);

            $this->_collection = $collection;
        }
		return $this->_collection;
	}

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType = null,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
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
        if (!$priceRender) {
            $priceRender = $this->getLayout()->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }

        $price = $priceRender->render(
            \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
            $product,
            $arguments
        );

        return $price;
    }

    /**
     * @inheritDoc
     */
    public function getIdentities()
    {
        $identities = [];

        foreach ($this->getProductCollection() as $item) {
            $identities = array_merge($identities, $item->getIdentities());
        }

        return $identities;
    }
}
