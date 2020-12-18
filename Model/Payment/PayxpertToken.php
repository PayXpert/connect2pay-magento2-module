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

namespace Payxpert\Connect2Pay\Model\Payment;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Escaper;
use Payxpert\Connect2Pay\Helper\Data as PayxpertHelper;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

use Magento\Payment\Model\Method\AbstractMethod;
use PayXpert\Connect2Pay\Connect2PayClient;

class PayxpertToken extends AbstractMethod
{
    const PAYMENT_METHOD_CODE = 'payxpert';

    protected $_code = self::PAYMENT_METHOD_CODE;
    protected $_isGateway = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;

    protected $checkoutSession;
    protected $order;
    protected $urlBuilder;
    protected $customerSession;
    protected $escaper;
    protected $helper;

    /**
     * PayXpert constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param StoreManagerInterface $storeManager
     * @param Session $checkoutSession
     * @param Order $order
     * @param CustomerSession $customerSession
     * @param Escaper $escaper
     * @param PayxpertHelper $helper
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        PaymentHelper $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        ObjectManagerInterface $objectManager,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        Session $checkoutSession,
        Order $order,
        CustomerSession $customerSession,
        Escaper $escaper,
        PayxpertHelper $helper,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->urlBuilder = $urlBuilder;
        $this->checkoutSession = $checkoutSession;
        $this->order = $order;
        $this->customerSession = $customerSession;
        $this->escaper = $escaper;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    public function getCustomerToken(\Magento\Sales\Model\Order $order)
    {
        $this->logger->debug(['Token token']);
        $order->getCustomerFirstname();
        $payment = $order->getPayment();
        $method = $order->getPayment()->getMethod();

        $originator = $this->helper->getConfig('payment/payxpert/originator');
        $password = $this->helper->getConfig('payment/payxpert/password');
        $url = $this->helper->getConfig('payment/payxpert/url');
        $api_url = $this->helper->getConfig('payment/payxpert/api_url');

        $c2pClient = new Connect2PayClient($this->getUrl(), $originator, $password);

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        $c2pClient->setOrderID($order->getId());
        // $c2pClient->setCustomerIP( $fields['customerIP'] );
        $c2pClient->setPaymentMethod(Connect2PayClient::PAYMENT_METHOD_CREDITCARD);
        $c2pClient->setPaymentMode(Connect2PayClient::PAYMENT_MODE_SINGLE);
        $c2pClient->setShopperID($order->getCustomerID());
        $c2pClient->setShippingType(Connect2PayClient::SHIPPING_TYPE_VIRTUAL);
        $c2pClient->setAmount($order->getGrandTotal() * 100);

        $c2pClient->setCurrency($order->getOrderCurrency()->getCurrencyCode());
        $c2pClient->setCtrlCallbackURL($this->urlBuilder->getUrl('payxpert/checkout/callback/'));

        if ($c2pClient->validate()) {
            if ($c2pClient->preparePayment()) {
                $customerToken = $c2pClient->getCustomerToken();
                $this->customerSession->setCustomerToken($customerToken);
                $_SESSION['customerToken'] = $customerToken;
                return $customerToken;

            } else {
                $message = "Customer token preparation error occured: " .
                    $this->escaper->escapeHtml($c2pClient->getClientErrorMessage());

                throw new \Magento\Framework\Validator\Exception(__($message));
            }
        } else {
            $message = "Validation error occured: " . $this->escaper->escapeHtml($c2pClient->getClientErrorMessage());
            throw new \Magento\Framework\Validator\Exception(__($message));
        }
    }

    public function getUrl()
    {
        $url = trim($this->helper->getConfig('payment/payxpert/url'));
        if (empty($url)) {
            $url = "https://connect2.payxpert.com";
        }
        return $url;
    }
}
