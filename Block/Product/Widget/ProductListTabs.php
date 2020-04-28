<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * cf_theme extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package cf_theme
 * @package linhphung
 */

namespace Magenest\Widget\Block\Product\Widget;

use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Catalog\Model\Product\Visibility;

class ProductListTabs extends \Magento\Framework\View\Element\Template implements BlockInterface, IdentityInterface
{
    const MAX_TABS = 5;
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Catalog\Helper\Product\Compare
     */
    protected $_compareProduct;
    /**
     * @var ImageBuilder
     * @since 102.0.0
     */
    protected $imageBuilder;

    protected $_abstractProduct;

    /**
     * @var \Magento\Framework\Url\EncoderInterface|null
     */
    private $urlEncoder;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $_wishlistHelper;

    /**
     * Json Serializer Instance
     *
     * @var Json
     */
    private $json;

    private $identities = [];

    /**
     * ProductListTabs constructor.
     * @param Template\Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Block\Product\Context $contextProduct
     * @param \Magento\Catalog\Block\Product\AbstractProduct $abstractProduct
     * @param array $data
     * @param EncoderInterface|null $urlEncoder
     */
    public function __construct(Template\Context $context,
                                CollectionFactory $productCollectionFactory,
                                \Magento\Catalog\Block\Product\Context $contextProduct,
                                \Magento\Catalog\Block\Product\AbstractProduct $abstractProduct,
                                Json $json = null,
                                array $data = [],
                                EncoderInterface $urlEncoder = null)
    {
        parent::__construct($context, $data);
        $this->setTemplate('Magenest_Widget::widget/product_list_tabs.phtml');
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->imageBuilder = $contextProduct->getImageBuilder();
        $this->_compareProduct = $contextProduct->getCompareProduct();
        $this->_wishlistHelper = $contextProduct->getWishlistHelper();
        $this->_abstractProduct = $abstractProduct;
        $this->json = $json ?: ObjectManager::getInstance()->get(Json::class);
        $this->urlEncoder = $urlEncoder ?: ObjectManager::getInstance()->get(EncoderInterface::class);
    }

    /**
     * Initialize block's cache
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
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
        $keyArr[] = $this->json->serialize($blockDataValue);
        return $keyArr;
    }

    /**
     * @return array
     */
    public function getListProductTabs()
    {
        $tabs = [];
        $maxTabs = self::MAX_TABS;
        for ($i = 1; $i <= $maxTabs; $i++) {
            $listProductTab = null;
            $TitleTab = $this->getData("title_tab" . $i);
            $idsProductTab = $this->getData("list_product_tab" . $i);
            if ($idsProductTab) {
                $idsProductTab = explode(",", $idsProductTab);
                $listProductTab = $this->getProducts($idsProductTab);
            }
            if ($TitleTab || $listProductTab) {
                $tabs[] = array(
                    'title' => $TitleTab,
                    'products' => $listProductTab
                );
            }
        }
        return $tabs;
    }
    /**
     * @param array $idsProductTab
     * @return mixed
     */
    protected function getProducts($idsProductTab)
    {
        $listProductCol = $this->_productCollectionFactory->create()
            ->addFieldToSelect("*")
            ->addFieldToFilter('entity_id', array('in' => $idsProductTab))
            ->addFieldToFilter('visibility', array('neq' => Visibility::VISIBILITY_NOT_VISIBLE));
        $listProduct = $listProductCol->getItems();
        foreach ($listProduct as $product){
            $this->identities = array_merge($this->identities, $product->getIdentities());
        }
        return $listProduct;
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->create($product, $imageId, $attributes);
    }

    /**
     * Whether redirect to cart enabled
     *
     * @return bool
     */
    public function isRedirectToCartEnabled()
    {
        return $this->_scopeConfig->getValue(
            'checkout/cart/redirect_to_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve Add Product to Compare Products List URL
     *
     * @return string
     */
    public function getAddToCompareUrl()
    {
        return $this->_compareProduct->getAddUrl();
    }

    /**
     * Retrieve add to wishlist params
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getAddToWishlistParams($product)
    {
        return $this->_wishlistHelper->getAddParams($product);
    }

    /**
     * Retrieve Product URL using UrlDataObject
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional the route params
     * @return string
     */
    public function getProductUrl($product, $additional = [])
    {
        if ($this->hasProductUrl($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            return $product->getUrlModel()->getUrl($product, $additional);
        }

        return '#';
    }

    /**
     * Check Product has URL
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function hasProductUrl($product)
    {
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get post parameters.
     *
     * @param Product $product
     * @return array
     */
    public function getAddToCartPostParams(Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlEncoder->encode($url),
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        $requestingPageUrl = $this->getRequest()->getParam('requesting_page_url');

        if (!empty($requestingPageUrl)) {
            $additional['useUencPlaceholder'] = true;
            $url = $this->_abstractProduct->getAddToCartUrl($product, $additional);
            return str_replace('%25uenc%25', $this->urlEncoder->encode($requestingPageUrl), $url);
        }

        return $this->_abstractProduct->getAddToCartUrl($product, $additional);
    }

    /**
     * Get product price.
     *
     * @param Product $product
     * @return string
     */
    public function getProductPrice(Product $product)
    {
        $priceRender = $this->getPriceRender();

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                FinalPrice::PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => Render::ZONE_ITEM_LIST,
                    'list_category_page' => true
                ]
            );
        }

        return $price;
    }

    /**
     * Specifies that price rendering should be done for the list of products.
     * (rendering happens in the scope of product list, but not single product)
     *
     * @return Render
     */
    protected function getPriceRender()
    {
        return $this->getLayout()->getBlock('product.price.render.default')
            ->setData('is_product_list', true);
    }

    /**
     * @inheritDoc
     */
    public function getIdentities()
    {
        return $this->identities;
    }

}
