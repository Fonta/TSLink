TSLink
======

Servergroup control on TeamSpeak from MyBB forum

1. What is LinkTS
2. Still to do
3. Already working
4. Installation of the plugin
5. Plugin enhancements to work with TSLink
6. Example of calling the functions of this plugin from somewhere else on your site.


1. What is LinkTS
========================================================================================================================

This plugin is created to add users on a teamspeak server to a group as soon as they register on the forum.
At our community someone becomes a member as soon as he registers on the forum and a VIP member when he donates to the community.
We are running WooCommerce to let members donate to the community.
As soon as we set the order to completed, the function UpdateMyBBDB_To1() and tslink_doupdategroups() are called from this plugin.
This sets the 0 (which is set to every user on installing the plugin in MyBB) to 1 and updates the usergroups on the Teamspeak server.
the function tslink_doupdategroups(), UpdateMyBBDB_To1 and UpdateMyBBDB_To0() are using the member's ip to determine the
right user in the databases.
In simple:
The plugin adds a row to the mybb_users db with a value of 0.
The 0 will represent member
When changed to 1, it will be a VIP member.

2. Still to do
========================================================================================================================

- With the new way of storing user ip addresses in MyBB 1.8 we had to convert the user's ip to 
  hex making the plugin incompatible with MyBB 1.6. Wish is to make the plugin backwards compatible with MyBB 1.6 so 
  we'll have to add a check if it's MyBB 1.6 or 1.8 and according to that number do or do not convert the ip addresses
  to hex for comparising.
- settings are now done in the config.php file in the /inc/plugins/tslink folder.
  Might make it so that more settings can be done from the AdminCP.
- Make it possible to view a member's status in the memberlist and/or show it on the post_author block.
- Maybe compatibility with login with Twitter & Google+ (really not sure about this one).

3. Already working
========================================================================================================================

- automatically adding users to a usergroup on teamspeak on registration completion. (after activation)
- UserCP option for the user to update his groups on the teamspeak server
- ModCP option to change a user's status
- AdminCP options to change a user's status + settings to enable or disable certain parts of the plugin.

4. Installation of the plugin
========================================================================================================================

- Edit the config.php in /inc/plugins/tslink folder.
- Upload everything to your forum's folder.
- Install & activate the plugin in the AdminCP.
** If you use the facebook plugin to let users login to your forum with their Facebook account, read point 5! **

Test & Have Fun!


5. Plugin enhancements to work with TSLink
========================================================================================================================

  5.1 Facebook login
  ==================

  If you use the Facebook login plugin please change the following line in /inc/plugins/MyFacebookConnect
  class_facebook.php:
    Find:
      $plugins->run_hooks("member_do_register_end");
    change to:
      $plugins->run_hooks("fb_register_end");

6. Example of calling the functions of this plugin from somewhere else on your site.
========================================================================================================================

  6.1 Woocommerce in Wordpress
  ============================
  
  Add this to the function.php of your theme:
    
    // hook into woocommerce when a order gets the status completed
    add_action( 'woocommerce_order_status_completed', 'Make_VIP_Member' );  
    /*
     * Do something after WooCommerce sets an order on completed
     */
    function Make_VIP_Member($order_id) {
    	
    	// order object (optional but handy)
    	$order = new WC_Order( $order_id );
    	
    	define("IN_LINKTS", 1);
    
    	require_once('/home/bugadmin/domains/bug-community.com/public_html/forum/inc/plugins/tslink/tslinkfunctions.php');
    
    	$givenip = get_post_meta( $order->id, '_customer_ip_address', true );
    
    	UpdateMyBBDB_To1($givenip);
    	tslink_doupdategroups($givenip);
    }
    
========================================================================================================================
