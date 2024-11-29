<?php

namespace Vendor\OmniChat\Api;

interface ShippingQuoteInterface
{
    /**
     * Get shipping quotes based on address and items.
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @param \Magento\Quote\Api\Data\CartItemInterface[] $items
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    public function getShippingQuotes($address, $items);
}
