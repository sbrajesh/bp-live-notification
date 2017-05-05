<?php
/**
 * Filter on the heartbeat recieved data and inject the new notifications data
 *
 * @param type $response
 * @param type $data
 * @param type $screen_id
 *
 * @return type
 */
function bpln_process_notification_request( $response, $data, $screen_id ) {


	if ( isset( $data['bpln-data'] ) ) {

		$notifications    = array();
		$notification_ids = array();

		$request = $data['bpln-data'];

		$last_notified_id = absint( $request['last_notified'] );

		if ( ! empty( $request ) ) {

			$notifications = bpln_get_new_notifications( get_current_user_id(), $last_notified_id );

			$notification_ids = wp_list_pluck( $notifications, 'id' );

			$notifications = bpln_get_notification_messages( $notifications );

		}
		//include our last notified id to the list
		$notification_ids[] = $last_notified_id;
		//find the max id that we are sending with this request
		$last_notified_id = max( $notification_ids );

		$response['bpln-data'] = array( 'messages' => $notifications, 'last_notified' => $last_notified_id );

	}

	return $response;
}

add_filter( 'heartbeat_received', 'bpln_process_notification_request', 10, 3 );
