# Agent Guidelines for magento2-headless-components

> **⚠️ IMPORTANT**: After making code changes, always review and update this file if your changes affect the documented structure, key files, configuration, architecture, or important notes. Keep this file in sync with the codebase.

## Project Overview

This is a **Magento 2 module** that provides a simple way to render phtml-based templates without XML layout registration. Includes caching, native Magento theme overrides, and first-class AlpineJS support via script companion files.

## Directory Structure

```
src/HeadlessComponents/
├── Block/              # Block classes
├── Service/            # Service layer classes
└── etc/                # Module configuration
```

## Key Files

| File | Purpose |
|------|---------|
| `Block/Headless.php` | Base block for headless components with caching support |
| `Service/Renderer.php` | Core rendering service - resolves templates and handles script companions |
| `Service/AttributesHtmlGenerator.php` | HTML attribute generator with XSS escaping |
| `etc/di.xml` | DI configuration (currently empty, consumers configure `componentModule`) |

## Coding Standards

- **PHP**: Magento 2 coding standards (PSR-12 based), `declare(strict_types=1)` required
- **Namespace**: `NeutromeLabs\HeadlessComponents\`
- **Module Name**: `NeutromeLabs_HeadlessComponents`

## Architecture Decisions

### Simple DI-based Configuration
The `Renderer` accepts a `$componentModule` string parameter (default: `NeutromeLabs_HeadlessComponents`). Consumer modules configure this via `di.xml`:

```xml
<type name="NeutromeLabs\HeadlessComponents\Service\Renderer">
    <arguments>
        <argument name="componentModule" xsi:type="string">Vendor_MyModule</argument>
    </arguments>
</type>
```

### Native Magento Theme Overrides
No custom theming system. Templates are overridden via standard Magento theme fallback:
```
app/design/frontend/Vendor/theme/Vendor_Module/templates/path/to/template.phtml
```

### Script Companion Pattern
For any template `foo/bar.phtml`, an optional `foo/bar.script.phtml` can contain AlpineJS logic. The companion is:
- Auto-detected by `Renderer`
- Rendered once per page (singleton pattern)
- Placed in `before.body.end` layout block (or inline if no layout available)

### Block Factory Pattern
`HeadlessFactory` creates `Headless` block instances. Each render call creates a new block with isolated data.

## Common Tasks

### Rendering a Component
```php
// In consumer module's ViewModel or Block
$renderer->render('atom/button', ['label' => 'Click me'], 'my-button');
```

### Short vs Full Template Paths
- Short: `atom/button` → `{$componentModule}::atom/button.phtml`
- Full: `Vendor_Module::path/template.phtml` → used as-is

## Dependencies

- `magento/framework`
- PHP `^8.1`

## Important Notes

1. **Do not add theming abstraction** — removed in v0.2.0 in favor of native Magento overrides
2. **Script companions are singletons** — rendered once per unique template path per request
3. **`$scriptLayoutParent` parameter** assumes the layout block exists (default: `before.body.end`)
4. **Exceptions in script companion rendering are silently caught** — prevents breaking main render
5. **Consumer modules** should wrap `Renderer` in their own ViewModel for template path prefixing

## Version

Current: `0.2.0` (see composer.json)
