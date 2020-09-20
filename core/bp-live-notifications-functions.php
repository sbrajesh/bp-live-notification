<?php
// No direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

/**
 * Get all new notifications after a given time for the current user
 *
 * @param int    $user_id user id.
 * @param string $last_notified last notified time.
 *
 * @return array
 */
function bpln_get_new_notifications( $user_id, $last_notified ) {

	global $wpdb;

	$bp = buddypress();

	$table = $bp->notifications->table_name;

	$registered_components = bp_notifications_get_registered_components();


	$components_list = array();

	foreach ( $registered_components as $component ) {
		$components_list[] = $wpdb->prepare( '%s', $component );
	}

	$components_list = implode( ',', $components_list );


	$query = "SELECT * FROM {$table} WHERE user_id = %d AND component_name IN ({$components_list}) AND id > %d AND is_new = %d ";

	$query = $wpdb->prepare( $query, $user_id, $last_notified, 1 );

	return $wpdb->get_results( $query );
}

/**
 * Get the last notification id for the user
 *
 * @param int $user_id user id.
 *
 * @return int
 */
function bpln_get_latest_notification_id( $user_id = 0 ) {

	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	global $wpdb;

	$bp = buddypress();

	$table = $bp->notifications->table_name;

	$registered_components = bp_notifications_get_registered_components();


	$components_list = array();

	foreach ( $registered_components as $component ) {
		$components_list[] = $wpdb->prepare( '%s', $component );
	}

	$components_list = implode( ',', $components_list );


	$query = "SELECT MAX(id) FROM {$table} WHERE user_id = %d AND component_name IN ({$components_list}) AND is_new = %d ";

	$query = $wpdb->prepare( $query, $user_id, 1 );

	return (int) $wpdb->get_var( $query );
}

/**
 * Get notifications messages
 *
 * @param array $notifications Notifications array.
 *
 * @return array
 */
function _bpln_get_notification_messages( $notifications ) {
	$messages = array();

	if ( empty( $notifications ) ) {
		return $messages;
	}

	$total_notifications = count( $notifications );

	$class  = 'bpln-notification-message bpln-buddypress';
	for ( $i = 0; $i < $total_notifications; $i ++ ) {
		$notification = $notifications[ $i ];

		if ( defined( 'BP_PLATFORM_VERSION' ) ) {
			$class  = 'bpln-notification-message bpln-buddyboss';
		}
		$avatar  = bpln_get_notification_avatar( $notification );
		$message = bpln_get_the_notification_description( $notification );

		$messages[] =  array(
		        'plain' => $message,
                'html' => '<div class="' . $class . '">' . $avatar . $message . '</div>'
        );
	}

	return $messages;
}

/**
 * Get a list of processed messages
 *
 * @param array $notifications notifications array.
 *
 * @return array
 */
function bpln_get_notification_messages( $notifications ) {
	return _bpln_get_notification_messages( $notifications );
}

/**
 * A copy of bp_get_the_notification_description to server our purpose of parsing notification to extract the message
 *
 * @see bp_get_the_notification_description
 *
 * @param stdClass $notification notification object.
 *
 * @return string
 */
function bpln_get_the_notification_description( $notification ) {

	$bp = buddypress();

	// Callback function exists.
	if ( isset( $bp->{$notification->component_name}->notification_callback ) && is_callable( $bp->{$notification->component_name}->notification_callback ) ) {
		$description = call_user_func( $bp->{$notification->component_name}->notification_callback, $notification->component_action, $notification->item_id, $notification->secondary_item_id, 1 );

		// @deprecated format_notification_function - 1.5
	} elseif ( isset( $bp->{$notification->component_name}->format_notification_function ) && function_exists( $bp->{$notification->component_name}->format_notification_function ) ) {
		$description = call_user_func( $bp->{$notification->component_name}->format_notification_function, $notification->component_action, $notification->item_id, $notification->secondary_item_id, 1 );

		// Allow non BuddyPress components to hook in.
	} else {

		/** This filter is documented in bp-notifications/bp-notifications-functions.php */
		$description = apply_filters_ref_array( 'bp_notifications_get_notifications_for_user', array(
			$notification->component_action,
			$notification->item_id,
			$notification->secondary_item_id,
			1,
			'string',
			$notification->component_action, // Duplicated so plugins can check the canonical action name.
			$notification->component_name,
			$notification->id,
		) );
	}

	/**
	 * Filters the full-text description for a specific notification.
	 *
	 * @param string $description Full-text description for a specific notification.
	 */
	return apply_filters( 'bp_get_the_notification_description', $description );
}

/**
 * Should we disable it in dashboard.
 *
 * @return bool
 */
function bpln_disable_in_dashboard() {
	// use this hook to disable notification in the backend.
	return apply_filters( 'bpln_disable_in_dashboard', false );
}

/**
 * Get notification avatar
 *
 * @param BP_Notifications_Notification $notification notification object.
 * @param int                           $avatar_size Avatar size.
 *
 * @return string
 */
function bpln_get_notification_avatar( $notification, $avatar_size = 30 ) {
	$component = $notification->component_name;

	$item_id = '';

	switch ( $component ) {
		case 'groups':
			if ( ! empty( $notification->item_id ) ) {
				$item_id = $notification->item_id;
				$object  = 'group';
			}
			break;
		case 'follow':
		case 'friends':
			if ( ! empty( $notification->item_id ) ) {
				$item_id = $notification->item_id;
				$object  = 'user';
			}
			break;
		default:
			if ( ! empty( $notification->secondary_item_id ) ) {
				$item_id = $notification->secondary_item_id;
				$object  = 'user';
			} else {
				$item_id = $notification->item_id;
				$object  = 'user';
			}
			break;
	}

	if ( ! $item_id || ! $object ) {
		return '';
	}

	if ( $object === 'group' ) {
		$link  = bp_get_group_permalink( new BP_Groups_Group( $item_id ) );
	} else {
		$link = bp_core_get_user_domain( $item_id );
	}

	ob_start();
	?>
		<a href="<?php echo $link ?>" class="bpln-item-avatar">
			<?php echo bp_core_fetch_avatar( array( 'item_id' => $item_id, 'object' => $object, 'width' => $avatar_size ) ); ?>
		</a>
	<?php

	return ob_get_clean();
}