<?php
session_start();
if (isset($_SESSION['username'])) {
    header("Location: stats.php");
    exit();
} else {
    header("Location: login.php");
    exit();
}
