<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\Environment;
use App\Core\Validator;
use App\Models\User;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This command must be run from the command line.\n");
    exit(1);
}

$basePath = dirname(__DIR__);
require $basePath . '/app/Helpers/functions.php';
spl_autoload_register(static function (string $class) use ($basePath): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $file = $basePath . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

Environment::load($basePath . '/.env');
$databaseConfig = require $basePath . '/config/database.php';
$pdo = Database::connection($databaseConfig);
$users = new User($pdo);

if ($users->hasAdmin()) {
    fwrite(STDERR, "An Admin account already exists; no account was created.\n");
    exit(1);
}

$fullName = trim((string) readline('Admin full name: '));
$email = strtolower(trim((string) readline('Admin email: ')));
$phone = trim((string) readline('Admin phone: '));

if (!Validator::stringLength($fullName, 2, 100)
    || !Validator::email($email)
    || strlen($email) > 150
    || !preg_match('/\A\+?[0-9][0-9\s().-]{6,19}\z/', $phone)) {
    fwrite(STDERR, "Invalid name, email, or phone; no account was created.\n");
    exit(1);
}

$password = readHiddenPassword();
$confirmation = readHiddenPassword('Confirm Admin password: ');
if (strlen($password) < 12 || strlen($password) > 72 || !hash_equals($password, $confirmation)) {
    fwrite(STDERR, "Password must be 12-72 bytes and both entries must match; no account was created.\n");
    exit(1);
}

try {
    if ($users->hasAdmin()) {
        fwrite(STDERR, "An Admin account already exists; no account was created.\n");
        exit(1);
    }

    $users->createAdmin($fullName, $email, $phone, password_hash($password, PASSWORD_DEFAULT));
} catch (\PDOException $exception) {
    if ($exception->getCode() === '23000') {
        fwrite(STDERR, "The email is already registered; no account was created.\n");
        exit(1);
    }

    throw $exception;
}

fwrite(STDOUT, "Admin account created successfully.\n");

function readHiddenPassword(string $prompt = 'Admin password: '): string
{
    fwrite(STDOUT, $prompt);
    if (PHP_OS_FAMILY === 'Windows') {
        $command = 'powershell.exe -NoProfile -Command "$secure = Read-Host -AsSecureString; '
            . '$pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure); '
            . 'try { [Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer) } '
            . 'finally { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($pointer) }"';
        $lines = [];
        exec($command, $lines, $exitCode);
        fwrite(STDOUT, PHP_EOL);

        if ($exitCode !== 0 || !isset($lines[0])) {
            throw new RuntimeException('Unable to read a password from the secure console prompt.');
        }

        return $lines[0];
    }

    $sttyMode = shell_exec('stty -g 2>/dev/null');
    if (is_string($sttyMode) && trim($sttyMode) !== '') {
        shell_exec('stty -echo');
        try {
            $password = fgets(STDIN);
        } finally {
            shell_exec('stty ' . escapeshellarg(trim($sttyMode)));
            fwrite(STDOUT, PHP_EOL);
        }
    } else {
        $password = fgets(STDIN);
    }

    if (!is_string($password)) {
        throw new RuntimeException('Unable to read password input.');
    }

    return rtrim($password, "\r\n");
}
