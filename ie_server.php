#!/usr/bin/php

<?php
$url = 'https://ieserver.net/cgi-bin/dip.cgi';
$objects = array(
  array(
    'username',
    'domain',
    'password'
  )
);

//IPアドレスの取得
$ip = file_get_contents('http://ieserver.net/ipcheck.shtml');

//IPアドレスが変更されているか調べる
$cache = new memcached();
$cache->addServer('localhost', 11211);
if ($cache->get('DDNSupdater:IPaddress') != $ip){
  foreach ($objects as $line) {
    $curl = curl_init();
    $post = 'username=' . $line[0] . '&domain=' . $line[1] . '&password=' . $line[2] . '&updatehost=1';
    $options = array(
      CURLOPT_URL => $url,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_POST => 1,
      CURLOPT_POSTFIELDS => $post
    );
    curl_setopt_array($curl, $options);
    curl_exec($curl);
    curl_close($curl);
  }
  $cache->set('DDNSupdater:IPaddress', $ip);
}
?>
