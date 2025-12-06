<?php
require_once "app/auth.php";
logoutUser();
header("Location: index.php");
exit;
