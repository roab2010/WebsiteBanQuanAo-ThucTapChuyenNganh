<?php
// session-manager.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Thiết lập bảo mật session
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    
    // Regenerate session ID để tránh session fixation
    if (!isset($_SESSION['created'])) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}
?>