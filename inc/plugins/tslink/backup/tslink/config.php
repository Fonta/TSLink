<?php

// Disallow direct access to this file for security reasons.
if (!defined("IN_LINKTS"))
{
	die("Direct initialization of this file is not allowed.");
}

// Database configuration.
$hostname = "localhost";
$username = "bugadmin_mybb";
$password = "TNGrtu5B";
$database = 'bugadmin_mybb18';
$table = 'mybb_users';

// Teamspeak connection.
$ts3_server = "localhost";
$ts3_server_port = "9987";
$ts3_query_port = "10011";
$ts3_username = "BuGBot";
$ts3_password = "y3Z1eDTP";
$ts3_nickname = "ForumRegistrar";

// Teamspeak groups.
$ts3_sgid_member = "11"; // the group id of the group which should be added to a user on teamspeak after they register on the forum
$ts3_sgid_vip_member = "19"; // the group id of the group which should be set when a user is a vip member

// Define the servergroups the plugin shouldn't even try to remove.
$ts3_sgid_dont_remove =  array("6", "14", "10", "24", "25", "26", "27", "31");

// Define which groups should have access to the TS Link ModCP module.
$tslink_modcp_groups =("15");

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