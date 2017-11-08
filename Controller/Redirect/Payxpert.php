<?php
/**
 Copyright 2016 PayXpert

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License. 
 */

namespace PayXpert\Connect2Pay\Controller\Redirect;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Psr\Log\LoggerInterface;
use Magento\Payment\Helper\Data as PaymentHelper;

class Payxpert extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    /**
     * @var Data
     */
    protected $_data;

    /**
     * Redirect construtor
     *
     * @param Context         $context
     * @param Session         $checkoutSession
     * @param LoggerInterface $logger
     * @param PaymentHelper   $paymentHelper
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        LoggerInterface $logger,
        PaymentHelper $paymentHelper
    )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->_logger = $logger;
        $this->_paymentHelper = $paymentHelper;
        parent::__construct($context);
    }
    public function execute()
    {
        try {
            $order = $this->_getCheckoutSession()->getLastRealOrder();

            if($order)
            {
                $method = $order->getPayment()->getMethod();
                $methodInstance = $this->_paymentHelper->getMethodInstance($method);
            }

            if ($methodInstance instanceof \PayXpert\Connect2Pay\Model\Payment\Payxpert) {
                $storeId = $order->getStoreId();
                $redirectUrl = $methodInstance->startTransaction($order);
                $this->_redirect($redirectUrl);

            } else {
                 throw new \Magento\Framework\Validator\Exception(__('Method is not PayXpert'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __($e->getMessage()));
            $this->_logger->critical($e);
            $this->_getCheckoutSession()->restoreQuote();
            $this->_redirect('checkout/cart');
        }
    }
    /**
     * Return checkout session object
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }
}