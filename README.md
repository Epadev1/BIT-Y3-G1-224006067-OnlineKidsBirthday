# Online Kids Birthday Party Planning

Kids Birthday is a PHP and MySQL web application for planning children's birthday parties. Parents can organize party details and vendor bookings; administrators manage users, vendors, and booking status.

## Features

- Parent registration and sign-in with password hashing and session-based authentication.
- Role-based access for parents and administrators.
- Create, view, update, and delete workflows for users, vendors, parties, and bookings.
- Party budgets, vendor prices, and booking amounts in Rwandan francs (RWF).
- Vendor directory with service filtering and asynchronous search.
- JSON vendor-search endpoint at `index.php?r=api/vendors&q=...`.
- PHP MVC-style request handling with controllers, models, views, sessions, and includes.
- PDO/MySQL persistence with prepared statements, foreign keys, unique constraints, and UTF-8 (`utf8mb4`).
- CSRF protection, server-side and browser-side validation, escaped HTML output, security headers, and private error logging.
- Responsive HTML5/CSS3 interface; vanilla JavaScript uses DOM events, client validation, and Fetch/AJAX vendor search.

The four sample vendor names and contact addresses are fictional demonstration data and are not verified business listings.

## Technology and requirements

- XAMPP with Apache and MySQL/MariaDB.
- PHP 8.0 or later with PDO MySQL enabled.
- PHP cURL enabled to run the HTTP smoke-test script; the web application does not use cURL.
- No Composer or npm dependencies.

> **Database dialect:** The SQL scripts use MySQL/MariaDB syntax and are not compatible with Microsoft SQL Server. Use MySQL/MariaDB tooling to edit and run them.

## Local setup with XAMPP

1. Place the project at `C:\xampp\htdocs\WEBB\Online-Kids-Birthday-Party-Planning-project\`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Open phpMyAdmin at `http://localhost/phpmyadmin`, or use the MySQL command line.
4. Import `database/schema.sql` to create the database and any missing tables.
5. Import `database/seed.sql` to add the demo accounts and sample data.
6. Browse to `http://localhost/WEBB/Online-Kids-Birthday-Party-Planning-project/`.

The schema uses `CREATE ... IF NOT EXISTS`; it does not drop, migrate, or update existing tables. Use a fresh demo database to get the supplied four-vendor dataset. Back up any database you need to keep, and do not drop existing data as part of setup.

The seed is designed to be re-run without duplicating its demo vendors, parties, or bookings. It restores the demo accounts to the documented usernames, roles, and passwords. Re-importing it resets those demo account credentials.

### Database configuration

By default, the application connects to the local XAMPP database using the values in `config/database.php`. Configure them there, or set these environment variables before starting Apache:

| Variable | Default |
|---|---|
| `DB_HOST` | `127.0.0.1` |
| `DB_PORT` | `3306` |
| `DB_NAME` | `kids_party_planner` |
| `DB_USER` | `root` |
| `DB_PASS` | Empty |

These are local-development defaults. Set a non-empty database password and update the configuration before deploying outside a local environment. Never publish production credentials.

### Application error logs

Error logs are written to `app.log` in a `kids-birthday-logs` directory under PHP's system temporary directory, outside the project web root. Ensure the web server can write to that temporary directory.

### Demo accounts

Both seeded accounts use the demo-only password `password`.

| Role | Username | Email |
|---|---|---|
| Administrator | `Admin` | `admin@gmail.com` |
| Parent | `Parent` | `parent@gmail.com` |

Change or remove these accounts before exposing an installation to a network. Parents can also register through the application.

## Main routes

The application uses the `r` query parameter and does not require URL rewriting.

| Route | Access | Purpose |
|---|---|---|
| `?r=home` | Public | Landing page and current planner totals |
| `?r=register`, `?r=login` | Public | Registration and sign-in |
| `?r=parties` | Signed in | View owned parties; administrators can view all |
| `?r=parties/create` | Signed in | Create a party |
| `?r=parties/view&id=1` | Party owner or administrator | View party details and bookings |
| `?r=vendors` | Public | Browse, filter, and search vendors |
| `?r=vendors/manage` | Administrator | Manage vendor records |
| `?r=bookings` | Signed in | View and manage owned bookings; administrators can view all |
| `?r=bookings/create` | Signed in | Request a vendor booking for a party |
| `?r=bookings/edit&id=1` | Booking owner or administrator | Update a booking; administrators can also change amount and status |
| `?r=users` | Administrator | Manage user records |
| `?r=profile` | Signed in | Update account details and password |
| `?r=api/vendors&q=cake&service=Cake` | Public | Search vendors and return JSON |

Create, update, and delete operations use POST requests protected by CSRF tokens.

## Access control

| Resource | Parent | Administrator |
|---|---|---|
| Users | Register; manage own profile | Manage users; cannot delete own account |
| Vendors | Browse | Create, update, and delete |
| Parties | Manage own parties | View and manage all parties |
| Bookings | Manage bookings for own parties | View all bookings; manage booking amount and status |

## Database design

The four-entity schema is organized in third normal form: account, vendor, party, and booking attributes are stored with their corresponding records. Foreign keys connect parties to users and bookings to parties and vendors. A unique `(party_id, vendor_id)` constraint prevents duplicate vendor bookings for a party. Each booking stores the amount agreed at booking time, preserving the booking price if a vendor's current price later changes.

## Project structure

```text
app/
  Controllers/   Request handling and validation
  Core/          Database, response, security, error, and logging helpers
  Models/        PDO data access
  Views/         HTML templates
  bootstrap.php  Session and class loading

assets/
  css/style.css
  images/        SVG illustrations and branding
  js/app.js

config/
  database.php

database/
  schema.sql
  seed.sql

tests/
  http_smoke.php   Database-backed HTTP smoke tests
  validation.php   Database-independent validation tests

index.php          Front controller and route table
TEST_PLAN.md       Manual test scenarios and verification record
```

## Tests and verification

Run the database-independent validation suite from the project root:

```powershell
C:\xampp\php\php.exe tests\validation.php
```

Run the database-backed HTTP smoke suite with Apache and MySQL running and the schema and seed imported:

```powershell
C:\xampp\php\php.exe tests\http_smoke.php
```

The HTTP suite creates temporary users, a party, a booking, and a vendor, then removes them. It uses but does not modify the seeded administrator. Run it against a local demo database and keep the database available until it finishes.

The latest local verification, recorded on 2026-11-20, used PHP 8.2.12 and MariaDB 10.4.32 on XAMPP:

- All 47 PHP files passed syntax checks.
- `assets/js/app.js` passed `node --check`.
- The schema and seed scripts ran successfully against the local MariaDB database.
- Validation suite: 22 passed, 0 failed.
- HTTP smoke suite: 64 passed, 0 failed.
- Browser check: the home page rendered and the mobile navigation opened at a narrow viewport.

See [TEST_PLAN.md](TEST_PLAN.md) for the manual test scenarios and the verification record. These results describe the local checks performed; they do not guarantee behavior in every hosting environment.

## Suggested live demonstration

The following sequence takes approximately 2–3 minutes:

1. Sign in as the demo parent and show the existing party and bookings.
2. Create and edit a party, then browse vendors and demonstrate live search and form validation.
3. Request a vendor booking and show its pending status.
4. Sign in as the administrator, update a booking status, edit a vendor, and show user management.
5. Attempt to access an administrator-only page as a parent and show that access is denied.

## Submission checklist

Complete any course-specific submission requirements separately from the code:

1. Record or present the suggested live demonstration.
2. Create and push the repository as `BIT-Y3-G1-224006067-OnlineKidsBirthday`.
3. Add `bemmy2005` as a repository collaborator.
4. Submit the repository URL and this summary on the LMS:

   “Kids Birthday is a responsive PHP/MySQL birthday-party planner with account registration, role-protected management of users, parties, vendors, and bookings, RWF budgeting, CSRF and output-escaping protections, and live JSON vendor search.”

