<?php

/*
Plugin Name: Send E-mail
Description: Add a contact form to any post just by inserting <code>[contact-form]</code> in the post.  Emails will be sent to the post's author.  As seen on WordPress.com.
Plugin URI: http://www.paulox.net/software-libero/send-e-mail
Author: Paolo Melchiorre
Author URI: http://www.paulox.net
Version: 1.1
*/

// TODO: Move to shortcode API

function contact_form_shortcode( $content ) {
	if ( false !== strpos($content, '[contact-form]') )
		return preg_replace_callback('|(<p>)?\[contact-form\](</p>)?|i', 'contact_form_html', $content);
	return $content;
}

function contact_form_html() {
	if ( ( function_exists( 'faux_faux' ) && faux_faux() ) || is_feed() )
		return '[contact-form]';

	global $wp_query, $contact_form_errors, $contact_form_values, $user_identity;
	if ( is_singular() )
		$id = $wp_query->get_queried_object_id();
	else
		$id = $GLOBALS['post']->ID;
	if ( !$id ) // something terrible has happened
		return '[contact-form]';

	$permalink = get_permalink( $id );

	ob_start();
		wp_nonce_field( 'contact-form_' . $id );
		$nonce = ob_get_contents();
	ob_end_clean();

	if ( is_array($contact_form_values) )
		extract($contact_form_values);

	foreach ( array('comment_author', 'comment_author_email', 'comment_author_url') as $k )
		if ( !isset($$k) )
			$$k = '';
		else
			$$k = attribute_escape( $$k );
	$comment_author_url = $comment_author_url ? $comment_author_url : 'http://';

	if ( !isset($comment_content) )
		$comment_content = '';
	else
		$comment_content = wp_specialchars( $comment_content );

	$r = "<div id='contact-form-$id'>\n";

	$errors = array();
	if ( is_wp_error($contact_form_errors) && $errors = (array) $contact_form_errors->get_error_codes() ) :
		$r .= "<div class='form-error'>\n<h3>" . __('Error!') . "</h3>\n<p>\n";
		foreach ( $contact_form_errors->get_error_messages() as $message )
			$r .= "\t$message<br />\n";
		$r .= "</p>\n</div>\n\n";
	elseif ( !empty($_POST) ) :
		$r .= "<address>" . __('Submitted') . "</address>\n\n";
		$r .= wpautop( $comment_content ) . "</div>";
		return $r;
	endif;

	$r .= "<form action='$permalink#contact-form-$id' method='post' class='contact-form commentsblock'>\n";
	if ( is_user_logged_in() ) :
		$r .= "\t<p>" . sprintf(__( "Logged in as %s." ), '<a href="'.get_option('siteurl').'/wp-admin/profile.php">'.$user_identity.'</a>') . " <a href='" . wp_logout_url(get_permalink()) . "' title='" . __('Log out of this account') . "'>" . __('Log out &raquo;') . "</a></p>\n";
	else :
		$r .= "\n<p>\n";
		$r .= "\t\t<input type='text' name='comment_author' id='name-$id' value='$comment_author' class='textbox' size='35' tabindex='1' />\n";
		$r .= "\t\t<label for='name-$id' class='name" . ( in_array('comment_author', $errors) ? ' form-error' : '' ) . "'>" . __('Name') ." <small><em>" . __('(required)') . "</em></small></label>\n";
		$r .= "\t</p>\n";
		$r .= "\n<p>\n";
		$r .= "\t\t<input type='text' name='comment_author_email' id='email-$id' value='$comment_author_email' class='textbox' size='35' tabindex='2' />\n";
		$r .= "\t\t<label for='name-$id' class='email" . ( in_array('comment_author_email', $errors) ? ' form-error' : '' ) . "'>" . __('E-mail') ." <small><em>" . __('(required)') . "</em></small></label>\n";
		$r .= "\t</p>\n";
		$r .= "\n<p>\n";
		$r .= "\t\t<input type='text' name='comment_author_url' id='url-$id' value='$comment_author_url' class='textbox' size='35' tabindex='3' />\n";
		$r .= "\t\t<label for='name-$id' class='url'>" . __('Website') . "</label>\n";
		$r .= "\t</p>\n";
	endif;
	$r .= "\n<p>\n";
	$r .= "\t\t<textarea name='comment_content' id='contact-form-comment-$id' cols='60' rows='10' tabindex='4'>$comment_content</textarea>\n";
	$r .= "\t</p>\n";
	$r .= "\t<p class='contact-submit'>\n";
	$r .= "\t\t<input style=\"text-transform:capitalize\" type='submit' tabindex='5' value='" . __( "send e-mail" ) . "' class='pushbutton-wide'/>\n";
	$r .= "\t\t$nonce\n";
	$r .= "\t\t<input type='hidden' name='contact-form-id' value='$id' />\n";
	$r .= "\t</p>\n";
	$r .= "</form>\n</div>";
	return $r;
}

function contact_form_pre_head() {
	global $post, $current_user, $user_identity;
	if ( !is_singular() || false === strpos($post->post_content, '[contact-form]') )
		return;

 	if ( !isset($_POST['contact-form-id']) )
		return;

	check_admin_referer( 'contact-form_' . $post->ID );

	global $contact_form_values, $contact_form_errors;

	$contact_form_values = array();
	$contact_form_errors = new WP_Error();

	list($comment_author, $comment_author_email, $comment_author_url) = is_user_logged_in() ?
		add_magic_quotes(array($user_identity, $current_user->data->user_email, $current_user->data->user_url)) :
		array($_POST['comment_author'], $_POST['comment_author_email'], $_POST['comment_author_url']);

	if ( !$comment_author = stripslashes(apply_filters( 'pre_comment_author_name', $comment_author )) )
		$contact_form_errors->add( 'comment_author', __('You must enter your name.') );

	$comment_author_email = stripslashes(apply_filters( 'pre_comment_author_email', $comment_author_email ));
	if ( !is_email($comment_author_email) )
		$contact_form_errors->add( 'comment_author_email', __('You must enter a valid email address.') );

	$comment_author_url = stripslashes(apply_filters( 'pre_comment_author_url', $comment_author_url ));
	if ( 'http://' == $comment_author_url )
		$comment_author_url = '';

	$comment_content = stripslashes($_POST['comment_content']);
	$comment_content = trim(wp_kses( $comment_content, array() ));
	if ( !$comment_content )
		$contact_form_errors->add( 'comment_content', __('You did not enter a comment!') );

	$vars = array('comment_author', 'comment_author_email', 'comment_author_url');
	foreach ( $vars as $var )
		$$var = str_replace(array("\n", "\r"), '', $$var); // I don't know if it's possible to inject this
	$vars[] = 'comment_content';

	$contact_form_values = compact($vars);

	if ( $contact_form_errors->get_error_codes() )
		return;

	if ( contact_form_is_spam( $contact_form_values ) )
		return;

	$post_author = get_userdata( $post->post_author );

	$headers =	"MIME-Version: 1.0\n" .
			"From: $comment_author <$comment_author_email>\n" .
			"Reply-To: $comment_author_email\n" .
			"Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";

	$subject = "[" . get_option( 'blogname' ) . "] " . wp_kses( $post->post_title, array() );
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

	wp_mail( $post_author->user_email, $subject, $message, $headers );
}

function contact_form_is_spam( $form ) {
	if ( !function_exists( 'akismet_http_post' ) )
		return false;

	global $akismet_api_host, $akismet_api_port;

	$form['comment_type'] = 'contact_form';
	$form['user_ip']      = preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
	$form['user_agent']   = $_SERVER['HTTP_USER_AGENT'];
	$form['referrer']     = $_SERVER['HTTP_REFERER'];
	$form['blog']         = get_option( 'home' );

	$ignore = array( 'HTTP_COOKIE' );

	foreach ( $_SERVER as $k => $value )
		if ( !in_array($k, $ignore) && is_string( $value ) )
			$form["$k"] = $value;

	$query_string = '';
	foreach ( array_keys($form) as $k )
		$query_string .= $k . '=' . urlencode( $form[$k] ) . '&';

	$response = akismet_http_post($query_string, $akismet_api_host, '/1.1/comment-check', $akismet_api_port);
	if ( 'true' == trim($response[1]) ) // true is spam
		return true;
	return false;
}

function contact_form_auto_p( $text ) {
	$text = str_replace( '[contact-form]', "\n[contact-form]\n", $text);
	return preg_replace( '|[\s]*\[contact-form\][\s]*|', "\n\n[contact-form]\n\n", $text );
}

add_filter( 'content_save_pre', 'contact_form_auto_p' );

add_action( 'wp', 'contact_form_pre_head' );
add_filter( 'the_content', 'contact_form_shortcode' );
?>
