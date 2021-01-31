<?php
/**
 Copyright 2021 PayXpert

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

use Magento\Framework\View\Asset\Repository;
use Payxpert\Connect2Pay\Model\Payment\Payxpert;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigGateway implements ConfigProviderInterface
{
    /**
     * @var string[]
     */

    protected $_assetRepo;
    protected $methodCode = Payxpert::PAYMENT_METHOD_CODE;
    /**
     * @var ScopeConfigInterface
     */
    protected $_config;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface   $config
     * @param Repository $assetRepo
     */
    public function __construct(
        ScopeConfigInterface   $config,
        Repository $assetRepo
    ) {
        $this->_assetRepo = $assetRepo;
        $this->_config = $config;
    }

    /**
     * @return array|void
     */
    public function getConfig(): array
    {
        $active          = $this->_config->getValue('payment/payxpert/active', ScopeInterface::SCOPE_STORE);
        $originatorId    = $this->_config->getValue('payment/payxpert/originator', ScopeInterface::SCOPE_STORE);
        $password        = $this->_config->getValue('payment/payxpert/password', ScopeInterface::SCOPE_STORE);
        $url             = $this->_config->getValue('payment/payxpert/url', ScopeInterface::SCOPE_STORE);
        $apiUrl          = $this->_config->getValue('payment/payxpert/api_url', ScopeInterface::SCOPE_STORE);
        $ideal           = $this->_config->getValue('payment/payxpert/ideal', ScopeInterface::SCOPE_STORE);
        $weChat          = $this->_config->getValue('payment/payxpert/wechat', ScopeInterface::SCOPE_STORE);
        $giropay         = $this->_config->getValue('payment/payxpert/giropay', ScopeInterface::SCOPE_STORE);
        $sofort          = $this->_config->getValue('payment/payxpert/sofort', ScopeInterface::SCOPE_STORE);
        $aliPay          = $this->_config->getValue('payment/payxpert/alipay', ScopeInterface::SCOPE_STORE);
        $przelewy24      = $this->_config->getValue('payment/payxpert/przelewy24', ScopeInterface::SCOPE_STORE);

        $aliPayImageUrl        = $this->_assetRepo->getUrl("Payxpert_Connect2Pay::images/alipay.png");
        $weChatImageUrl        = $this->_assetRepo->getUrl("Payxpert_Connect2Pay::images/wechat.png");
        $creditCardPayImageUrl = $this->_assetRepo->getUrl("Payxpert_Connect2Pay::images/creditcard.png");
        $giroPayImageUrl       = $this->_assetRepo->getUrl("Payxpert_Connect2Pay::images/giropay.png");
        $idealImageUrl         = $this->_assetRepo->getUrl("Payxpert_Connect2Pay::images/ideal.png");
        $przelewy24ImageUrl    = $this->_assetRepo->getUrl("Payxpert_Connect2Pay::images/przelewy24.png");
        $sofortImageUrl        = $this->_assetRepo->getUrl("Payxpert_Connect2Pay::images/sofort.png");

        if (!$active) {
            return [];
        }

        return [
            'payment' => [
                $this->methodCode => [
                    'originator'      => $originatorId,
                    'password'        => $password,
                    'url'             => $url,
                    'apiUrl'          => $apiUrl,
                    'ideal'           => $ideal,
                    'giropay'         => $giropay,
                    'sofort'          => $sofort,
                    'weChat'          => $weChat,
                    'przelewy24'      => $przelewy24,
                    'alipay'          => $aliPay,

                    'aliPayImageUrl'     => $aliPayImageUrl,
                    'weChatImageUrl'     => $weChatImageUrl,
                    'giroPayImageUrl'    => $giroPayImageUrl,
                    'idealImageUrl'      => $idealImageUrl,
                    'przelewy24ImageUrl' => $przelewy24ImageUrl,
                    'creditCardPayImageUrl' => $creditCardPayImageUrl,
                    'sofortImageUrl'     => $sofortImageUrl,

                ],
            ],
        ];
    }
}
