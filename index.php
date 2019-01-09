<?php
require_once(__DIR__ . "/logic/scripts/load_input.php");

?><!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="format-detection" content="telephone=no">
  <title>お問い合わせ - 入力</title>
  <meta name="description" content="">
</head>
<body>

<h1>お問い合わせ - 入力</h1>

<form id="form" action="confirm.php" method="post">

<input type="hidden" name="_token" value="<?php echo $mailform->getToken(); ?>">
<table>
<?php foreach ($items as $section => $item) : ?>
<tr>
  <?php if ($section !== 'agree') : ?>
  <th>
    <label for="<?php echo $section; ?>"><?php echo $item['label']; ?></label>
    <?php echo ($item['required']) ? '<span>必須</span>' : ''; ?>
  </th>
  <?php endif; ?>
  <td>
  <?php if ($item['type'] === 'checkbox' && $section !== 'agree') : ?>
    <?php foreach (array_keys($item['detail']) as $index => $value) : ?>
    <p>
      <label>
        <input
          type="<?php echo $item['type']; ?>"
          name="<?php echo $section; echo ($item['multiple']) ? '[]' : ''; ?>"
          value="<?php echo $item['detail'][$value]; ?>"
          <?php echo (isset($old[$section]) && in_array($item['detail'][$value], $old[$section])) ? 'checked' : ''; ?>>
        <span><?php echo $item['detail'][$value]; ?></span>
      </label>
    </p>
    <?php endforeach; ?>
    <?php if($errors && isset($errors[$section])) : ?>
      <p><?php echo $errors[$section]; ?></p>
    <?php endif; ?>
  <?php elseif ($item['type'] === 'radio') : ?>
    <?php foreach (array_keys($item['detail']) as $index => $value) : ?>
    <p>
      <label>
        <input
          type="<?php echo $item['type']; ?>"
          name="<?php echo $section; ?>"
          value="<?php echo $item['detail'][$value]; ?>"
          <?php echo ($item['required'] && $index === 0) ? 'required' : ''; ?>
          <?php echo (isset($old[$section]) && $old[$section] === $item['detail'][$value]) ? 'checked' : ''; ?>>
        <span><?php echo $item['detail'][$value]; ?></span>
      </label>
    </p>
    <?php endforeach; ?>
    <?php if($errors && isset($errors[$section])) : ?>
      <p><?php echo $errors[$section]; ?></p>
    <?php endif; ?>
  <?php elseif ($item['type'] === 'select') : ?>
    <select
      id="<?php echo $section; ?>"
      name="<?php echo $section; ?>"
      <?php echo ($item['required']) ? 'required' : ''; ?>>
      <?php foreach (array_keys($item['option']) as $index => $value) : ?>
        <option
          value="<?php echo $item['option'][$value]; ?>"
          <?php
            if (isset($old[$section]) && $old[$section] === $item['option'][$value]) {
              echo 'selected';
            } elseif (($index + 1) == $item['selected']) {
              echo 'selected';
            }
          ?>><?php echo $item['option'][$value]; ?></option>
      <?php endforeach; ?>
    </select>
    <?php if($errors && isset($errors[$section])) : ?>
      <p><?php echo $errors[$section]; ?></p>
    <?php endif; ?>
  <?php elseif ($item['type'] === 'textarea') : ?>
    <textarea
      id="<?php echo $section; ?>"
      name="<?php echo $section; ?>"
      <?php echo ($item['required']) ? 'required' : ''; ?>><?php echo (isset($old[$section])) ? $old[$section] : ''; ?></textarea>
    <?php if($errors && isset($errors[$section])) : ?>
      <p><?php echo $errors[$section]; ?></p>
    <?php endif; ?>
  <?php elseif ($section !== 'agree') : ?>
    <input
      type="<?php echo $item['type']; ?>"
      id="<?php echo $section; ?>"
      name="<?php echo $section; ?>"
      value="<?php echo (isset($old[$section])) ? $old[$section] : ''; ?>"
      <?php echo ($item['required']) ? 'required' : ''; ?>>
    <?php if($errors && isset($errors[$section])) : ?>
      <p><?php echo $errors[$section]; ?></p>
    <?php endif; ?>
  <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
<?php if (isset($items['agree'])) : ?>
<tr>
  <td colspan="2">
    <label for="agree">
      <input
        type="checkbox"
        id="agree"
        name="agree"
        value="同意する"
        <?php echo (isset($old['agree']) && $old['agree'] === '同意する') ? 'checked' : ''; ?>>
      <span>利用規約に同意する</span>
    </label>
    <?php if($errors && isset($errors['agree'])) : ?>
      <p><?php echo $errors['agree']; ?></p>
    <?php endif; ?>
  </td>
</tr>
<?php endif; ?>
</table>

<div>
  <button type="submit" name="confirm">確 認</button>
</div>

</form>

</body>
</html>
