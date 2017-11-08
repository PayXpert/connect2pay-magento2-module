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

namespace PayXpert\Connect2Pay\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{	
  public function getConfig($config_path)
  {
    return $this->scopeConfig->getValue(
      $config_path,
      \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    );
  }
}