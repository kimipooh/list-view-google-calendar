=== Google Calendar List View ===
Contributors: kimipooh
Donate link: 
Tags: Google Calendar
Requires at least: 4.0
Tested up to: 4.8
Stable tag: 1.43
License: GPL v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The plugin is to create a shortcode for displaying the list view of a public Google Calendar.
 
== Description ==

The plugin is to create a shortcode for displaying the list view of a public Google Calendar.

= Document =

Please see "Google Calendar List View Settings" in Setting menu or [Japanese document](https://kitaney-wordpress.blogspot.jp/2017/04/google-calendar-list-view-google.html)

== Installation ==

1. Upload the plugin folder to /wp-content/plugins/ directory.
2. Activate the plugin in your WordPress admin panel.

== Frequently Asked Questions ==
= How to get past events. =
Please set up "ALL" or past date to "Start Date" in the setting menu.
Default setting is to get from now to future events.

= Cannot get Google Calendar event? =
Please check "Google Calendar API Key" or "Google Calendar ID". If their setting is right, please wait for a day because the API queries limitation may be beyond. (Reference: https://developers.google.com/google-apps/calendar/pricing) 

= Can get events in multi Google Calendar? =
Yes. You can set Google Calendar ID and API Key in the shortcode.

= How to remove the settings =
Deactivate the plugin.

== Screenshots ==
1. List View of a public Google Calendar
2. How to use the Shortcode
3. Sample of public Google Calendars
4. How to use it
5. Goolge Calendar API Settings
6. General Settngs
7. Feature Expansion & Other notice

== Changelog ==

= 1.43 =
* Tested up WordPress 4.8 and PHP 7.1.

= 1.42 =

* fixed. "lvgc_each_output_data" hook. 

= 1.41 =

* fixed. "lvgc_each_output_data" hook. 

= 1.4 =

* added "lvgc_each_output_data" hook for handling each output data. 

= 1.3 =

* fixed default html_tag setting and added shortcode option "id".

= 1.2 =

* added two hooks of "lvgc_output_data" and "lvgc_gc_data".
"lvgc_output_data" hook can handle for the output data. "lvgc_gc_data" hook can handled for getting Google Calendar data.

* Fixed timezone issue by using current_time and get_date_from_gmt function instead of date function.

* Added to a class "list-view-google-calendar-holding" to html tag for a holding event.

= 1.1 =

* added 4 shortcode options (max_view, html_tag_class, html_tag_date_class, html_tag_title_class)

= 1.0 =

* First Released.


