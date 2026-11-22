<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../app/Core/helpers.php';
require __DIR__ . '/../app/Models/Vendor.php';

$longValidEmail = str_repeat('a', 64) . '@' . str_repeat('b', 63) . '.' . str_repeat('c', 60) . '.com';
$seedSql = file_get_contents(__DIR__ . '/../database/seed.sql');
if (is_string($seedSql)) {
    $seedSql = str_replace(["\r\n", "\r"], "\n", $seedSql);
}
$demoHashes = [];
if (is_string($seedSql)) {
    preg_match_all("/\\('[^']+', '(?:admin|parent)@gmail\\.com', '([^']+)', '(?:admin|parent)'\\)/", $seedSql, $hashMatches);
    $demoHashes = $hashMatches[1] ?? [];
}
$demoPasswordIsValid = count($demoHashes) === 2;
foreach ($demoHashes as $hash) {
    $demoPasswordIsValid = $demoPasswordIsValid && password_verify('password', $hash);
}
$expectedVendorSeeds = [
    "SELECT 'Umunezero Cakes Kigali', 'Cake', 35000, 'umunezerocakes@gmail.com'\nFROM DUAL\nWHERE NOT EXISTS",
    "SELECT 'Imirasire Party Decor Kigali', 'Decorations', 45000, 'imirasiredecor@gmail.com'\nFROM DUAL\nWHERE NOT EXISTS",
    "SELECT 'Ibyishimo Kids Entertainment', 'Entertainment', 60000, 'ibyishimokids@gmail.com'\nFROM DUAL\nWHERE NOT EXISTS",
    "SELECT 'Kigali Little Moments Photography', 'Photography', 50000, 'kigalilittlemoments@gmail.com'\nFROM DUAL\nWHERE NOT EXISTS",
];
$vendorSeedsMatch = is_string($seedSql)
    && count(array_filter($expectedVendorSeeds, static fn(string $row): bool => strpos($seedSql, $row) !== false)) === 4
    && Vendor::SERVICE_TYPES === ['Cake', 'Decorations', 'Entertainment', 'Photography'];
$originalPost = $_POST;
$originalGet = $_GET;
$_POST = ['record_id' => '00042', 'array_id' => ['42'], 'array_text' => ['unexpected']];
$validPostedId = post_int('record_id') === 42;
$arrayPostedIdRejected = post_int('array_id') === 0;
$arrayFormValueSafe = form_value('array_text') === '';
$_GET = ['search' => '  cake  ', 'array_search' => ['cake']];
$queryStringTrimmed = query_string('search') === 'cake';
try {
    query_string('array_search');
    $arrayQueryRejected = false;
} catch (HttpException $exception) {
    $arrayQueryRejected = $exception->getStatusCode() === 400;
}
$_POST = $originalPost;
$_GET = $originalGet;

$cases = [
    'accepts a standard email' => validate_email('parent@gmail.com'),
    'rejects an email longer than the database column' => strlen($longValidEmail) > 190 && !validate_email($longValidEmail),
    'seeded demo users can authenticate with the documented password' => $demoPasswordIsValid,
    'seed contains four Rwanda-context vendors with matching Gmail contacts' => $vendorSeedsMatch,
    'accepts an 8-byte password' => valid_password('12345678'),
    'accepts the maximum 72-byte password' => valid_password(str_repeat('p', 72)),
    'rejects a password shorter than 8 bytes' => !valid_password('1234567'),
    'rejects a password longer than 72 bytes' => !valid_password(str_repeat('p', 73)),
    'accepts a zero-padded numeric record ID' => $validPostedId,
    'rejects array-shaped record IDs' => $arrayPostedIdRejected,
    'renders array-shaped form input safely as empty' => $arrayFormValueSafe,
    'trims string query values' => $queryStringTrimmed,
    'rejects array-shaped query values with HTTP 400' => $arrayQueryRejected,
    'accepts a whole RWF value' => whole_rwf_amount('50000') === 50000.0,
    'accepts a whole RWF value with leading zeroes' => whole_rwf_amount('000050000') === 50000.0,
    'accepts a zero RWF value' => whole_rwf_amount('0') === 0.0,
    'accepts the maximum supported RWF value' => whole_rwf_amount('9999999') === 9999999.0,
    'rejects a fractional RWF value' => whole_rwf_amount('50000.50') === null,
    'rejects a negative RWF value' => whole_rwf_amount('-1') === null,
    'rejects an RWF value above the supported maximum' => whole_rwf_amount('10000000') === null,
    'rejects non-numeric RWF input' => whole_rwf_amount('fifty thousand') === null,
    'rejects scientific notation for RWF input' => whole_rwf_amount('5e4') === null,
];

$failures = 0;
foreach ($cases as $name => $passed) {
    echo ($passed ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL;
    if (!$passed) {
        $failures++;
    }
}

echo count($cases) - $failures . ' passed, ' . $failures . ' failed.' . PHP_EOL;
exit($failures === 0 ? 0 : 1);
