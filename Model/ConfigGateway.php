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

namespace PayXpert\Connect2Pay\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;

use PayXpert\Connect2Pay\Model\Payment\Payxpert;
use Magento\Checkout\Model\ConfigProviderInterface;

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
        $active = $this->_config->getValue('payment/payxpert/active');
        $originatorId = $this->_config->getValue('payment/payxpert/originator');
        $password = $this->_config->getValue('payment/payxpert/password');
        $url = $this->_config->getValue('payment/payxpert/url');
        $apiUrl = $this->_config->getValue('payment/payxpert/api_url');

        if (!$active) {
            return [];
        }


        $config = [
            'payment' => [
                $this->methodCode => [
                    'originator'  => $originatorId,
                    'password'    => $password,
                    'url'         => $url,
                    'apiUrl'      =>  $apiUrl
                ],
            ],
        ];

        return $config;
    }
}