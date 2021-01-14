<?php

namespace Payxpert\Connect2Pay\Block;

use Magento\Customer\Model\Context;
use Magento\Sales\Model\Order;
use Payxpert\Connect2Pay\Model\Payment\PayxpertToken;
use Payxpert\Connect2Pay\Model\ConfigGateway;
use Payxpert\Connect2Pay\Helper\Data as PayxpertHelper;

class Seamless extends \Magento\Framework\View\Element\Template
{

    protected $checkoutSession;
    protected $customerSession;
    protected $payxpertToken;
    protected $helper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        PayxpertToken $_payxpertToken,
        PayxpertHelper $_helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->helper = $_helper;
        $this->customerSession = $customerSession;
        $this->payxpertToken = $_payxpertToken;
    }

    /**
     * Render additional order information lines and return result html
     *
     * @return string
     */
    public function getAdditionalInfoHtml()
    {
        return $this->_layout->renderElement('order.success.additional.info');

    }

    /**
     * Initialize data and prepare it for output
     *
     * @return string
     */
    protected function _beforeToHtml()
    {
        $this->prepareBlockData();
        return parent::_beforeToHtml();
    }

    /**
     * Prepares block data
     *
     * @return void
     */
    protected function prepareBlockData()
    {
//        $order = $this->checkoutSession->getLastRealOrder();
//
//        $this->addData(
//            ['iframe' => $this->paymentModel->startTransaction($order)]
//        );
//        return null;
    }

    public function getCustomerTokenForBlock()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        return $this->payxpertToken->getCustomerToken($order);
    }

    /**
     * Prepares block data
     *
     * @return string
     */
    public function getPayxpertUrl()
    {
        $url = trim($this->helper->getConfig('payment/payxpert/url'));
        if (empty($url)) {
            $url = "https://connect2.payxpert.com";
        }
        return $url;
    }
}
