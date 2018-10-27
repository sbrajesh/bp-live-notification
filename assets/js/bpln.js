/*BuddyPress Live Notification Javascript*/
jQuery(document).ready(function ($) {

    var _lastNotified = bpln.last_notified;
    var _newNotificationsCount = 0;

    //set interval to 5s
    wp.heartbeat.interval('fast');

    $(document).on('heartbeat-tick.bpln-data', function (event, data) {

        if (data.hasOwnProperty('bpln-data')) {
            var bplnData = data['bpln-data'];

            updateLastNotified(bplnData.last_notified);

            var messages = bplnData.messages;

            if (messages === undefined || messages.length === 0) {
                return;
            }

            for (var i = 0; i < messages.length; i++) {
                bpln.notify(messages[i]);
            }

            // fire custom event bpln:new_notifications.
            emitNewNotificationsRecieved(messages.length, messages);
        }
    });

    $(document).on('heartbeat-send', function (e, data) {
        data['bpln-data'] = {last_notified: getLastNotified()};
    });

    /**
     * Overwrite bpln.notify to use your own notification like sweetalert or growl
     *
     * @param {type} message
     * @returns {undefined}
     */
    function notify(message) {

        if ($.achtung === undefined) {
            return;
        }
        $.achtung({
            message: message,
            timeout: bpln.timeout
        }); // show for 10 seconds.
    }


//a theme author can override the bpln.notify and that will be used instead of the bundled notification system
    bpln.notify = notify;
    bpln.get_count = function get_count() {
        return _newNotificationsCount;
    };

    $(document).on('bpln:new_notifications', function (evt, data) {

        if (data.count && data.count > 0) {
            updateCountText($('#ab-pending-notifications'), data.count);

            var myAccountNotificationMenu = $('#wp-admin-bar-my-account-notifications > a span');
            // if the count menu does not exist.
            if (!myAccountNotificationMenu.length) {
                if ($('#wp-admin-bar-my-account-notifications').length) {
                    $('#wp-admin-bar-my-account-notifications > a').append(' <span class="count">' + data.count + " </span>");
                    $('#wp-admin-bar-my-account-notifications-unread a').append(' <span class="count">' + data.count + " </span>");
                }
            } else {
                updateCountText(myAccountNotificationMenu, data.count);
                updateCountText($('#wp-admin-bar-my-account-notifications-unread span'), data.count);
            }

            var $listParent = $('#wp-admin-bar-bp-notifications-default');

            if ($listParent.length) {
                $listParent.append("<li>" + data.messages.join("</li><li>") + "</li>");
            }
        }
    });

// private functions.
    /**
     * Get last notified time
     *
     * @returns string
     */
    function getLastNotified() {
        // last notified is accessible in this scope but not outside.
        return _lastNotified;
    }

    /**
     * Set last notified time
     *
     * @param time String datetime
     * @returns null
     */
    function updateLastNotified(time) {
        _lastNotified = time;
    }

    function emitNewNotificationsRecieved(count, messages) {
        $(document).trigger('bpln:new_notifications', [{count: count, messages: messages}]);
    }

    function updateCountText(elements, count) {
        // don't do anything if the element does not exist or the count is zero.
        if (!elements.length || !count) {
            return;
        }

        elements.each(function () {
            var $element = $(this);
            var currentCount = parseInt($element.text());
            currentCount = currentCount + parseInt(count) - 0;
            $element.text('' + currentCount);
        });
    }

});//end of $(document).ready()
