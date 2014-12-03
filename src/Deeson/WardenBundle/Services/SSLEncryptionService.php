<?php

/**
 * @file
 * Service used for encrypting and decrypting messages using OpenSSL.
 */

namespace Deeson\WardenBundle\Services;

use Deeson\WardenBundle\Exception\SSLEncryptionException;

class SSLEncryptionService {

  /**
   * @var string
   */
  protected $privateKey;

  /**
   * @var string
   */
  protected $publicKey;

  /**
   * @param string $publicKeyFile
   * @param string $privateKeyFile
   */
  public function __construct($publicKeyFile, $privateKeyFile) {
    $this->privateKeyFile = $privateKeyFile;
    $this->publicKeyFile = $publicKeyFile;
    $this->loadKeys();
  }

  /**
   * @throws \Exception
   */
  public function loadKeys() {
    if (file_exists($this->publicKeyFile)) {
      $this->publicKey = $this->loadKey($this->publicKeyFile);
      $this->privateKey = $this->loadKey($this->privateKeyFile);
    }
    else {
      $config = array(
        "digest_alg" => "sha512",
        "private_key_bits" => 4096,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
      );

      $res = openssl_pkey_new($config);
      openssl_pkey_export($res, $this->privateKey);
      $pubKey = openssl_pkey_get_details($res);
      $this->publicKey = $pubKey["key"];

      $this->saveKey($this->publicKeyFile, $this->publicKey);
      $this->saveKey($this->privateKeyFile, $this->privateKey);
    }
  }

  /**
   * @return string
   */
  public function getPublicKey() {
    return $this->publicKey;
  }

  /**
   * @param string $filename
   *   The file to write to
   * @param string $key
   *   The data to write to the file
   * @throws \Exception
   *   If the file cannot be written to
   */
  protected function saveKey($filename, $key) {
    if (file_put_contents($filename, $key) === FALSE) {
      throw new SSLEncryptionException('Unable to save security key to ' . $filename);
    }
  }

  /**
   * @param string $filename
   *   The file path to load a key from
   * @return string
   *   The contents of the file
   * @throws \Exception
   *   If the file cannot be read from
   */
  protected function loadKey($filename) {
    $key = file_get_contents($filename);
    if ($key === FALSE) {
      throw new SSLEncryptionException('Unable to read security key from file ' . $filename);
    }
    return $key;
  }

  /**
   * Encrypt a message with the public key.
   *
   * @param mixed $data
   *   The data to encrypt.
   *
   * @return string
   *   The encrypted text
   *
   * @throws SSLEncryptionException
   */
  public function encrypt($data) {
    $plaintext = json_encode($data);

    $public_key = $this->getPublicKey();

    $result = openssl_seal($plaintext, $message, $keys, array($public_key));

    if ($result === FALSE || empty($keys[0]) || empty($message) || $message === $plaintext) {
      throw new SSLEncryptionException('Unable to encrypt a message: ' . openssl_error_string());
    }

    $envelope = (object) array(
      'key' => base64_encode($keys[0]),
      'message' => base64_encode($message),
    );

    return base64_encode(json_encode($envelope));
  }

  /**
   * Decrypt a message with the private key
   *
   * @param string $cypherText
   *   The encrypted text
   * @return string
   *   The plain text
   *
   * @throws SSLEncryptionException
   */
  public function decrypt($cypherText) {
    $envelope = json_decode(base64_decode($cypherText));

    if (!is_object($envelope) || empty($envelope->key) || empty($envelope->message)) {
      throw new SSLEncryptionException('Encrypted message is not understood');
    }

    $key = base64_decode($envelope->key);
    $message = base64_decode($envelope->message);

    $decrypted = '';
    $result = openssl_open($message, $decrypted, $key, $this->privateKey);

    if ($result === FALSE) {
      throw new SSLEncryptionException('Unable to decrypt a message: ' . openssl_error_string());
    }

    return json_decode($decrypted);
  }

}