<?php
require_once(__DIR__ . '/load_common.php');

$errors = null;

// 結果
if (isset($_SESSION['errors'])) {
  $errors = $_SESSION['errors'];
}

/**
 * back
 */
$old = null;
$old = $_SESSION;
