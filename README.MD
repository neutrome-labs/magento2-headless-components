    Uncomplicate rich modular layouts based on AlpineJS-components
    with Magento 2 Headless Components experience.

# Magento 2 Headless Components Module

Provides a simple way to render phtml-based templates,
without the need to register them in the xml layout.
Includes caching, native Magento theme overrides, and first-class AlpineJS support.

## Overview

This is a tool to do a
```php
\Magento\Framework\App\ObjectManager::getInstance()
    ->get(\Magento\Framework\View\LayoutInterface::class)
    ->createBlock(\Magento\Framework\View\Element\Template::class)
    ->setTemplate('Vendor_Module::component/headless/block/template.phtml')
    ->toHtml();
```
in a "better" way:

* Native Magento theme overrides
* Block caching
* FPC compatible
* JS-companion blocks for AlpineJS

### The Problem

1) Magento 2 has no ability to simply render templates like `{{ templates/button }}`
as almost every other templating engine provides.
2) AlpineJS-based themes and components become hard to upgrade when overwritten,
because of the need to copy-paste the whole JS logic within the template.

### Proposed Solution

This module provides an easier way to render templates, with first-class AlpineJS support
via decoupling JS logic from templates into `.script.phtml` companion files.
The layout system is used for macro-level layouting (`header`, `footer`, `product-details`),
and Headless Components for atom/block level - `button`, `input`, `accordion`.

## Installation

```bash
composer require neutromelabs/magento2-headless-components
bin/magento setup:upgrade
```

## How To Use

### Basic Usage

```php
/** view/frontend/templates/some/template.phtml */

<?= $block->headlessComponentRenderer->render('atom/input-text', [
    'type' => 'email',
    'name' => 'email',
    'label' => __('Email'),
    'attributes' => [
        'required' => 1,
        'value' => $address->getEmail(),
        'x-model' => 'displayEmail',
        '@change' => 'await save()'
    ]
], 'address-form.input-email') ?>
```

### Configuration

Configure the component module via DI:

```xml
<!-- di.xml -->
<type name="NeutromeLabs\HeadlessComponents\Service\Renderer">
    <arguments>
        <argument name="componentModule" xsi:type="string">Vendor_MyComponents</argument>
    </arguments>
</type>
```

Then create your templates:
```
Vendor/MyComponents/view/frontend/templates/atom/input-text.phtml
Vendor/MyComponents/view/frontend/templates/atom/input-text.script.phtml  <!-- AlpineJS companion -->
```

### Overriding Components

Use standard Magento theme overrides:
```
app/design/frontend/Vendor/theme/Vendor_MyComponents/templates/atom/input-text.phtml
```

### Script Companions (AlpineJS)

For any template `foo/bar.phtml`, create `foo/bar.script.phtml` for the JS logic.
The script companion is automatically detected and rendered once per page in `before.body.end`.

```html
<!-- atom/button.phtml -->
<button x-data="myButton()" @click="handleClick">
    <span x-text="label"></span>
</button>
```

```html
<!-- atom/button.script.phtml -->
<script>
function myButton() {
    return {
        label: 'Click me',
        handleClick() {
            console.log('clicked');
        }
    }
}
</script>
