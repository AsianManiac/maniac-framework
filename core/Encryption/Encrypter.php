<?php

/**
 * Encryption service for the Maniac Framework.
 *
 * Provides methods to encrypt and decrypt data using AES-GCM ciphers (AES-128-GCM
 * or AES-256-GCM). The class ensures secure encryption with authentication tags
 * and proper key length validation.
 */

namespace Core\Encryption;

use Exception;
use RuntimeException;

class Encrypter
{
    protected string $key;
    protected string $cipher = 'aes-256-gcm'; // Default to AES-256-GCM

    /**
     * Initialize the encrypter with a key.
     *
     * @param string $key The encryption key (16 bytes for AES-128-GCM, 32 bytes for AES-256-GCM).
     * @throws RuntimeException If the key or cipher is invalid.
     */
    public function __construct(string $key)
    {
        if (static::supported($key, $this->cipher)) {
            $this->key = $key;
        } else {
            throw new RuntimeException('The only supported ciphers are AES-128-GCM and AES-256-GCM.');
        }
    }

    /**
     * Check if the key and cipher combination is valid.
     *
     * @param string $key The encryption key.
     * @param string $cipher The cipher algorithm.
     * @return bool True if the key length matches the cipher requirements.
     */
    public static function supported(string $key, string $cipher): bool
    {
        $length = mb_strlen($key, '8bit');
        $cipherAlgo = strtolower($cipher);

        if ($cipherAlgo === 'aes-128-gcm') return $length === 16;
        if ($cipherAlgo === 'aes-256-gcm') return $length === 32;

        return false;
    }

    /**
     * Encrypt a value.
     *
     * @param mixed $value The value to encrypt.
     * @param bool $serialize Whether to serialize the value before encryption.
     * @return string The base64-encoded encrypted payload (includes IV, value, and tag).
     * @throws RuntimeException If OpenSSL is not available or the cipher is unsupported.
     * @throws Exception If encryption or JSON encoding fails.
     */
    public function encrypt(mixed $value, bool $serialize = true): string
    {
        if (!function_exists('openssl_encrypt')) {
            throw new RuntimeException('OpenSSL PHP extension is required.');
        }
        if (!in_array($this->cipher, openssl_get_cipher_methods())) {
            throw new RuntimeException("Cipher algorithm '{$this->cipher}' is not supported by OpenSSL.");
        }

        $iv = random_bytes(openssl_cipher_iv_length($this->cipher));
        $valueToEncrypt = $serialize ? serialize($value) : (string) $value;

        // Use AEAD (GCM mode provides authentication automatically)
        $tag = '';
        $encrypted = openssl_encrypt(
            $valueToEncrypt,
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($encrypted === false) {
            throw new Exception('Could not encrypt the data.');
        }

        $jsonPayload = json_encode(['iv' => base64_encode($iv), 'value' => base64_encode($encrypted), 'tag' => base64_encode($tag)]);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Could not encode JSON payload.');
        }

        return base64_encode($jsonPayload);
    }

    /**
     * Decrypt a payload.
     *
     * @param string $payload The base64-encoded encrypted payload.
     * @param bool $unserialize Whether to unserialize the decrypted value.
     * @return mixed The decrypted value.
     * @throws RuntimeException If OpenSSL is not available.
     * @throws Exception If the payload is invalid or decryption fails.
     */
    public function decrypt(string $payload, bool $unserialize = true): mixed
    {
        if (!function_exists('openssl_decrypt')) {
            throw new RuntimeException('OpenSSL PHP extension is required.');
        }

        $payload = json_decode(base64_decode($payload), true);

        if (!$this->isValidPayload($payload)) {
            throw new Exception('The payload is invalid.');
        }

        $iv = base64_decode($payload['iv']);
        $tag = base64_decode($payload['tag']);
        $encryptedValue = base64_decode($payload['value']);

        $decrypted = openssl_decrypt(
            $encryptedValue,
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($decrypted === false) {
            throw new Exception('Could not decrypt the data (payload likely forged).');
        }

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }

    /**
     * Verify the encryption payload structure.
     *
     * @param mixed $payload The decoded JSON payload.
     * @return bool True if the payload is valid.
     */
    protected function isValidPayload(mixed $payload): bool
    {
        return is_array($payload) && isset($payload['iv'], $payload['value'], $payload['tag']) &&
            strlen(base64_decode($payload['iv'], true)) === openssl_cipher_iv_length($this->cipher);
    }
}
