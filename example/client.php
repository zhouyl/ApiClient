<?php

$config = array(
    'gateway' => 'http://127.0.0.1',
    'headers' => array(
        'Host: api.fanqie88.com',
    ),
    'port' => 9433,
    'timeout' => 10,
);

$client = new \ZhouYL\Api\Client($config);

$uid = 12345;

// get user info
$userinfo = $client->get('user/get', array('uid' => $uid));

// set user profile
$userinfo = $client->post('user/profile', $profile + array('uid' => $uid));