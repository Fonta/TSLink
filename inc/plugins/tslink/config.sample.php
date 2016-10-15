<?php

// Disallow direct access to this file for security reasons.
if (!defined('IN_LINKTS')) {
    die('Direct initialization of this file is not allowed.');
}

// Database configuration.
$hostname = 'localhost';
$username = 'username';
$password = 'password';
$database = 'database';

if (!defined('TABLE_PREFIX')) {
    define('TABLE_PREFIX', mybb_);
}

// The MyBB users table in the DB. This should be just fine, but if you are also using this plugin from outside MyBB you'll have to enter it fully like in the second (disabled) line.
$table = TABLE_PREFIX.'users';
//$table = 'mybb_users';

// Teamspeak connection.
$ts3_server = 'localhost';
$ts3_server_port = '9987'; //default 9987
$ts3_query_port = '10011'; //default 10011
$ts3_username = 'serveradmin'; //shouldnt use server admin but if you want, you can.
$ts3_password = 'password';
$ts3_nickname = 'SomeNicknameOnTS';

// Define which groups should have access to the TS Link ModCP module.
$tslink_modcp_groups = ('15'); // MyBB group ID - Find it in AdminCP in MyBB!

// Groups underneath are servergroup ids in TS!!
// Teamspeak groups.
$ts3_sgid_member = '11'; // the group id of the group which should be added to a user on teamspeak after they register on the forum
$ts3_sgid_don_member = '19'; // the group id of the group which should be set when a user is a Donating member
$ts3_sgid_vip_member = '35'; // the group id of the group which should be set when a user is a vip member

// Define the servergroups the plugin shouldn't even try to remove.
$ts3_sgid_dont_remove = ['6', '14', '10', '33'];

// Should we log actions performed by the plugin?
// If set to true, make sure the log folder inside the tsling folder is writable!
$tslink_log = true; // set this option to true or false to enable/disable logging.

// DONT CHANGE ANYTHING UNDERNEATH!!!!
// Piece of pie to determine which IP to work with.
if (!isset($givenip)) {
    $givenip = '';
}

if ($givenip == '') {
    if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $givenip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } else {
        $givenip = $_SERVER['REMOTE_ADDR'];
    }
    $mybb_ip = bin2hex(inet_pton($givenip));
} else {
    $mybb_ip = bin2hex(inet_pton($givenip));
}
