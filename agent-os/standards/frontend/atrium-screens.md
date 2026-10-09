# Frontend: Keystone's screens are built from Atrium

- Atrium owns every component and style of the suite. Keystone ships no `resources/css`, no stylesheet registration (`StyleRegistry`, `Atrium::css()`) and no Blade component namespace.
- Views under `resources/views/ui` use `x-atrium::*` components plus the layout utilities Atrium safelists. No `<style>` block and no `style` attribute.
- Form controls in table cells and inline rows are `x-atrium::form.input|select|textarea|checkbox` with `bare`; amounts with a currency use the input's `prefix` slot. Give repeated checkboxes sharing a name an explicit `id`.
- Record details use `x-atrium::description-list`; bars use `x-atrium::progress`; the flash status and first error use `<x-atrium::flash />`.
- Domain views (`assets/tile`, `categories/branch`, `partials/slot-picker`) stay in Keystone but use only safelisted classes.
- Each main show screen ends with `<x-atrium::audit-trail source="keystone" :subject="$record" />`; the product list carries `<x-atrium::audit-trail source="keystone" />`. Both render nothing until refactor-circus/keen is installed. Product versions remain the revert feature.
- `tests/Feature/Ui/StylesTest.php` asserts `AtriumStyles::missingClasses()` and `AtriumStyles::inlineStyles()` are `[]`. When a class is missing, use the closest safelisted one and ask for it in Atrium.
