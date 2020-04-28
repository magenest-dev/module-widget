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

namespace Magenest\Widget\Block\Adminhtml\Widget;

use Magento\Backend\Block\Template;

class Render extends Template
{
    protected $_element;
    public function __construct(Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    public function setElement($element)
    {
        $this->_element = $element;
        return $this;
    }

    public function getElement()
    {
        return $this->_element;
    }
}
