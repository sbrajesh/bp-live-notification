=== BuddyPress Live Notification ===
Contributors: buddydev,anusharma,sbrajesh
Tags: buddypress, notifications, buddypress-live-notification
Requires at least: 4.5
Tested up to: 5.5.1
Stable tag: 2.1.1
License: GPLv2 
License URI: http://www.gnu.org/licenses/gpl-2.0.html

BuddyPress Live Notification adds a Facebook Like realtime user notifications for BuddyPress sites.

== Description ==

BuddyPress Live Notification adds Facebook Like real-time notifications for user.

= How it works:- =
Shows live notifications to other members on a BuddyPress based social network. The new and improved version uses WordPress heartbeat api to fetch notifications
 and allows a theme author to implement their own ui for notifying the update. 

Please do let us know your thoughts & suggestion on our blog [BuddyDev](https://buddydev.com/buddypress/introducing-buddypress-live-notification-2-0/)

**Do you need more awesome BuddyPress Plugins?** Checkout [BuddyPress Plugins by BuddyDev](https://buddydev.com/plugins/)
== Installation ==

This section describes how to install the plugin and get it working.


1. Download the zip file and extract
1. Upload `bp-live-notification` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu.
1. Alternatively you can use WordPress Plugin installer from Dashboard->Plugins->Add New to add this plugin
1. Enjoy

== Frequently Asked Questions ==

= Does This plugin works without BuddyPress =
No, It needs you to have BuddyPress installed and activated and the BuddyPress notifications component must be enabled

= Where Do I Ask for support? =
Please visit [BuddyDev](https://buddydev.com/support/forums/) for support.

== Screenshots ==

1. This shows live notification message screenshot-1.png
1. This shows live update of notification bar screenshot-2.png

== Changelog ==
= 2.1.1 =
1. Changed notification to use the avatar of the context item( e.g contains avatar+message).

= 2.1.0 =
1. Tested with BuddyPress 3.2
1. Code improvement
1. Prevents fatal error when BuddyPress is not active.

= 2.0.2 =
1. Compatibility with BuddyPress 2.8.2
1. Code improvement

= 2.0.1 =
1. Fixes the infinite notification bug.
1. Uses latest notification id instead of time for checking the new notifications.

= 2.0.0 =
1. Complete rewrite for better code and efficiency. 
1. Uses WordPress heartbeat api instead of long polling via the ajax. 
1. Allows theme authors to replace the inbuilt notification UI with a different one . 

= 1.0.5 =
1. Updated for properly handling json response. 
= 1.0.4 =

== Other Notes ==
 Please leave a comment on our blog [BuddyDev](http://buddydev.com/plugins/buddypress-live-notification/)