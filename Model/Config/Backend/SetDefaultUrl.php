<?php

namespace Vendor\OmniChat\Model\Config\Backend;

use Magento\Framework\App\Config\Value;

class SetDefaultUrl extends Value
{
    const DEFAULT_URL = 'https://magento-api.omni.chat';

    public function afterLoad()
    {
        if (!$this->getValue()) {
            $this->setValue(self::DEFAULT_URL);
        }
    }
}
