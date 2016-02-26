<?php

    // Disallow direct access to this file for security reasons.
    if (!defined('IN_MYBB')) {
        die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
    }

    // DEFINE PLUGINLIBRARY
    // Define the path to the plugin library, if it isn't defined yet.
    if (!defined('PLUGINLIBRARY')) {
        define('PLUGINLIBRARY', MYBB_ROOT.'inc/plugins/pluginlibrary.php');
    }

    define('IN_LINKTS', 1);

    // Plugin Info
    function tslink_info()
    {
        return [
            'name'            => 'Teamspeak Link',
            'description'     => 'Automatically add a user to a desired group on teamspeak after registration. Or let the user do this manually through the UserCP.',
            'website'         => 'http://www.bug-community.com',
            'author'          => 'Fonta',
            'authorsite'      => 'http://www.bug-community.com',
            'version'         => '1.3.4',
            'compatibility'   => '18*',
            'codename'        => 'TSLink',
        ];
    }

    function tslink_install()
    {
        global $db, $PL, $lang, $mybb;

        if (!$lang->tslink) {
            $lang->load('tslink');
        }

        if (!file_exists(PLUGINLIBRARY)) {
            flash_message($lang->tslink_pluginlibrary_missing, 'error');
            admin_redirect('index.php?module=config-plugins');
        }

        $PL or require_once PLUGINLIBRARY;

        $PL->settings('tslink_settings', $lang->setting_group_tslink, $lang->setting_group_tslink_desc,
            [
                'enabled' => [
                    'title'       => $lang->setting_tslink_enable,
                    'description' => $lang->setting_tslink_enable_desc,
                    'value'       => '1',
                ],
                'onregister' => [
                    'title'       => $lang->setting_tslink_onregister,
                    'description' => $lang->setting_tslink_onregister_desc,
                    'value'       => '1',
                ],
                'admincp' => [
                    'title'       => $lang->setting_tslink_admincp,
                    'description' => $lang->setting_tslink_admincp_desc,
                    'value'       => '1',
                ],
                'modcp' => [
                    'title'       => $lang->setting_tslink_modcp,
                    'description' => $lang->setting_tslink_modcp_desc,
                    'value'       => '1',
                ],
                'usercp' => [
                    'title'       => $lang->setting_tslink_usercp,
                    'description' => $lang->setting_tslink_usercp_desc,
                    'value'       => '1',
                ],
            ]
        );

        if ($db->field_exists('memberstatus', 'users')) {
            // Don't do anything
        } else {
            // Insert our memberstatus column into the database.
            $db->query('ALTER TABLE '.TABLE_PREFIX.'users ADD (`memberstatus` int(10) NOT NULL DEFAULT 0)');
        }

        $db->query('CREATE TABLE IF NOT EXISTS '.TABLE_PREFIX.'tslink_uids (
          uid int(10) NOT NULL,
          ts_uid varchar(50) NOT NULL,
          ts_cldbid int(10) DEFAULT NULL,
          UNIQUE KEY (uid)
        ) ');
    }

    function tslink_is_installed()
    {
        global $settings;

        // This plugin creates settings on install. Check if setting exists.
        if (isset($settings['tslink_settings_enabled'])) {
            return true;
        }
    }

    function tslink_uninstall()
    {
        global $db, $PL;

        $PL or require_once PLUGINLIBRARY;

        // Delete our column. -- temporarily disabled --
        //$db->query("ALTER TABLE " . TABLE_PREFIX . "users DROP `memberstatus`");

        //Delete the templates
        $db->delete_query('templategroups', "title = 'Teamspeak Link Templates'");

        // Drop settings.
        $PL->settings_delete('tslink_settings');
    }

    function tslink_activate()
    {
        global $db;

        $q = $db->simple_select('templategroups', 'COUNT(*) as count', "title = 'Teamspeak Link Templates'");
        $c = $db->fetch_field($q, 'count');
        $db->free_result($q);

        if ($c < 1) {
            $ins = [
                'prefix'           => 'tslink',
                'title'            => 'Teamspeak Link Templates',
            ];
            $db->insert_query('templategroups', $ins);
        }

        $ins = [
            'tid'              => null,
            'title'            => 'tslink_usercp_menu',
            'template'         => $db->escape_string('
<tr>
<td class="tcat smalltext">
	<div class="expcolimage">
		<img src="{$theme[\'imgdir\']}/collapse{$collapsedimg[\'usercptslink\']}.png" id="usercptslink_img" class="expander" alt="[-]" title="[-]" />
	</div>
	<div>
		<span>
		<strong>{$lang->tslink_menu_title}</strong>
		</span>
	</div>
</td>
</tr>
<tbody style="{$collapsed[\'usercptslink_e\']}" id="usercptslink_e">
	<tr>
		<td class="trow1 smalltext">
			<a href="usercp.php?action=tslink" class="usercp_nav_item usercp_nav_tslink">{$lang->tslink_menu_link}</a>
		</td>
	</tr>
</tbody>
<style type="text/css">
.usercp_nav_tslink {
	background: url(\'images/tslink/teamspeak3-icon.png\') no-repeat left center;
}
</style>'),
            'sid'            => '-2',
            'version'        => $mybb->version + 1,
        ];
        $db->insert_query('templates', $ins);

        $ins = [
            'tid'              => null,
            'title'            => 'tslink_usercp_settings',
            'template'         => $db->escape_string('
<html>
<head>
<title>{$lang->tslink_title} - {$mybb->settings[\'bbname\']}</title>
{$headerinclude}
</head>
<body>
 {$header}
<table width="100%" border="0" align="center">
<tr>
	 {$usercpnav}
	<td valign="top">
		 {$inlinesuccess}
			<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
			<thead>
			<tr>
				<th class="thead">
					<img style="padding-top:3px;" src="images/tslink/teamspeak3.png" height="16px"> <strong>{$lang->tslink_title}</strong>
				</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td class="trow1">
				<p>{$lang->tslink_usercp_message}</p>
				</td>
			</tr>
			</tbody>
			</table>
			<div style="text-align:center;">
			<form name="LinkTS" method="post" action="usercp.php?action=tslink">
				<input type="submit" class="button" value="{$lang->tslink_usercp_submit_button}" name="tslink_dolink" />
			</form>
		</div>
	</td>
</tr>
</table>
 {$footer}
</body>
</html>'),
            'sid'            => '-2',
            'version'        => $mybb->version + 1,
        ];
        $db->insert_query('templates', $ins);

        $ins = [
            'tid'              => null,
            'title'            => 'tslink_modcp_menu',
            'template'         => $db->escape_string('<tr>
	<td class="tcat smalltext">
		<div class="expcolimage"><img src="{$theme[\'imgdir\']}/collapse{$collapsedimg[\'modcptslink\']}.png" id="modcptslink_img" class="expander" alt="[-]" title="[-]" /></div>
	<div><span><strong>{$lang->tslink_menu_title}</strong></span></div>
	</td>
</tr>
<tbody style="{$collapsed[\'modcptslink_e\']}" id="modcptslink_e">
	<tr><td class="trow1 smalltext"><a href="modcp.php?action=tslink" class="modcp_nav_item modcp_nav_tslink">{$lang->tslink_menu_link}</a></td></tr>
</tbody>
<style type="text/css">.modcp_nav_tslink { background: url(\'images/tslink/teamspeak3-icon.png\') no-repeat left center;}</style>'),
            'sid'            => '-2',
            'version'        => $mybb->version + 1,
        ];
        $db->insert_query('templates', $ins);

        $ins = [
            'tid'              => null,
            'title'            => 'tslink_modcp_page_template',
            'template'         => $db->escape_string('<html>
<head>
<title>{$lang->tslink_title} - {$mybb->settings[\'bbname\']}</title>
{$headerinclude}
</head>
<body>
{$header}
<form action="modcp.php" method="post">
<input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
<table width="100%" border="0" align="center">
<tr>
{$modcp_nav}
<td valign="top">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="3"><strong>{$lang->tslink_title}</strong></td>
</tr>
<tr>
<td class="tcat" align="center" width="20%"><span class="smalltext"><strong>{$lang->tslink_modcp_username}</strong></span></td>
<td class="tcat" align="center" width="20%"><span class="smalltext"><strong>{$lang->tslink_modcp_status}</strong></span></td>
<td class="tcat" align="center" width="15%"><span class="smalltext"><strong>{$lang->tslink_modcp_options}</strong></span></td>
</tr>
{$tslink_rows}
</table>
</td>
</tr>
</table>
</form>
{$footer}
</body>
</html>'),
            'sid'            => '-2',
            'version'        => $mybb->version + 1,
        ];
        $db->insert_query('templates', $ins);

        $insert_array = [
        'title'        => 'tslink_modcp_row',
        'template'     => $db->escape_string('<tr>
<td class="{$alt_bg}" align="center">{$user[\'username\']}</td>
<td class="{$alt_bg}" align="center">{$status}</td>
<td class="{$alt_bg}" align="center">{$linktochange}</td>
</tr>'),
        'sid'        => '-2',
        'version'    => '',
    ];
        $db->insert_query('templates', $insert_array);

        include MYBB_ROOT.'/inc/adminfunctions_templates.php';
        find_replace_templatesets('modcp_nav', '#'.preg_quote('{$modcp_nav_users}').'#i', '{$modcp_nav_users}<!-- tslink -->');
    }

    function tslink_deactivate()
    {
        global $db;

        $db->delete_query('templates', "title LIKE 'tslink_%' AND sid='-2'");

        include MYBB_ROOT.'/inc/adminfunctions_templates.php';
        find_replace_templatesets('modcp_nav', '#'.preg_quote('<!-- tslink -->').'#i', '');
    }

    require_once MYBB_ROOT.'/inc/plugins/tslink/tslinkfunctions.php';

    function tslink_mybb_hooks()
    {
        global $mybb, $plugins;

        require MYBB_ROOT.'inc/plugins/tslink/config.php';

        // Define hooks when the plugin is enabled in the settings.
        if ($mybb->settings['tslink_settings_enabled']) {

            // Global hook
            $plugins->add_hook('global_start', 'tslink_global');

            if ($mybb->settings['tslink_settings_onregister']) {
                // Hook the function to add the user to a certain group.
                // You can use other hooks like member_do_register_end - just take a look at the mybb documentation.
                $plugins->add_hook('member_activate_accountactivated', 'tslink_update_uids', $givenip);
                $plugins->add_hook('fb_register_end', 'tslink_update_uids', $givenip);
            }

            if ($mybb->settings['tslink_settings_admincp']) {
                //Hooks for the AdminCP
                $plugins->add_hook('admin_load', 'tslink_admin');
                $plugins->add_hook('admin_user_menu', 'tslink_admin_user_menu');
                $plugins->add_hook('admin_user_action_handler', 'tslink_admin_user_action_handler');
            }

            if ($mybb->settings['tslink_settings_modcp']) {
                // Hooks for the ModCP.
                $plugins->add_hook('modcp_start', 'tslink_modcp');
            }

            if ($mybb->settings['tslink_settings_usercp']) {
                // Hooks for the UserCP.
                $plugins->add_hook('usercp_menu', 'tslink_usercp_menu', 40);
                $plugins->add_hook('usercp_start', 'tslink_usercp');
            }
        }
    }

    tslink_mybb_hooks();
