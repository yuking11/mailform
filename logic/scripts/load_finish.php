<?php
require_once(__DIR__ . '/load_common.php');

/**
 * finish
 */
if (isset($_POST['submit']) && $_POST['_token'] === $_SESSION['_token']) {
  $value = $_SESSION;
  $form_item = $mailform->setValue($items, $value);
  $mailform->sendMail($form_item);
} elseif (isset($_POST['back'])) {
  // back
  header('Location: /index.php');
  exit();
} else {
  // error
  $mailform->deleteSession();
  header('Location: /index.php');
  exit();
}
