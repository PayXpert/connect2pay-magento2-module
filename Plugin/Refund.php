<?php

declare(strict_types=1);

namespace Payxpert\Connect2Pay\Plugin;

use Magento\Sales\Api\Data\CreditmemoInterface;
//use Payxpert\Connect2Pay\Model\Service\CreditmemoService;
//use SpellPayment\Magento2Module\Model\Method\Checkout;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Payxpert\Connect2Pay\Model\ConfigGateway;

/**
 * Process online refunds
 */
class Refund
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Closure $proceed
     * @param CreditmemoInterface $creditmemo
     * @param $offlineRequested
     * @return CreditmemoInterface
     * @throws \Exception
     */
    public function aroundRefund(
//        CreditmemoService $subject,
        \Closure $proceed,
        CreditmemoInterface $creditmemo,
        $offlineRequested
    ) {
        /** @var CreditmemoInterface $result */
        $result = $proceed($creditmemo, $offlineRequested);
        $order = $result->getOrder();
        $payment = $order->getPayment();
        $methodCode = $payment->getMethod();
        $this->logger->debug('Credit memo transaction ID', [$methodCode, $payment->getRefundTransactionId()]);

        if (!$offlineRequested && $methodCode == 'payxpert') {
            $transactionId = $payment->getRefundTransactionId();
            $amount = $creditmemo->getGrandTotal();

            $params = [
                'amount' => round($amount * 100),
            ];
            $this->logger->debug('Credit memo transaction ID', [$transactionId, $params['amount']]);

        }

        return $result;
    }

}
