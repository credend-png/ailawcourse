<?php
require_once '../config.php';
session_destroy();
redirect(ADMIN_URL . '/login.php');
