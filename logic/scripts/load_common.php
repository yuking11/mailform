<?php
session_start();

header("Content-type: text/html; charset=utf-8");
//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

require_once(__DIR__ . '/MailForm.php');

$mailform = new MailForm();
$items = $mailform->items;
