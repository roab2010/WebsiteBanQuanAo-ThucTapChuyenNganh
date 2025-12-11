<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
    

    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    

    if (!isset($_SESSION['created'])) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}
?>