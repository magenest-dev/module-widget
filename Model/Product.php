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

namespace Magenest\Widget\Model;

use Magento\Framework\DataObject;

class Product extends DataObject
{
	/**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_reportCollection;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

	/**
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $reportCollection
     * @param \Magento\Catalog\Model\Product\Visibility                 $catalogProductVisibility
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface      $localeDate
     * @param \Magento\Store\Model\StoreManagerInterface                $storeManager
     * @param array                                                     $data
     */
	public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $reportCollection,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
        ) {
        $this->_localeDate = $localeDate;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_reportCollection = $reportCollection;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_storeManager = $storeManager;
        parent::__construct($data);
    }

    /**
     * Latest product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getLatestProducts($config = array())
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create()->addAttributeToSelect('*');
        if(is_array($config['cats']) && !empty($config['cats'])){
            $collection->addFieldToFilter('visibility', array(
               \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
               \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG
               ))
            ->addMinimalPrice()
            ->addUrlRewrite()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addFinalPrice();
            $collection ->joinTable(
                'catalog_category_product',
                'product_id=entity_id',
                array('category_id'=>'category_id'),
                null,
                'left')
            ->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $config['cats']))))
            ->groupByAttribute('entity_id');
        }
        $collection->addStoreFilter()
        ->setPageSize(isset($config['pagesize'])?$config['pagesize']:5)
        ->setCurPage(isset($config['curpage'])?$config['curpage']:1);
        return $collection;
    }

    /**
     * Best seller product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getBestsellerProducts($config = array())
    {
        $storeId = $this->_storeManager->getStore(true)->getId();
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create()->addAttributeToSelect('*');
        if(is_array($config['cats']) && !empty($config['cats'])){
            $collection->addFieldToFilter('visibility', array(
               \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
               \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG
               ))
            ->addMinimalPrice()
            ->addUrlRewrite()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addFinalPrice();
            $collection ->joinTable(
                'catalog_category_product',
                'product_id=entity_id',
                array('category_id'=>'category_id'),
                null,
                'left')
            ->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $config['cats']))))
            ->groupByAttribute('entity_id');
        }
        $collection->addStoreFilter()
        ->joinField(
            'qty_ordered',
            'sales_bestsellers_aggregated_monthly',
            'qty_ordered',
            'product_id=entity_id',
            'at_qty_ordered.store_id=' . (int)$storeId,
            'at_qty_ordered.qty_ordered > 0',
            'left'
            )
        ->setPageSize(isset($config['pagesize'])?$config['pagesize']:5)
        ->setCurPage(isset($config['curpage'])?$config['curpage']:1);
        return $collection;
    }

    /**
     * Random product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getRandomProducts($config = array())
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create()->addAttributeToSelect('*');
        if(is_array($config['cats']) && !empty($config['cats'])){
            $collection->addFieldToFilter('visibility', array(
               \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
               \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG
               ))
            ->addMinimalPrice()
            ->addUrlRewrite()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addFinalPrice();
            $collection ->joinTable(
                'catalog_category_product',
                'product_id=entity_id',
                array('category_id'=>'category_id'),
                null,
                'left')
            ->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $config['cats']))))
            ->groupByAttribute('entity_id');
        }
        $collection->addStoreFilter()
        ->setPageSize(isset($config['pagesize'])?$config['pagesize']:5)
        ->setCurPage(isset($config['curpage'])?$config['curpage']:1);
        $collection->getSelect()->order('rand()');
        return $collection;
    }

    /**
     * Top rated product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getTopratedProducts($config = array())
    {
        $storeId = $this->_storeManager->getStore(true)->getId();
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create()->addAttributeToSelect('*');
        if(is_array($config['cats']) && !empty($config['cats'])){
            $collection->addFieldToFilter('visibility', array(
               \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
               \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG
               ))
            ->addMinimalPrice()
            ->addUrlRewrite()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addFinalPrice();
            $collection ->joinTable(
                'catalog_category_product',
                'product_id=entity_id',
                array('category_id'=>'category_id'),
                null,
                'left')
            ->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $config['cats']))))
            ->groupByAttribute('entity_id');
        }
        $collection->addStoreFilter()
        ->joinField(
            'ves_review',
            'review_entity_summary',
            'reviews_count',
            'entity_pk_value=entity_id',
            'at_ves_review.store_id=' . (int)$storeId,
            'ves_review > 0',
            'left'
            )
        ->setPageSize(isset($config['pagesize'])?$config['pagesize']:5)
        ->setCurPage(isset($config['curpage'])?$config['curpage']:1);
        $collection->getSelect()->order('ves_review DESC');
        return $collection;
    }

    /**
     * Speical product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getSpecialProducts($config = array())
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create()->addAttributeToSelect('*');
        if(is_array($config['cats']) && !empty($config['cats'])){
            $collection->addFieldToFilter('visibility', array(
               \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
               \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG
               ))
            ->addMinimalPrice()
            ->addUrlRewrite()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addFinalPrice();
            $collection ->joinTable(
                'catalog_category_product',
                'product_id=entity_id',
                array('category_id'=>'category_id'),
                null,
                'left')
            ->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $config['cats']))))
            ->groupByAttribute('entity_id');
        }
        $collection->addStoreFilter()
        ->addMinimalPrice()
        ->addUrlRewrite()
        ->addTaxPercents()
        ->addFinalPrice();
        $collection->setPageSize(isset($config['pagesize'])?$config['pagesize']:5)
        ->setCurPage(isset($config['curpage'])?$config['curpage']:1);
        $collection->getSelect()->where('price_index.final_price < price_index.price');
        return $collection;
    }

    /**
     * Most viewed product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getMostViewedProducts($config = array())
    {
    	/** @var $collection \Magento\Reports\Model\ResourceModel\Product\CollectionFactory */
        $collection = $this->_reportCollection->create()->addAttributeToSelect('*');
        if(is_array($config['cats']) && !empty($config['cats'])){
            $collection->addFieldToFilter('visibility', array(
               \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH,
               \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG
               ))
            ->addMinimalPrice()
            ->addUrlRewrite()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addFinalPrice();
            $collection ->joinTable(
                'catalog_category_product',
                'product_id=entity_id',
                array('category_id'=>'category_id'),
                null,
                'left')
            ->addAttributeToFilter( array( array('attribute' => 'category_id', 'in' => array('finset' => $config['cats']))))
            ->groupByAttribute('entity_id');
        }
        $collection->addStoreFilter()
        ->setPageSize(isset($config['pagesize'])?$config['pagesize']:5)
        ->setCurPage(isset($config['curpage'])?$config['curpage']:1);
        return $collection;
    }

    /**
     * @param $source_key
     * @param array $config
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductBySource($source_key, $config = [])
    {
        switch ($source_key) {
            case 'latest':
            $collection = $this->getLatestProducts($config);
            break;
            case 'special':
            $collection = $this->getSpecialProducts($config);
            break;
            case 'popular':
            $collection = $this->getMostViewedProducts($config);
            break;
            case 'best_seller':
            $collection = $this->getBestsellerProducts($config);
            break;
            case 'top_rated':
            $collection = $this->getTopratedProducts($config);
            break;
            case 'random':
            $collection = $this->getRandomProducts($config);
            break;
            default:
            $collection = $this->getRandomProducts($config);
            break;
        }
        return $collection;
    }
}
