<?php

namespace Vendor\OmniChat\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\App\ProductMetadataInterface;

class NotifyApiOnSaveConfig implements ObserverInterface
{
    protected $logger;
    protected $scopeConfig;
    protected $encryptor;
    protected $curl;
    protected $moduleList;
    protected $productMetadata;

    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        Curl $curl,
        ModuleListInterface $moduleList,
        ProductMetadataInterface $productMetadata
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->curl = $curl;
        $this->moduleList = $moduleList;
        $this->productMetadata = $productMetadata;
    }

    public function execute(Observer $observer)
    {
        try {
            $encryptedKey = $this->scopeConfig->getValue('vendor_omnichat/general/key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $encryptedToken = $this->scopeConfig->getValue('vendor_omnichat/general/token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $key = $this->encryptor->decrypt($encryptedKey);
            $token = $this->encryptor->decrypt($encryptedToken);

            $url = $this->scopeConfig->getValue('vendor_omnichat/general/webhook_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $client = new \GuzzleHttp\Client();
            $client->request('POST', rtrim($url, '/') . '/v1/plugin/configuration', [
                'headers' => [
                    'x-api-key' => $key,
                    'x-api-secret' => $token
                ],
                'json' => [
                    'magento_version' => $this->productMetadata->getVersion(),
                    'magento_edition' => $this->productMetadata->getEdition(),
                    'omnichat_plugin_version' => $this->moduleList->getOne('Vendor_OmniChat')['setup_version'] ?? 'unknown'
                ]
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->logger->error('[OmniChatPlugin - ClientException] Error send save config webhook: ' . $e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Invalid credentials')
            );
        } catch (\Exception $e) {
            $this->logger->error('[OmniChatPlugin - Exception] Error send save config webhook: ' . $e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Error send save config webhook: ' . $e->getMessage())
            );
        }
    }
}
