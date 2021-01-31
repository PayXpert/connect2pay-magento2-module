<?php

namespace Payxpert\Connect2Pay\Plugin;

use Closure;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\CsrfValidator;
use Magento\Framework\App\RequestInterface;

class CsrfValidatorSkip
{
    /**
     * @param CsrfValidator $subject
     * @param Closure $proceed
     * @param RequestInterface $request
     * @param ActionInterface $action
     */
    public function aroundValidate(
        $subject,
        Closure $proceed,
        RequestInterface $request,
        ActionInterface $action
    ) {
        if ($request->getModuleName() == 'payxpert') {
            return; // Skip CSRF check
        }
        $proceed($request, $action); // Proceed Magento 2 core functionalities
    }
}
