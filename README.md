# LeanCommerce_CustomSpecialPriceColor

Magento 2 module for Grupo Dico / Todomuebles that controls the visual presentation of `special_price` without changing Magento price calculation or transactional price data.

## Purpose

`LeanCommerce_CustomSpecialPriceColor` adds configurable labels and colors around special-price displays in catalog contexts. It is presentation-only: it does not change `final_price`, `regular_price`, cart totals, checkout, minicart, orders, invoices, catalog price rules, cart price rules, or quote/order data.

## Business Context

Grupo Dico uses several visual price presentations in the catalog:

- Normal special price.
- `Súper Oferta`.
- Informative third price, shown as `Al pagar`.

These presentations share a common visual model so merchandising teams can control labels, label colors, price colors, and price background colors from Magento admin configuration and EAV attributes.

## Functional Scope

The module supports three presentation modes:

1. **Normal Special Price**: Visual formatting for Magento's native active `special_price`.
2. **Súper Oferta**: Global presentation applied when product attribute `super_oferta` is enabled and Magento `special_price` is active.
3. **Third Price / Al pagar**: Informative, non-transactional amount rendered from product attribute `al_pagar_precio`.

## Visual Presentation Fields

Each presentation may define:

- `label`
- `label_color`
- `price_color`
- `price_background_color`

Empty or invalid colors are normalized to `null`; the frontend should not render empty inline style values.

## Priority Rules

Final presentation priority:

1. `super_oferta = 1` plus active Magento `special_price`.
2. `al_pagar_precio`.
3. Normal `special_price` cascade.
4. Native theme fallback.

Normal `special_price` cascade:

1. Product values.
2. Current category values, when a category context is available.
3. Global configuration.
4. Native theme fallback.

`al_pagar_precio` is informational only. It renders an additional visual price block and never modifies Magento transactional prices.

## Configuration Scope

Normal special price presentation can be configured at:

- Product level.
- Category level.
- Global configuration level.

`Súper Oferta` presentation is global only.

Third price / `Al pagar` presentation is global only, with the amount stored on the product.

## Product Attributes

The module defines or updates these product attributes:

- `special_price_label`: Optional normal special-price label override.
- `special_price_label_color`: Optional normal special-price label color.
- `special_price_color`: Optional normal special-price value color.
- `special_price_background_color`: Optional normal special-price background color.
- `super_oferta`: Boolean trigger for the `Súper Oferta` presentation.
- `al_pagar_precio`: Informative third-price amount.

Product presentation attributes are visible in Product Edit and are assigned to the General group by the module's data patches. Product color fields are rendered as Magento UI color pickers through the product EAV metadata plugin.

## Category Attributes

The module defines or updates these category attributes:

- `special_price_label`: Optional normal special-price label override.
- `special_price_label_color`: Optional normal special-price label color.
- `special_price_color`: Optional normal special-price value color.
- `special_price_background_color`: Optional normal special-price background color.

Category fields are rendered in the category admin form through `view/adminhtml/ui_component/category_form.xml`.

## Admin Configuration

Global configuration is stored under `catalog/special_price_color/...`.

General flags:

- `catalog/special_price_color/enabled`
- `catalog/special_price_color/apply_in_plp`
- `catalog/special_price_color/apply_in_pdp`
- `catalog/special_price_color/apply_in_carousels`

Normal special price:

- `catalog/special_price_color/default_label`
- `catalog/special_price_color/default_label_color`
- `catalog/special_price_color/default_color`
- `catalog/special_price_color/default_background_color`

`Súper Oferta`:

- `catalog/special_price_color/super_oferta_label`
- `catalog/special_price_color/super_oferta_label_color`
- `catalog/special_price_color/super_oferta_price_color`
- `catalog/special_price_color/super_oferta_background_color`

Third price / `Al pagar`:

- `catalog/special_price_color/third_price_label`
- `catalog/special_price_color/third_price_label_color`
- `catalog/special_price_color/third_price_price_color`
- `catalog/special_price_color/third_price_background_color`

The global color fields use a module-specific admin color picker block because Magento system configuration fields are not UI Component form fields.

## Architecture

Main module components:

- `Model/Config.php`: Reads global configuration values.
- `Model/Presentation/SpecialPricePresentation.php`: DTO describing the resolved visual presentation.
- `Model/Resolver/SpecialPricePresentationResolver.php`: Resolves presentation mode and visual fields.
- `Model/Resolver/SpecialPriceColorResolver.php`: Backward-compatible color resolver for normal special price color.
- `Model/Color/HexColorValidator.php`: Validates supported HEX color strings.
- `Model/Color/HexColorNormalizer.php`: Normalizes color input to supported HEX strings or `null`.
- `Block/Pricing/Render/FinalPriceBox.php`: Extends Magento final price rendering with presentation data and cache keys.
- `view/frontend/templates/product/price/final_price.phtml`: Renders special-price presentation markup.
- `Plugin/Catalog/Ui/DataProvider/Product/Form/Modifier/SpecialPriceColorMetaPlugin.php`: Applies color picker metadata to dynamic product EAV fields.
- `view/adminhtml/ui_component/category_form.xml`: Adds category presentation fields to category admin.
- `Observer/FlushCacheOnConfigSave.php`: Invalidates cache after global config changes.
- `Observer/FlushCacheOnProductSave.php`: Invalidates cache after relevant product saves.
- `Observer/FlushCacheOnCategorySave.php`: Invalidates cache after relevant category saves.

## Product Admin UI Notes

Product attributes are dynamic EAV fields generated by Magento's product form data provider. Product color picker metadata is applied through `SpecialPriceColorMetaPlugin`.

Do not duplicate product fields through `product_form.xml`. Duplicating them can create conflicting metadata or duplicate fields.

Product color fields must keep:

- `componentType = field`
- A unique `dataScope` matching the attribute code.
- A unique `inputName` such as `product[special_price_color]`.
- `formElement = colorPicker`.
- `component = Magento_Ui/js/form/element/color-picker`.
- `elementTmpl = ui/form/element/color-picker`.

The `special_price_label` field remains a normal text input.

## Cache and Invalidation

The module invalidates relevant cache when presentation-affecting data changes:

- Global configuration save cleans broad frontend cache types.
- Product save invalidates product-related cache when presentation fields or trigger attributes change.
- Category save invalidates category-related cache when presentation fields change.

`FinalPriceBox` cache keys include:

- Request context.
- Category context when available.
- Presentation mode.
- A short hash of the resolved presentation.

This prevents stale presentation markup from being reused across products, categories, and display contexts.

## Frontend Behavior

The module affects catalog price presentation in enabled contexts:

- Product listing pages.
- Product detail pages.
- Search results.
- Product widgets and carousels.

It does not change:

- Price calculation.
- Cart, checkout, or minicart prices.
- Quote, order, invoice, or email totals.
- Catalog price rules or cart price rules.

The frontend includes a PDP-safe reapply script for compatibility with installations that replace price markup through Magento's `priceBox` reload flow.

## Out of Scope

- Transactional price calculation.
- Checkout, cart, minicart, order, invoice, and email changes.
- Catalog price rules and cart price rules.
- Theme cleanup outside this module.
- Configurable selected-child color behavior.

## Compatibility

Target compatibility:

- Magento 2.4.5
- Magento 2.4.8
- PHP 7.4-compatible syntax
- PHP 8.x runtime compatible

## Installation

Run from Magento root:

```bash
bin/magento module:enable LeanCommerce_CustomSpecialPriceColor
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento indexer:reindex catalog_product_attribute
bin/magento cache:flush
```

## Validation Checklist

CLI:

```bash
find app/code/LeanCommerce/CustomSpecialPriceColor -name "*.php" -print0 | xargs -0 -n1 php -l
find app/code/LeanCommerce/CustomSpecialPriceColor -name "*.xml" -print0 | xargs -0 -n1 php -r 'foreach (array_slice($argv,1) as $f) { echo $f, PHP_EOL; simplexml_load_file($f) ?: exit(1); }'
find app/code/LeanCommerce/CustomSpecialPriceColor -name "*.js" -print0 | xargs -0 -n1 node --check
bin/magento setup:di:compile
```

Manual browser checks:

- Admin global configuration fields render and save.
- Product Edit shows product presentation fields in General.
- Product color fields use color pickers and preserve correct input names.
- Category Edit shows category presentation fields.
- PLP normal special-price presentation follows product, category, global, theme priority.
- PDP normal special-price presentation follows product, global, theme priority when no category context is available.
- Search results use product, global, theme priority.
- Widgets and carousels use product, global, theme priority.
- `Súper Oferta` presentation applies only when `super_oferta` is enabled and `special_price` is active.
- `Al pagar` renders as an informative third-price block and does not affect transactional price.
- Price reload behavior remains stable with Magento `priceBox` updates.

## Release Notes 1.0.2

- Adds complete visual presentation support for normal special price, `Súper Oferta`, and `Al pagar`.
- Adds product and category presentation fields for normal special price.
- Adds global presentation configuration for normal special price, `Súper Oferta`, and third price.
- Adds presentation resolver, cache-aware price renderer integration, and frontend markup support.
- Adds admin color picker support for global, category, and product color fields.
- Adds cache invalidation observers for configuration, product, and category changes.
