# Gudang Mama

Inventory and document system (slips, invoices, payments, repacks, movings, reports), built in plain PHP on XAMPP with Bootstrap and jQuery.

- Setup of two-factor login: see [docs/TOTP.md](docs/TOTP.md).
- PHP dependencies (spreadsheet export, 2FA, QR codes): `composer install`.

## How a request travels

Every URL goes through `controller/index.php?action=...`, which hands the action to one controller. The controller loads data from `model/`, sets variables such as `$pageState`, `$result` and `$products`, and then `require_once`s one view. The view prints the HTML and loads its JS file, and the JS calls `index.php?action=...` again over AJAX to fill things in live.

```
browser → controller/index.php → xxx_controller.php → model/ (data)
                                        ↓
                               view/xxx.php (HTML) → js/xxx.js → AJAX back to index.php
```

## Controllers

| Controller | Handles | Views it opens |
|---|---|---|
| `page_controller.php` | `show_*`: opening a "new" page | slip_form, invoice_form, payment_form, repack_form, moving_form, hutang, piutang, amends |
| `amend_controller.php` | `amend_update` (open an amend page), `amend_update_data` (save it), deletes | the same `*_form.php` views, in amend mode |
| `create_controller.php` | `create_*`: saving a new document | none (redirects) |
| `master_data_controller.php` | master data list, create, edit, delete | read, master_form, register (users), delete |
| `ajax_controller.php`, `generator_controller.php`, `report_controller.php` | data the JS asks for: document details, auto numbers, report rows | none (returns JSON/text) |
| `export_controller.php` | PDF, Excel, logs | none (files) |

## Views

### Shared pieces

| File | What it is |
|---|---|
| `header.php` / `footer.php` | Top of every page (`<head>`, CSS, nav menu, red message bar) and bottom (Bootstrap JS). |
| `partials/form_field.php` | `form_field()`: prints one label + input (see below). |
| `source_sidebar.php` | "Select a slip" list on new invoice and payment pages. Set `$sidebarPurpose` to `invoice` or `payment`; it only changes the hint text. |
| `master_helpers.php` | Small helpers for the master data pages (titles, labels, URLs). |
| `report_top.php` / `report_bottom.php` | Frame of every report page (heading, month/year filters, results box). |

### Pages

Each form handles both **new** and **amend**. It works out which one from `$pageState`.

| View | Page | Amend when | JS |
|---|---|---|---|
| `slip_form.php` | Slip in / out / tax out | `$pageState` starts with `amend_slip_` | `index.js` |
| `invoice_form.php` | Invoice in / out / tax / moving | starts with `amend_invoice_` | `invoice.js` + `document_sidebar.js` |
| `payment_form.php` | Payment in / out / tax / moving | starts with `amend_payment_` | `payment.js` + `document_sidebar.js` |
| `repack_form.php` | Repack | `$pageState === 'amend_repack'` | `repack.js` |
| `moving_form.php` | Moving | `$pageState === 'amend_moving'` | `moving.js` |
| `master_form.php` | Master data create / edit | controller sets `$masterEdit` | none |
| `read.php` / `register.php` / `delete.php` | Master list / user form / delete confirmation | | `master_ui.js` (list) |
| `amends.php` | "Edit slips / invoices / …" list | | `amends.js` |
| `dashboard.php` / `hutang.php` / `piutang.php` | Storage / hutang / piutang reports | | `storage.js` / `hutang.js` / `piutang.js`, all plus `report_ui.js` |
| `login.php`, `two_factor.php` | Login, 2FA | | none |

Views include each other with plain relative paths, e.g. `include "header.php";`.

### `form_field()`

`form_field()` prints one label + input box, so the same HTML isn't copy-pasted for every field.

```php
form_field('no_sj', 'No SJ', ['placeholder' => 'di isi', 'oninput' => 'getDetailsFromSJ()', 'required' => true]);
```

prints

```html
<div class="gm-form-field"><label for="no_sj">No SJ *</label><input name="no_sj" type="text" id="no_sj" placeholder="di isi" oninput="getDetailsFromSJ()" required></div>
```

- Arguments: the field id (also used as `name`), the label, then the options `type`, `value`, `placeholder`, `oninput`, `required` and `readonly`.
- `required` adds the ` *` to the label for you.
- `value => null` leaves the box empty. Forms pass `null` on new pages and the saved value on amend pages, which is how one field list serves both.

It's used in `invoice_form.php` and `payment_form.php`. Near the top of each file is a `$fields = [...]` list per mode (in, out, moving). To add, remove or reorder a field, edit that list.

## CSS

| File | Styles |
|---|---|
| `base.css` | Brand colours (`--gm-orange`, `--gm-cream`, `--gm-sage`, `--gm-soft-sage`, `--gm-ink`) and global resets |
| `header.css` | The nav bar |
| `forms.css` | All `gm-form-*` pages, plus invoice/payment/repack/moving extras and the sidebar |
| `reports.css` | `gm-report-*` pages |
| `master.css` | `gm-master-*` pages |

A class's prefix tells you which file styles it. Use the colour variables instead of typing hex codes.
