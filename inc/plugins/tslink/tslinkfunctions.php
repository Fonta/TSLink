<?php

    // Disallow direct access to this file for security reasons.
    if (!defined('IN_LINKTS')) {
        die('Direct initialization of this file is not allowed.');
    }

    // Include the Teamspeak Framework.
    require_once 'Teamspeak3/TeamSpeak3.php';

    function simple_array_intersect($a, $b)
    {
        $a_assoc = $a != array_values($a);
        $b_assoc = $b != array_values($b);
        $ak = $a_assoc ? array_keys($a) : $a;
        $bk = $b_assoc ? array_keys($b) : $b;
        $out = [];
        for ($i = 0; $i < count($ak); $i++) {
            if (in_array($ak[$i], $bk)) {
                if ($a_assoc) {
                    $out[$ak[$i]] = $a[$ak[$i]];
                } else {
                    $out[] = $ak[$i];
                }
            }
        }

        return $out;
    }

    function tslink_global()
    {
        global $mybb, $lang, $templatelist;

        if ($templatelist) {
            $templatelist = explode(',', $templatelist);
        }
        // Fixes common warnings (due to $templatelist being void).
        else {
            $templatelist = [];
        }

        if (THIS_SCRIPT == 'usercp.php') {
            $templatelist[] = 'tslink_usercp_menu';
        }

        if (THIS_SCRIPT == 'usercp.php' and $mybb->input['action'] == 'tslink') {
            $templatelist[] = 'tslink_usercp_settings';
        }

        $templatelist = implode(',', array_filter($templatelist));

        $lang->load('tslink');
    }

    function tslink_admin_user_menu(&$sub_menu)
    {
        global $lang;
        $lang->load('tslink');
        $sub_menu[] = ['id' => 'tslink', 'title' => $lang->tslink_plugin_name, 'link' => 'index.php?module=user-tslink'];

        return $sub_menu;
    }

    function tslink_admin_user_action_handler(&$actions)
    {
        $actions['tslink'] = ['active' => 'tslink', 'file' => 'tslink'];
    }

    function tslink_admin()
    {
        global $db, $lang, $mybb, $page, $run_module, $action_file, $plugins, $cache;

        $lang->load('tslink');

        if ($run_module == 'user' && $action_file == 'tslink') {
            $page->add_breadcrumb_item($lang->tslink_plugin_name, 'index.php?module=tslink');

            if ($mybb->input['action'] == 'tslink_changestatus') {
                $uid = intval($mybb->input['uid']);
                $changeto = intval($mybb->input['changeto']);

                $db->query('UPDATE '.TABLE_PREFIX."users SET memberstatus= '".$changeto."' WHERE uid='".$uid."'");

                $queryip = $db->simple_select('users', 'lastip', "uid='$uid'");
                $bin_ip_in_db = $db->fetch_field($queryip, 'lastip');
                $givenip = my_inet_ntop($db->unescape_binary($bin_ip_in_db));

                // If there's an ip of the user in de database, update the unique id's in the database
                if (!empty($givenip)) {
                    tslink_update_uids($givenip);
                    $message = $lang->tslink_status_changed;
                }
                // If there's no lastip of the user in the database - dont try to update the unique id's in the database
                if (empty($givenip)) {
                    tslink_update_groups($uid);
                    $message = $lang->tslink_status_changed;
                }

                admin_redirect('index.php?module=user-tslink');
            }

            if (!$mybb->input['action']) {
                $page->output_header($lang->tslink);

                $sub_tabs['tslink'] = [
                    'title'       => $lang->tslink_tab_home,
                    'link'        => 'index.php?module=user-tslink',
                    'description' => $lang->tslink_admin_tab_home_desc,
                ];

                $page->output_nav_tabs($sub_tabs, 'tslink');

                $form = new Form('index.php?module=user-tslink', 'post');

                $form_container = new FormContainer($lang->tslink_admin_table_heading_users);
                $form_container->output_row_header($lang->tslink_admin_row_username, ['class' => 'align_left', width => '50%']);
                $form_container->output_row_header($lang->tslink_admin_row_status, ['class' => 'align_center']);
                $form_container->output_row_header($lang->tslink_admin_row_options, ['class' => 'align_center']);
                $form_container->output_row_header($lang->tslink_admin_row_options, ['class' => 'align_center']);

                $query = $db->simple_select('users', 'uid, username, memberstatus', '', ['order_by' => 'username', 'order_dir' => 'ASC']);

                while ($users = $db->fetch_array($query)) {
                    $form_container->output_cell("<div style=\"\"><strong>{$users['username']}</strong></div>");

                    if ($users['memberstatus'] == '0') {
                        $form_container->output_cell('<div style=""><strong>Member</strong></div>', ['class' => 'align_center']);
                        $form_container->output_cell("<a href=\"index.php?module=user-tslink&amp;action=tslink_changestatus&amp;uid={$users['uid']}&amp;changeto=1\">{$lang->tslink_admin_row_changestatus1}</a>", ['class' => 'align_center']);
                        $form_container->output_cell("<a href=\"index.php?module=user-tslink&amp;action=tslink_changestatus&amp;uid={$users['uid']}&amp;changeto=2\">{$lang->tslink_admin_row_changestatus2}</a>", ['class' => 'align_center']);
                    } elseif ($users['memberstatus'] == '1') {
                        $form_container->output_cell('<div style=""><strong>Donating Member</strong></div>', ['class' => 'align_center']);
                        $form_container->output_cell("<a href=\"index.php?module=user-tslink&amp;action=tslink_changestatus&amp;uid={$users['uid']}&amp;changeto=0\">{$lang->tslink_admin_row_changestatus0}</a>", ['class' => 'align_center']);
                        $form_container->output_cell("<a href=\"index.php?module=user-tslink&amp;action=tslink_changestatus&amp;uid={$users['uid']}&amp;changeto=2\">{$lang->tslink_admin_row_changestatus2}</a>", ['class' => 'align_center']);
                    } elseif ($users['memberstatus'] == '2') {
                        $form_container->output_cell('<div style=""><strong>VIP Member</strong></div>', ['class' => 'align_center']);
                        $form_container->output_cell("<a href=\"index.php?module=user-tslink&amp;action=tslink_changestatus&amp;uid={$users['uid']}&amp;changeto=0\">{$lang->tslink_admin_row_changestatus0}</a>", ['class' => 'align_center']);
                        $form_container->output_cell("<a href=\"index.php?module=user-tslink&amp;action=tslink_changestatus&amp;uid={$users['uid']}&amp;changeto=1\">{$lang->tslink_admin_row_changestatus1}</a>", ['class' => 'align_center']);
                    }

                    $form_container->construct_row();
                }

                $form_container->end();
                $form->end();

                $page->output_footer();
            }
        }
    }

    function tslink_modcp()
    {
        global $db, $mybb, $lang, $templates, $theme, $headerinclude, $header, $footer, $modcp_nav, $multipage;

        require __DIR__.'/config.php';

        $tslink_modcp_access = explode(',', $tslink_modcp_groups);
        $mybb_user_groups = explode(',', $mybb->user['additionalgroups']);

        if (simple_array_intersect($tslink_modcp_access, $mybb_user_groups) || $mybb->usergroup['cancp'] == 1) {
            eval('$tslink_modcp_menu_template = "'.$templates->get('tslink_modcp_menu').'";');
            $modcp_nav = str_replace('<!-- tslink -->', $tslink_modcp_menu_template, $modcp_nav);
        }

        if ($mybb->input['action'] == 'tslink_dochange') {
            $mybb_uid = intval($mybb->input['uid']);
            $changeto = intval($mybb->input['changeto']);

            $db->query('UPDATE '.TABLE_PREFIX."users SET memberstatus= '".$changeto."' WHERE uid='".$mybb_uid."'");

            $queryip = $db->simple_select('users', 'lastip', "uid='$mybb_uid'");
            $bin_ip_in_db = $db->fetch_field($queryip, 'lastip');
            $givenip = my_inet_ntop($db->unescape_binary($bin_ip_in_db));

            // If there's an ip of the user in de database, update the unique id's in the database
            if (!empty($givenip)) {
                tslink_update_uids($givenip);
                $message = $lang->tslink_status_changed;
            }
            // If there's no lastip of the user in the database - dont try to update the unique id's in the database
            if (empty($givenip)) {
                tslink_update_groups($mybb_uid);
                $message = $lang->tslink_status_changed;
            }

            redirect('modcp.php?action=tslink', $message);
        }

        if ($mybb->input['action'] == 'tslink') {
            add_breadcrumb($lang->nav_modcp, 'modcp.php');
            add_breadcrumb($lang->tslink_title, 'modcp.php?action=tslink');

            global $db, $mybb, $lang, $templates, $theme, $headerinclude, $header, $footer, $modcp_nav, $multipage;

            $query = $db->simple_select('users', 'uid, username, memberstatus', '', ['order_by' => 'username', 'order_dir' => 'ASC']);

            while ($users = $db->fetch_array($query)) {
                $alt_bg = alt_trow();
                $user['username'] = build_profile_link($users['username'], $users['uid']);

                if ($users['memberstatus'] == '0') {
                    $status = 'Member';
                    $linktochange = '<a href="modcp.php?action=tslink_dochange&amp;uid='.$users['uid'].'&amp;changeto=1">'.$lang->tslink_modcp_changestatus1.' </a>-
								 		<a href="modcp.php?action=tslink_dochange&amp;uid='.$users['uid'].'&amp;changeto=2"> '.$lang->tslink_modcp_changestatus2.'</a>
										';
                } elseif ($users['memberstatus'] == '1') {
                    $status = 'Donating Member';
                    $linktochange = '<a href="modcp.php?action=tslink_dochange&amp;uid='.$users['uid'].'&amp;changeto=0">'.$lang->tslink_modcp_changestatus0.' </a>-
								 		<a href="modcp.php?action=tslink_dochange&amp;uid='.$users['uid'].'&amp;changeto=2"> '.$lang->tslink_modcp_changestatus2.'</a>
										';
                } elseif ($users['memberstatus'] == '2') {
                    $status = 'VIP Member';
                    $linktochange = '<a href="modcp.php?action=tslink_dochange&amp;uid='.$users['uid'].'&amp;changeto=0">'.$lang->tslink_modcp_changestatus0.' </a>-
								 		<a href="modcp.php?action=tslink_dochange&amp;uid='.$users['uid'].'&amp;changeto=1"> '.$lang->tslink_modcp_changestatus1.'</a>
										';
                }

                eval('$tslink_rows .= "'.$templates->get('tslink_modcp_row').'";');
            }

            eval('$content = "'.$templates->get('tslink_modcp_page_template').'";');
            output_page($content);
        }
    }

    function tslink_usercp_menu()
    {
        global $mybb, $templates, $theme, $usercpmenu, $lang, $collapsed, $collapsedimg;

        eval('$usercpmenu .= "'.$templates->get('tslink_usercp_menu').'";');
    }

    function tslink_usercp()
    {
        global $mybb, $lang, $inlinesuccess;

        // Execute the funtion to add the user to his servergroup.
        if ($mybb->input['action'] == 'tslink' and $mybb->request_method == 'post') {
            tslink_update_uids($givenip);
        }

        // Settings page.
        if ($mybb->input['action'] == 'tslink') {
            add_breadcrumb($lang->nav_usercp, 'usercp.php');
            add_breadcrumb($lang->tslink_title, 'user.php?action=tslink');

            global $db, $theme, $templates, $headerinclude, $header, $footer, $plugins, $usercpnav;

            eval('$content = "'.$templates->get('tslink_usercp_settings').'";');
            output_page($content);
        }
    }

    function tslink_update_uids($givenip)
    {
        require __DIR__.'/config.php';

        // Connect to the database.
        $ConnectDB = new mysqli($hostname, $username, $password, $database);

        // check connection
        if ($ConnectDB->connect_errno) {
            die($ConnectDB->connect_error);
        }

        // Get the member from the mybb database.
        $mybb_user_query = "SELECT * FROM $table WHERE HEX(lastip) = '$mybb_ip' LIMIT 1";
        $mybb_users = $ConnectDB->query($mybb_user_query) or trigger_error($ConnectDB->error."[$mybb_user_query]");
        $mybb_user = $mybb_users->fetch_array(MYSQLI_ASSOC);

        // Get the memberstatus from the user.
        $mybb_uid = $ConnectDB->real_escape_string($mybb_user['uid']);

        // Connect to teamspeak server, authenticate and spawn an object for the virtual server on the server port.
        $ts3_VirtualServer = TeamSpeak3::factory("serverquery://$ts3_username:$ts3_password@$ts3_server:$ts3_query_port/?server_port=$ts3_server_port&nickname=tslink_bot");

        // Get the users from the teamspeak database.
        // Define how many records we want to query at once.
        // The maximum amount of records TeamSpeak will reply is 200.
        $maxaantalperque = 200;

        // Get the total amount of entries in the database.
        $DBClientEntries = $ts3_VirtualServer->execute('clientdblist -count', ['duration' => 1])->toList('count');

        // Calculate how many times we have to do a query until we have all entries from the teamspeak database.
        $aantalqueries = $DBClientEntries['count'] / $maxaantalperque;
        $aantalqueries = ceil($aantalqueries);

        // Query the teamspeak database as many times as needed.
        $i = 1;
        while ($i <= $aantalqueries) {
            if ($i == 1) {
                $maxaantalvorige = 0;
            }
            $maxaantaldezeque = $i * $maxaantalperque;
            try {
                $ClientArrays[$i] = $ts3_VirtualServer->execute('clientdblist', ['start' => $maxaantalvorige, 'duration' => $maxaantaldezeque])->toAssocArray('cldbid');
            } catch (Exception $e) {
                // Catches the error(s) if any. But don't do anything with it.
            }
            $maxaantalvorige = $maxaantaldezeque + 1;
            $i++;
        }

        // Lets see if we can find the user in the teamspeak database.
        foreach ($ClientArrays as $Clients) {
            foreach ($Clients as $ts3_Client) {
                // Check if the user's ip address is known in the teamspeak database.
                if ($ts3_Client['client_lastip'] == $givenip) {
                    try {
                        // Put the user's client unique identifier and database id into the database for later usage.
                        $ts_uid = $ConnectDB->real_escape_string($ts3_Client['client_unique_identifier']);
                        $ts_cldbid = $ConnectDB->real_escape_string($ts3_Client['cldbid']);
                        mysqli_query($ConnectDB, 'INSERT INTO '.TABLE_PREFIX."tslink_uids (`uid`, `ts_uid`, `ts_cldbid`) VALUES ('".$mybb_uid."', '".$ts_uid."', '".$ts_cldbid."')");
                    } catch (Exception $e) {
                        echo $e;
                        die();

                        // Catches the error(s) if any. But don't do anything with it.
                    }
                }
            }
        }

        // Close connection
        $ConnectDB->close();

        // Now we finally have all unique id's for this user's ip, let's update his groups
        tslink_update_groups($mybb_uid);
    }

    function tslink_update_groups($mybb_uid)
    {
        require __DIR__.'/config.php';

        // Connect to the database.
        $ConnectDB = new mysqli($hostname, $username, $password, $database);

        // check connection
        if ($ConnectDB->connect_errno) {
            die($ConnectDB->connect_error);
        }

        // Get the member from the mybb database.
        $getit = "SELECT * FROM $table WHERE uid = '$mybb_uid' LIMIT 1";
        $rows = $ConnectDB->query($getit);
        $row = $rows->fetch_array(MYSQLI_ASSOC);

        // Get the memberstatus from the user.
        $memberstatus = $row['memberstatus'];

        // Let's determine which servergroup to use according to the status of the user.
        if ($memberstatus == '2') {
            $ServerGroupID_ToAdd = $ts3_sgid_vip_member;
        } elseif ($memberstatus == '1') {
            $ServerGroupID_ToAdd = $ts3_sgid_don_member;
        } else {
            $ServerGroupID_ToAdd = $ts3_sgid_member;
        }

        // Get the user's unique id's from the mybb database
        $get_ts_uids = "SELECT * FROM mybb_tslink_uids WHERE uid = '$mybb_uid' ";
        $ts_unique_ids = $ConnectDB->query($get_ts_uids);

        // Connect to teamspeak server, authenticate and spawn an object for the virtual server on the server port.
        $ts3_VirtualServer = TeamSpeak3::factory("serverquery://$ts3_username:$ts3_password@$ts3_server:$ts3_query_port/?server_port=$ts3_server_port&nickname=$ts3_nickname");

        foreach ($ts_unique_ids as $ts_unique_id) {

            // First lets remove all groups the user is member of.
            // First get all servergroups the user is member of.
            $ClientServerGroups = $ts3_VirtualServer->execute('servergroupsbyclientid', ['cldbid' => $ts_unique_id['ts_cldbid']])->toAssocArray('sgid');
            $c = 0;

            // For every servergroup found, remove it.
            foreach ($ClientServerGroups as $Client_ServerGroup) {
                $csg["$c"] = $Client_ServerGroup['sgid'];
                $c++;
                foreach ($csg as $ClientServerGroupID) {
                    // Except for the servergroups we don't want to have removed.
                        if (in_array($ClientServerGroupID, $ts3_sgid_dont_remove)) {
                            // The servergroup given shouldn't be removed so don't do anything.
                        } else {
                            try {
                                $ts3_VirtualServer->execute('servergroupdelclient', ['sgid' => $ClientServerGroupID, 'cldbid' => $ts_unique_id['ts_cldbid']]);
                            } catch (Exception $e) {
                                // Catches the error(s) if any. But don't do anything with it.
                            }
                        }
                }
            }
            try {
                // Add the user to the servergroup.
                $ts3_VirtualServer->execute('servergroupaddclient', ['sgid' => $ServerGroupID_ToAdd, 'cldbid' => $ts_unique_id['ts_cldbid']]);
            } catch (Exception $e) {
                // Catches the error(s) if any. But don't do anything with it.
            }
        }

        // Close connection
        $ConnectDB->close();

        // Close teamspeak connection
        unset($ts3_VirtualServer);
    }

    function UpdateMyBBDB_To1($givenip)
    {
        require __DIR__.'/config.php';

        // Connect to the database.
        $ConnectDB = new mysqli($hostname, $username, $password, $database);

        // Check connection.
        if ($ConnectDB->connect_errno) {
            die($ConnectDB->connect_error);
        }

        // Update the MyBB database.
        $UpdateMyBBDBQuery = "UPDATE $table SET memberstatus = '1' WHERE HEX(lastip) = '$mybb_ip'";
        $ConnectDB->query($UpdateMyBBDBQuery);

        // Close connection
        $ConnectDB->close();
    }

    function UpdateMyBBDB_To0($givenip)
    {
        require __DIR__.'/config.php';

        // Connect to the database.
        $ConnectDB = new mysqli($hostname, $username, $password, $database);

        // Check connection.
        if ($ConnectDB->connect_errno) {
            die($ConnectDB->connect_error);
        }

        // Update the MyBB database.
        $UpdateMyBBDBQuery = "UPDATE $table SET memberstatus = '0' WHERE HEX(lastip) = '$mybb_ip'";
        $ConnectDB->query($UpdateMyBBDBQuery);

        // Close connection
        $ConnectDB->close();
    }

    function UpdateMyBBDB($givenip, $memberstatus)
    {
        require __DIR__.'/config.php';

        // Connect to the database.
        $ConnectDB = new mysqli($hostname, $username, $password, $database);

        // Check connection.
        if ($ConnectDB->connect_errno) {
            die($ConnectDB->connect_error);
        }

        // Update the MyBB database.
        $UpdateMyBBDBQuery = "UPDATE $table SET memberstatus = '$memberstatus' WHERE HEX(lastip) = '$mybb_ip'";
        $ConnectDB->query($UpdateMyBBDBQuery);

        // Close connection
        $ConnectDB->close();
    }
