<?php
declare(strict_types=1);

namespace session;

/**
 * Session data getter
 */
function get(string $key): mixed
{
    init();

    return $_SESSION[$key] ?? null;
}

/**
 * Session data (un)setter
 */
function set(string $key, mixed $val): void
{
    init();

    if ($val === null) {
        unset($_SESSION[$key]);
    } else {
        $_SESSION[$key] = $val;
    }
}

/**
 * Initializes session
 */
function init(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.use_strict_mode', '1');
        session_start();

        if (!empty($_SESSION['_deleted']) && $_SESSION['_deleted'] < time() - 180) {
            session_destroy();
            session_start();
        }
    }
}

/**
 * Regenerates session ID
 */
function regenerate(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $id = session_create_id();
    $_SESSION['_deleted'] = time();
    session_commit();
    ini_set('session.use_strict_mode', '0');
    session_id($id);
    session_start();
}
