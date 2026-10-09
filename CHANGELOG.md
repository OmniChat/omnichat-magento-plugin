[Semantic Version](https://semver.org/spec/v2.0.0.html)

## v1.8.0

- [SCN-373](https://omnichat.atlassian.net/browse/SCN-373)

### Feat
- Notify OmniChat when products change (save, delete and mass attribute update) so the intelligent catalog (OIC) stays current; sends only ids and SKUs (configurable children as their parent) to `/v1/product-webhooks`, with a 3s timeout and without failing the save

## v1.7.0

- [SCN-372](https://omnichat.atlassian.net/browse/SCN-372)

### Feat
- Send `updated_at` (UTC save time) in the order webhook

### Fix
- Keep the order items in the order webhook when the order has shipments; they were replaced by the last shipment's items, which carry no price

## v1.6.0

### Feat
- Send installed Magento edition and version and OmniChat plugin version in webhook
- Adjusts cart injection in user section in ecommerce

## v1.5.2

### Fix
- Fixes plugin configuration files

## v1.5.1

### Fix
- Fixes the installation of the package that was generating duplicate folders

## v1.5.0

### Feat
- Add fields item_id, product_id and image_url in order items when send order webhook

## v1.4.3

### Fix
- Add quote_id in order webhook

## v1.4.2

### Fix
- Fix autocomplete of webhook url

## v1.4.1

### Fix
- OmniChat webhooks url

## v1.4.0

### Feat
- Add field to save omnichat webhooks url

## v1.3.0

### Feat
- Implements custom shipping quotes API

## v1.2.0

### Feat
- Implements orders webhook
- Implements save omnichat config webhook

## v1.1.1

### Fix
- Fix cart load in checkout 

## v1.1.0

### Feat
- Implements plugin configuration in the admin panel.

## v1.0.0

### Feat
- Added initial configuration to allow acess cart with id.