<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * cf_theme extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package cf_theme
 * @package linhphung
 */

namespace Magenest\Widget\Block\Adminhtml\Widget\Product;


use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory;

class Chooser extends Template
{
    protected $_elementFactory;

    /**
     * Chooser constructor.
     * @param Template\Context $context
     * @param Factory $elementFactory
     * @param array $data
     */
    public function __construct(Template\Context $context,
                                Factory $elementFactory,
                                array $data = [])
    {
        parent::__construct($context, $data);
        $this->_elementFactory = $elementFactory;
    }
    /**
     * Prepare chooser element HTML
     *
     * @param AbstractElement $element Form Element
     * @return AbstractElement
     */
    public function prepareElementHtml(AbstractElement $element){
        $htmlId = $element->getId();
        $data = $element->getData();

        $data['after_element_html'] = $this->_afterElementHtml($element);
        $data['readonly'] = 'readonly';
        $htmlItem = $this->_elementFactory->create('text', ['data' => $data]);
        $htmlItem
            ->setId("{$htmlId}")
            ->setForm($element->getForm())
            ->addClass('entities');
        $return = <<<HTML
                <div id="{$htmlId}-container" class="chooser_container">{$htmlItem->getElementHtml()}</div>
HTML;
        $element->setData('after_element_html', $return);
        return $element;
    }

    protected function _afterElementHtml($element)
    {
        $html = $this->getLayout()->createBlock('Magenest\Widget\Block\Adminhtml\Widget\Render')
            ->setTemplate("Magenest_Widget::widget/product/product_grid_render.phtml")
            ->setElement($element)
            ->toHtml();

        return $html;
    }

}