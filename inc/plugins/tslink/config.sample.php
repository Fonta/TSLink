<?php

    // Disallow direct access to this file for security reasons.
    if (!defined("IN_LINKTS"))
    {
        die("Direct initialization of this file is not allowed.");
    }

    // Database configuration.
    $hostname = "localhost";
    $username = "database_username";
    $password = "database_password";
    $database = 'database_name';
    $table = 'mybb_users'; // better not to change this setting

    // Teamspeak connection.
    $ts3_server = "ip_address_of_the_teamspeak_server";
    $ts3_server_port = "9987"; // 9987 is default
    $ts3_query_port = "10011"; // 10011 is default
    $ts3_username = "query_user_username";
    $ts3_password = "query_user_password";
    $ts3_nickname = "what_name_should_be_displayed_in_teamspeak";

    // Teamspeak groups.
    $ts3_sgid_member = "11"; // the group id of the group which should be added to a user on teamspeak after they register on the forum
    $ts3_sgid_vip_member = "19"; // the group id of the group which should be set when a user is a vip member

    // Define the servergroups the plugin shouldn't even try to remove.
    $ts3_sgid_dont_remove = array("6", "14", "10", "24", "18");

    // Define which groups should have access to the TS Link ModCP module.
    $tslink_modcp_groups =("4, 15");

    // Define the user's ip address.
    if ($givenip == '')
    {
        $givenip = $_SERVER['REMOTE_ADDR'];
        $mybb_ip = bin2hex(inet_pton($givenip));
    }
    else {
        $mybb_ip = bin2hex(inet_pton($givenip));	
    }

?>