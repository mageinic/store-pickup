<?php

namespace MageINIC\StorePickup\Block\StorePickup;

use MageINIC\StorePickup\Helper\Data;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\Html\Link as MainLink;
use Magento\Framework\View\Element\Template\Context;

class Link extends MainLink
{
    /**
     * @var Data
     */
    private Data $helperData;

    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * Link Constructor
     *
     * @param Context $context
     * @param Data $helperData
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data    $helperData,
        Escaper $escaper,
        array   $data = []
    ) {
        $this->helperData = $helperData;
        $this->escaper = $escaper;
        parent::__construct($context, $data);
    }

    /**
     * Render block HTML
     *
     * @return string|void
     */
    public function _toHtml()
    {
        if ($this->helperData->getPosition() == 'toplink') {
            return '<li class="link store-pickup-link">' .'<a ' . $this->getLinkAttributes() . ' >'
                . $this->escaper->escapeHtml($this->getLabel()) . '</a></li>';
        }
    }
}
