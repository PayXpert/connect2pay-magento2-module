<?php
/**
 * Copyright 2016 PayXpert
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

namespace Payxpert\Connect2Pay\Controller\Checkout;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Payment\Helper\Data as PaymentHelper;
use Payxpert\Connect2Pay\Model\Payment\Payxpert as PayxpertModel;
use Payxpert\Connect2Pay\Helper\Data as PayxpertHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Psr\Log\LoggerInterface;

use Magento\Payment\Model\Method\Logger;
use Magento\Framework\App\Action\Action;
use PayXpert\Connect2Pay\Connect2PayClient;
use Magento\Framework\Controller\ResultFactory;

class Callback extends Action
{

    protected $checkoutSession;
    protected $customerSession;
    protected $paymentHelper;
    protected $payxpertModel;
    protected $payxpertHelper;
    protected $order;
    protected $invoiceSender;
    protected $logger;
    protected $resultFactory;

    /**
     * Callback constructor.
     *
     * @param Context $context
     * @param Session $checkoutSession
     * @param CustomerSession $customerSession
     * @param PaymentHelper $paymentHelper
     * @param PayxpertModel $payxpertModel
     * @param PayxpertHelper $payxpertHelper
     * @param Order $order
     * @param InvoiceSender $invoiceSender
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
        InvoiceSender $invoiceSender,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->paymentHelper = $paymentHelper;
        $this->payxpertModel = $payxpertModel;
        $this->payxpertHelper = $payxpertHelper;
        $this->order = $order;
        $this->invoiceSender = $invoiceSender;
        $this->logger = $logger;
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
    }

    /**
     * Callback from PayXpert after payment
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $this->logger->debug("Params Callback", $params);

        $c2pClient = new Connect2PayClient(
            $this->payxpertModel->getUrl(),
            $this->payxpertHelper->getConfig('payment/payxpert/originator'),
            $this->payxpertHelper->getConfig('payment/payxpert/password')
        );

        if ($c2pClient->handleCallbackStatus()) {

            // Get the Error code
            $status = $c2pClient->getStatus();
            $merchantToken = $status->getMerchantToken();
            $this->customerSession->setMerchatTokenInSession($merchantToken);
            $paymentStatus = $c2pClient->getPaymentStatus($merchantToken);

            $errorCode = $status->getErrorCode();
            // Custom data that could have been provided in ctrlCustomData when creating
            $merchantData = $status->getCtrlCustomData();

            $transaction = $paymentStatus->getLastTransactionAttempt();

            $currency = $status->getCurrency();

            $amount = $status->getAmount() / 100;

            $orderId = $status->getOrderID();

            $order = $this->order->load($orderId);

            if (true) {
                $this->logger->debug("Callback MD5: ", $merchantData);

                if ($order != null) {
                    $this->logger->debug("OrderID: " . $orderId);

                    $log = "Received a new transaction status from " .
                        $_SERVER["REMOTE_ADDR"] .
                        ". Merchant token: " . $merchantToken .
                        ", Status: " . $status->getStatus() .
                        ", Error code: " . $errorCode;
                    if ($errorCode >= 0) {
                        $log .= ", Error message: " . $status->getErrorMessage();
                        $log .= ", Order ID: " . $orderId;
                        $log .= ", Transaction ID: " . $transaction->getTransactionID();
                    }

                    $this->logger->notice($log);

                    if ($errorCode == '000') {
                        $this->logger->debug("Error code: " . $errorCode);

                        $orderStatus = $order::STATE_COMPLETE;
                        $payment = $order->getPayment();

                        $payment->setTransactionId($transaction->getTransactionID())
                            ->setLastTransId($transaction->getTransactionID())
                            ->setCurrencyCode($currency)
                            ->setIsTransactionClosed(true)
                            ->setStatus($orderStatus)
                            ->registerCaptureNotification($amount, true);
                        $order->setState($orderStatus);
                        $order->addStatusHistoryComment($log, $orderStatus);
                        $order->save();

                        $invoice = $payment->getCreatedInvoice();

                        if ($invoice && !$order->getEmailSent()) {

                            $this->invoiceSender->send($invoice);
                            $message = 'Notified customer about invoice #' . $invoice->getIncrementId();
                            $order->addStatusToHistory($orderStatus, $message, true)->save();

                        }

                        // Send back a response to mark this transaction as notified on the payment
                        // page
                        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                        $result->setData(["status" => "OK", "message" => "Status recorded"]);
                        return $result;
                    }
                } else {
                    $this->logger->critical(
                        "Error. No order found for token " . $merchantToken . " in callback from " .
                        $_SERVER["REMOTE_ADDR"] . "."
                    );
                }
            } else {
                $this->logger->critical(
                    "Error. invalid token " . $merchantToken . " in callback from " . $_SERVER["REMOTE_ADDR"] . "."
                );
            }

        } else {
            $this->logger->critical("Error. Received an incorrect status from " . $_SERVER["REMOTE_ADDR"] . ".");
        }
    }
}
