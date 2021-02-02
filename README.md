# Magento 2 PayXpert payment module
### Version for Magento 2.x

PLEASE NOTE: THIS VERSION IS NOT COMPATIBLE WITH MAGENTO 1.x.

The author of this plugin can NEVER be held responsible for this software.
There is no warranty what so ever. You accept this by using this software.

## Changelog
* 1.2.1 - Alipay disable fix. Credit Card configuration added.
* 1.2.0 - Added payment method selection and online refund. Iframe removed.
* 1.1.3 - Removed deprecated methods
* 1.1.2 - Removed PHP version requirement
* 1.1.1 - PSR-4
* 1.1.0 - Iframe support
* 1.0.1 - Fixed callback response
* 1.0.0 - Initial Release

## Installation

### Manually

1. Go to Magento® 2 root folder

2. Enter following commands to install module:

   ```
   composer require payxpert/connect2pay-magento2-module
   ```

   Wait while dependencies are updated.

3. Enter following commands to enable module:

   ```
   php bin/magento module:enable Payxpert_Connect2Pay
   php bin/magento setup:upgrade
   php bin/magento setup:di:compile
   php bin/magento setup:static-content:deploy 
   ```

4. If Magento® is running in production mode, deploy static content: 

   ```
   php bin/magento setup:static-content:deploy
   ```

5. Configure the PayXpert extension in Magento® Admin under *Stores* >
   *Configuration* > *Sales* > *Payment Methods* > *PayXpert*.


## Support
Please visit the PayXpert website (http://www.payxpert.com) for our support contact details.

## Requirements

1) For Magento® 2.2.x

2) This extension requires [Connect2Pay PHP Client.](https://github.com/PayXpert/connect2pay-php-client)

When using composer or installation through the Magento® Marketplace this will be installed automatically.

To install manually, enter the following command in your Magento® 2 root folder:
```
composer require payxpert/connect2pay
```
