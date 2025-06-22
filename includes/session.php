<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireUserType($allowed_types) {
    requireLogin();
    if (!in_array($_SESSION['user_type'], $allowed_types)) {
        header('Location: unauthorized.php');
        exit();
    }
}

function getUserInfo() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'user_type' => $_SESSION['user_type'],
        'full_name' => $_SESSION['full_name'] ?? $_SESSION['username']
    ];
}

function logout() {
    session_destroy();
    header('Location: index.php');
    exit();
}
?>