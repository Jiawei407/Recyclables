# Role layout browser checks

These checks use a separate disposable database. The layout smoke test visits all 82 role routes at 320px and 1440px. The controls test submits real evidence, approves it and publishes an announcement. Never use an existing application database or run the HTTP mutation suite against the browser fixture database.

PHP with MySQLi/DOM/cURL, MySQL or MariaDB, Node and Playwright are needed for these developer checks. Playwright must be available through Node module resolution, or set `RECYCLABLES_PLAYWRIGHT_MODULE` to its module path. Use its installed Chromium, or point `RECYCLABLES_CHROME` to a local Chrome executable. For axe checks, set `RECYCLABLES_AXE_PATH` to an available `axe-core/axe.min.js`. These tools are test dependencies; the application needs no npm install or build step.

From the repository root in PowerShell, choose a unique new schema and an explicit test password:

```powershell
$php = 'C:\xampp\php\php.exe'
$env:RECYCLABLES_TEST_DB_NAME = 'recyclables_test_browser_unique_suffix'
$env:RECYCLABLES_TEST_DB_HOST = '127.0.0.1'
$env:RECYCLABLES_TEST_DB_PORT = '3306'
$env:RECYCLABLES_TEST_DB_USER = 'root'
$env:RECYCLABLES_TEST_DB_PASSWORD = ''
$env:RECYCLABLES_BROWSER_PASSWORD = 'BrowserTest123!'
& $php tests/prepare-database.php
& $php tests/browser/seed.php
```

The importer refuses an existing schema. The seeder requires the importer marker and refuses any nonempty application table. It creates the four test accounts and related records inside one transaction, with an exclusively created random private upload. It never clears existing records.

Start a private server in a second PowerShell window using the same database settings. The application settings have a separate prefix, so map them explicitly. Use an unused loopback port and an absolute session directory:

```powershell
foreach ($key in 'HOST','PORT','NAME','USER','PASSWORD') {
    [Environment]::SetEnvironmentVariable('RECYCLABLES_DB_' + $key,
        [Environment]::GetEnvironmentVariable('RECYCLABLES_TEST_DB_' + $key))
}
$env:RECYCLABLES_TEST_SERVER_TOKEN = [guid]::NewGuid().ToString('N')
New-Item -ItemType Directory -Force tests/.runtime/browser-sessions | Out-Null
$sessionPath = (Resolve-Path tests/.runtime/browser-sessions).Path
& $php -d "session.save_path=$sessionPath" -d upload_max_filesize=256M -d post_max_size=300M -S 127.0.0.1:8132 -t . tests/Support/server-router.php
```

Set the same test variables in that second window before starting it; environment changes in one PowerShell window do not automatically appear in another. Keep its token for the handshake below. The supplied router blocks test/tool paths and serves `/__test_health` only in a test server with a token.

In the first window, verify the server database and token before running either script:

```powershell
$env:RECYCLABLES_BROWSER_URL = 'http://127.0.0.1:8132'
$expectedToken = '<token from the private server window>'
$health = Invoke-RestMethod "$env:RECYCLABLES_BROWSER_URL/__test_health"
if ($health.database -ne $env:RECYCLABLES_TEST_DB_NAME -or $health.token -ne $expectedToken) {
    throw 'Wrong test server or database'
}
$env:RECYCLABLES_TEST_SERVER_TOKEN = $expectedToken
node tests/browser/role-layout.cjs
node tests/browser/role-layout-controls.cjs
```

Fixture emails are `tp810001@mail.apu.edu.my`, `faculty@mail.apu.edu.my`, `coordinator@mail.apu.edu.my` and `admin@mail.apu.edu.my`; all use the explicit fixture password. Do not import demo accounts. Reports and screenshots are written to ignored `tests/.runtime/`. The controls test mutates fixtures and creates another private upload; use a fresh disposable schema for a clean repeat. Stop the owned private PHP server after testing. Remove only uploads referenced by the owned disposable schema before retiring it; do not delete unrelated files from `storage/uploads`.
