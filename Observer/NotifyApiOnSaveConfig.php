<?php

namespace Vendor\OmniChat\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\Client\Curl;

class NotifyApiOnSaveConfig implements ObserverInterface
{
    protected $logger;
    protected $scopeConfig;
    protected $encryptor;
    protected $curl;

    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        Curl $curl
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->curl = $curl;
    }

    public function execute(Observer $observer)
    {
        try {
            $encryptedKey = $this->scopeConfig->getValue('vendor_omnichat/general/key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $encryptedToken = $this->scopeConfig->getValue('vendor_omnichat/general/token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $key = $this->encryptor->decrypt($encryptedKey);
            $token = $this->encryptor->decrypt($encryptedToken);

            $url = "https://magento-api.omni.chat/v1/plugin/configuration";
            $client = new \GuzzleHttp\Client();
            $client->request('POST', $url, [
                'headers' => [
                    'x-api-key' => $key,
                    'x-api-secret' => $token
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
