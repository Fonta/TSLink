<?php

    // Disallow direct access to this file for security reasons.
    if (!defined('IN_LINKTS')) {
        die('Direct initialization of this file is not allowed.');
    }

    // Database configuration.
    $hostname = 'localhost';
    $username = 'database_username';
    $password = 'database_password';
    $database = 'database_name';

    // The MyBB users table in the DB. This should be just fine, but if you are also using this plugin from outside MyBB you'll have to enter it fully like in the second (disabled) line.
    $table = TABLE_PREFIX.'users';
    //$table = 'mybb_users';

    // Teamspeak connection.
    $ts3_server = 'ip_address_of_the_teamspeak_server';
    $ts3_server_port = '9987'; // 9987 is default
    $ts3_query_port = '10011'; // 10011 is default
    $ts3_username = 'query_user_username';
    $ts3_password = 'query_user_password';
    $ts3_nickname = 'what_name_should_be_displayed_in_teamspeak';

    // Teamspeak groups.
    $ts3_sgid_member = '11'; // the group id of the group which should be added to a user on teamspeak after they register on the forum
    $ts3_sgid_don_member = '19'; // the group id of the group which should be set when a user is a Donating member
    $ts3_sgid_vip_member = '35'; // the group id of the group which should be set when a user is a vip member

    // Define the servergroups the plugin shouldn't even try to remove.
    $ts3_sgid_dont_remove = ['6', '14', '10', '24', '25', '26', '27', '28', '31', '33'];

    // Define which groups should have access to the TS Link ModCP module.
    $tslink_modcp_groups = ('15');

    // DONT CHANGE ANYTHING UNDERNEATH!!!!
    // Piece of pie to determine which IP to work with.
    if (!isset($givenip)) {
        $givenip = '';
    }

    if ($givenip == '') {
        $givenip = $_SERVER['REMOTE_ADDR'];
        $mybb_ip = bin2hex(inet_pton($givenip));
    } else {
        $mybb_ip = bin2hex(inet_pton($givenip));
    }
