<?php
session_start();
$_SESSION['user_id'] = 12;
$_SESSION['firstName'] = 'kent jocel';
$_SESSION['lastName'] = 'lusdoc';
header("Location: ../views/staff/dashboard.php");
exit();
