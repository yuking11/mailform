<?php
require_once(__DIR__ . "/logic/scripts/load_confirm.php");

?><!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="format-detection" content="telephone=no">
  <title>お問い合わせ - 確認</title>
  <meta name="description" content="">
</head>
<body>

<h1>お問い合わせ - 確認</h1>

<form id="form" action="finish.php" method="post">

<input type="hidden" name="_token" value="<?php echo $mailform->getToken(); ?>">
<table>
<?php foreach ($form_item as $section => $item) : ?>
<tr>
  <th>
    <label for="<?php echo $section; ?>"><?php echo $item['label']; ?></label>
    <?php echo ($item['required']) ? '<span>必須</span>' : ''; ?>
  </th>
  <td>
  <?php if ($item['type'] === 'checkbox' && $section !== 'agree') : ?>
    <p>
      <?php echo (is_array($item['value'])) ? implode(', ', $item['value']) : $item['value']; ?>
    </p>
  <?php elseif ($item['type'] === 'textarea') : ?>
    <div>
      <?php echo nl2br($item['value']); ?>
    </div>
  <?php else : ?>
    <p>
      <?php echo $item['value']; ?>
    </p>
  <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
</table>

<div>
  <button type="submit" name="back">修正する</button>
  <button type="submit" name="submit">送 信</button>
</div>

</form>

</body>
</html>
