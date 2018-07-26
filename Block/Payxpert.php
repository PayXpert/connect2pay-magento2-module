<?php
namespace Payxpert\Connect2Pay\Block;

use Magento\Customer\Model\Context;
use Magento\Sales\Model\Order;

class Payxpert extends \Magento\Framework\View\Element\Template
{

    protected $checkoutSession;
    protected $customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Payxpert\Connect2Pay\Model\Payment\Payxpert $paymentModel,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->paymentModel = $paymentModel;
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
        $order = $this->checkoutSession->getLastRealOrder();

        $this->addData(
            ['iframe' => $this->paymentModel->startTransaction($order)]
        );

        return true;
    }
}
