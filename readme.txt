=== Advanced Booking Calendar ===
Contributors: BookingCalendar
Tags: Booking Calendar, Online Buchung Kalender, Booking System, Hotel Booking, Belegungsplan, Bed and Breakfast, Hotel Management Online Booking, Booking, Accommodation, Booking System Plugin, Buchungskalendar, Ferienwohnung, Hotel, Hotel Booking Software, Online Hotel Software, Reservation, Reservation System, Room Availability, Rooms,
Requires at least: 4.3.0
Tested up to: 5.1.1
Stable tag: 1.5.9
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Booking Calendar that makes managing your online reservations easy. A great Booking System plugin for Accommodations. 

== Description ==
Booking Calendar for Accommodations. The easy way to manage your bookings and raise your occupancy rate. This Reservation System is made for modern Hoteliers who want to get hold of their online reservations.

= Booking System =
* Fully **responsive**, backend and frontend. Check your availabilities on your phone.
* Bookings stored in **your WordPress database**
* Booking will **generate an email** where you can accept or reject bookings
* Guests will receive emails when their **reservation** is generated, confirmed or rejected
* **Easy to manage prices** by seasons and room types
* All reservations are **easy to access and manage** in the backend.
* Change the **minimum number of nights** a guest has to stay for different seasons.

= Booking Calendar =
* Calendar **overview** for all your active rooms in your Hotel
* Single **calendar for each room type**
* Create calendars for up to 15 rooms
* Every calendar is responsive and **works great on mobile**

= Booking Form =
* Responsive form that **searches for matching rooms** by date and guest count
* Inputs can be stored in **Cookies**
* Generates one reservation per booking

https://www.youtube.com/watch?v=qQNN6cX6NwM

= Analytics =
* Analytic function helps you to identify high seasons
* Find the best pricings for your Hotel
* See how many requests fail and what the average person count is

= Google Universal Analytics =
* Integrates with your own **Google Universal Analytics** account
* User steps in the booking form are tracked and can be used to define a target
* Every action by the user on the **booking calendar is tracked** and helps you to identify new potentials

= Pro Version =
* A lot of additional features in the [Pro Version](https://booking-calendar-plugin.com/pro-download/)
* Payment Gateways: PayPal and Stripe
* Add Discounts to your Booking Form and payment fees
* Custom fields, combine extras with specific calendars
* Export bookings as ical file, MailChimp integration

= Even more features =
* Availability check **widget**
* **Email templates** for each email
* **Cookies** can be enabled to store user inputs
* Select email address to receive booking notifications
* Switch between showing the currency sign before or after the price.
* Ready for **translation**
* Comes with **German**, **Dutch**, **French**, **Italian**, **Russian**, **Bosnian**, **Czech**, **Romanian**, **Portuguese**, **Slovak** (partly) and **Hungarian** (partly)  translations

Any questions? Contact us [via email](mailto:support@booking-calendar-plugin.com) or [Twitter](https://twitter.com/BookingCal)
Or visit us at [https://www.booking-calendar-plugin.com](https://www.booking-calendar-plugin.com)

== Installation ==

For a complete setup guide take a look at: https://booking-calendar-plugin.com/setup-guide/

https://www.youtube.com/watch?v=qQNN6cX6NwM

1. Install and activate the plugin through your WordPress admin.
2. Check the settings on the "Advanced Booking Calendar" settings page.
3. Create calendars, rooms and seasons.
4. Add shortcodes and widget.

= Shortcodes =
This plugin uses four different shortcodes you can put on your pages. We recommend to use one page for each shortcode.

* Calendar Overview / **[abc-overview]**
The shortcode [abc-overview] shows all calendars and there availabilities by month.
* Single Calendar / **[abc-single calendar=X]**
The shortcode [abc-single calendar=X] needs a calendar id instead of X. You can find the ids on your calendar settings page. Example [abc-single calendar=1]
* Booking form / **[abc-bookingform]**
The booking form fulfils two tasks: finding the right room for users and generating booking requests. Every user action happens onpage via AJAX, so the page does not reload during interactions. We recommend to enter the shortcode [abc-bookingform] on a single page.

= Widget =
You can also use an availability check widget. Just go to "Appearance / Widgets" and add the widget.

== Screenshots ==

1. **Frontend overview** for all room types
2. **Single calendar** with date selection and "Book Now"-button
3. Booking form **without page reloads** and easy booking
4. **Backend overview** of all confirmed reservations
5. Managing bookings in the backend
6. Easiest way to **manage prices** and calendars
7. **Analytic features** help you to find high request times
8. Various settings to localize this plugin
9. Placeholdes and **email templates**

== Changelog ==

= 1.5.9 =
* Fixed broken legend for Single Calendar blocks.
* Fixed Booking Form block when there was no selected calendar.

= 1.5.8 =
* Added Gutenberg Blocks.
* Improved error message for missing access level in the backend.
* Fixed access level for analytics page.
* Fixed wrong title (tool tip) in the single calendar.

= 1.5.7 =
* Fixed a half-day-bug on the single calendar.

= 1.5.6 =
* Fixed missing booking id in guest mails.

= 1.5.5 =
* Fixed broken layout when some inputs in the booking form were disabled.
* Fixed empty line when there was no info text for a calendar in the booking form.
* Fixed regex in input field for admin email in the settings.

= 1.5.4 =
* Added a checkbox to the booking form (GDPR). You can activate it in the settings of the booking form and change the text in the text customizations.

= 1.5.3 =
* Added payment column to booking overview (if both payment types are enabled)
* Fixed the first day of the month, it was blocked when it was the first day of a booking
* Removed a css file which was loaded from googleapi.com

= 1.5.2 =
* Added sorting of extras
* Added a booking id to the booking overview, email placeholders and made it searchable
* Added ability to limit the booking form to specific calendars

= 1.5.1 =
* Added 100% translations for hu_HU
* Updated translations for ru_RU
* Fixed duplicate in email header (Thanks to https://profiles.wordpress.org/c2lr)

= 1.5.0 =
* Fixed same-day-bookings in datepicker.
* Fixed typo in de_DE.
* Fixed double title-line in widget.
* Fixed missing translations on Analytics-page.
* Added availability check for change of booking status when editing a booking.

= 1.4.9 =
* Added dropdown to change booking state when editing a booking.
* Added Polish translations.
* Added export function to settings.
* Added 'custom message' to bookings and admin email.
* Fixed missing translations in payment settings.
* Fixed bug when there was an apostroph in blogname.


= 1.4.8 =
* Added edit and change-room-button in bookings search.
* Fixed dependencies for datepicker translations in bookingform.
* Fixed missing price on booking editing.
* Fixed missing translations for titles on single calendar.
* Changed database schema to work for some MySQL versions.

= 1.4.7 =
* Added the setting for getting a copy of every guest email.
* Added Danish translations.
* Added CSS class for 'stay too short'-span.
* Changed the text "total price" on the single calendar to the customizable text "room price".
* Fixed missing calendar button in the visual editor when a page builder plugin was used.
* Fixed a timezone-related bug on single calendar.

= 1.4.6 =
* Replaced deprecated jQuery functions

= 1.4.5 =
* Fixed broken person dropdown for IE and Edge.
* Improved email validation in the booking form.
* Improved date inputs in booking form and widget. They are now read only, which helps mobile users a lot.
* Removed more unused code.

= 1.4.4 =
* Added Access Levels. You can now set the access level for user roles. Big thanks to Stefano.
* Fixed slashes in email templates.
* Fixed missing translations for persons in extras.
* Fixed an issue that caused an error when unconfirmed bookings were confirmed.
* Fixed deletion of seasons. Sometimes they blocked other season assignments.
* Fixed backend function for creating a booking when there is no payment configured.
* Removed old code from booking form.

= 1.4.3 =
* Improved editing process. Empty email field is now allowed when editing a booking.

= 1.4.2 =
* Added the editing function for bookings.
* Added the option to add custom CSS.
* Added the option to treat unconfirmed / open bookings like confirmed bookings. If enabled unconfirmed bookings are blocking dates in the frontend. 

= 1.4.1 =
* Fixed an issue with extras for more than 1 person.

= 1.4.0 =
* Added new shortcode parameters 'hide_other' and 'hide_tooshort'.
* Added the possibility to activate an extra only when a certain number of guests are in a booking.
* Improved the booking table in the backend. Now hides empty fields.
* Fixed an error message when saving a calendar.
* Fixed wrong text label in admin email (thanks to Heiko).
* Fixed wrong list of mandatory extras in admin email (thanks to Heiko). 

= 1.3.9 =
* Improved support for older PHP versions. 

= 1.3.8 =
* Added i10n-support for text customization.
* Added half-day logic to single calendar.
* Added customized 'room type'-label to admin mail.
* Fixed a minor bug for new installations of 1.3.7.

= 1.3.7 =
* Added sorting options for the backend.
* Improved the compatibility of the booking form and the widget.
* Fixed a minor bug for new installations of 1.3.6.

= 1.3.6 =
* Added a missing text label for the text customization.

= 1.3.5 =
* Added a new way to customize labels and buttons in the booking form and the single calendar.
* Improved the compatibility of the widget to some themes.
* Fixed a bug causing an error message when canceling or rejecting a booking.

= 1.3.4 =
* Fixed an issue causing a broken layout in guest mails (thanks to Rolf)

= 1.3.3 =
* Added a TinyMCE button the WordPress visual editor, so it's easier to add shortcodes to pages.
* Added the possibility to add float values as prices (e.g. $80.50)
* Improved the compatibility with certain themes.
* Fixed an issue that caused errors when adding a new calendar.

= 1.3.2 =
* Fixed an issue causing HTML to be escaped in the bookings overview (thanks to Heiko)
* Fixed a bug where special characters in the Blogname caused problems when sending emails (thanks to Rainer)

= 1.3.1 =
* Added Bosnian and Romanian language files (thanks to Senad and Leslie)
* Fixed a bug that caused "no available rooms" in the booking form

= 1.3.0 =
* Improved the connection between the Single Calendar and the Booking Form. After clicking on 'Book now' on the Single Calendar the Booking Form loads with the selected calendar ranked first.
* Added a new option to commit usage data to improve the plugin.
* Added a new option to keep plugin data in the database after deletion.
* Changed the location of the backend link.

= 1.2.8 =
* Fixed a minor bug when adding or editing a new calendar.

= 1.2.7 =
* Fixed a bug when sending guest emails
* Improved the admin emails

= 1.2.6 =
* Fixed a bug when calculating the minimum number of nights for a stay

= 1.2.5 =
* Improved compatibility to WordPress 4.6
* Improved the usability of the confirm & reject buttons in the mails. If you click on them when logged out, you get redirected to the login form.

= 1.2.4 =
* Changed the example date in the settings to 2016-12-15, to make it easier to figure out what days and months are.
* Changed the minimum price of an extra to 0.01.
* Fixed a minor bug when editing existing extras (thanks to Afinfo).
* Fixed a bug when calculating extra prices for confirmation mails (thanks to Bizbees).
* Fixed a formatting bug for prices. All prices shown are now formatted correctly.
* Fixed a bug in the price calculation when there were only optional extras.
* Improved the layout on the booking form confirmation page.

= 1.2.3 = 
* Added the possibility for admins to enter bookings with checkin/checkout-dates in the past.
* Improved the calendar legend. "Partly booked" now only shows when there are two or more rooms for the selected calendar id (or in total for the calendar overview).
* Fixed a minor bug when sending mails. The extras were not shown, when confirming a booking (thanks to Johan).
* Fixed a minor bug when adding a new extra.

= 1.2.2 =
* Updated Dutch translation (thanks to Johan). 
* Fixed a minor bug when sending mails. The extras were not shown, when there was only one (thanks to Johan). 

= 1.2.1 =
* Fixed a minor bug with the booking form layout. 

= 1.2.0 =
* Added new feature called "extras". You can now add optional or mandatory extras like "final cleaning" or costs for additional towels. Just select the price and its calculation (day/night/person etc.) and the extra will automatically show up in the booking form.
* Added the possibility to change the address fields in the booking form. Select between the options "required", "optional" or "disabled". If you change the address fields, please make sure to update your email templates. 
* Fixed a bug in the widget.
* Fixed a bug in the single calendar when cookies are enabled.
* Fixed a bug in the booking form for the Internet Explorer.

= 1.1.10 =
* Added support for PHP versions < 5.3.
* Fixed a typo.

= 1.1.9 =
* You can now show a legend explaining the colors on the calendars. Just add "legend=1" to a shortcode add, i.e. "[abc-single calendar=1 legend=1]". Works for single and overview calendars.
* Control when a day is shown as "partly booked" in the calendars. Just edit your existing calendars and enter the threshold of number of bookings for every calendar when to show "partly booked".

= 1.1.8 =
* Now able to configure the minimum stay. Enter the number of nights for each calendar or season.
* Rejected and canceled bookings can now get deleted.
* Fixed minor CSS issues.

= 1.1.7 =
* The drop down with the number of persons in the booking form now shows the highest number of guests for a single room.
* Error messages in the booking form are now smaller and the empty form fields now have a red border.
* Calendars with only one room are now a single row in the availability overview in the backend.
* Updated Portuguese language pack.

= 1.1.6 =
* Added translations for jQuery Datepicker
* Fixed 'Change room' function

= 1.1.5 =
* Added translations for jQuery Validition
* Some minor bug fixes.

= 1.1.4 =
* Added a widget for an availability check. Users can select dates and quickly start the booking form. 
* Added a Portuguese language pack (thanks to Miguel!)
* After selecting a date on the single calendar and clicking on "book now", the booking form loads automatically now. 
* Fixed missing translations in single calendar.
* Fixed a bug in the price calculation for seasons (thanks to Michael and Leslie).
* Changed notices when adding a calendar in the backend.
* Calendars with existing bookings can't be deleted anymore. 

= 1.1.3 =
* Changed query for table creation.
* Fixed a bug for using cookies.
* Old cookie values do not get deleted, but ignored.

= 1.1.2 =
* Fixed a bug when editing the rooms of a calendar.
* Fixed a bug for certain MySQL versions.

= 1.1.1 =
* Added a setup checklist.
* Added notices when changes happen on Season & Calendar setting page.
* Old cookie values are now getting deleted.
* Fixed a backend problem with the date format 'd/m/Y'.
* Fixed a bug where the confirmation email did not work.
* Fixed a bug on the analytics page when there were no calendars yet.

= 1.1 =
* New setting to change the position of the currency sign (before or after the amount).
* New setting to add tiny powered-by-link below the Calendar Overview.
* New backend module to collect feedback by user.
* Storing plugin version number from now on.
* Fixed an error for date format 'd/m/Y' in the booking form.
* Added CSS to make Calendar Overview and Booking Form look better in some themes.
* Changed ID to shortcode in the WP-admin calendar table.
* Changed button in Booking Form from "Book now" to "Select room".

= 1.0.2 =
* Fixed error that made bookings in the past possible.
* Checkout was called checkin on the single calendar when cookies were disabled.

= 1.0.1 =
* Fixed translations

= 1.0 =
* Initial release