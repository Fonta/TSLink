TSLink
======

Servergroup control on TeamSpeak from MyBB forum

1. What is TSLink
2. Still to do
3. Already working
4. Installation of the plugin
5. Plugin enhancements to work with TSLink
6. Example of calling the functions of this plugin from somewhere else on your site.


1. What is TSLink
========================================================================================================================

This plugin is created to add users on a teamspeak server to a group as soon as they register on the forum.
At our community someone becomes a member as soon as he registers on the forum and a VIP member when he donates to the community.
We are running WooCommerce to let members donate to the community.
As soon as we set the order to completed, the function UpdateMyBBDB_To1() and tslink_update_uids() are called from this plugin.
This sets the user's status to 1 in the MyBB DB and update's the user's groups on the TeamSpeak server.

In simple:
The plugin adds a row to the mybb_users db with a value of 0.
The 0 will represent member
When changed to 1, it will be a VIP member.

2. Still to do
========================================================================================================================

- Update the readme
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

- Make a Teamspeak query user with enough i_group_member_add_power and i_group_member_remove_power to add and removes users     from the desired groups.
- Edit the config.php in /inc/plugins/tslink folder.
- Upload everything to your forum's folder.
- Install & activate the plugin in the AdminCP.
** If you use the facebook plugin to let users login to your forum with their Facebook account, read point 5! **

Test & Have Fun!


5. Plugin enhancements to work with TSLink
========================================================================================================================

  - Facebook login

  If you use the Facebook login plugin please change the following line in /inc/plugins/MyFacebookConnect
  class_facebook.php:
    Find:
      $plugins->run_hooks("member_do_register_end");
    change to:
      $plugins->run_hooks("fb_register_end");

6. Example of calling the functions of this plugin from somewhere else on your site.
========================================================================================================================

  - Woocommerce in Wordpress
  
  Add this to the function.php of your theme:
```php    
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
    	tslink_update_uids($givenip);
    }
```    
========================================================================================================================
