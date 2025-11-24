<?php
// config-js.php
// KHÔNG có session_start() ở đây

$site_config = [
    'name' => '3 chàng lính ngự lâm',
    'apiUrl' => '/api/',
    'isLoggedIn' => isset($_SESSION['user_id']),
    'currentUser' => $_SESSION['username'] ?? '',
    'userType' => $_SESSION['user_type'] ?? '',
    'primaryColor' => '#d60000',
    'secondaryColor' => '#007bff'
];
?>
<script>
    window.SITE_CONFIG = <?php echo json_encode($site_config); ?>;
</script>