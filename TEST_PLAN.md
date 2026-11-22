# Test Plan

This document defines the local manual test scenarios and records the latest automated and browser verification. Run tests only in a local demo environment.

## Test environment and safety

Before testing:

1. Start Apache and MySQL/MariaDB in XAMPP.
2. Import `database/schema.sql` and `database/seed.sql`.
3. Open `http://localhost/WEBB/Online-Kids-Birthday-Party-Planning-project/`.
4. Use the seeded demo credentials: `admin@gmail.com` or `parent@gmail.com`, password `password`.

Use a disposable local database for tests that create, update, or delete records. Back up any data that must be retained. The HTTP smoke suite creates and cleans up temporary records; the manual scenarios may require cleanup or a fresh demo database.

## Manual test scenarios

| ID | Scenario | Steps | Expected result |
|---|---|---|---|
| 1 | Parent sign-in | Sign in as `parent@gmail.com` with password `password`. | Sign-in succeeds, the session is regenerated, and parent navigation is shown. |
| 2 | Invalid sign-in | Submit a valid email address with an incorrect password. | A generic invalid-credentials message appears; no authenticated session is created. |
| 3 | Parent registration | Register with a unique email and a valid password. | The account is stored with a password hash and the new parent is signed in. |
| 4 | Duplicate registration | Register again using an email already in use. | The form reports the duplicate; no second account is created. |
| 5 | Party creation | As a parent, create a future-dated party with a theme and whole-number RWF budget. | The party appears in the parent's list, belongs to that parent, and displays its budget in RWF. |
| 6 | Party validation | Try a past date, empty theme, negative budget, or fractional RWF budget. | Invalid input is rejected with a useful validation message and no invalid party is stored. |
| 7 | Party update | Edit a party's theme or budget and save. | The updated values persist and appear in the party details or list. |
| 8 | Party deletion | Delete a party that has a booking. | The party and its dependent booking records are removed. |
| 9 | Vendor search and JSON | Search for `cake` in the vendor directory and request `?r=api/vendors&q=cake`. | The directory updates asynchronously; the endpoint returns `{ok:true,data:{vendors:[...]}}` with matching results. |
| 10 | Booking creation and update | As a parent, book a vendor, edit the booking, and change its party or vendor. | A new booking starts as `pending`; a change updates the amount to the vendor price and resets the status to `pending`. |
| 11 | Duplicate booking | Request the same vendor more than once for the same party. | A duplicate is rejected with a user-facing error. |
| 12 | Booking deletion | Delete a booking owned by the signed-in parent. | The booking is removed from the list. |
| 13 | Administrator booking status | Sign in as the administrator and change a booking to `confirmed`. | The status update persists and is displayed. |
| 14 | Vendor management | As administrator, create a vendor, edit it, and delete it. | Each operation persists; deleting the vendor removes dependent bookings. |
| 15 | User management and authorization | As administrator, create, update, and delete a non-current user. Then try `?r=users` as a parent. | Administrator operations succeed; the parent receives HTTP 403. The administrator cannot delete their own account. |
| 16 | CSRF rejection | Submit a POST form with a missing or invalid `_csrf` token. | The request is rejected with HTTP 419 and the requested mutation does not occur. |
| 17 | Escaped user content | Save a party theme containing `<script>alert(1)</script>` and view the party list. | The value is rendered as escaped text; the script does not execute. |
| 18 | Responsive layout | View the home page, vendor search, and data tables at a narrow mobile viewport. | Navigation remains operable, content stays readable, and wide tables can be scrolled as designed. |
| 19 | Response hardening | Load a normal page and an invalid route; inspect response headers and error output. | Security headers are present; errors do not reveal file paths, SQL, or stack traces. |
| 20 | Error logging | In a disposable local environment, temporarily configure an invalid database name and request a page or API endpoint. | The visitor sees a generic HTML or JSON error; the log records the error type and route without credentials under PHP's temporary directory, outside the project web root. The log cannot be fetched through the site. Restore the valid configuration afterward. |
| 21 | Browser form validation | Enter an invalid email, past date, or out-of-range amount in a form with browser validation. | A field-specific browser validation message appears; server-side validation remains authoritative. |
| 22 | Seeded vendor directory | Open the vendor directory after importing the seed into a fresh demo database. | Exactly four fictional vendors appear with the expected service categories, contact addresses, and RWF prices. |
| 23 | Vendor service filter | Select the `Cake` filter, search, then clear the search. Review service choices in the administrator vendor form. | Results remain filtered correctly through search. Available categories are Cake, Decorations, Entertainment, and Photography. |
| 24 | RWF and email bounds | Submit a fractional budget, vendor price, or administrator booking amount; then submit a vendor email longer than 190 characters. | The server rejects fractional RWF amounts and overlong email input; no invalid record is stored. |
| 25 | Password bounds | Try passwords shorter than 8 bytes and longer than 72 bytes during registration, profile update, and administrator user creation. | Invalid lengths are rejected; passwords from 8 through 72 bytes are accepted. |
| 26 | Malformed record IDs | Submit an array-shaped or non-numeric ID for a party, booking, vendor, or user action. | The request is handled as invalid and does not modify or delete a record. |
| 27 | Test-runner web access | Request `tests/http_smoke.php` or `tests/validation.php` through Apache. | The scripts return HTTP 404 and do not run over HTTP; they remain runnable from the command line. |
| 28 | Malformed search input | Request the vendor JSON endpoint with `q[]=cake`. | The endpoint returns a structured JSON HTTP 400 response, not a server error. |
| 29 | Shared layout and navigation | Load the home page at desktop and mobile widths. Follow the logo and Home link; inspect page landmarks and the mobile menu. | The links return to the home page; header, navigation, main, and footer landmarks render; the mobile menu opens and remains usable. |
| 30 | Home totals and content | Sign in as the seeded administrator and load the home page with known vendor, party, and booking data. | Totals match the database, the site is branded `Kids Birthday`, the header says `Hi, Admin`, and `Big magic.` appears upright. |

## Automated checks

Run the database-independent validation tests from the project root:

```powershell
C:\xampp\php\php.exe tests\validation.php
```

Run the database-backed HTTP smoke tests with Apache and MySQL/MariaDB running and the schema and seed imported:

```powershell
C:\xampp\php\php.exe tests\http_smoke.php
```

The HTTP smoke suite exercises public pages, vendor search, registration and sign-in, parent and administrator workflows, validation, access controls, CSRF handling, and cleanup of its temporary records. It uses but does not modify the seeded administrator.

## Latest verification record

Verification was performed locally on 2026-11-20 using PHP 8.2.12, MariaDB 10.4.32, and XAMPP. During the project review, all listed syntax and automated checks passed. The HTTP suite creates temporary records and cleans them up; use a disposable local database when running it.

| Check | Result |
|---|---|
| PHP syntax | All 47 PHP files passed. |
| JavaScript syntax | `assets/js/app.js` passed `node --check`. |
| Database scripts | The schema and seed scripts ran successfully against the local MariaDB database. |
| Database-independent tests | 22 passed, 0 failed. |
| Database-backed HTTP tests | 64 passed, 0 failed. |
| Log-path check | A test log was created under PHP's temporary directory, outside the project root, then removed. |
| Browser check | The home page showed `Hi, Admin`; `Big magic.` computed to normal font style; mobile navigation opened at a narrow viewport. |

These results record the checks actually run. The manual scenarios above remain a checklist for additional review; they should not be treated as individually executed unless a tester records their results.
