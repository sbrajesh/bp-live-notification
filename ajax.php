<?php
/**
 * Filter on the heartbeat recieved data and inject the new notifications data
 * 
 * @param type $response
 * @param type $data
 * @param type $screen_id
 * @return type
 */
function bpln_process_notification_request( $response, $data, $screen_id ) {
    
	
	
	if ( isset( $data['bpln-data'] ) ) {
		
		$notifications = array();
		
		$request = $data['bpln-data'];
		
		if( ! empty( $request ) ){
			
			$notifications = bpln_get_new_notifications( get_current_user_id(), $request['last_notified'] );
			$notifications = bpln_get_notification_messages( $notifications );
		}
		
		
		$response['bpln-data'] = array('messages'=> $notifications, 'last_notified'=> current_time ('mysql')  );
		
    }
    return $response;
}

add_filter( 'heartbeat_received', 'bpln_process_notification_request', 10, 3 );
