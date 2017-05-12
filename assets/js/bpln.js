/*BuddyPress Live Notification Javascript*/
jQuery(document).ready(function(){
	
    var jq = jQuery;
	
	var last_notified = bpln.last_notified;
	
	var new_notifications_count = 0;
	
	
	
    //set interval to 5s 
	wp.heartbeat.interval( 'fast' );
	
	jq(document).on( 'heartbeat-tick.bpln-data', function( event, data ) {
        
		if ( data.hasOwnProperty( 'bpln-data' ) ) {
			var bpln_data = data['bpln-data'] ;
			
			update_last_notified( bpln_data.last_notified );
			
			var messages = bpln_data.messages;
			
			if( messages == undefined || messages.length == 0 )
				return ;
			
			for( var i =0; i< messages.length; i++ ) {
			   bpln.notify(messages[i]);
			  
			}
			//fire custom event bpln:new_notifications
			fire_on_new_notifications_recieved(messages.length, messages );
        }
    });
	
	jq(document).on( 'heartbeat-send', function( e, data ) {
			data['bpln-data'] = {last_notified: get_last_notified()};
	});

/**
 * Overwrite bpln.notify to use your own notification like sweetalert or growl
 * 
 * @param {type} message
 * @returns {undefined}
 */	
function notify( message ) {
	
	if( jq.achtung != undefined )
		jq.achtung({message: message, timeout: bpln.timeout});//show for 10 seconds	
}


//a theme author can override the bpln.notify and that will be used instead of the bundled notification system
bpln.notify = notify;

function get_count() {
	
	return new_notifications_count;
	
}

bpln.get_count = get_count;//seems meaningless to me now but let us keep it for now

//see what I am doing here, well don't follow me as I am breaking my own rules in the below code but still you can get an idea from the event handling 

jq( document ).on('bpln:new_notifications', function(evt, data ){
		
	if( data.count && data.count>0 ){
	
		update_count_text( jq('#ab-pending-notifications'), data.count );
		jq('#ab-pending-notifications').removeClass('no-alert').addClass('alert');
		
		var my_act_notification_menu = jq('#wp-admin-bar-my-account-notifications > a span');
		//if the count menu does not exis
		if(  ! my_act_notification_menu.get(0 ) ) {
		
			
			if( jq('#wp-admin-bar-my-account-notifications').get(0) ) { 
					jq('#wp-admin-bar-my-account-notifications > a').append(' <span class="count">'+data.count+" </span>");
					jq('#wp-admin-bar-my-account-notifications-unread a').append(' <span class="count">'+data.count+" </span>");
			}
		}else{
			
			update_count_text( my_act_notification_menu, data.count );
			update_count_text( jq('#wp-admin-bar-my-account-notifications-unread span'), data.count );
			
		}
		
		var list_parent = jq('#wp-admin-bar-bp-notifications-default');
		
		if( list_parent.get(0) ) {
			
			jq('#wp-admin-bar-no-notifications').hide();
			list_parent.append("<li>"+data.messages.join("</li><li>") + "</li>"  );
			
			
		}
		
		
	}
	
});



//private functions 
/**
 * Get last notified time
 * 
 * @returns string
 */
function get_last_notified() {
	//last notified is accessible in this scope but not outside
	return last_notified;
}

/**
 * Set last notified time
 * 
 * @param time String datetime
 * @returns null
 */	
function update_last_notified( time ) {

	last_notified = time;
}

function fire_on_new_notifications_recieved(count, messages) {
	
	jq( document ).trigger( "bpln:new_notifications", [{count: count, messages: messages}] );
}

function update_count_text( elements, count) {
	//don't do anything if the element does not exist or the count is zero
	
	if( ! elements.get(0) || ! count  )
		return;
	
	elements.each( function() {
		var element = jq(this);
		var current_count = parseInt( element.text() );
		
		current_count = current_count + parseInt(count) - 0;
	
		element.text( '' + current_count );
	});
	
	
}

});//end of jq(document).ready()
