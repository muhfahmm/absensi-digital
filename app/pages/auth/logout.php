<?php
// app/pages/auth/logout.php
require_once '../../functions/helpers.php';

session_start();
session_destroy();

redirect('app/pages/auth/login.php');
