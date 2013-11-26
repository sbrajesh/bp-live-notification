<?php
/* Plugin Name: BuddyPress Live Notification
 * Plugin URI: http://buddydev.com/plugins/buddypress-live-notification/
 * Version: 1.0.4
 * Description: Adds a Facebook Like realtime notification for user
 * Author: Brajesh Singh
 * Author URI: http://buddydev.com/members/sbrajesh
 * License: GPL
 * Last Modified: November 20, 2013
 * 
 * */


//load css, we are using modified achtung jquery plugin, so we will load their css
add_action("wp_print_styles","bpln_add_css");
add_action("admin_print_styles","bpln_add_css");

function bpln_add_css(){
    if(!is_user_logged_in()||is_admin()&&bpln_disable_in_dashboard())
        return;
    
    $plugin_url=plugin_dir_url(__FILE__);
    wp_enqueue_style("achtung_css",$plugin_url."notice/ui.achtung.css");
 }

//load javascript
add_action("wp_print_scripts","bpln_add_js");

function  bpln_add_js(){
    if(!is_user_logged_in()||is_admin()&&bpln_disable_in_dashboard())
        return;
    $plugin_url=plugin_dir_url(__FILE__);
  //  wp_enqueue_script("achtung",$plugin_url."notice/ui.achtung.js",array("jquery"));
    wp_enqueue_script("bpln_js",$plugin_url."bpln.js",array("jquery","json2"));
}

/**
 * Loads textdomain
 */
add_action ( 'bp_loaded', 'bpln_load_textdomain');

/**
* Load plugin textdomain for translation
*/
function bpln_load_textdomain(){

    $locale = apply_filters( 'bp-live-notification_get_locale', get_locale() );
    
    $plugin_url=plugin_dir_url(__FILE__);
   // if load .mo file
   if ( !empty( $locale ) ) {
       $mofile_default = sprintf( '%slanguages/%s.mo',  $plugin_url, $locale );
       $mofile = apply_filters( 'bp-live-notification_load_textdomain_mofile', $mofile_default );

           if (is_readable( $mofile ) ) 
               // make sure file exists, and load it
               load_textdomain( 'bp-live-notification', $mofile );
   }

}

//handle ajax request
//main function
function bpln_check_notification(){
    if(!is_user_logged_in())
        return;
  if(!function_exists("bp_is_active"))
	return;
    //check_ajax_referer("bpln_check");
    $resp=array();//response array

    $ids=$_POST['notification_ids'];

    if(empty($ids))
        $notification_ids_old=array();
    else
        $notification_ids_old=explode(",", $ids);

    $notification_ids_current=bpln_get_all_notification_ids(bp_loggedin_user_id());
    
    $notification_ids_diff=array_diff($notification_ids_current, $notification_ids_old);//difference between the current i
    
    if(!$notification_ids_diff)
        $notification_ids_diff=array_diff($notification_ids_old,$notification_ids_current);
    $count_new=count($notification_ids_diff);
   
    if($count_new>0){//we have notifications
        $resp["change"]="yes";
        $resp["current_ids"]=$notification_ids_current;
        //collect the specific new messages messages
        $resp['messages']=bpln_get_notification_messages($notification_ids_diff);//ignored if change is no
        $resp['notification_all']=bpln_get_all_notification();//replace all notification in admin bar as we don't have a way to differentiate using any dom technique
    }
    else
        $resp["change"]="no";

    echo json_encode($resp);
    exit(0);//for wp admin
}
add_action("wp_ajax_bpln_check_notification","bpln_check_notification");

//get all notification for current user
function bpln_get_all_notification() {
	global $bp;

	if ( !is_user_logged_in() )
		return false;
if(bp_use_wp_admin_bar())
    return bpln_get_notifications_for_wpadminbar();

	$html= '<li id="bp-adminbar-notifications-menu"><a href="' . $bp->loggedin_user->domain . '">';
	$html.=__( 'Notifications', 'buddypress' );

	if ( $notifications = bp_core_get_notifications_for_user( $bp->loggedin_user->id ) )
            $html.="<span>".count( $notifications )."</span>";


	$html.= '</a><ul>';

	if ( $notifications ) {
		$counter = 0;
		for ( $i = 0; $i < count($notifications); $i++ ) {
			$alt = ( 0 == $counter % 2 ) ? ' class="alt"' : '';
			$html.="<li {$alt} >". $notifications[$i] ."</li>";
			 $counter++;
		}
	}
 else
    $html.="<li><a href='".$bp->loggedin_user->domain."'>".__( 'No new notifications.', 'buddypress' )."</a></li>";



	$html.= '</ul></li>';
        return $html;
}

function bpln_get_notifications_for_wpadminbar(){
    global $bp;

	if ( !is_user_logged_in() )
		return false;
  //$html= '<li class="menupop" id="wp-admin-bar-bp-notifications">';
  $html='<a href="' . $bp->loggedin_user->domain . '" aria-haspopup="true"  class="ab-item">';
        if ( $notifications = bp_core_get_notifications_for_user( bp_loggedin_user_id(), 'object' ) ) {
		$menu_title = sprintf( __( 'Notifications <span id="ab-pending-notifications" class="pending-count">%s</span>', 'buddypress' ), count( $notifications ) );
	} else {
		$menu_title = __( 'Notifications', 'buddypress' );
	}
    $html.=$menu_title;       
  $html.='</a>';
  $html.='<div class="ab-sub-wrapper">';
  $html.='<ul class="ab-submenu" id="wp-admin-bar-bp-notifications-default">';
  
  if ( !empty( $notifications ) ) {
		foreach ( (array)$notifications as $notification ) {
                   $html.=' <li id="wp-admin-bar-notification-'.$notification->id.'">';
                   $html.='<a href="'.$notification->href.'" class="ab-item">'.$notification->content.'</a>';
                   $html.='</li>';
			
		}
	} 
  		
     $html.='</ul>';
     $html.='</div>';
    // $html.='</li>';
return $html;

}

//a copy of bp_core_get_notifications_for_user just modified
function bpln_get_notification_messages($notification_ids){
if(!is_user_logged_in())
        return;
  global $bp,$wpdb;
  $list_ids="(".join(",", $notification_ids).")";
  $notifications =$wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$bp->core->table_name_notifications} WHERE id in {$list_ids} AND is_new = %d", 1)  );
  $renderable=array();

  /* Group notifications by component and component_action and provide totals */
	for ( $i = 0; $i < count($notifications); $i++ ) {
		$notification = $notifications[$i];

		$grouped_notifications[$notification->component_name][$notification->component_action][] = $notification;
	}

	if ( !$grouped_notifications )
		return false;

	/* Calculated a renderable outcome for each notification type */
	foreach ( (array)$grouped_notifications as $component_name => $action_arrays ) {
		if ( !$action_arrays )
			continue;

		foreach ( (array)$action_arrays as $component_action_name => $component_action_items ) {
			$action_item_count = count($component_action_items);

			if ( $action_item_count < 1 )
				continue;

			if ( isset($bp->{$component_name}->format_notification_function )&&function_exists( $bp->{$component_name}->format_notification_function ) ) {
				$renderable[] = call_user_func( $bp->{$component_name}->format_notification_function, $component_action_name, $component_action_items[0]->item_id, $component_action_items[0]->secondary_item_id, $action_item_count );
			
                        } elseif ( isset( $bp->{$component_name}->notification_callback ) && function_exists( $bp->{$component_name}->notification_callback ) ) {
				
					$content  = call_user_func( $bp->{$component_name}->notification_callback, $component_action_name, $component_action_items[0]->item_id, $component_action_items[0]->secondary_item_id, $action_item_count, 'array' );
                                        if ( is_string( $content ) ) {
						$renderable[] = $content;
						
					} else {
						$renderable[] = $content['text'];
						
					}
                                
                                        
                                        
                                
            }               
		}
	}

	return $renderable;//array of messages
}

/**
 * get all notification id for user
 * returns array of ids or empty
 */
function bpln_get_all_notification_ids( $user_id ) {
		global $wpdb, $bp;
 		return $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$bp->core->table_name_notifications} WHERE user_id = %d AND is_new = 1", $user_id ) );
}


//store the ids in some hidden footer element, we will use jquery to move this data to elemnt datastore for security
 add_action("wp_footer","bpln_store_ids");
 add_action("admin_footer","bpln_store_ids");

function bpln_store_ids(){
    if(!is_user_logged_in())
        return;
 ?>
 <div id="bpln-notification-ids" style="display:none;">
 <?php echo join(",",bpln_get_all_notification_ids(bp_loggedin_user_id()));?>
 </div>
<?php
}
 
function bpln_disable_in_dashboard(){
    return apply_filters('bpln_disable_in_dashboard',false);//use this hook to disable notification in the backend
}
?>