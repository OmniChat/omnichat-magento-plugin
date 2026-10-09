<?php
namespace Vendor\OmniChat\Model;

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableProductResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Tells OmniChat which products changed; the adapter fetches their current
 * data from the store, so only ids and SKUs travel.
 */
class ProductWebhook
{
    // The product save waits for this call.
    const TIMEOUT_SECONDS = 3;
    // Keeps a mass update from becoming one huge request.
    const CHUNK_SIZE = 50;

    protected $productResource;
    protected $configurableProductResource;
    protected $storeManager;
    protected $curlFactory;
    protected $scopeConfig;
    protected $encryptor;
    protected $logger;

    public function __construct(
        ProductResource $productResource,
        ConfigurableProductResource $configurableProductResource,
        StoreManagerInterface $storeManager,
        CurlFactory $curlFactory,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        LoggerInterface $logger
    ) {
        $this->productResource = $productResource;
        $this->configurableProductResource = $configurableProductResource;
        $this->storeManager = $storeManager;
        $this->curlFactory = $curlFactory;
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->logger = $logger;
    }

    public function parentIds($productId)
    {
        return $this->configurableProductResource->getParentIdsByChild($productId) ?: [];
    }

    /**
     * A configurable child becomes its parent: the parent is the product the
     * OmniChat catalog knows, with the child as one of its variants.
     */
    public function notifyByIds(array $productIds)
    {
        try {
            $ids = [];
            foreach ($productIds as $productId) {
                foreach ($this->parentIds($productId) ?: [$productId] as $id) {
                    $ids[$id] = true;
                }
            }

            if (!$ids) {
                return;
            }

            $products = [];
            foreach ($this->productResource->getProductsSku(array_keys($ids)) as $row) {
                $products[] = ['id' => (string) $row['entity_id'], 'sku' => $row['sku']];
            }

            $this->send($products);
        } catch (\Exception $e) {
            $this->logger->error('[OmniChatPlugin] Error resolving changed products: ' . $e->getMessage());
        }
    }

    /**
     * Never throws: a failed notice must not fail the product save.
     */
    public function send(array $products)
    {
        foreach (array_chunk($products, self::CHUNK_SIZE) as $chunk) {
            try {
                $url = $this->scopeConfig->getValue('vendor_omnichat/general/webhook_url', ScopeInterface::SCOPE_STORE);
                $key = $this->encryptor->decrypt($this->scopeConfig->getValue('vendor_omnichat/general/key', ScopeInterface::SCOPE_STORE));
                $token = $this->encryptor->decrypt($this->scopeConfig->getValue('vendor_omnichat/general/token', ScopeInterface::SCOPE_STORE));

                if (!$url) {
                    return;
                }

                // Own client: the shared Curl would carry this timeout over to
                // the order webhook in the same request.
                $curl = $this->curlFactory->create();
                $curl->setTimeout(self::TIMEOUT_SECONDS);
                $curl->setOption(CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'x-api-key: ' . $key,
                    'x-api-secret: ' . $token
                ]);
                $curl->post(rtrim($url, '/') . '/v1/product-webhooks', json_encode([
                    // Picks the integration when the retailer has several stores.
                    'store_url' => $this->storeManager->getDefaultStoreView()->getBaseUrl(),
                    'products' => $chunk,
                ]));
            } catch (\Exception $e) {
                $this->logger->error('[OmniChatPlugin] Error send product webhook: ' . $e->getMessage());
            }
        }
    }
}
