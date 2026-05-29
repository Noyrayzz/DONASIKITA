<?php
require_once '../config/db.php';
session_destroy();
go(APP_URL . '/auth/login.php');
