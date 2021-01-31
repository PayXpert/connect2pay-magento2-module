<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Payxpert\Connect2Pay\Plugin\Backend;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use PayXpert\Connect2Pay\Connect2PayClient;
use Magento\Sales\Model\Order;
use Payxpert\Connect2Pay\Helper\Data as PayxpertHelper;
use Payxpert\Connect2Pay\Model\Payment\Payxpert as PayxpertModel;

class CreditmemoRepositoryInterface
{
    protected $logger;
    protected $orderRepository;
    protected $connect2payClient;
    protected $order;
    protected $payxpertHelper;
    protected $payxpertModel;

    public function __construct(
        LoggerInterface $_logger,
        OrderRepositoryInterface $orderRepository,
        Order $_order,
        PayxpertHelper $_payxpertHelper,
        PayxpertModel $_payxpertModel
    ) {
        $this->logger = $_logger;
        $this->orderRepository = $orderRepository;
        $this->order = $_order;
        $this->payxpertHelper = $_payxpertHelper;
        $this->payxpertModel = $_payxpertModel;
    }
    public function beforeSave(
        \Magento\Sales\Api\CreditmemoRepositoryInterface $subject,
        CreditmemoInterface $entity
    ) {
        $orderId = $entity->getOrderId();
        $this->logger->debug('beforeSave Orderid: '. $orderId);
        $order = $this->orderRepository->get($orderId);
        $payment = $order->getPayment();
        $methodCode = $payment->getMethod();
        $this->logger->debug('beforeSave methodCode: '. $methodCode);

        $transactionId = $entity->getTransactionId();
        $this->logger->debug('beforeSave TransactionID: '. $transactionId);
        $this->logger->debug('beforeSave Grand total: '. $entity->getGrandTotal());
        $this->logger->debug('beforeSave amount: '. round($entity->getGrandTotal() * 100));

        if (isset($transactionId) && $methodCode == 'payxpert') {
            $transactionId = str_replace("-refund", "", $transactionId);
            $this->logger->debug('beforeSave TransactionID Correct: '. $transactionId);

            $amount = (int) round($entity->getGrandTotal() * 100);

            $c2pClient = new Connect2PayClient(
                $this->payxpertModel->getUrl(),
                $this->payxpertHelper->getConfig('payment/payxpert/originator'),
                $this->payxpertHelper->getConfig('payment/payxpert/password')
            );
            $status = $c2pClient->refundTransaction($transactionId, $amount);

            if ($status != null && $status->getCode() != null) {
                $comments = "Refund result:<br />";
                $comments .= "~ Error code: " . $status->getCode() . "<br />";
                $comments .=  "~ Error message: " . $status->getMessage() . "<br />";
                $comments .=  "~ Transaction ID: " . $status->getTransactionID() . "<br />";
                $comments .=  "~ Operation: " . $status->getOperation() . "<br />";
                $order->addCommentToStatusHistory($comments, false, false);
                $this->orderRepository->save($order);

            } else {
                $this->logger->error("Payxpert refund error: " . $c2pClient->getClientErrorMessage());
            }
        }
    }
}
