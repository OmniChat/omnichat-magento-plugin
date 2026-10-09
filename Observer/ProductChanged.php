<?php
namespace Vendor\OmniChat\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Vendor\OmniChat\Model\ProductWebhook;

/**
 * catalog_product_save_commit_after runs after the commit, so the adapter
 * never reads the product before the change lands.
 */
class ProductChanged implements ObserverInterface
{
    protected $productWebhook;

    public function __construct(ProductWebhook $productWebhook)
    {
        $this->productWebhook = $productWebhook;
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        // A deleted product has no row left to resolve: send it as is so the
        // adapter deactivates it.
        if ($observer->getEvent()->getName() === 'catalog_product_delete_after_done') {
            $this->productWebhook->send([['id' => (string) $product->getId(), 'sku' => $product->getSku()]]);

            return;
        }

        $this->productWebhook->notifyByIds([$product->getId()]);
    }
}
