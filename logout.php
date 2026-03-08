<?php
session_start();

// Destroying all sessions
session_destroy();

// Redirecting to login page
header('Location: login.php');
exit;