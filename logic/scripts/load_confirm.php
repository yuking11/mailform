<?php
require_once(__DIR__ . '/load_common.php');

$errors = null;

/**
 * POSTされた時の処理
 */
if (isset($_POST['confirm']) && $_POST['_token'] === $_SESSION['_token']) {
  $value = $_POST;
  $form_item = $mailform->setValue($items, $value);
  $errors = $mailform->validation($form_item);

  if (count($errors) === 0) {
    $_SESSION['errors'] = [];
    $mailform->setSession($items, $value);
  } else {
    $_SESSION = $_POST;
    $_SESSION['errors'] = $errors;
    header('Location: /index.php');
    exit();
  }
} else {
  $mailform->deleteSession();
  header('Location: /index.php');
  exit();
}
