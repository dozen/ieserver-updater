#!/usr/bin/php

<?php
//Memcachedを利用する場合はtrueにしてください。
define(USE_MEMCACHED, false);
define(MEMCACHED_HOST, 'localhost');
define(MEMCACHED_PORT, 11211);

$objects = array(
    array(
        'username',
        'domain',
        'password'
    )
);

define(IESERVER_URL, 'https://ieserver.net/cgi-bin/dip.cgi');

//IPアドレスの取得
$ip = file_get_contents('http://ifconfig.me/ip');
$ip = trim($ip[0]);

//IPアドレスが変更されているか調べる
if (USE_MEMCACHED) {
  $cache = new memcached();
  $cache->addServer(MEMCACHED_HOST, MEMCACHED_PORT);
  $cachedip = $cache->get('ie_server:myipaddress');
} else {
  $cachedipfile = __DIR__ . 'myipaddress';
  if (file_exists($cachedipfile)) {
    $cachedip = file($cachedipfile);
    $cachedip = trim($cachedip[0]);
  } else {
    $cachedip = false;
  }
}
if ($cachedip != $ip) {
  foreach ($objects as $line) {
    $curl = curl_init();
    $post = 'username=' . $line[0] . '&domain=' . $line[1] . '&password=' . $line[2] . '&updatehost=1';
    $options = array(
        CURLOPT_URL => IESERVER_URL,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $post
    );
    curl_setopt_array($curl, $options);
    curl_exec($curl);
    curl_close($curl);
  }
  if (USE_MEMCACHED) {
    $cache->set('ie_server:myipaddress', $ip);
  } else {
    $fp = fopen($cachedipfile, 'w');
    fwrite($fp, $ip);
    fclose($fp);
  }
}
?>
