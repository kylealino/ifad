# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

FODS (Financial Obligation and Disbursement System) is a CodeIgniter 4 app for a government office. It covers budget allotment, ORS/BURS obligations, disbursement vouchers, cashier outputs (LDDAP-ADA, ADA, RCI), procurement (PPMP, PR, Abstract), the SAOB report, and maintenance data (payees, projects, UACS, users/roles).

## Running it

- Runs under **XAMPP** (Apache + MariaDB/MySQL, PHP ≥ 8.1) at `http://localhost/fods-local/public/` (`app/Config/App.php` `baseURL`, `indexPage = ''`).
- DB: MySQLi, database `fods-local` on localhost (`app/Config/Database.php`). `.env` only sets `CI_ENVIRONMENT = production`, so errors are not shown in the browser. Check `writable/logs/log-YYYY-MM-DD.log`. The README's `.env` database setup is out of date: the DB settings live in `Database.php`.
- The framework is vendored in `system/`, and there is no `vendor/` directory. Composer/PHPUnit aren't installed, and `tests/` only has the CI4 sample tests. Verify changes by loading the page in the browser, and run `php -l <file>` for syntax.
- There are no CI4 migrations. Schema changes go in hand-written, re-runnable SQL files at the repo root (`migration-<topic>-MMDDYYYY.sql`). `fods-local.sql` is the full current dump, and `__db_backup/` holds older dumps. Take a backup dump before you run a migration.

## Architecture

The app uses one controller, one model, one view folder and one JS file per module. It does **not** use idiomatic CI4: there's no query builder, no resource routes, no entities and no foreign keys.

| Piece | Location |
|---|---|
| Routes | `app/Config/Routes.php`. Each module has a GET and a POST route to `MyX::index` with `['filter' => 'myauthuser']` |
| Sidebar | `app/Config/Navigation.php` (data-driven). Rendered by `app/Views/templates/myheader.php`, which shows only the items the user can access |
| Access codes | `app/Config/AccessCodes.php` constants. Never hardcode the literal codes |
| Access checks | `app/Helpers/access_helper.php` (`fods_has_access`, `fods_has_any_access`, `fods_require_access`, `fods_current_user`), autoloaded through `Config\Autoload::$helpers` |
| Auth filter | `app/Filters/Myauthuser.php` checks the session key `__xsys_myuserzicas_is_logged__` |
| Controllers | `app/Controllers/MyX.php`. `index()` switches on `getPostGet('meaction')` (`MAIN`, `MAIN-SAVE`, `MAIN-LOAD`, `MAIN-DELETE`, `PRINT-*`, …) |
| Models | `app/Models/MyXModel.php`, with raw parameterized `$this->db->query("... ?", [...])` |
| Views | `app/Views/<module>/<module>-main.php`, wrapped in `templates/myheader.php` / `myfooter.php` |
| PDFs | `*-pdf.php` views that `require APPPATH . 'ThirdParty/fpdf/fpdf.php'` and draw with FPDF |
| JS | `public/assets/js/<area>/<module>.js`, each with one global `function __mysys_x_ent(){...}`. Shared helpers are in `public/assets/js/mysysapps.js` |
| CSS / layout | `public/assets/css/fods.css` (layout + UI kit), `public/assets/js/fods-layout.js` (sidebar) |
| Misc helpers | `app/Models/MyLibzSysModel.php` (string sanitizers, date formatting, select/pagination HTML) |

### Request/response conventions
- Constructors repeat the same boilerplate: `$this->request`, `$this->myx = model(...)`, `$this->db`, `$this->session`, and `$this->cuser = session('__xsys_myuserzicas__')` (the username).
- Saves are AJAX POSTs. The model echoes a `<script>` block (toastr + redirect, or an error alert) and then calls `exit;`, and the JS injects that HTML into an output div. Save payloads use a legacy `"a|b"` delimited format. Keep it byte-identical.
- Every insert/update/delete/approve branch in a model starts with `fods_require_access(\Config\AccessCodes::X_ACTION, 'Saving'|'Updating'|'Deleting'|'Approve'|...)`. `MAIN` checks `fods_has_access(X_VIEW)` and otherwise returns `view('errors/html/access-restricted')`.
- **Modernized modules** (User Mgmt, Payee, Supplies/Products, Project, UACS, Budget Allotment/Approval, ORS and BURS Entry/Approval, Disbursement/Disbursement Approval, LDDAP-ADA/ADA and their Approval pages, RCI) use a list page (page head, stat tiles, filter pills, client-side DataTable from a JSON var). An editor modal loads through `MAIN-LOAD` JSON. Approval pages use a review modal with a two-step certify/disapprove, where remarks are required to disapprove. All of these list pages share one card layout, computed in each module's JS from the list data for the year picked in the page head (default: the current year, remembered per page; "All years" is an option): **Pending** (on approval pages: "Waiting for you", meaning only the steps the user can act on), **Returned** (disapproved), **Approved**, and **Total amount**. Each card also filters the list. Budget drafts are a filter pill, noted under Total. The other modules (PPMP, PR, Abstract, SAOB) still use the older single form + table layout.
- **ORS and BURS are twins.** They share the same schema (`tbl_burs_*` mirrors `tbl_ors_*`) and the same code with the names swapped. Keep them in sync. The only BURS differences are: FC 07 projects (`fundcluster_id='2'`), blank default particulars, and the BURS form labels. ORS certification goes Box A → Box B, and both steps use the one `ORSAPPROVAL_APPROVE` (38005) code. A certified ORS is locked, and editing a disapproved one resets it to Box A.
- **Disbursement** follows the same pattern. A DV is prepared from an ORS or BURS: `OBLIGATION-LOAD` copies the header and deductions, and sums the lines per responsibility code. Saving writes the DV no. back to that ORS/BURS by `serialno`. Approval is three steps in order: Box A (supervisor) → Box C (accounting) → Box D (approved for payment), stored in the `certa`, `certc` and `certd` columns (statuses `pending_a`, `pending_c`, `pending_d`, `approved`, `disapproved`). All three use the one `DISBURSEMENTAPPROVAL_APPROVE` (40005) code, but a step can only be decided by the signatory named on that box, matched on `myua_user.full_name` (`certified_a`, `certified_c`; Box D is not stored and follows the net amount through `MyDisbursementModel::box_d`, which the PDF also uses). The Administrator role (`fods_is_admin()`) can decide any step. The rule lives in `MyDisbursementModel::can_decide`. A DV is locked once Box A approves it, and editing a disapproved one sends it back to Box A. Every change is logged to `tbl_disbursement_history`, which the editor and the review modal show as a timeline. DV tax rules round each amount to 2 decimals before totaling, which is different from ORS.
- **LDDAP-ADA and ADA** each have a single approval step (`LDDAPADAAPPROVAL_APPROVE` 41005, `ADAAPPROVAL_APPROVE` 42005). The approval columns are `is_pending`, `is_approved`, `is_disapproved`, `approver` and `remarks` on `tbl_lddapada_hd` and `tbl_ada`. An approved record is locked, and editing a disapproved one sends it back to pending. An LDDAP-ADA line's allotment class is the largest-amount UACS on that DV's own ORS/BURS (`MyLDDAPADAModel::dv_lines`). ADA prints by `recid` (`dvno` still works for old links).
- **RCI** has no approval step. Its cards are Reports, LDDAP-ADAs covered, DVs paid and Total amount, and its year is the encoded date (`created_at`/`created_by`, not `added_*`). Picking an LDDAP-ADA adds one line per DV (`MyRCIModel::lddapada_lines`, which takes the responsibility code from the DV's ORS/BURS). The save payload is 12 `x|x` fields per line.
- **Messages** (`mymessages`, topbar inbox before the profile, not in the sidebar) has chats and support tickets. Everyone can chat (direct or group, `tbl_conversation_hd/_member/_msg`, unread = messages after the member's `last_read_id`; `is_archived` on the member row hides a chat for that person only, and any new message unarchives it for everyone; the person who created a group chat (`tbl_conversation_hd.added_by`) can remove members through `CONV-REMOVE`, which logs an `is_system` note in the chat; images and files go in `tbl_conversation_attachment` under `writable/uploads/chats/<year>/`, served by `meaction=CHAT-FILE` to members only) and file tickets (`tbl_ticket_hd`, thread in `tbl_ticket_dt` whose first row is the description and `is_system` rows are status notes, plus `tbl_ticket_attachment` and `tbl_ticket_seen`). Everyone can file tickets and follow their own under **My tickets**, where each ticket is a chat-style conversation with support. `TICKET_VIEW` 37001 opens the **Support desk** tab: cards (Open, Waiting for a reply, In progress, Resolved/closed), breakdowns by type, priority and module, and every ticket in a table that opens in a review modal. `TICKET_UPDATE` 37003 adds replying as support and changing status. The requester can only close or reopen. A row's `needs_reply` means the requester spoke last on an open ticket. Saving a ticket closes the form and reloads the list. Images only (JPG/PNG/GIF/WEBP, 5 x 5 MB), stored under `writable/uploads/tickets/<year>/` and served only through `meaction=ATTACHMENT` after a ticket access check. `templates/myheader.php` renders the unread badge and `public/assets/js/fods-inbox.js` polls it. The old `Support` controller (`tbl_support_*`) is superseded and no longer linked.

## Access control (RBAC)

- Code format: `<2-digit family><3-digit action>`. Actions are 001 View, 002 Add, 003 Update, 004 Delete, 005 Approve. Families run from 20 Payee through 35 RCI, plus 36 Budget Approval, 37 Support Tickets, 38 ORS Approval, 39 BURS Approval, 40 Disbursement Approval, 41 LDDAP-ADA Approval and 42 ADA Approval. **Every approval page is its own module with only View (which opens the page) and Approve**. Entry modules never carry approve codes. On the approval pages, view-only users see a read-only review. **A new module takes the next family number (43)**, gets constants in `AccessCodes.php`, and gets rows in `tbl_access_modules` named `"<Module> - <Action>"` (the permissions modal splits on `" - "`).
- `tbl_roles` + `tbl_role_access` define roles, and `myua_user.role_id` assigns them. `tbl_user_access` is a per-user **cache** that `MyUserManagementModel::syncUserAccess` regenerates, and the helper reads from it.
- Signatory flags on `myua_user` (`ada_tag`, `cert_tag`, `is_ppmp_signatory`) are separate from module access. Passwords are SHA-512 in `hash_password`, and `is_active` is enforced at login.

## Database conventions

- `tbl_<module>_hd` (header) / `tbl_<module>_dt` (lines). Budget splits use `_direct_{ps,mooe,co}_dt` / `_indirect_{ps,mooe,co}_dt`.
- Every table has `recid` int PK, `added_by`, and `added_at`/`added_on` datetime. Money is `decimal(25,2)`. Flags are `int(11)` 0/1.
- There are no foreign keys. Tables are joined on matching string values (`dvno`, `payee_name`, …).
- Approval columns repeat in each module: `is_pending`, `is_approved_cert{a,b}`, `is_disapproved_cert{a,b}`, `cert{a,b}_remarks`, `cert{a,b}_approver`.

## Adding a module (checklist)

1. Add GET + POST routes with the `myauthuser` filter.
2. Add an entry to `Config/Navigation.php` with its access code(s). Don't hand-edit the sidebar markup.
3. Add the next access-code family in `AccessCodes.php`, plus a migration SQL that inserts the `tbl_access_modules` rows.
4. Write the controller, model, view and JS using an existing modernized module (e.g. `MyPayee`) as the template.
5. Add a migration SQL for the `_hd`/`_dt` tables.

## Gotchas

- The repo has many stale copies (`*_06102025.php`, `* copy.php`, `*.php-backup09292026`, `report-backup/`). Only the unsuffixed file is live. Don't edit the copies.
- Some routes have no auth filter (`dashboard/saob-session-data`, the CSV exports under `MySaobReport`, `mylogin-auth`). `GET myaccount` is registered twice. CI4 keeps the first route, so GET goes to `MyAccount` and only POST reaches `MyUserAccount`.
- `ExternalApi` and `UacsApi` are small standalone endpoints outside the module pattern.
