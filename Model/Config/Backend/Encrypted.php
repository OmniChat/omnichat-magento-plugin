<?php

namespace Vendor\OmniChat\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Encryption\EncryptorInterface;

class Encrypted extends Value
{
    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        EncryptorInterface $encryptor,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->encryptor = $encryptor;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function beforeSave()
    {
        $value = $this->getValue();
        if (!empty($value)) {
            $this->setValue($this->encryptor->encrypt($value));
        }
        return parent::beforeSave();
    }

    public function afterLoad()
    {
        $value = $this->getValue();
        if (!empty($value)) {
            $decryptedValue = $this->encryptor->decrypt($value);
            $maskedValue = str_repeat('*', max(strlen($decryptedValue) - 4, 0)) . substr($decryptedValue, -4);
            $this->setValue($maskedValue);
        }
        return parent::afterLoad();
    }
}
