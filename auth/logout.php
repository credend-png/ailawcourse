<?php
// auth/logout.php
require_once '../config.php';
session_destroy();
redirect(SITE_URL . '/auth/login.php?msg=loggedout');
