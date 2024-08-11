=== Google Calendar List View ===
Contributors: kimipooh
Donate link: 
Tags: Google Calendar
Requires at least: 5.4
Requires PHP: 7.4
Tested up to: 6.6.1
Stable tag: 7.1.2
License: GPL v2  or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The plugin is to create a shortcode for displaying the list view of public Google Calendars.
 
== Description ==

The plugin is to create a shortcode for displaying the list view of public Google Calendars.

= Document =

Please see the [documentation](https://info.cseas.kyoto-u.ac.jp/en/links-en/plugin-en/wordpress-dev-info-en/google-calendar-list-view) in detail. [Japanese documentation](https://info.cseas.kyoto-u.ac.jp/links-ja/plugin-ja/wordpress-dev-info/google-calendar-list-view)

== Installation ==

1. Upload the plugin folder to /wp-content/plugins/ directory.
2. Activate the plugin in your WordPress admin panel.

== Frequently Asked Questions ==
= What can you do with a hook? =
You can customize the display of events.
The styles provided by the plugin are available in the library/tags folder of the plugin. For details, please see the [documentation](https://info.cseas.kyoto-u.ac.jp/en/links-en/plugin-en/wordpress-dev-info-en/google-calendar-list-view) in detail. [Japanese documentation](https://info.cseas.kyoto-u.ac.jp/links-ja/plugin-ja/wordpress-dev-info/google-calendar-list-view)

= How to get past events. =
Please set up "ALL" or past date to "Start Date" in the setting menu.
Default setting is to get from now to future events.

= Cannot get Google Calendar event? =
Please check "Google Calendar API Key" or "Google Calendar ID". If their setting is right, please wait for a day because the API queries limitation may be beyond. (Reference: https://developers.google.com/google-apps/calendar/pricing) 

= Can get events in multi Google Calendar? =
Yes. You can set Google Calendar ID and API Key in the shortcode.

= How to remove the settings =
Deactivate the plugin.

= How to hide a specific event =
By setting up hash tag (#display none or #display off) in an event description, so the event isn’t displayed.

= How to change the message when there are no events =
You input the message to "No Event Message" item in the general setting of the plugin setting or "no_event_message" shortcode option. 
If the value is empty, "There are no events." is set. Else if the value is "none", the message is hidden. If "no_event_message" shortcode option is set, the message is overwritten by the shortcode message

= How to manually fix timezone related deviations =
Set to shift the hours, minutes, and seconds by setting the value of "Fix Timezone Offset” in this plugin setting menu.

== Screenshots ==
1. List View of a public Google Calendar
2. How to use the Shortcode
3. Sample of public Google Calendars
4. How to use it
5. Goolge Calendar API Settings
6. General Settngs
7. Feature Expansion & Other notice
8. Grouping events by month

== Changelog ==
= 7.1.2 =
* Added the values of three selections; “yes|link”, “yes|map”, and “yes|link|map", to the "view_location" shortcode option. When setting up ‘view_location=“yes|link”', the link is added for URL (http:// or https://) the location value of Google Calendar. Then, when setting up ‘view_location=“yes|map”, the link to Google Maps is added to the location value of Google Calendar. Moreover, when setting up ‘view_location=“yes|link|map”, the link is added for URL (http:// or https://) the location value of Google Calendar and if there is not URL in the location value, the link of Google Maps is added to it.

= 7.1.1 =
* Fixed k_getAPIDataCurl function in "includes/getAPIDataCurl.php" for the issue that may be a failure to get content without the referer header.
* Tested up WordPress 6.6.1 with PHP 8.3.6

= 7.1.0 =
* Tested up WordPress 6.1 with PHP 8.2.
* Tested up WordPress 6.5.2 with PHP 8.3.6.

= 7.0.0 =
* The function for retrieving from the Google Calendar API has been changed from file_get_contents to curl for improving the connection timeout issue in case of not responsing API.

= 6.9.2 =
* Changed to not show end date if view_end_date is set and the event period is only within a day.
* Tested up WordPress 6.0.
* Tested up WordPerss 6.1.
* Changed the supported version of WordPress from 4.0 to 5.4 or higher.

= 6.9.1 =
* Fixed debug mode.

= 6.9 =
* Supported to fix the end date on all-day event because the end date value getting Google Calendar API is next day in the case of all-day events. 

= 6.8 =
* Added the "view_end_date" shortcode option. If the view_end_date value isn't empty, the end date is displayed, using the value of view_end_date as the delimiter string after the start date, except “html_tag” shortcode option is "li2" or customized by the hook. 

= 6.7.2 =
* Added hook-specific variables "translate_month_values”(array) for using WordPress Core's translation feature to store the month name.
* Changed “li-month" in the shortcut option “html_tag" to use WordPress Core's translation feature to display the month name.

= 6.7.1 =
* Implemented the hook 'lvgc_each_output_data' to customize the month-by-month display. Added hook-specific variables "start_date_month_value", "pre_start_date_month_value", and "month_value".

= 6.7 =
* Added “li-month" to the shortcut option "html_tag". "li-month" display style is that events that take place on the same month will be shown together. See the documentation in details. For technical detals, see the plugin library/tags/li-month.php.

= 6.6 =
* Add the function to shift the hours, minutes, and seconds by setting the value of "Fix Timezone Offset” in this plugin setting menu if it cannot solve the timezone issue.
* Change datetime function instead of strtotime to support the year 2038 issue regarding some date processing.

= 6.5.2 =
* Fixed an issue when template "p" in "html_tag" shortcode option is used.
* Tested up WordPress 5.8 and php 8.0.0.

= 6.5.1 =
* Fixed an issue where template "li2" in "html_tag" shortcode option cannot be used.

= 6.5 =
* Fixed an error that occurred when "html_tag" shortcode option contained an unexpected value.
* Added "no_event_link" shortcode option. If the "no_event_link" option is enabled and the value isn't empty, the event link is removed.

= 6.4.1 =
* The translation of the location name is fixed to be valid. As a result, the default value of the view_location_name shortcut option is now empty. If it is set to empty, Location: will be used.

= 6.4 =
* If the view_location option is enabled, the location name changed from “Venue:” to “Location:”.
* Added the "view_location_name" shortcode option. If you change the location name that is displayed when the view_location option is enabled , set the option. 

= 6.3 = 
* Allowed the html tag on "$gc_description" value for the hook.
* Added title attribute to event links, and added the ability to display excerpt of the event description (maximum: 1024 byes) in tooltips.
* Added “li2" to the shortcut option "html_tag". "li2" display style is that events that take place on the same day will be shown together. See the plugin library/tags/li2.php for technical details.

= 6.2 = 
* Fixed the issue where sorting from the setting menu was not available.

= 6.1 = 
* Added the change of the message to the setting menu and shortcode option when there are no events. If the value is empty, "There are no events." is set. Else if the value is "none", the message is hidden. If "no_event_message" shortcode option is set, the message is overwritten by the shortcode message

= 6.0 = 
* Add the hash tag (#display). by setting up hash tag (#display none or #display off) in an event description, so the event isn’t displayed.
* Add the elements of "hash_tags_display_value", "element_count" for "lvgc_each_output_data" hook.
* If there are no events, "there are no events." message is displayed.
* Tested up WordPress 5.7 and php 8.0.0.

= 5.9.1 = 
* Fixed the warnings when "WP_DEBUG" is true.
* Tested up WordPress 5.6.2 and php 8.0.0.

= 5.9 = 
* Added the Google Calendar original values to the "lvgc_each_output_data" hook. The values is referred in https://developers.google.com/calendar/v3/reference/events#resource.
* Tested up WordPress 5.6 and php 7.4.2.

= 5.8 = 
* Added the response of CSRF (Cross-Site Request Forgery) vulnerability for this plugin's settings.
* Tested up WordPress 5.3.2 and php 7.4.2.

= 5.7 = 
* Added "view_location" shortcode option. If the value is not empty, location data is displayed with the title.
* Added "$gc_location" variable in the hook.

= 5.6 = 
* Tested up WordPress 5.3.2 and php 7.4.1.
* Fixed warning messages in case that "start_date" and "end_date" shortcode options are not set.
* Fixed the issue that it referred to Google Calendar even if the default "Google Calendar ID" or "API Key" aren't set. 

= 5.5 = 
* Fixed the issue that "orderbysort" setting in the admin setting menu isn't applied.
* Fixed the warnings when some Google calendar values, such as a description, are empty.

= 5.4 = 
* Fixed the hook "lvgc_each_output_data" issue.
* Added "$end_date_num" variable in the hook.

= 5.3 = 
* Tested up WordPress 5.3 and php 7.3.
* Fixed the sort issue for the multi-calendar.

= 5.2 = 
* Tested up WordPress 5.2.4 and php 7.3.
* Fixed descending sort issue.
* Improve the argument of load_plugin_textdomain function for WordPress 3.7 or later. 

= 5.1 = 
* Tested up WordPress 5.2.2 and php 7.3.
* Fixed the array check issue in get_google_calendar_contents function.
* Fixed the error message issue in case that there are not any shortcode options.
* Fixed the warning message issue  for file_get_contents function.

= 5.0 = 
* Supported the strtotime date format for shortcut option "start_date" and "end_date". Various date formats for strtotime function, such as "now", "+1 days", "-2 days", "yesterday", "-1 week", and so on can be used.
* Added new shortcode option "max_display". "max_view" is for getting the number of items from Google Calendars. "max_display" is for displaying the number of items. If "max_display" isn't set, "max_display" is automatically set the value of "max_view" value. If there are mixed events (1.2.3. events in Japanese, 5.6.7. events in English) and set "max_view=5", lang="en", 5. and 6. events are only displayed. Therefore, you need to set "max_view" is 6 or more and "max_display" is 5. By these shortcut options, the plugin gets 6 ore more events and picks up 5 events in English among 6 or more events. 
* Fixed the issue which could not get a date if the WordPress timezone setting is "Offset" time (+9, -1, etc.).
* Fixed the processing issue regarding max_view (maxResults) value in case of multi Google calendars.


= 4.6 = 
* Allowed the html tag on "description" of Google Calendar.

= 4.5.1 = 
* Fixed help message for "start_date", "end_date" shortcode's date format on the setting menu.

= 4.5 = 
* Fixed the processing of "start_date", "end_date" shortcode's options.
* Fixed the default value of "orderbysort" shortcode's option (default: descending)

= 4.4 = 
* Added the value of "gc_description" on the hook.

= 4.3 = 
* Fixed current_time function error.
* Tested up WordPress 5.0 and php 7.2.

= 4.2 = 
* Tested up WordPress 4.9.

= 4.1 = 
* Added target="_blank" in a link tag.

= 4.0 = 
* Support of Multi Google Calendars.
* Fixed getting local timestamp in case of not set "start_date" option.
* Fixed the processing for #organizer hash tag in case of including a space in the value. 
* Changed the default for orderbysort is descending.

= 3.1 = 
* re-uploaded library/tags/lip.php template.

= 3.0 =
* Added the hash tag "#organizer". If you set the hash tag, you can use $hash_tags_organizer_value or $hash_tags['organizer']['value'] on the hook.
* Added shortcode option "enable_view_category". If you want to display the category (#type and #organizer), please set this value to "true" or not empty value (default is "")
* Deleted "html_tag_date_class" and "html_tag_title_class" for migrating "html_tag_class".
* Reduced the code of the template tag files (library/tags/) for more easily use.

= 2.2 =
* Moved get_select_lang_data function to the extended class.
* Create gclv_hash_tags class for getting the hash tags in the description field on Google Calendar
* Added the template html_tag "lip".
* Fixed CSS name.
* Added the attribution "hash_tags" and "hash_tags_type_title" for the hook.

= 2.11 =
* Fixed to display a debug message in case of using lang shortcode.

= 2.1 =
* Added shortcode option "lang". 

= 2.0 =
* Removed "lvgc_output_data" and "lvgc_gc_data" hooks for the security reason.
* Fixed the setting name.
* Added the secure option "hook_secret_key" on the shortcode for "lvgc_each_output_data" hook.


= 1.51 =
* Added the translation for the documentation link.

= 1.5 =
* Fixed the translation issue.
* Fixed the documentation.

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


