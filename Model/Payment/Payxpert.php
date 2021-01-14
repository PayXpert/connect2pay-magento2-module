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

use Magento\Variable\Model\VariableFactory;
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

class Payxpert extends AbstractMethod
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
    protected $variable;

    /**
     * PayXpert constructor.
     *
     * @param VariableFactory $_variable
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param PaymentHelper $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param ObjectManagerInterface $objectManager
     * @param UrlInterface $urlBuilder
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
        VariableFactory $_variable,
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
        $this->variable = $_variable;
        $this->urlBuilder = $urlBuilder;
        $this->checkoutSession = $checkoutSession;
        $this->order = $order;
        $this->customerSession = $customerSession;
        $this->escaper = $escaper;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    public function startTransaction(
        Order $order,
        $paymentMethod = "CreditCard",
        $paymentNetwork = false
    ) {
        $order->getCustomerFirstname();
        $payment = $order->getPayment();
        $method = $order->getPayment()->getMethod();

        $originator = $this->helper->getConfig('payment/payxpert/originator');
        $password = $this->helper->getConfig('payment/payxpert/password');
        $url = $this->helper->getConfig('payment/payxpert/url');
        $api_url = $this->helper->getConfig('payment/payxpert/api_url');
        $url2 = $this->getUrl();

        $c2pClient = new Connect2PayClient($this->getUrl(), $originator, $password);

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        $c2pClient->setOrderID($order->getId());
        // $c2pClient->setCustomerIP( $fields['customerIP'] );
        $c2pClient->setPaymentMethod($paymentMethod);
        if ($paymentNetwork) {
            $c2pClient->setPaymentNetwork($paymentNetwork);
        }

        $this->logger->debug([$url2, 'url']);

        $c2pClient->setPaymentMode(Connect2PayClient::PAYMENT_MODE_SINGLE);
        $c2pClient->setShopperID($order->getCustomerID());
        $c2pClient->setShippingType(Connect2PayClient::SHIPPING_TYPE_VIRTUAL);
        $c2pClient->setAmount($order->getGrandTotal() * 100);
        $c2pClient->setOrderDescription("Order #" . $order->getId());
        $c2pClient->setCurrency($order->getOrderCurrency()->getCurrencyCode());

        $c2pClient->setShopperFirstName($order->getCustomerFirstname());
        $c2pClient->setShopperLastName($order->getCustomerLastname());
        $c2pClient->setShopperAddress($billingAddress->getStreetLine(1));
        $c2pClient->setShopperZipcode($billingAddress->getPostcode());
        $c2pClient->setShopperCity($billingAddress->getCity());
        $c2pClient->setShopperState($billingAddress->getRegion());
        $c2pClient->setShopperCountryCode($billingAddress->getCountryId());
        $c2pClient->setShopperPhone($billingAddress->getTelephone());
        $c2pClient->setShopperEmail($order->getCustomerEmail() ?: $billingAddress->getEmail());

        if ($shippingAddress) {
            $c2pClient->setShipToFirstName($shippingAddress->getCustomerFirstname());
            $c2pClient->setShipToLastName($shippingAddress->getCustomerLastname());
            $c2pClient->setShipToAddress($shippingAddress->getStreetLine(1));
            $c2pClient->setShipToZipcode($shippingAddress->getPostcode());
            $c2pClient->setShipToCity($shippingAddress->getCity());
            $c2pClient->setShipToState($shippingAddress->getRegion());
            $c2pClient->setShipToCountryCode($shippingAddress->getCountryId());
            $c2pClient->setShipToPhone($shippingAddress->getTelephone());
        }

        $c2pClient->setCtrlRedirectURL($this->urlBuilder->getUrl('payxpert/checkout/success/'));
        $c2pClient->setCtrlCallbackURL($this->urlBuilder->getUrl('payxpert/checkout/callback/'));

        $md5 = md5($order->getId() . $order->getGrandTotal() . $c2pClient->getPassword());

        $c2pClient->setCtrlCustomData($this->checkoutSession->getSessionId());

        if ($c2pClient->validate()) {
            if ($c2pClient->preparePayment()) {
//                $this->logger->cr($c2pClient->getMerchantToken());
//                $this->logger->debug("Params Success", $params);

                $this->customerSession->setMerchantToken($c2pClient->getMerchantToken());
                $_SESSION['merchantToken'] = $c2pClient->getMerchantToken();
                $customerToken = $c2pClient->getCustomerToken();
                $merchantToken = $c2pClient->getMerchantToken();

                $variable = $this->variable->create();
                $data = [
                    'code' => $customerToken,
                    'name' => 'Merchant token from customer token',
                    'html_value' => '',
                    'plain_value' => $merchantToken,

                ];
                $variable->setData($data);
                try {
                    $variable->save();
                } catch (\Exception $e) {
                    throw new \Magento\Framework\Validator\Exception();
                }

                $paymentUrl = $c2pClient->getCustomerRedirectURL();

            } else {
                $message = "Preparation error at " . $url2 . " occurred: " .
                    $this->escaper->escapeHtml($c2pClient->getClientErrorMessage());

                throw new \Magento\Framework\Validator\Exception(__($message));
            }
        } else {
            $message = "Validation error occurred: " . $this->escaper->escapeHtml($c2pClient->getClientErrorMessage());
            throw new \Magento\Framework\Validator\Exception(__($message));
        }

        return $paymentUrl;
    }

    public function getUrl()
    {
        $url = trim($this->helper->getConfig('payment/payxpert/url'));
        if (empty($url)) {
            $url = "https://connect2.payxpert.com";
        }
        return preg_replace("(^https?://)", "", $url);
    }
}
