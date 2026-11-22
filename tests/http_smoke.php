<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

/**
 * Dependency-free HTTP smoke test for Kids Birthday.
 *
 * Usage (from the project root):
 *   C:\xampp\php\php.exe tests\http_smoke.php
 *
 * Optional arguments:
 *   --base=...       Override the front-controller URL.
 *   --keep           Keep the disposable test account/data for inspection.
 *
 * The test creates one disposable parent account, one party, one booking and
 * one vendor, then deletes them. The seeded admin account is used only for
 * admin checks and is not modified.
 */

$options = getopt('', ['base::', 'keep']);
$base = rtrim($options['base'] ?? 'http://localhost/WEBB/Online-Kids-Birthday-Party-Planning-project/index.php', '?&');
$keep = array_key_exists('keep', $options);
$config = require __DIR__ . '/../config/database.php';
$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['host'],
    $config['port'],
    $config['database'],
    $config['charset']
);
$pdo = new PDO($dsn, $config['username'], $config['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$stamp = date('YmdHis') . bin2hex(random_bytes(3));
$email = 'smoke.' . $stamp . '@gmail.com';
$name = 'Smoke Parent ' . $stamp;
$password = 'Smoke-Start-123';
$newPassword = 'Smoke-Changed-456';
$userId = null;
$partyId = null;
$bookingId = null;
$tempVendorId = null;
$managedUserId = null;
$passes = 0;
$failures = 0;

final class Client
{
    private string $jar;
    public string $lastStatus = '0';
    public string $lastBody = '';
    public string $lastHeaders = '';

    public function __construct(private string $base)
    {
        $this->jar = tempnam(sys_get_temp_dir(), 'kidsbirthday_cookie_');
        if ($this->jar === false) {
            throw new RuntimeException('Could not create a cookie jar.');
        }
    }

    public function __destruct()
    {
        if (is_file($this->jar)) {
            @unlink($this->jar);
        }
    }

    public function get(string $route, array $query = []): self
    {
        return $this->request('GET', $route, $query, null);
    }

    public function post(string $route, array $data = []): self
    {
        return $this->request('POST', $route, [], $data);
    }

    private function request(string $method, string $route, array $query, ?array $data): self
    {
        $url = $this->base . '?r=' . rawurlencode($route);
        if ($query) {
            $url .= '&' . http_build_query($query);
        }
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_COOKIEJAR => $this->jar,
            CURLOPT_COOKIEFILE => $this->jar,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_USERAGENT => 'KidsBirthdaySmokeTest/1.0',
        ]);
        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data ?? []));
        }
        $raw = curl_exec($curl);
        if ($raw === false) {
            $message = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException('HTTP request failed: ' . $message);
        }
        $this->lastStatus = (string) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $this->lastHeaders = (string) substr($raw, 0, (int) curl_getinfo($curl, CURLINFO_HEADER_SIZE));
        $this->lastBody = (string) substr($raw, (int) curl_getinfo($curl, CURLINFO_HEADER_SIZE));
        curl_close($curl);
        return $this;
    }

    public function csrf(): string
    {
        if (preg_match('/name="_csrf" value="([a-f0-9]+)"/', $this->lastBody, $match)) {
            return $match[1];
        }
        return '';
    }

    public function location(): string
    {
        if (preg_match('/^Location:\s*(.+)$/mi', $this->lastHeaders, $match)) {
            return trim($match[1]);
        }
        return '';
    }
}

function check(string $name, bool $condition, string $detail = ''): void
{
    global $passes, $failures;
    if ($condition) {
        $passes++;
        echo "[PASS] {$name}\n";
        return;
    }
    $failures++;
    echo "[FAIL] {$name}" . ($detail !== '' ? " — {$detail}" : '') . "\n";
}

function findId(PDO $pdo, string $sql, array $parameters): ?int
{
    $statement = $pdo->prepare($sql);
    $statement->execute($parameters);
    $id = $statement->fetchColumn();
    return $id === false ? null : (int) $id;
}

$public = new Client($base);
$parent = new Client($base);
$oldLogin = new Client($base);
$admin = new Client($base);

try {
    // Public pages and the JSON endpoint.
    $public->get('home');
    check('Home page returns 200', $public->lastStatus === '200', 'status ' . $public->lastStatus);
    check(
        'Home page shows current vendor, party, and booking totals',
        str_contains($public->lastBody, 'aria-label="Planner totals"')
        && str_contains($public->lastBody, '<strong>' . (int) $pdo->query('SELECT COUNT(*) FROM vendors')->fetchColumn() . '</strong> vendors')
        && str_contains($public->lastBody, '<strong>' . (int) $pdo->query('SELECT COUNT(*) FROM parties')->fetchColumn() . '</strong> parties')
        && str_contains($public->lastBody, '<strong>' . (int) $pdo->query('SELECT COUNT(*) FROM bookings')->fetchColumn() . '</strong> bookings')
    );
    check(
        'Shared page shell has linked logo, semantic main, and footer',
        str_contains($public->lastBody, 'aria-label="Kids Birthday home"')
        && str_contains($public->lastBody, '<main class="main-content" id="main-content">')
        && str_contains($public->lastBody, '<footer class="site-footer">')
        && str_contains($public->lastBody, '<a href="index.php?r=home">Home</a>')
    );
    check('Security headers are present', str_contains($public->lastHeaders, "Content-Security-Policy:") && str_contains($public->lastHeaders, 'X-Content-Type-Options: nosniff'));
    check('X-Powered-By is removed', !str_contains($public->lastHeaders, 'X-Powered-By:'));

    $public->get('does-not-exist');
    check('Unknown route returns 404', $public->lastStatus === '404', 'status ' . $public->lastStatus);
    check('404 page is rendered', str_contains($public->lastBody, 'wandered off'));

    $public->get('vendors');
    check('Vendor directory renders', $public->lastStatus === '200' && str_contains($public->lastBody, 'Umunezero Cakes Kigali'));
    check('Fresh demo seed contains exactly four vendors', (int) $pdo->query('SELECT COUNT(*) FROM vendors')->fetchColumn() === 4);
    $requiredVendors = [
        ['Umunezero Cakes Kigali', 'Cake', 35000, 'umunezerocakes@gmail.com'],
        ['Imirasire Party Decor Kigali', 'Decorations', 45000, 'imirasiredecor@gmail.com'],
        ['Ibyishimo Kids Entertainment', 'Entertainment', 60000, 'ibyishimokids@gmail.com'],
        ['Kigali Little Moments Photography', 'Photography', 50000, 'kigalilittlemoments@gmail.com'],
    ];
    $vendorCheck = $pdo->prepare(
        'SELECT COUNT(*) FROM vendors WHERE name = ? AND service_type = ? AND price = ? AND contact_email = ?'
    );
    $seedVendorsMatch = true;
    foreach ($requiredVendors as $vendorRow) {
        $vendorCheck->execute($vendorRow);
        $seedVendorsMatch = $seedVendorsMatch && (int) $vendorCheck->fetchColumn() === 1;
    }
    check('All four Rwanda-context vendor names, categories, RWF prices, and emails match', $seedVendorsMatch);

    $public->get('api/vendors', ['q' => 'cake']);
    $payload = json_decode($public->lastBody, true);
    check('Vendor JSON search succeeds', $public->lastStatus === '200' && is_array($payload) && ($payload['ok'] ?? false) === true);
    $cakeVendors = $payload['data']['vendors'] ?? [];
    check('Vendor JSON search filters cake vendors', count($cakeVendors) >= 1 && count(array_filter($cakeVendors, static fn(array $v): bool => $v['service_type'] !== 'Cake')) === 0);

    $public->get('api/vendors', ['q' => ['cake']]);
    $payload = json_decode($public->lastBody, true);
    check('Array-shaped search input returns JSON 400', $public->lastStatus === '400' && ($payload['ok'] ?? true) === false);

    $allVendorCount = findId($pdo, 'SELECT COUNT(*) FROM vendors', []);
    $public->get('api/vendors', ['service' => 'NotAService']);
    $payload = json_decode($public->lastBody, true);
    check('Invalid service filter is ignored', $public->lastStatus === '200' && count($payload['data']['vendors'] ?? []) === $allVendorCount);

    // Registration and the parent flow.
    $parent->get('register');
    check('Registration page returns a CSRF token', $parent->csrf() !== '');
    $parent->post('auth/register', [
        '_csrf' => $parent->csrf(),
        'username' => $name,
        'email' => $email,
        'password' => $password,
    ]);
    check('Registration redirects to the home page', $parent->lastStatus === '303', 'status ' . $parent->lastStatus);
    $userId = findId($pdo, 'SELECT user_id FROM users WHERE email = ?', [$email]);
    $hashStatement = $pdo->prepare('SELECT password_hash FROM users WHERE user_id = ?');
    $hashStatement->execute([$userId]);
    check('Registered user is stored with a hash', $userId !== null && password_verify($password, (string) $hashStatement->fetchColumn()));

    $public->get('register');
    $public->post('auth/register', [
        '_csrf' => $public->csrf(),
        'username' => 'Long Password User',
        'email' => 'long.password.' . $stamp . '@gmail.com',
        'password' => str_repeat('p', 73),
    ]);
    check('Registration rejects passwords longer than 72 bytes', $public->lastStatus === '200' && str_contains($public->lastBody, 'between 8 and 72 bytes'));

    $public->get('register');
    $public->post('auth/register', [
        '_csrf' => $public->csrf(),
        'username' => $name,
        'email' => $email,
        'password' => $password,
    ]);
    check('Duplicate registration is rejected', $public->lastStatus === '200' && str_contains($public->lastBody, 'already uses that email'));

    $public->get('login');
    $public->post('auth/login', ['_csrf' => $public->csrf(), 'email' => $email, 'password' => 'definitely-wrong']);
    check('Invalid login is rejected', $public->lastStatus === '200' && str_contains($public->lastBody, 'Email or password is incorrect'));

    $parent->get('register');
    check('Signed-in user is redirected away from registration', $parent->lastStatus === '303');

    $parent->get('profile');
    check('Profile page loads for the parent', $parent->lastStatus === '200' && str_contains($parent->lastBody, 'profile/update'));
    $parent->post('profile/update', [
        '_csrf' => $parent->csrf(),
        'username' => $name,
        'email' => $email,
        'current_password' => $password,
        'new_password' => str_repeat('p', 73),
        'confirm_password' => str_repeat('p', 73),
    ]);
    check('Profile rejects passwords longer than 72 bytes', $parent->lastStatus === '200' && str_contains($parent->lastBody, 'between 8 and 72 bytes'));

    $parent->get('profile');
    $parent->post('profile/update', [
        '_csrf' => $parent->csrf(),
        'username' => $name . ' Updated',
        'email' => $email,
        'current_password' => $password,
        'new_password' => $newPassword,
        'confirm_password' => $newPassword,
    ]);
    check('Profile update redirects', $parent->lastStatus === '303', 'status ' . $parent->lastStatus);
    check('Profile name is updated', findId($pdo, 'SELECT COUNT(*) FROM users WHERE user_id = ? AND username = ?', [$userId, $name . ' Updated']) === 1);
    $parent->get('profile');
    $parent->post('logout', ['_csrf' => $parent->csrf()]);
    check('Parent can sign out', $parent->lastStatus === '303');
    $parent->get('login');
    $parent->post('auth/login', ['_csrf' => $parent->csrf(), 'email' => $email, 'password' => $newPassword]);
    check('Parent can sign in with the new password', $parent->lastStatus === '303', 'status ' . $parent->lastStatus);
    $oldLogin->get('login');
    $oldLogin->post('auth/login', ['_csrf' => $oldLogin->csrf(), 'email' => $email, 'password' => $password]);
    check('Old password no longer signs in', $oldLogin->lastStatus === '200' && str_contains($oldLogin->lastBody, 'Email or password is incorrect'));

    $parent->get('parties');
    check('Party list loads for the parent', $parent->lastStatus === '200' && str_contains($parent->lastBody, 'My parties'));
    $parent->get('parties/create');
    $parent->post('parties/store', [
        '_csrf' => $parent->csrf(),
        'child_name' => 'Smoke Child',
        'party_date' => date('Y-m-d', strtotime('-1 day')),
        'theme' => 'Validation test',
        'budget' => '1000',
    ]);
    check('Past party date is rejected', $parent->lastStatus === '200' && str_contains($parent->lastBody, 'cannot be in the past'));

    $parent->get('parties/create');
    $parent->post('parties/store', [
        '_csrf' => $parent->csrf(),
        'child_name' => 'Smoke Child',
        'party_date' => date('Y-m-d', strtotime('+5 days')),
        'theme' => 'Fractional budget test',
        'budget' => '1000.50',
    ]);
    check('Fractional RWF party budget is rejected', $parent->lastStatus === '200' && str_contains($parent->lastBody, 'whole RWF budget'));

    $partyDate = date('Y-m-d', strtotime('+45 days'));
    $parent->get('parties/create');
    $parent->post('parties/store', [
        '_csrf' => $parent->csrf(),
        'child_name' => 'Smoke Child',
        'party_date' => $partyDate,
        'theme' => 'Space explorers',
        'budget' => '500000',
    ]);
    check('Party creation redirects', $parent->lastStatus === '303', 'status ' . $parent->lastStatus);
    $partyId = findId($pdo, 'SELECT party_id FROM parties WHERE parent_id = ? ORDER BY party_id DESC LIMIT 1', [$userId]);
    check('Party is stored for the parent', $partyId !== null);

    $parent->get('parties/view', ['id' => (int) $partyId]);
    check('Party detail page shows the budget summary', $parent->lastStatus === '200' && str_contains($parent->lastBody, 'Booked so far'));

    $parent->get('parties/create');
    $parent->post('parties/store', [
        '_csrf' => $parent->csrf(),
        'child_name' => 'XSS Child',
        'party_date' => date('Y-m-d', strtotime('+50 days')),
        'theme' => '<script>alert(1)</script>',
        'budget' => '1000',
    ]);
    $xssPartyId = findId($pdo, 'SELECT party_id FROM parties WHERE parent_id = ? ORDER BY party_id DESC LIMIT 1', [$userId]);
    $parent->get('parties');
    check('Stored HTML is escaped in the party list', $xssPartyId !== null && str_contains($parent->lastBody, '&lt;script&gt;') && !str_contains($parent->lastBody, '<script>alert(1)</script>'));

    $parent->get('parties/edit', ['id' => (int) $partyId]);
    $parent->post('parties/update', [
        '_csrf' => $parent->csrf(),
        'party_id' => (int) $partyId,
        'child_name' => 'Smoke Child',
        'party_date' => $partyDate,
        'theme' => 'Space explorers updated',
        'budget' => '600000',
    ]);
    check('Party update redirects', $parent->lastStatus === '303');
    check('Party update persists', findId($pdo, 'SELECT COUNT(*) FROM parties WHERE party_id = ? AND theme = ? AND budget = 600000', [$partyId, 'Space explorers updated']) === 1);

    $parent->get('vendors');
    check('Vendor cards offer a booking action', str_contains($parent->lastBody, 'Book this vendor'));
    $vendorRows = $pdo->query('SELECT vendor_id, price FROM vendors ORDER BY vendor_id LIMIT 2')->fetchAll();
    $vendorId = (int) ($vendorRows[0]['vendor_id'] ?? 0);
    $vendorPrice = (int) ($vendorRows[0]['price'] ?? 0);
    $secondVendorId = (int) ($vendorRows[1]['vendor_id'] ?? 0);
    $secondVendorPrice = (int) ($vendorRows[1]['price'] ?? 0);

    $parent->get('bookings/create', ['party_id' => (int) $partyId, 'vendor_id' => (int) $vendorId]);
    check(
        'Booking form preselects the vendor',
        $parent->lastStatus === '200'
        && preg_match('/<option value="' . (int) $vendorId . '" selected>/', $parent->lastBody) === 1
    );
    $parent->post('bookings/store', [
        '_csrf' => $parent->csrf(),
        'party_id' => (int) $partyId,
        'vendor_id' => (int) $vendorId,
    ]);
    check('Booking creation redirects', $parent->lastStatus === '303', 'status ' . $parent->lastStatus);
    $bookingId = findId($pdo, 'SELECT booking_id FROM bookings WHERE party_id = ? ORDER BY booking_id DESC LIMIT 1', [$partyId]);
    check('Booking starts as pending with the vendor price', $bookingId !== null && findId($pdo, 'SELECT COUNT(*) FROM bookings WHERE booking_id = ? AND status = ? AND amount = ?', [$bookingId, 'pending', $vendorPrice]) === 1);

    $parent->get('bookings/edit', ['id' => (int) $bookingId]);
    check('Booking edit form loads', $parent->lastStatus === '200' && str_contains($parent->lastBody, 'Save booking'));
    $parent->post('bookings/update', [
        '_csrf' => $parent->csrf(),
        'booking_id' => (int) $bookingId,
        'party_id' => (int) $partyId,
        'vendor_id' => (int) $secondVendorId,
    ]);
    check('Parent can change a booking vendor', $parent->lastStatus === '303');
    check('Changed booking is pending with the new vendor price', findId($pdo, 'SELECT COUNT(*) FROM bookings WHERE booking_id = ? AND vendor_id = ? AND status = ? AND amount = ?', [$bookingId, $secondVendorId, 'pending', $secondVendorPrice]) === 1);

    $parent->get('bookings/create');
    $parent->post('bookings/store', [
        '_csrf' => $parent->csrf(),
        'party_id' => (int) $partyId,
        'vendor_id' => (int) $secondVendorId,
    ]);
    check('Duplicate booking is rejected', $parent->lastStatus === '200' && str_contains($parent->lastBody, 'already booked'));

    $parent->get('users');
    check('Parent receives 403 on the admin user list', $parent->lastStatus === '403' && str_contains($parent->lastBody, 'Access denied'));
    $parent->get('vendors/manage');
    check('Parent receives 403 on vendor management', $parent->lastStatus === '403' && str_contains($parent->lastBody, 'Access denied'));

    $parent->get('parties');
    $parent->post('parties/delete', ['_csrf' => 'wrong-token', 'party_id' => (int) $partyId]);
    check('Invalid CSRF token returns 419', $parent->lastStatus === '419');

    // Admin flow.
    $admin->get('login');
    $admin->post('auth/login', ['_csrf' => $admin->csrf(), 'email' => 'admin@gmail.com', 'password' => 'password']);
    check('Seeded admin can sign in', $admin->lastStatus === '303', 'status ' . $admin->lastStatus);
    $admin->get('bookings/edit', ['id' => (int) $bookingId]);
    $admin->post('bookings/update', [
        '_csrf' => $admin->csrf(),
        'booking_id' => (int) $bookingId,
        'party_id' => (int) $partyId,
        'vendor_id' => (int) $secondVendorId,
        'amount' => '1000.50',
        'status' => 'pending',
    ]);
    check('Fractional RWF booking amount is rejected', $admin->lastStatus === '200' && str_contains($admin->lastBody, 'whole RWF amount'));

    $admin->get('users');
    check('Admin user list loads', $admin->lastStatus === '200' && str_contains($admin->lastBody, 'Manage users'));
    $managedEmail = 'managed.' . $stamp . '@gmail.com';
    $admin->get('users/create');
    $admin->post('users/store', [
        '_csrf' => $admin->csrf(),
        'username' => 'Long Password Managed User',
        'email' => 'managed.long.' . $stamp . '@gmail.com',
        'password' => str_repeat('p', 73),
        'role' => 'parent',
    ]);
    check('Admin user creation rejects passwords longer than 72 bytes', $admin->lastStatus === '200' && str_contains($admin->lastBody, 'between 8 and 72 bytes'));

    $admin->get('users/create');
    $admin->post('users/store', [
        '_csrf' => $admin->csrf(),
        'username' => 'Smoke Managed User',
        'email' => $managedEmail,
        'password' => 'Smoke-Managed-789',
        'role' => 'parent',
    ]);
    check('Admin can create a user', $admin->lastStatus === '303');
    $managedUserId = findId($pdo, 'SELECT user_id FROM users WHERE email = ?', [$managedEmail]);
    $admin->get('users/edit', ['id' => (int) $managedUserId]);
    $admin->post('users/update', [
        '_csrf' => $admin->csrf(),
        'user_id' => (int) $managedUserId,
        'username' => 'Smoke Managed User Updated',
        'email' => $managedEmail,
        'password' => '',
        'role' => 'parent',
    ]);
    check('Admin can update a user', $admin->lastStatus === '303' && findId($pdo, 'SELECT COUNT(*) FROM users WHERE user_id = ? AND username = ?', [$managedUserId, 'Smoke Managed User Updated']) === 1);
    $admin->get('users');
    $admin->post('users/delete', ['_csrf' => $admin->csrf(), 'user_id' => (int) $managedUserId]);
    check('Admin can delete a user', $admin->lastStatus === '303' && findId($pdo, 'SELECT COUNT(*) FROM users WHERE user_id = ?', [$managedUserId]) === 0);
    $managedUserId = null;

    $admin->get('bookings');
    $admin->post('bookings/status', ['_csrf' => $admin->csrf(), 'booking_id' => (int) $bookingId, 'status' => 'confirmed']);
    check('Admin can update booking status', $admin->lastStatus === '303' && findId($pdo, 'SELECT COUNT(*) FROM bookings WHERE booking_id = ? AND status = ?', [$bookingId, 'confirmed']) === 1);

    $admin->get('vendors/create');
    $admin->post('vendors/store', [
        '_csrf' => $admin->csrf(),
        'name' => 'Smoke Test Vendor',
        'service_type' => 'Photography',
        'price' => '12345.50',
        'contact_email' => 'vendor.decimal.' . $stamp . '@gmail.com',
    ]);
    check('Fractional RWF vendor price is rejected', $admin->lastStatus === '200' && str_contains($admin->lastBody, 'whole RWF price'));

    $admin->get('vendors/create');
    $longEmail = str_repeat('a', 64) . '@' . str_repeat('b', 63) . '.' . str_repeat('c', 60) . '.com';
    $admin->post('vendors/store', [
        '_csrf' => $admin->csrf(),
        'name' => 'Long Email Test Vendor',
        'service_type' => 'Photography',
        'price' => '12345',
        'contact_email' => $longEmail,
    ]);
    check('Overlong vendor email is rejected', strlen($longEmail) > 190 && $admin->lastStatus === '200' && str_contains($admin->lastBody, 'valid contact email'));

    $admin->get('vendors/create');
    $admin->post('vendors/store', [
        '_csrf' => $admin->csrf(),
        'name' => 'Smoke Test Vendor',
        'service_type' => 'Photography',
        'price' => '12345',
        'contact_email' => 'vendor.' . $stamp . '@gmail.com',
    ]);
    check('Admin can create a vendor', $admin->lastStatus === '303');
    $tempVendorId = findId($pdo, 'SELECT vendor_id FROM vendors WHERE contact_email = ?', ['vendor.' . $stamp . '@gmail.com']);
    $admin->get('vendors/edit', ['id' => (int) $tempVendorId]);
    $admin->post('vendors/update', [
        '_csrf' => $admin->csrf(),
        'vendor_id' => (int) $tempVendorId,
        'name' => 'Smoke Test Vendor Updated',
        'service_type' => 'Photography',
        'price' => '13000',
        'contact_email' => 'vendor.' . $stamp . '@gmail.com',
    ]);
    check('Admin can update a vendor', $admin->lastStatus === '303' && findId($pdo, 'SELECT COUNT(*) FROM vendors WHERE vendor_id = ? AND price = 13000', [$tempVendorId]) === 1);
    $admin->get('vendors/manage');
    $admin->post('vendors/delete', ['_csrf' => $admin->csrf(), 'vendor_id' => (int) $tempVendorId]);
    check('Admin can delete a vendor', $admin->lastStatus === '303' && findId($pdo, 'SELECT COUNT(*) FROM vendors WHERE vendor_id = ?', [$tempVendorId]) === 0);
    $tempVendorId = null;

    $admin->get('users');
    $selfId = findId($pdo, 'SELECT user_id FROM users WHERE email = ?', ['admin@gmail.com']);
    $admin->post('users/delete', ['_csrf' => $admin->csrf(), 'user_id' => (int) $selfId]);
    check('Admin cannot delete their own account', $admin->lastStatus === '400');

    // Cleanup through the UI: delete the booking, party, then the account.
    $parent->get('bookings');
    $parent->post('bookings/delete', ['_csrf' => $parent->csrf(), 'booking_id' => (int) $bookingId]);
    check('Parent can delete a booking', $parent->lastStatus === '303' && findId($pdo, 'SELECT COUNT(*) FROM bookings WHERE booking_id = ?', [$bookingId]) === 0);
    $bookingId = null;

    $parent->get('parties/view', ['id' => (int) $partyId]);
    $parent->post('parties/delete', ['_csrf' => $parent->csrf(), 'party_id' => (int) $partyId]);
    check('Parent can delete a party', $parent->lastStatus === '303' && findId($pdo, 'SELECT COUNT(*) FROM parties WHERE party_id = ?', [$partyId]) === 0);
    $partyId = null;

    if ($keep) {
        echo "\nDisposable account left in place: {$email} / {$newPassword}\n";
    }
} finally {
    if (!$keep) {
        if ($bookingId !== null) {
            $pdo->prepare('DELETE FROM bookings WHERE booking_id = ?')->execute([$bookingId]);
        }
        if ($partyId !== null) {
            $pdo->prepare('DELETE FROM parties WHERE party_id = ?')->execute([$partyId]);
        }
        if ($userId !== null) {
            $pdo->prepare('DELETE FROM users WHERE user_id = ?')->execute([$userId]);
        }
        if ($managedUserId !== null) {
            $pdo->prepare('DELETE FROM users WHERE user_id = ?')->execute([$managedUserId]);
        }
        if ($tempVendorId !== null) {
            $pdo->prepare('DELETE FROM vendors WHERE vendor_id = ?')->execute([$tempVendorId]);
        }
    }
}

echo "\n{$passes} passed, {$failures} failed.\n";
exit($failures === 0 ? 0 : 1);
