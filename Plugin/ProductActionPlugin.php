<?php
namespace Vendor\OmniChat\Plugin;

use Magento\Catalog\Model\Product\Action;
use Vendor\OmniChat\Model\ProductWebhook;

/**
 * Mass "Update attributes" (price, status, ...) writes straight to the
 * attribute tables and fires no product save event.
 */
class ProductActionPlugin
{
    protected $productWebhook;

    public function __construct(ProductWebhook $productWebhook)
    {
        $this->productWebhook = $productWebhook;
    }

    public function afterUpdateAttributes(Action $subject, $result, $productIds)
    {
        $this->productWebhook->notifyByIds((array) $productIds);

        return $result;
    }
}
