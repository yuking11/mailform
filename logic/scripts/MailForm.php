<?php

/**
 * MailForm
 */
class MailForm
{

  const ITEMS = __DIR__ . '/../config/config_items.ini';
  public $items;

  function __construct()
  {
    $this->items = parse_ini_file(self::ITEMS, true);
  }

/**
 * esc html
 */
  private function _h($value)
  {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
  }

  /**
   * アイテムに値をセットする
   */
  public function setValue($form_item, $value)
  {
    foreach (array_keys($form_item) as $name) {
      if (isset($value[$name])) {
        if (is_array($value[$name])) {
          foreach ($value[$name] as $key => $item) {
            $form_item[$name]['value'][] = $this->_h($item);
          }
        } else {
          $form_item[$name]['value'] = $this->_h($value[$name]);
        }
      } else {
        $form_item[$name]['value'] = null;
      }
    }
    return $form_item;
  }

  /**
   * セッションに値をセットする
   */
  public function setSession($form_item, $value)
  {
    foreach (array_keys($form_item) as $name) {
      if (isset($value[$name])) {
        $_SESSION[$name] = $value[$name];
      }
    }
  }

  /**
   * セッションに値をセットする
   */
  public function deleteSession()
  {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
      );
    }
    session_destroy();
  }

  /**
   * トークンを生成してinput要素を返す
   */
  public function getToken()
  {
    if (!isset($_SESSION['_token'])) {
      $_SESSION['_token'] = $this->random(40);
    }
    return $this->_h($_SESSION['_token']);
  }

  /**
   * トークン削除
   */
  public function deleteToken()
  {
    if (isset($_SESSION['_token'])) {
      unset($_SESSION['_token']);
    }
  }

  /**
   * Generate a more truly "random" alpha-numeric string.
   *
   * @param  int  $length
   * @return string
   */
  private function random($length = 16)
  {
    $string = '';
    while (($len = strlen($string)) < $length) {
      $size = $length - $len;
      // $bytes = random_bytes($size);
      $bytes = bin2hex(openssl_random_pseudo_bytes(32));
      $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
    }
    return $string;
  }

  /**
   * バリデーション
   */
  public function validation($form_item)
  {
    $is_error = false;
    $errors = [];

    foreach ($form_item as $name => &$item) {

      // メールアドレス書式簡易チェック
      if ($item['type'] === 'email' && !$this->isValidEmail($item['value'], true) && $item['required']) {
        $is_error = true;
        $errors[$name] = '不正な' . $item['label'] . 'です。';
        continue;
      }

      // 最大文字数チェック
      if (isset($item['maxlength']) && mb_strlen($item['value']) > $item['maxlength']) {
        $is_error = true;
        $errors[$name] = $item['label'] . 'は' . $item['maxlength'] . '文字以内で入力してください。';
        continue;
      }

      // 最小文字数チェック
      // １文字以上入力された場合のみチェックする。必須にしたい場合は別途 required を指定する
      if (isset($item['minlength']) && strlen($item['value']) > 0 && mb_strlen($item['value']) < $item['minlength']) {
        $is_error = true;
        $errors[$name] = $item['label'] . 'は' . $item['minlength'] . '文字以上入力してください。';
        continue;
      }

      // 最大数値チェック
      if (isset($item['max_number']) && strlen($item['value']) > 0) {
        if (!is_numeric($item['value']) || $item['value'] > $item['max_number']) {
          $is_error = true;
          $errors[$name] = $item['label'] . 'は' . $item['max_number'] . '以内の値を入力してください。';
          continue;
        }
      }

      // 最小数値チェック
      if (isset($item['min_number']) && strlen($item['value']) > 0) {
        if (!is_numeric($item['value']) || $item['value'] < $item['min_number']) {
          $is_error = true;
          $errors[$name] = $item['label'] . 'は' . $item['max_number'] . '以上の値を入力してください。';
          continue;
        }
      }

      // 必須項目チェック
      if ($item['required'] && !$this->validateHasValue($item['value'])) {
        if ($item['type'] === 'select' || $item['type'] === 'radio') {
          $errors[$name] = $item['label'] . 'を選択してください。';
        } else if ($item['type'] === 'checkbox') {
          $errors[$name] = $item['label'] . 'を1つ以上選択してください。';
        } else {
          $errors[$name] = $item['label'] . 'を入力してください。';
        }
        $is_error = true;
        continue;
      }

      // 半角数字のみかどうかチェックする
      if (isset($item['numeric']) && strlen($item['value']) > 0 && !preg_match('/\A[0-9]*\z/u', $item['value'])) {
        $is_error = true;
        $errors[$name] = $item['label'] . 'は数値を入力してください。';
        continue;
      }

      // 電話番号かどうかチェックする
      if (isset($item['phone']) && strlen($item['value']) > 0 && !preg_match('/\A\d{2,5}-\d{1,4}-\d{4}\z/u', $item['value'])) {
        $is_error = true;
        $errors[$name] = '不正な' . $item['label'] . 'です。';
        continue;
      }

    }

    return $errors;
  }

  // メールアドレスバリデーション
  private function isValidEmail($email, $check_dns = false) {
    switch (true) {
      case !filter_var($email, FILTER_VALIDATE_EMAIL):
      case !preg_match('/@([\w.-]++)\z/', $email, $m):
        return false;
      case !$check_dns:
      case checkdnsrr($m[1], 'MX'):
      case checkdnsrr($m[1], 'A'):
      case checkdnsrr($m[1], 'AAAA'):
        return true;
      default:
        return false;
    }
  }

  /**
   * $valueが値（1文字以上の文字列）を持っているかどうかを確認する
   * @param mixed(string|array) $value
   * @return bool $exists
   */
  private function validateHasValue($value)
  {
    $exists = false;

    if (is_array($value)) {
      // 配列の場合は、各要素毎に確認する。
      // 要素があっても空文字の場合もあるので文字数を確認する。
      foreach ($value as $multiple_item_value) {
        if (strlen($multiple_item_value) !== 0) {
          $exists = true;
          break;
        }
      }
    } else {
      if (strlen($value) !== 0) {
        $exists = true;
      }
    }
    return $exists;
  }



  public function sendMail($form_item)
  {
    require_once(__DIR__ . '/qdmail.php');
    require_once(__DIR__ . '/qdsmtp.php');

    $this->deleteToken();

    $mail = new Qdmail();
    $mail->charset('UTF-8', 'base64');

    $value = $_SESSION;
    $mail_config = parse_ini_file(__DIR__ . '/../config/config.ini', true);

    // fromアドレス
    $from = array($mail_config['mail']['from'], $mail_config['mail']['from_name']);

    // Reply-toアドレス
    if (isset($mail_config['mail']['reply_to'])) {
      // 固定の Reply-to アドレスを指定
      $mail->replyto($mail_config['mail']['reply_to']);
    } else if (isset($mail_config['mail']['reply_to_item'])) {
            // フォーム入力値を Reply-to アドレスに指定
      $reply_to_item = $mail_config['mail']['reply_to_item'];
      $mail->replyto($value[$reply_to_item]);
    }

    // toアドレス
    $to = $mail_config['mail']['to'];
    $subject = $mail_config['mail']['subject'];
    $body = $this->replaceText($mail_config['mail']['body'], false, $form_item);

    // to / cc 設定
    $mail->to($this->multiAddress($to));
    if (isset($mail_config['mail']['cc'])) {
      $mail->cc($this->multiAddress($mail_config['mail']['cc']));
    }

    $mail->from($from);
    $mail->subject($subject);
    $mail->text($body);

    if ($mail->send()) {
      // 自動返信メール
      if (isset($mail_config['reply_mail']['to_item'])) {
        // CC / BCC / Reply-to をクリア
        $mail->cc = array();
        $mail->replyto = array();

        $to_item = $mail_config['reply_mail']['to_item'];
        $to = $form_item[$to_item]['value'];

        if (isset($mail_config['reply_mail']['from'])) {
          if (isset($mail_config['reply_mail']['from_name'])) {
            $from = array($mail_config['reply_mail']['from'], $mail_config['reply_mail']['from_name']);
          } else {
            $from = $mail_config['reply_mail']['from'];
          }
          $mail->from($from);
        }
        // Reply-toアドレス
        if (isset($mail_config['reply_mail']['reply_to'])) {
          // 固定の Reply-to アドレスを指定
          $mail->replyto($mail_config['reply_mail']['reply_to']);
        }
        if (isset($mail_config['reply_mail']['subject'])) {
          // サブジェクト取得
          $subject = $mail_config['reply_mail']['subject'];
        }
        $body = $mail_config['reply_mail']['body'];
        $body .= $this->replaceText($mail_config['mail']['body'], false, $form_item);

        $mail->to($to);
        $mail->subject($subject);
        $mail->text($body);
        $mail->send();
      }
      $this->deleteSession();
    } else {
      header('Location: /index.php');
      exit();
    }

  }

  /**
   * メール本文に入力値を置換する
   * @param string $str
   * @param boolean $subject_mode
   * @return string $str
   */
    private function replaceText($str, $subject_mode = false, $form_item)
    {
      foreach ($form_item as $name => $item) {
        if ($subject_mode && $item['type'] === 'textarea') {
          continue;
        }
        $value = $item['value'];
        if ($item['type'] === 'checkbox' && is_array($value)) {
          $value = implode('、', $value);
        } else if ($item['type'] === 'select' && is_array($value)) {
          $value = implode('、', $value);
        }
        if ($item['type'] === 'price') {
          $value = number_format($value);
        }
        $str = str_replace('{' . $name . '}', $value, $str);
      }
      return $str;
    }

  /**
   * 複数のtoアドレスを指定できるqdmail用の形式に変換する
   * @param string $config_value
   * @return array $address_list
   */
  private function multiAddress($config_value)
  {
    $address_list = array();
    foreach (explode(',', $config_value) as $value) {
      if (strlen($value) > 0) {
        $address_list[] = array(trim($value), '');
      }
    }
    return $address_list;
  }
}
