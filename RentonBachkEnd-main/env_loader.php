<?php
/**
 * Minimal, dependency-free .env loader.
 *
 * Runs from index.php BEFORE CodeIgniter bootstraps (no Composer/BASEPATH
 * available yet), so config files under application/config/{ENVIRONMENT}/
 * can read secrets via getenv() instead of hardcoding them in tracked PHP.
 *
 * A real OS/server-level environment variable always wins over the .env
 * file, so hosting panels that set env vars natively keep working untouched.
 */
function renton_load_env($path)
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        $len = strlen($value);
        if ($len >= 2 && (($value[0] === '"' && $value[$len - 1] === '"') || ($value[0] === "'" && $value[$len - 1] === "'"))) {
            $value = substr($value, 1, -1);
        }

        if (getenv($key) === false) {
            putenv($key.'='.$value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
