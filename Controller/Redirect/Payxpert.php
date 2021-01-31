<?php
/**
 * Copyright 2021 PayXpert
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Payxpert\Connect2Pay\Controller\Redirect;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Framework\App\RequestInterface;

class Payxpert extends \Magento\Framework\App\Action\Action
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;
    /**
     * @var Session
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
    protected $_request;
    protected $_bankTransferPaymentNetworks;
    /**
     * @var PaymentHelper
     */
    private $_paymentHelper;

    /**
     * Redirect construtor
     *
     * @param RequestInterface $request
     * @param Context $context
     * @param Session $checkoutSession
     * @param LoggerInterface $logger
     * @param PaymentHelper $paymentHelper
     * @param array $bankTransferPaymentNetworks
     */
    public function __construct(
        RequestInterface $request,
        Context $context,
        Session $checkoutSession,
        LoggerInterface $logger,
        PaymentHelper $paymentHelper,
        array $bankTransferPaymentNetworks = []
    ) {
        $this->_bankTransferPaymentNetworks = $bankTransferPaymentNetworks;
        $this->_request = $request;
        $this->_checkoutSession = $checkoutSession;
        $this->_logger = $logger;
        $this->_paymentHelper = $paymentHelper;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $order = $this->_getCheckoutSession()->getLastRealOrder();
            $methodInstance = (object) [];
            if ($order) {
                $method = $order->getPayment()->getMethod();
                $methodInstance = $this->_paymentHelper->getMethodInstance($method);
            }

            if ($methodInstance instanceof \Payxpert\Connect2Pay\Model\Payment\Payxpert) {
                $params = $this->_request->getParams();

                // Getting array from @file fontend/di.xml
                $bankTransferPaymentNetworksDi = $this->_bankTransferPaymentNetworks;

                $paymentMethod = $params['paymentMethod'];
                $paymentNetwork = '';

                foreach ($bankTransferPaymentNetworksDi as $paymentNetwork) {
                    if ($paymentMethod == $paymentNetwork) {
                        $paymentMethod = 'BankTransfer';
                        break;
                    } else {
                        $paymentNetwork = false;
                    }
                }
                
                $storeId = $order->getStoreId();
                $this->_logger->debug("Payments", [$paymentMethod, $paymentNetwork]);
                try {
                    $redirectUrl = $methodInstance->startTransaction($order, $paymentMethod, $paymentNetwork);
                } catch (\Exception $exception) {
                    throw new \Magento\Framework\Validator\Exception(
                        __('Payment network is not set. Probably dependency injection not compiled')
                    );
                }
                $this->_redirect($redirectUrl);

            } else {
                throw new \Magento\Framework\Validator\Exception(__('Method is not PayXpert'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
            $this->_logger->critical($e);
            $this->_getCheckoutSession()->restoreQuote();
            $this->_redirect('checkout/cart');
        }
    }

    /**
     * Return checkout session object
     *
     * @return Session
     */
    protected function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }
}
