<?php

namespace Vendor\OmniChat\Model;

use Vendor\OmniChat\Api\ShippingQuoteInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ShippingMethodManagementInterface;
use Magento\Quote\Model\Quote\Address\ToAddressInterface;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Customer\Model\Session;

class Quote implements ShippingQuoteInterface
{
    private $quoteFactory;
    private $shippingMethodManagement;
    private $productRepository;
    private $itemFactory;

    public function __construct(
        QuoteFactory $quoteFactory,
        ShippingMethodManagementInterface $shippingMethodManagement,
        ProductRepositoryInterface $productRepository,
        ItemFactory $itemFactory
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->productRepository = $productRepository;
        $this->itemFactory = $itemFactory;
    }

    public function getShippingQuotes($address, $items)
    {
        $quote = $this->quoteFactory->create();
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->addData($address->getData());

        foreach ($items as $itemData) {
            $product = $this->productRepository->get($itemData['sku']);
            $quoteItem = $this->itemFactory->create();
            $quoteItem->setProduct($product);
            $quoteItem->setQty($itemData['qty']);
            $quote->addItem($quoteItem);
        }

        $quote->setIsSuperMode(true);
        $quote->collectTotals();
        $quote->save();

        $cartId = $quote->getId();
        $result = $this->shippingMethodManagement->estimateByExtendedAddress($cartId, $address, $items);
        $quote->delete();

        return $result;
    }
}
