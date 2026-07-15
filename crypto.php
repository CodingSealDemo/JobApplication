<?php
// crypto.php
// Helper functions for AES-256-CBC field encryption and decryption.

require_once 'config.php';

/**
 * Encrypts a plaintext string.
 * @param string $data Plaintext data.
 * @return string Base64 encoded payload including salt/IV and ciphertext.
 */
function encrypt_field($data) {
    if ($data === null || $data === '') {
        return '';
    }

    // Hash key to ensure it is exactly 32 bytes
    $key = hash('sha256', ENCRYPTION_KEY, true);

    // Generate a strong random IV
    $iv_length = openssl_cipher_iv_length(ENCRYPTION_METHOD);
    $iv = openssl_random_pseudo_bytes($iv_length);

    // Encrypt the data
    $ciphertext = openssl_encrypt($data, ENCRYPTION_METHOD, $key, OPENSSL_RAW_DATA, $iv);

    // Combine IV and ciphertext for storage, then base64 encode
    return base64_encode($iv . $ciphertext);
}

/**
 * Decrypts a base64 encoded ciphertext.
 * @param string $data Base64 encoded payload.
 * @return string Plaintext data or original data if decryption fails.
 */
function decrypt_field($data) {
    if ($data === null || $data === '') {
        return '';
    }

    $decoded = base64_decode($data, true);
    if ($decoded === false) {
        return $data; // Return original if not valid base64
    }

    $key = hash('sha256', ENCRYPTION_KEY, true);
    $iv_length = openssl_cipher_iv_length(ENCRYPTION_METHOD);

    if (strlen($decoded) < $iv_length) {
        return $data; // Malformed payload
    }

    $iv = substr($decoded, 0, $iv_length);
    $ciphertext = substr($decoded, $iv_length);

    $decrypted = openssl_decrypt($ciphertext, ENCRYPTION_METHOD, $key, OPENSSL_RAW_DATA, $iv);

    if ($decrypted === false) {
        return $data; // Fallback to original if decryption fails (e.g., if already plaintext)
    }

    return $decrypted;
}
