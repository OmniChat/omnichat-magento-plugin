<?php

namespace Vendor\OmniChat\Plugin;

use Magento\Quote\Model\QuoteFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Session\SessionManagerInterface;

class CartLoaderPlugin
{
    protected $quoteFactory;
    protected $checkoutSession;
    protected $context;
    protected $sessionManager;

    public function __construct(
        QuoteFactory $quoteFactory,
        CheckoutSession $checkoutSession,
        Context $context,
        SessionManagerInterface $sessionManager
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->checkoutSession = $checkoutSession;
        $this->context = $context;
        $this->sessionManager = $sessionManager;
    }

    public function beforeExecute(\Magento\Checkout\Controller\Cart\Index $subject)
    {
        $request = $this->context->getRequest();
        $cartId = $request->getParam('cart_id');

        if ($cartId) {
            $quote = $this->quoteFactory->create()->load($cartId);
            if ($quote->getId()) {
                $this->checkoutSession->clearStorage();
                $this->checkoutSession->replaceQuote($quote);
                $this->checkoutSession->setQuoteId($quote->getId());
            }
        }
    }
}
