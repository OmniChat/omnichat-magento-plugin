# OmniChat - Magento 2 Module

## Description

This Magento 2 module allows you to integrate your magento store with OmniChat 

---

## Features

- Allows cart access via token-based URL (e.g., `/cart/?cart_id=123`).
- Extends the default Magento cart page layout.
- Sending order updates to OmniChat.
- Automatic configuration of integration in OmniChat.
- Abandoned carts and Abandoned cart campaigns.
- Integration with OmniChat Whizz agent
---

## Installation Instructions

Follow these steps to install the module in your Magento 2 instance:

### Step 1: Download the module

You can install this module using **Composer**. Run the following command:

```bash
composer require omnichat/magento2
```

### Step 2: Enable the module

After the module is installed, run the following commands to enable it:

```bash
php bin/magento module:enable Vendor_OmniChat
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```

### Step 3: Clear Cache

Clear the Magento cache:

```bash
php bin/magento cache:clean
php bin/magento cache:flush
```

Now, the module should be successfully installed and activated.

---

### Folder Structure

Here is the folder structure of the module:

```text
app
└── code
    └── Vendor
        └── OmniChat
            ├── etc
            │   └── adminhtml
            │   │   └── routes.xml
            │   │   └── sytem.xml
            │   └── acl.xml
            │   └── config.xml
            │   └── di.xml
            │   └── events.xml
            │   └── module.xml
            ├── Model
            │   ├── Config
            │   │   └── Backend
            │   │       └── Encrypted.php
            ├── Observer
            │   └── NotifyApiOnSaveConfig.php 
            │   └── OrderAfterSave.php       
            ├── Plugin
            │   └── CartLoaderPlugin.php
            ├── view
            │   └── adminhtml
            │       └── layout
            │       │   └── adminhtml_system_config_edit.xml
            │       └── web
            │           └── css
            │           └── js
            └── registration.php
```
- `etc/adminhtml/routes.xml`: Defines the custom routes for the module.
- `etc/adminhtml/system.xml`: Defines the configuration options available in the admin panel.
- `acl.xml`: Contains permissions configuration settings for the module in admin panel.
- `config.xml`: Contains configuration settings for the module in admin panel.
- `di.xml`: The dependencies injection for the module.
- `events.xml`: Declares event observers for the module.
- `module.xml`: Declares the module in Magento.
- `Model/Config/Backend/Encrypted.php`: Contains the logic for encrypt and decrypt fields used in admin panel.
- `Observer/NotifyApiOnSaveConfig.php`: Contains the logic for handling events when save config in admin panel.
- `Observer/OrderAfterSave.php`: Contains the logic for handling events after save order.
- `Plugin/CartLoaderPlugin.php`: Contains the logic for managing cart access.
- `view/adminhtml/layout/adminhtml_system_config_edit.xml`: Defines the layout for the admin panel frontend.
- `view/web/css`: Contains custom css for module in admin panel.
- `view/web/js`: Contains custom js for module in admin panel.
---

## Uninstallation

To remove the module from your Magento 2 instance:

1. Disable the module:
   ```bash
   php bin/magento module:disable Vendor_OmniChat
   ```

2. Remove the module using Composer:
   ```bash
   composer remove omnichat/magento2
   ```

3. Run the following commands to complete the removal:
   ```bash
   php bin/magento setup:upgrade
   php bin/magento setup:di:compile
   php bin/magento cache:clean
   ```
