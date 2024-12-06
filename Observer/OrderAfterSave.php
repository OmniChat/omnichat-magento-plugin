<?php
namespace Vendor\OmniChat\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\HTTP\Client\Curl;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableProductResource;
use Magento\Catalog\Model\ProductRepository;
use Magento\Store\Model\StoreManagerInterface;

class OrderAfterSave implements ObserverInterface
{
    protected $orderRepository;
    protected $productRepository;
    protected $imageHelper;
    protected $storeManager;
    protected $configurableProductResource;
    protected $curl;
    protected $scopeConfig;
    protected $encryptor;
    protected $logger;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProductRepository $productRepository,
        ImageHelper $imageHelper,
        StoreManagerInterface $storeManager,
        ConfigurableProductResource $configurableProductResource,
        Curl $curl,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->storeManager = $storeManager;
        $this->configurableProductResource = $configurableProductResource;
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            $data = $this->getOrderData($order);

            $this->sendWebhook($data);

        } catch (\Exception $e) {
            $this->logger->error('[OmniChatPlugin] Error send order webhook' . $e->getMessage());
        }
    }

    protected function getOrderData($order)
    {
        $items = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $itemId = $item->getProductId();
            $product = $this->productRepository->getById($itemId);
            $productId = $product->getTypeId() === 'simple' ? $itemId : $this->getParentId($itemId);
            $imageUrl = $this->getProductImageUrl($product);

            $items[] = [
                'sku' => $item->getSku(),
                'name' => $item->getName(),
                'price' => $item->getPrice(),
                'quantity' => $item->getQtyOrdered(),
                'total' => $item->getRowTotal(),
                'item_id' => $itemId,
                'product_id' => $productId,
                'image_url' => $imageUrl
            ];
        }

        $shipments = $order->getShipmentsCollection();
        $shipmentData = [];
        foreach ($shipments as $shipment) {
            $tracks = [];
            foreach ($shipment->getAllTracks() as $track) {
                $tracks[] = [
                    'carrier_code' => $track->getCarrierCode(),
                    'title' => $track->getTitle(),
                    'tracking_number' => $track->getTrackNumber(),
                ];
            }

            $items = [];
            foreach ($shipment->getAllItems() as $item) {
                $items[] = [
                    'sku' => $item->getSku(),
                    'name' => $item->getName(),
                    'quantity' => $item->getQty(),
                ];
            }

            $shipmentData[] = [
                'shipment_id' => $shipment->getId(),
                'increment_id' => $shipment->getIncrementId(),
                'tracks' => $tracks,
                'items' => $items,
                'state' => $shipment->getState(),
                'status' => $shipment->getStatus()
            ];
        }

        return [
            'order_id' => $order->getId(),
            'quote_id' => $order->getQuoteId(),
            'increment_id' => $order->getIncrementId(),
            'coupon' => $order->getCouponCode(),
            'status' => $order->getStatus(),
            'state' => $order->getState(),
            'total' => $order->getGrandTotal(),
            'subtotal' => $order->getSubtotal(),
            'discount' => $order->getDiscountAmount(),
            'tax' => $order->getTaxAmount(),
            'shipping' => $order->getShippingAmount(),
            'total_paid' => $order->getTotalPaid(),
            'total_due' => $order->getTotalDue(),
            'created_at' => $order->getCreatedAt(),
            'customer' => [
                'id' => $order->getCustomerId(),
                'email' => $order->getCustomerEmail(),
                'firstname' => $order->getCustomerFirstname(),
                'lastname' => $order->getCustomerLastname(),
            ],
            'billing_address' => $this->formatAddress($order->getBillingAddress()),
            'shipping_address' => $this->formatAddress($order->getShippingAddress()),
            'payment_method' => $order->getPayment()->getMethod(),
            'items' => $items,
            'shipment_info' => $shipmentData,
            'type' => 'ORDER',
        ];
    }

    protected function getParentId($productId)
    {
        $parentIds = $this->configurableProductResource->getParentIdsByChild($productId);
        return !empty($parentIds) ? $parentIds[0] : null;
    }

    protected function getProductImageUrl($product)
    {
        try {
            $image = $product->getData('image');
            if (!$image || $image === 'no_selection') {
                return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/placeholder/image.jpg';
            }

            return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $image;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function formatAddress($address)
    {
        return [
            'firstname' => $address->getFirstname(),
            'lastname' => $address->getLastname(),
            'street' => implode(' ', $address->getStreet()),
            'city' => $address->getCity(),
            'postcode' => $address->getPostcode(),
            'country' => $address->getCountryId(),
            'region' => $address->getRegion(),
            'country_id' => $address->getCountryId(),
            'telephone' => $address->getTelephone(),
        ];
    }

    protected function sendWebhook(array $data)
    {
        $url = $this->scopeConfig->getValue('vendor_omnichat/general/webhook_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $encryptedKey = $this->scopeConfig->getValue('vendor_omnichat/general/key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $encryptedToken = $this->scopeConfig->getValue('vendor_omnichat/general/token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $key = $this->encryptor->decrypt($encryptedKey);
        $token = $this->encryptor->decrypt($encryptedToken);

        $this->curl->setOption(CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-api-key: ' . $key,
            'x-api-secret: ' . $token
        ]);
        $this->curl->post(rtrim($url, '/') . '/v1/webhooks', json_encode($data));
    }
}
