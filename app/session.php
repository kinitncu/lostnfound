<?php
$scheme = parse_url(APP_URL, PHP_URL_SCHEME) ?: 'http';
$secure = $scheme === 'https';

session_name('spcf_lnf_sid');
session_set_cookie_params([
  'lifetime' => 0,
  'path' => '/',
  'domain' => '',
  'secure' => $secure,
  'httponly' => true,
  'samesite' => 'Lax',
]);
session_start();