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

namespace Payxpert\Connect2Pay\Controller\Checkout;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Payment\Helper\Data as PaymentHelper;
use Payxpert\Connect2Pay\Model\Payment\Payxpert as PayxpertModel;
use Payxpert\Connect2Pay\Helper\Data as PayxpertHelper;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Magento\Framework\Session\SessionManagerInterface;


use Magento\Framework\App\Action\Action;
use PayXpert\Connect2Pay\Connect2PayClient;

class Success extends Action
{

    protected $checkoutSession;
    protected $customerSession;
    protected $paymentHelper;
    protected $payxpertModel;
    protected $payxpertHelper;
    protected $order;
    protected $logger;

    /**
     * Success constructor.
     *
     * @param Context         $context
     * @param Session         $checkoutSession
     * @param CustomerSession $customerSession
     * @param PaymentHelper   $paymentHelper
     * @param PayxpertModel   $payxpertModel
     * @param PayxpertHelper  $payxpertHelper
     * @param Order           $order
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        CustomerSession $customerSession,
        PaymentHelper $paymentHelper,
        PayxpertModel $payxpertModel,
        PayxpertHelper $payxpertHelper,
        Order $order,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->paymentHelper = $paymentHelper;
        $this->payxpertModel = $payxpertModel;
        $this->payxpertHelper = $payxpertHelper;
        $this->order = $order;
        $this->logger = $logger;

        parent::__construct($context);
    }

    /**
     * Return from PayXpert after payment
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();

//        $merchantToken = $this->checkoutSession->getMerchatTokenInSession();
        $merchantToken = $this->customerSession->getMerchatTokenInSession();
        $merchantToken2 = $this->customerSession->getMerchantToken();
        $this->logger->debug("Success start");
        $this->logger->debug("Params Success", $params);
        $this->logger->debug("Merchant cusstomer Success token:" . $merchantToken);
        $this->logger->debug("Merchant cusstomer Success token:" . $merchantToken2);

        if ($merchantToken != null) {
            // Extract data received from the payment page
            $data = $params["data"];
            $this->logger->debug("Success 2nd");
            if ($data != null) {
                $this->logger->debug("Success 3rd");

                // Setup the client and decrypt the redirect Status
                $c2pClient = new Connect2PayClient(
                    $this->payxpertModel->getUrl(),
                    $this->payxpertHelper->getConfig('payment/Payxpert/originator'),
                    $this->payxpertHelper->getConfig('payment/Payxpert/password')
                );
                if ($c2pClient->handleRedirectStatus($data, $merchantToken)) {
                    // Get the Error code
                    $status = $c2pClient->getStatus();

                    $errorCode = $status->getErrorCode();
                    $merchantData = $status->getCtrlCustomData();
                    $orderId = $status->getOrderID();

                    $session = $this->checkoutSession;
                    $session->setQuoteId($orderId);
                    $session->getQuote()->setIsActive(false)->save();
                    // errorCode = 000 => payment is successful
                    if ($errorCode == '000') {
                        // Display the payment confirmation page
                        $this->checkoutSession->start();
                        $this->_redirect('checkout/onepage/success?utm_nooverride=1');
                        return;
                    } else {
                        // Display the cart page
                        if ($session->getLastRealOrderId()) {
                            $order = $this->order->loadByIncrementId($session->getLastRealOrderId());
                            if ($order->getId()) {
                                $order->cancel()->save();
                            }
                            $this->checkoutSession->restoreQuote();
                        }
                    }
                }
            } else {
                $this->messageManager->addNoticeMessage(__('Invalid return from PayXpert.'));
                $this->_redirect('checkout/cart');
            }
        }

        $this->_redirect('checkout/cart');
    }
}
