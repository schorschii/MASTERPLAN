<?php

session_start();

if(!isset($_SESSION['mp_login'])) {
	redirectToLogin();
}
if(!isset($_SESSION['mp_installation']) || $_SESSION['mp_installation'] != dirname(__FILE__)) {
	error_log('auth error: mp installation not matching '.dirname(__FILE__));
	redirectToLogin();
}

function redirectToLogin() {
	header('Location: login.php');
	die();
}
