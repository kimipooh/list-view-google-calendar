=== Google Calendar List View ===
Contributors: kimipooh
Donate link: 
Tags: Google Calendar
Requires at least: 4.0
Tested up to: 4.7.3
Stable tag: 1.1
License: GPL v2

The plugin is to create a shortcode for displaying the list view of a public Google Calendar.
 
== Description ==

The plugin is to create a shortcode for displaying the list view of a public Google Calendar.

== Installation ==

First of all, please install this plugin and activate it.

= Usage =

Shortcode: [gc_list_view] (Put the shortcode on a post or page.)

The following shortcode option is priority than setting values.

[gc_list_view start_date="YYYY-MM-DD/ALL" end_date="YYYY-MM-DD" date_format="Y.m.d" orderbysort="ascending/descending" g_id="Google Calendar ID" g_api_key="Google Calendar API Key" html_tag="li/p/dd" max_view=10]

1. start_date is the value of "Start Date" (Default value is empty (= current date)). (strtotime date format is supported.) If "ALL" value is setting up, start_date value is unlimited.
2. end_date is the value of "End Date". (strtotime date format is supported.)
3. date_format is (date format is supported.)
4. orderbysort can select "ascending" or descending". It behaves like ordersort by Google Calendar API v2.
5. g_id is Google Calendar ID. If you use multi Google Calendar, set this value.
6. g_api_key is Google Calendar API Key. If you use multi Google Calendar API Key, set this value.
7. max_view is the maximum number of view. Default is 10 (same as maxResults value in Google Calendar API Settings)
8. html_tag is used by the output Google Calendar events.
9. html_tag_class is html_tag class. Default is list-view-google-calendar.
10. html_tag_date_class is html_tag date class. Default is list-view-google-calendar_date
11. html_tag_title_class is html_tag title class. Default is list-view-google-calendar_title.

About the detail information, Please see "Google Calendar List View Settings" in Setting menu or https://kitaney-wordpress.blogspot.jp/2017/04/google-calendar-list-view-google.html (in Japanese).

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

== Changelog ==

= 1.1 =
* added 4 shortcode options (max_view, html_tag_class, html_tag_date_class, html_tag_title_class)

= 1.0 =
* First Released.

== Upgrade Notice ==

= 1.0 =
* First Released.

