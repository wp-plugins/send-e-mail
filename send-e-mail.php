<?php

/*
Plugin Name: Send E-mail
Description: Add a contact form to any post, page or text widget. Messages will be sent to any email address you choose. As seen on wordpress.com with added i18n.
Plugin URI: http://www.paulox.net/software-libero/send-e-mail
Author: Paolo Melchiorre
Author URI: http://www.paulox.net
Version: 1.3
*/

function contact_form_shortcode( $atts ) {
	global $post;

	$default_to = get_option( 'admin_email' );
	$default_subject = "[" . get_option( 'blogname' ) . "]";

	if ( $atts['widget'] ) {
		$default_subject .=  " Sidebar";
	} elseif ( $post->ID ) {
		$default_subject .= " ". wp_kses( $post->post_title, array() );
		$post_author = get_userdata( $post->post_author );
		$default_to = $post_author->user_email;
	}

	extract( shortcode_atts( array(
		'to' => $default_to,
		'subject' => $default_subject,
		'show_subject' => 'no',
		'widget' => 0 //This is not exposed to the user. Works with contact_form_widget_atts
	), $atts ) );

	if ( ( function_exists( 'faux_faux' ) && faux_faux() ) || is_feed() )
		return '[contact-form]';

	global $wp_query, $contact_form_errors, $contact_form_values, $user_identity, $contact_form_last_id;

	if ( $widget )
		$id = 'widget-' . $widget;
	elseif ( is_singular() )
		$id = $wp_query->get_queried_object_id();
	else
		$id = $GLOBALS['post']->ID;
	if ( !$id ) // something terrible has happened
		return '[contact-form]';

	if ( $id == $contact_form_last_id )
		return;
	else
		$contact_form_last_id = $id;

	ob_start();
		wp_nonce_field( 'contact-form_' . $id );
		$nonce = ob_get_contents();
	ob_end_clean();

	$message_sent = contact_form_send_message( $to, $subject, $widget );

	if ( is_array( $contact_form_values ) )
		extract( $contact_form_values );

	foreach ( array( 'comment_author', 'comment_author_email', 'comment_author_url' ) as $k )
		if ( !isset( $$k ) )
			$$k = '';
		else
			$$k = attribute_escape( $$k );
	$comment_author_url = $comment_author_url ? $comment_author_url : 'http://';

	if ( !isset( $comment_content ) )
		$comment_content = '';
	else
		$comment_content = wp_specialchars( $comment_content );

	$emails = str_replace( ' ', '', $to );
	$emails = explode( ',', $emails );
	foreach ( (array) $emails as $email ) {
		if ( is_email( $email ) && ( !function_exists( 'is_email_address_unsafe' ) || !is_email_address_unsafe( $email ) ) )
			$valid_emails[] = $email;
	}
	$to = ( $valid_emails ) ? $valid_emails : $default_to;

	$r = "<div id='contact-form-$id'>\n";

	$errors = array();
	if ( is_wp_error( $contact_form_errors ) && $errors = (array) $contact_form_errors->get_error_codes() ) :
		$r .= "<div class='form-error'>\n<h3>" . __( "Caution:" ) . "</h3>\n<p>\n";
		foreach ( $contact_form_errors->get_error_messages() as $message )
			$r .= "\t$message<br />\n";
		$r .= "</p>\n</div>\n\n";
	elseif ( $message_sent ) :
		$r .= "<h3>" . __( 'Success!' ) . "</h3>\n\n";
		$r .= wpautop( $comment_content ) . "</div>";
		
		// Reset for multiple contact forms. Hacky
		$contact_form_values['comment_content'] = '';

		return $r;
	endif;

	$r .= "<form action='#contact-form-$id' method='post' class='contact-form commentsblock'>\n";
	if ( is_user_logged_in() ) :
		$r .= "\t<p>" . sprintf( __('Logged in as <a href="%1$s">%2$s</a>.'), get_option('siteurl') . '/wp-admin/profile.php', $user_identity ) . " <a href='" . wp_logout_url(get_permalink()) . "' title='" . __('Log out of this account') . "'>" . __('Log out &raquo;') . "</a></p>\n";
	else :
		$r .= "\n<p>\n";
		$r .= "\t\t<input type='text' name='comment_author' id='name-$id' value='$comment_author' class='textbox' size='35' tabindex='1' />\n";
		$r .= "\t\t<label for='name-$id' class='name" . ( in_array( 'comment_author', $errors ) ? ' form-error' : '' ) . "'>" . __('Name') ." <small><em>" . __('(required)') . "</em></small></label>\n";
		$r .= "\t</p>\n";
		$r .= "\n<p>\n";
		$r .= "\t\t<input type='text' name='comment_author_email' id='email-$id' value='$comment_author_email' class='textbox' size='35' tabindex='2' />\n";
		$r .= "\t\t<label for='email-$id' class='email" . ( in_array( 'comment_author_email', $errors ) ? ' form-error' : '' ) . "'>" . __('E-mail') ." <small><em>" . __('(required)') . "</em></small></label>\n";
		$r .= "\t</p>\n";
		$r .= "\n<p>\n";
		$r .= "\t\t<input type='text' name='comment_author_url' id='url-$id' value='$comment_author_url' class='textbox' size='35' tabindex='3' />\n";
		$r .= "\t\t<label for='url-$id' class='url'>" . __( 'Website' ) . "</label>\n";
		$r .= "\t</p>\n";
	endif;
	if ( 'yes' == strtolower( $show_subject ) ) {
		$r .= "\n<p>\n";
		$r .= "\t\t<input type='text' name='contact_form_subject' id='subject-$id' value='" . esc_attr( $subject ) . "' class='textbox'/>\n";
		$r .= "\t\t<label for='subject-$id' class='subject'>" . __( 'Subject' ) . "</label>\n";
		$r .= "\t</p>\n";
	}
	$r .= "\n<p>\n";
	$r .= "\t\t<textarea name='comment_content' id='contact-form-comment-$id' cols='60' rows='10' tabindex='4'>$comment_content</textarea>\n";
	$r .= "\t</p>\n";
	$r .= "\t<p class='contact-submit'>\n";
	$r .= "\t\t<input style='text-transform:capitalize' type='submit' tabindex='5' value='" . __( "send e-mail" ) . "' class='pushbutton-wide'/>\n";
	$r .= "\t\t$nonce\n";
	$r .= "\t\t<input type='hidden' name='contact-form-id' value='$id' />\n";
	$r .= "\t</p>\n";
	$r .= "</form>\n</div>";

	return $r;
}
add_shortcode( 'contact-form', 'contact_form_shortcode' );

function contact_form_send_message( $to, $subject, $widget ) {
	global $post;

 	if ( !isset( $_POST['contact-form-id'] ) )
		return;
		
	if ( ( $widget && 'widget-' . $widget != $_POST['contact-form-id'] ) || ( !$widget && $post->ID != $_POST['contact-form-id'] ) )
		return;

	if ( $widget )
		check_admin_referer( 'contact-form_widget-' . $widget );
	else
		check_admin_referer( 'contact-form_' . $post->ID );

	global $contact_form_values, $contact_form_errors, $current_user, $user_identity;

	$contact_form_values = array();
	$contact_form_errors = new WP_Error();

	list($comment_author, $comment_author_email, $comment_author_url) = is_user_logged_in() ?
		add_magic_quotes( array( $user_identity, $current_user->data->user_email, $current_user->data->user_url ) ) :
		array( $_POST['comment_author'], $_POST['comment_author_email'], $_POST['comment_author_url'] );

	if ( !$comment_author = stripslashes( apply_filters( 'pre_comment_author_name', $comment_author ) ) )
		$contact_form_errors->add( 'comment_author', __('Error: please fill the required fields (name, email).') );

	$comment_author_email = stripslashes( apply_filters( 'pre_comment_author_email', $comment_author_email ) );
	if ( !is_email( $comment_author_email ) )
		$contact_form_errors->add( 'comment_author_email', __( 'Error: please enter a valid email address.' ) );

	$comment_author_url = stripslashes( apply_filters( 'pre_comment_author_url', $comment_author_url ) );
	if ( 'http://' == $comment_author_url )
		$comment_author_url = '';

	$comment_content = stripslashes( $_POST['comment_content'] );
	$comment_content = trim( wp_kses( $comment_content, array() ) );
	if ( !$comment_content )
		$contact_form_errors->add( 'comment_content', __( 'Error: please type a comment.' ) );

	$contact_form_subject = stripslashes( $_POST['contact_form_subject'] );
	$contact_form_subject = trim( wp_kses( $contact_form_subject, array() ) );
	if ( !$contact_form_subject )
		$contact_form_subject = $subject;

	$vars = array( 'comment_author', 'comment_author_email', 'comment_author_url', 'contact_form_subject' );
	foreach ( $vars as $var )
		$$var = str_replace( array("\n", "\r" ), '', $$var ); // I don't know if it's possible to inject this
	$vars[] = 'comment_content';

	$contact_form_values = compact( $vars );

	if ( $contact_form_errors->get_error_codes() )
		return;

	$spam = '';
	$is_spam = contact_form_is_spam( $contact_form_values );
	if ( is_wp_error( $is_spam ) )
		return; // abort
	else if ( $is_spam )
		$spam = '***SPAM*** ';

	$headers =	"From: $comment_author <$comment_author_email>\n" .
			"Reply-To: $comment_author_email\n" .
			"Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";

	$subject = apply_filters( 'contact_form_subject', $spam . $contact_form_subject );
	$time_string = __('Y-m-d G:i:s');
	$time = date_i18n( __($time_string), current_time( 'timestamp' ) );
	$ip = preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );

	$message = "$comment_content

-- 
";
	$message .= __("Name");
	$message .= ": $comment_author (";
	$message .= is_user_logged_in() ?
			__( "OK" ) :
			__( "Invalid user ID." );
	$message .= ")
";
	$message .= __("E-mail");
	$message .= ": $comment_author_email
";
	$message .= __("Website");
	$message .= ": $comment_author_url
";
	$message .= __("IP");
	$message .= ": $ip
";

	$message = apply_filters( 'contact_form_message', $message );

	$to = apply_filters( 'contact_form_to', $to );

	return wp_mail( $to, $subject, $message, $headers );
}

/*
 * @return	true: it's spam, mark it as such
 * 		false: it's not spam, let it ride
 * 		WP_Error: it's spam, abort
 */
function contact_form_is_spam( $form ) {
	return apply_filters( 'contact_form_is_spam', false, $form );
}

function contact_form_is_spam_akismet( $return, $form ) {
	global $akismet_api_host, $akismet_api_port;

	$form['comment_type'] = 'contact_form';
	$form['user_ip']      = preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
	$form['user_agent']   = $_SERVER['HTTP_USER_AGENT'];
	$form['referrer']     = $_SERVER['HTTP_REFERER'];
	$form['blog']         = get_option( 'home' );

	$ignore = array( 'HTTP_COOKIE' );

	foreach ( $_SERVER as $k => $value )
		if ( !in_array( $k, $ignore ) && is_string( $value ) )
			$form["$k"] = $value;

	$query_string = '';
	foreach ( array_keys( $form ) as $k )
		$query_string .= $k . '=' . urlencode( $form[$k] ) . '&';

	$response = akismet_http_post( $query_string, $akismet_api_host, '/1.1/comment-check', $akismet_api_port );
	if ( 'true' == trim( $response[1] ) ) // 'true' is spam
		return new WP_Error( 'akismet' ); // abort
	return $return;
}

function contact_form_widget_atts( $text ) {
	static $widget = 0;
	
	$widget++;

	return str_replace( '[contact-form', '[contact-form widget="' . $widget . '"', $text );
}
add_filter( 'widget_text', 'contact_form_widget_atts', 0 );

function contact_form_widget_shortcode_hack( $text ) {
	$old = $GLOBALS['shortcode_tags'];
	remove_all_shortcodes();
	add_shortcode( 'contact-form', 'contact_form_shortcode' );
	$text = do_shortcode( $text );
	$GLOBALS['shortcode_tags'] = $old;
	return $text;
}

function contact_form_init() {
	if ( function_exists( 'akismet_http_post' ) )
		add_filter( 'contact_form_is_spam', 'contact_form_is_spam_akismet', 10, 2 );
	if ( !has_filter( 'widget_text', 'do_shortcode' ) )
		add_filter( 'widget_text', 'contact_form_widget_shortcode_hack', 5 );
}
add_action( 'init', 'contact_form_init' );

