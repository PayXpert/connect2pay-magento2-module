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

namespace Payxpert\Connect2Pay\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;

use Payxpert\Connect2Pay\Model\Payment\Payxpert;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigGateway implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCode = Payxpert::PAYMENT_METHOD_CODE;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface   $config
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ScopeConfigInterface   $config,
        ObjectManagerInterface $objectManager
    ) {
        $this->_config = $config;
        $this->_objectManager = $objectManager;
    }

    /**
     * @return array|void
     */
    public function getConfig()
    {
        $active          = $this->_config->getValue('payment/payxpert/active', ScopeInterface::SCOPE_STORE);
        $originatorId    = $this->_config->getValue('payment/payxpert/originator', ScopeInterface::SCOPE_STORE);
        $password        = $this->_config->getValue('payment/payxpert/password', ScopeInterface::SCOPE_STORE);
        $url             = $this->_config->getValue('payment/payxpert/url', ScopeInterface::SCOPE_STORE);
        $apiUrl          = $this->_config->getValue('payment/payxpert/api_url', ScopeInterface::SCOPE_STORE);
        $iframe          = $this->_config->getValue('payment/payxpert/iframe', ScopeInterface::SCOPE_STORE);
        $seamlessPayment = $this->_config->getValue('payment/payxpert/seamless_payment', ScopeInterface::SCOPE_STORE);
        $toditoCash      = $this->_config->getValue('payment/payxpert/todito_cash', ScopeInterface::SCOPE_STORE);
        $bankTransfer    = $this->_config->getValue('payment/payxpert/bank_transfer', ScopeInterface::SCOPE_STORE);
        $directDebit     = $this->_config->getValue('payment/payxpert/direct_debit', ScopeInterface::SCOPE_STORE);
        $weChat          = $this->_config->getValue('payment/payxpert/wechat', ScopeInterface::SCOPE_STORE);
        $line            = $this->_config->getValue('payment/payxpert/line', ScopeInterface::SCOPE_STORE);
        $aliPay          = $this->_config->getValue('payment/payxpert/alipay', ScopeInterface::SCOPE_STORE);

        if (!$active) {
            return [];
        }

        $config = [
            'payment' => [
                $this->methodCode => [
                    'originator'      => $originatorId,
                    'password'        => $password,
                    'url'             => $url,
                    'apiUrl'          => $apiUrl,
                    'iframe'          => $iframe,
                    'seamlessPayment' => $seamlessPayment,
                    'toditoCash'      => $toditoCash,
                    'bankTransfer'    => $bankTransfer,
                    'directDebit'     => $directDebit,
                    'weChat'          => $weChat,
                    'line'            => $line,
                    'aliPay'          => $aliPay
                ],
            ],
        ];

        return $config;
    }
}
