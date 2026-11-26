# RAFIS Dashboard (Laravel)

App-layer code for the seven prompts in `claude_laravel_prompts.pdf`:
migrations, models, dashboard controller + Blade/Tailwind view, factories + seeder,
form validation + routes, native Google Drive upload (replaces Make.com), and RBAC.

These files are meant to be dropped into a fresh Laravel project (11/12/13).
They were syntax-checked with `php -l` but **not booted or run**, because the
build environment could not reach Packagist to install Laravel. Run the test
suite first (step 5) and tell me about anything that fails.

## Setup

```bash
laravel new rafis        # or: composer create-project laravel/laravel rafis
cd rafis
composer require google/apiclient:^2
```

1. Copy everything from this folder over the new project (same paths).
   - `app/Models/User.php` **replaces** the default one.
   - `database/seeders/DatabaseSeeder.php` **replaces** the default one.
   - `routes/web.php` **replaces** the default one.
2. Add to `.env` (see "Google Drive" below):
   ```
   GOOGLE_DRIVE_CLIENT_ID=
   GOOGLE_DRIVE_CLIENT_SECRET=
   GOOGLE_DRIVE_REFRESH_TOKEN=
   GOOGLE_DRIVE_FOLDER_ID=
   ```
3. `php artisan migrate --seed`
4. `npm install && npm run dev` (Tailwind via Vite) and `php artisan serve`
5. `php artisan test`

## Seeded logins (password: `password`)

Login is by email. The seeded emails are **placeholders** (`@rafis.test`); each person
replaces theirs with their real email, and changes the password, on the **My account**
page (top bar) after first login.

| Role  | Email                 |
|-------|-----------------------|
| OIC   | capao@rafis.test     |
| Admin | admin@rafis.test      |
| Staff | benosa@rafis.test, tabuada@rafis.test, ajose@rafis.test, ... |

## Creating accounts

OIC and Admin see an **Accounts** button in the top bar. **+ New account** asks for name,
email, a temporary password and role, and creates (or links) the person's staff record.
An OIC can only create Staff accounts; an Admin can create Staff, OIC and Admin accounts.
New people change the password and email under **My account**.

OIC and Admin can also click **Edit** on an account to change the full name or role, or to
reset the password to a new temporary one (the person is signed out). Only OIC/Admin can do
this; staff can only change their own email and password. An OIC can only edit Staff
accounts, an Admin can edit anyone, and nobody can change their own role.

## Roles

| Role  | Can do |
|-------|--------|
| Staff | See only their own travel orders; upload completion files for them |
| OIC   | See all entries and the Accomplishment Summary; create registries; change status |
| Admin | Everything |

Rules live in `app/Policies/TravelOrderPolicy.php` (auto-discovered, nothing to register).
The UI hides actions with `@can`, and the controllers/form request enforce the same
rules server-side, so hiding a button is never the only protection.

## Google Drive

Uploads go through `app/Services/GoogleDriveUploader.php`, which calls the Drive API
directly via `google/apiclient` and names files `[TO Number] - [File Name]`
(e.g. `TO-2026-13104 - receipt.pdf`). I used the API client rather than a Flysystem
adapter because the upload needs Drive's `webViewLink` back to store in
`google_drive_link`, which adapters don't return cleanly.

One-time credentials:
1. In Google Cloud Console, enable the Drive API and create an OAuth client ID.
2. Generate a refresh token for the account that owns the shared folder
   (OAuth Playground with scope `https://www.googleapis.com/auth/drive.file`
   or `.../drive` if the folder was created by someone else).
3. Put the shared folder's ID (the last part of its URL) in `GOOGLE_DRIVE_FOLDER_ID`.

Connection timeouts, expired/invalid credentials and API errors are caught and shown
as a friendly message on the dashboard; details go to `storage/logs/laravel.log`.

## Design notes

- "Completed" on the progress bars = `SUBMITTED` **or** `APPROVED`, since the RBAC
  prompt adds an `APPROVED` status. Change `TravelOrderStatus::completed()` if you
  want SUBMITTED only.
- The personnel table is named `personnel` (Laravel wouldn't pluralise it).
- Staff accounts are linked to a personnel record through `users.personnel_id`.
- Uploading a file does not change the status; only an OIC/Admin does.
