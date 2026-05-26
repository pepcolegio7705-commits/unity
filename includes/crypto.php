<?php
    define("SECRET_KEY", "JAWSISTEMAS1809"); // Usá una clave segura
    define("CIPHER_METHOD", "AES-128-ECB");

    function encriptarId($id) {
        return urlencode(openssl_encrypt($id, CIPHER_METHOD, SECRET_KEY));
    }

    function desencriptarId($token) {
        return intval(openssl_decrypt(urldecode($token), CIPHER_METHOD, SECRET_KEY));
    }