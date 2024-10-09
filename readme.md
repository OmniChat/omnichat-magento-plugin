# OmniChat - Magento 2 Module

## Description

This Magento 2 module allows users to access the cart page using a unique id associated with each cart.

---

## Features

- Allows cart access via token-based URL (e.g., `/cart/?cart_id=123`).
- Extends the default Magento cart page layout.

---

## Installation Instructions

Follow these steps to install the module in your Magento 2 instance:

### Step 1: Download the module

You can install this module using **Composer**. Run the following command:

```bash
composer require vendor/omnichat
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
            │   ├── module.xml
            ├── registration.php
            └── Plugin
                └── CartLoaderPlugin.php
```

- `module.xml`: Declares the module in Magento.
- `di.xml`: The dependencies injection
- `CartLoaderPlugin.php`: Contains the logic for managing cart access.

---

## Uninstallation

To remove the module from your Magento 2 instance:

1. Disable the module:
   ```bash
   php bin/magento module:disable Vendor_OmniChat
   ```

2. Remove the module using Composer:
   ```bash
   composer remove vendor/omnichat
   ```

3. Run the following commands to complete the removal:
   ```bash
   php bin/magento setup:upgrade
   php bin/magento setup:di:compile
   php bin/magento cache:clean
   ```
