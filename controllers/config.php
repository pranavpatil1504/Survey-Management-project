<?php
function load_env() {
    $env_file = $_SERVER['DOCUMENT_ROOT'] . '/project/.env';

    if (!file_exists($env_file)) {
        throw new Exception('.env file not found');
    }

    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        $_ENV[$key] = $value;
    }
}
