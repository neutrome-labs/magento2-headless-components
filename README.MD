    Uncomplicate rich modular layouts based on AlpineJS-components
    with Magento 2 Headless Components experience.

# Magento 2 Headless Components Module

Provides a simple way to render phtml-based templates,
without the need to register them in the xml layout
batteries included (caching, theming, first-class AlpineJS support, etc.).

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

* Theming
* Caching
* FPC
* JS-compainion blocks

### The Problem

1) Magento 2 has no ability just to write smth like {{ templates/button }}
to render templates as an almost every other templating engines.
2) AlpineJS-based themes and components becomes hard to upgrade when overwritten,
because of the need to copy-paste the whole JS logic within the template.

### Proposed Solution

This module adds ability for other modules to make use of easier way
to render templates in a simpler way, along with the first-class AlpineJS support
via decoupling the JS logic from the templates, caching and FPC support,
and theming out-of-the-box.
Thus the layout system is used for macro-level layouting (`header`, `footer`, `product-details`),
and the Headless Components for the atom/block level - `button`, `input`, `accordion`.

## Where To Use

When you building a module, which views will be overridden by the end users,
or just want to get rid of xml hell - this module is for you :).

## How To Use

```php
/** view/frontend/templates/some/template.phtml */

//  call to the renderer fn           with a (template path,     parameters,
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
// and the name in the virtual DOM)
], 'address-form.input-email') ?>

// and once more
<?= $block->headlessComponentRenderer->render('block/messages', [
    'scope' => 'address-form',
], 'address-form.messages') ?>

```

```html
/** view/frontend/templates/component/headless/block/messages.phtml */

<? $scope = $block->getData('scope'); ?>
<div x-data="myVendorModuleHeadlessCreateMessages()"
     @myvendormodulemessage-<?= $scope ?>.window="add($event.detail)">
    <template x-if="messages.length > 0">
        <ul>
            <template x-for="message in messages">
                <li style="border: 1px solid black; margin-bottom: 10px;">
                    <span x-text="message.text"></span>
                </li>
            </template>
        </ul>
    </template>
</div>
```

```html
/** view/frontend/templates/component/headless/block/messages.script.phtml */

<script>
    myVendorModuleHeadlessCreateMessages = function () {
        return {
            messages: [],
            add: function (message) {
                if (!message?.id) {
                    message.id = Math.random().toString(36).substring(7);
                }

                this.messages.push(message);
                setTimeout(() => {
                    this.remove(message.id);
                }, 3000);
            },
            ...
        };
    };
</script>
```

See full example in the [EXAMPLE.MD](EXAMPLE.MD)
