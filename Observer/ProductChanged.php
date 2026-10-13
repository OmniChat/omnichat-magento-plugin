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
    // Same product instance travels from delete_before to delete_after_done.
    const PARENT_IDS = 'omnichat_parent_ids';

    protected $productWebhook;

    public function __construct(ProductWebhook $productWebhook)
    {
        $this->productWebhook = $productWebhook;
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        switch ($observer->getEvent()->getName()) {
            // The parent link is gone after the delete: keep it while it exists.
            case 'catalog_product_delete_before':
                $product->setData(self::PARENT_IDS, $this->productWebhook->parentIds($product->getId()));

                return;

            // A deleted variant changes its parents, which the adapter refetches;
            // a deleted product has no row left, so it goes as is to be deactivated.
            case 'catalog_product_delete_after_done':
                $parentIds = $product->getData(self::PARENT_IDS);

                if ($parentIds) {
                    $this->productWebhook->notifyByIds($parentIds);
                } else {
                    $this->productWebhook->send([['id' => (string) $product->getId(), 'sku' => $product->getSku()]]);
                }

                return;

            default:
                $this->productWebhook->notifyByIds([$product->getId()]);
        }
    }
}
