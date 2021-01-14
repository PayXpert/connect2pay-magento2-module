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

namespace Payxpert\Connect2Pay\Controller\Iframe;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Psr\Log\LoggerInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Checkout\Model\Session\SuccessValidator;

class Payxpert extends \Magento\Checkout\Controller\Onepage
{
    public function execute()
    {
        $session = $this->getOnepage()->getCheckout();
        if (!SuccessValidator::class) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $session->clearQuote();

        $resultPage = $this->resultPageFactory->create();
        $this->_eventManager->dispatch(
            'checkout_onepage_controller_success_action',
            ['order_ids' => [$session->getLastOrderId()]]
        );
        return $resultPage;
    }
}
