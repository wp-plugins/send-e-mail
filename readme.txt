=== Send E-mail ===
Contributors: paulox
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=10617157
Tags: akismet, contact, contact form, email, i18n, international, l10n, language, localization, .mo file, multilingual, plugin, .po file, signature, wordpress.com
Stable tag: 1.3
Requires at least: 2.5
Tested up to: 3.1

Add a contact form to any post, page or text widget. Messages will be sent to any email address you choose. As seen on wordpress.com with added i18n.

== Description ==

Add a contact form to any post or page by inserting `[contact-form]` in the post. Messages will be sent to the post's author or any email address you choose.

Or add a contact form to a text widget.  Messages will be sent to the email address set in your Settings -> General admin panel or any email address you choose.

Your email address is never shown, and the sender never learns it (unless you reply to the email).

As seen on WordPress.com.

This plugin was imported from [Grunion Contact Form](http://wordpress.org/extend/plugins/grunion-contact-form/ "Grunion Contact Form - wordpress.org") but it adds full internationalization using localized string from Wordpress core.

An e-mail [signature block](http://en.wikipedia.org/wiki/Signature_block "Signature block - Wikipedia") was appended to the end of the e-mail message containing the sender's name, email address, website and IP and delimited from the body of the message by a single line consisting of exactly two hyphens, followed by a space, followed by the end of line ("-- \n"). This [signature cut line](http://tools.ietf.org/html/rfc3676#section-4.3 "Usenet Signature Convention - RFC3676 - IETF") allows software to automatically mark or remove the sig block.

= Configuration =

The `[contact-form]` shortcode has the following parameters:

* `to`: A comma separated list of email addresses to which the messages will be sent.
  If you leave this blank: contact forms in posts and pages will send messages to the post or page's author; and
  contact forms in text widgets will send messages to the email address set in Settings -> General.

  Example: `[contact-form to="you@me.com"]`

  Example: `[contact-form to="you@me.com,me@you.com,us@them.com"]`

* `subject`: The e-mail subject of the message defaults to `[{Blog Title}] {Sidebar}` for text widgets
  and `[{Blog Title}] {Post Title}` for posts and pages. Set your own default with the subject option.

  Example: `[contact-form subject="My Contact Form"]`

* `show_subject`: You can allow the user to edit the subject by showing a new field on the form. The
  field will be populated with the default subject or the subject you have set with the previous option.

  Example: `[contact-form subject="My Contact Form" show_subject="yes"]`

== Installation ==

1. Upload the entire `send-e-mail` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Add a contact form to any post or page by inserting `[contact-form]` in the post.

== Frequently Asked Questions ==

= What about localization ? =

This plugin was full internationalized because use localized string from Wordpress core.

= What about spam? Will I get a lot from the contact form? =

If you have [Akismet](http://akismet.com "Akismet Site") installed on your blog, you shouldn't get much spam.
All the messages people send to you through the contact form will be filtered through Akismet.

= Anyone can put whatever they want in the name and email boxes. How can I know who's really sending the message? =

If a logged member of your site sends you a message, the end of the email will let you know that the message was sent by a verified user.
Otherwise, you can't trust anything... just like a blog comment.

Anonymity is both a curse and a blessing :)

= My blog has multiple authors. Who gets the email? =

By default, the email is sent to the author of the post with the contact form in it. So each author on your blog can have his or her own contact form.

In the contact form shortcode, you can specify what email address(es) messages should be sent to with the `to` parameter.

= Great! But how will my visitors know who they're sending a message to? =

Just make the title of your post "Contact Mary" or put "Hey, drop John a line with the form below" in the body of your post.

== Screenshots ==

1. Send E-mail - English
2. Send E-mail - Español
3. Send E-mail - Italiano

== Changelog ==

= 1.3 =
* Tested with WordPress 3.1.
* More internationalized text.
* Fixed some typo.
* This version is aligned with the 1.2 version of Grunion Contact Form.
* Fix a PHP Warning in some CGI environments.
* Move to shortcode API.
* Add `to`, `subject` and `show-subject` options.
* Allow use in text widgets.
* Move spam check to a filter.

= 1.2 =
* Tested with WordPress 2.9
* Fix in the readme.txt

= 1.1 =
* Imported from Grunion Contact Form.
* Added full internationalization using localized string from Wordpress core.

== Upgrade Notice ==

= 1.3 =
Now with more internationalized text, options and a fixes.

= 1.2 =
This version was tested with WordPress 2.9

= 1.1 =
This version adds full internationalization to plugin using localized string from Wordpress core.

== License ==

= Send E-mail =

Copyright (C) 2011 Paolo Melchiorre

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but **without any warranty**; without even the implied warranty of **merchantability** or **fitness for a particular purpose**. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see [Licenses - GNU Project](http://www.gnu.org/licenses/ "Licenses - GNU Projec").

== Donation ==

This plugin is free for everyone! Since it's released under the **GPL License**, you can use it free of charge on your personal or commercial blog. But if you enjoy this plugin, you can thank me and leave a [small donation](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=10617157 "Donate with PayPal") for the time I've spent writing and supporting this plugin.
