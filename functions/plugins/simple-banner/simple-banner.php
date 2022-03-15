<?php

define ('VERSION', '2.10.6');

register_activation_hook( __FILE__, 'simple_banner_activate' );
function simple_banner_activate() {
	add_action('admin_menu', 'simple_banner_menu');
}

function get_stripped_option($string) {
	$allowed_html = wp_kses_allowed_html('post');
	$string_value = wp_kses(get_option( $string ), $allowed_html, []);
	$stripped_string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string_value );
 
    return $stripped_string;
}

// Disabled Pages/Posts functionns
function get_disabled_pages_array() {
	return array_filter(explode(',', get_stripped_option('disabled_pages_array')));
}
function get_post_object() {
	return get_posts(array('include' => array(get_the_ID())));
}
function get_is_current_page_a_post() {
	return !empty(get_post_object());
}
function get_disabled_on_posts() {
	return get_stripped_option('disabled_on_posts');
}
function get_disabled_on_current_page() {
	$disabled_on_current_page = (!empty(get_disabled_pages_array()) && in_array(get_the_ID(), get_disabled_pages_array()))
								|| (get_disabled_on_posts() && get_is_current_page_a_post());
	return $disabled_on_current_page;
}


add_action( 'wp_enqueue_scripts', 'simple_banner' );
function simple_banner() {
    // Enqueue the style
	wp_register_style('simple-banner-style',  get_stylesheet_directory_uri() . '/functions/plugins/simple-banner/simple-banner.css', '', VERSION);
    wp_enqueue_style('simple-banner-style');
	// Set Script parameters
	$disabled_on_current_page = get_disabled_on_current_page();
	$script_params = array(
		// script specific parameters
		'version' => VERSION,
		'hide_simple_banner' => get_stripped_option('hide_simple_banner'),
		'simple_banner_position' => get_stripped_option('simple_banner_position'),
		'header_margin' => get_stripped_option('header_margin'),
		'header_padding' => get_stripped_option('header_padding'),
		'simple_banner_text' => get_stripped_option('simple_banner_text'),
		'pro_version_enabled' => get_stripped_option('pro_version_enabled'),
		'disabled_on_current_page' => $disabled_on_current_page,
		// debug specific parameters
		'debug_mode' => get_stripped_option('debug_mode'),
		'id' => get_the_ID(),
		'disabled_pages_array' => get_disabled_pages_array(),
		// 'post_object' => get_post_object(),
		'is_current_page_a_post' => get_is_current_page_a_post(),
		'disabled_on_posts' => get_disabled_on_posts(),
		'simple_banner_font_size' => get_stripped_option('simple_banner_font_size'),
		'simple_banner_color' => get_stripped_option('simple_banner_color'),
		'simple_banner_text_color' => get_stripped_option('simple_banner_text_color'),
		'simple_banner_link_color' => get_stripped_option('simple_banner_link_color'),
		'simple_banner_close_color' => get_stripped_option('simple_banner_close_color'),
		'simple_banner_text' => $disabled_on_current_page ? '' : get_stripped_option('simple_banner_text'),
		'simple_banner_custom_css' => get_stripped_option('simple_banner_custom_css'),
		'simple_banner_scrolling_custom_css' => get_stripped_option('simple_banner_scrolling_custom_css'),
		'simple_banner_text_custom_css' => get_stripped_option('simple_banner_text_custom_css'),
		'simple_banner_button_css' => get_stripped_option('simple_banner_button_css'),
		'site_custom_css' => get_stripped_option('site_custom_css'),
		'keep_site_custom_css' => get_stripped_option('keep_site_custom_css'),
		'site_custom_js' => get_stripped_option('site_custom_js'),
		'keep_site_custom_js' => get_stripped_option('keep_site_custom_js'),
		'wp_body_open_enabled' => get_stripped_option('wp_body_open_enabled'),
		'wp_body_open' => function_exists('wp_body_open'),
		'close_button_enabled' => get_stripped_option('close_button_enabled'),
		'close_button_expiration' => get_stripped_option('close_button_expiration'),
		'close_button_cookie_set' => isset($_COOKIE['simplebannerclosed']),
	);
	// Enqueue the script
    wp_register_script('simple-banner-script', get_stylesheet_directory_uri() . '/functions/plugins/simple-banner/simple-banner.js', array( 'jquery' ), VERSION);
	wp_localize_script('simple-banner-script', 'simpleBannerScriptParams', $script_params);
    wp_enqueue_script('simple-banner-script');
}

// Use `wp_body_open` action
if ( function_exists( 'wp_body_open' ) && get_stripped_option('wp_body_open_enabled') ) {
	add_action( 'wp_body_open', 'simple_banner_body_open' );
}
function simple_banner_body_open() {
	// if not disabled use wp_body_open
	$disabled_on_current_page = get_disabled_on_current_page();
	$close_button_enabled = get_stripped_option('close_button_enabled');
	$closed_cookie = $close_button_enabled && isset($_COOKIE['simplebannerclosed']);
	$closed_button = get_stripped_option('close_button_enabled') ? '<button id="simple-banner-close-button" class="simple-banner-button">&#x2715;</button>' : '';

	if (!$disabled_on_current_page && !$closed_cookie) {
		echo '<div id="simple-banner" class="simple-banner" role="banner"><div class="simple-banner-text"><span>' 
		. get_stripped_option('simple_banner_text') 
		. '</span></div>' 
		. $closed_button 
		. '</div>';
	}
}

// Prevent CSS removal from optimizer plugins by putting a dummy item in the
//add_action( 'wp_footer', 'prevent_css_removal');
//function prevent_css_removal()
//{
	//echo '<div class="simple-banner simple-banner-text" style="display:none !important"></div>';
//}

// Add custom CSS/JS
add_action( 'wp_head', 'simple_banner_custom_options');
function simple_banner_custom_options()
{
	$closed_cookie = get_stripped_option('close_button_enabled') && isset($_COOKIE["simplebannerclosed"]);

	$disabled_on_current_page = get_disabled_on_current_page();
	$banner_is_disabled = $disabled_on_current_page || get_stripped_option('hide_simple_banner') == "yes";

	if ($banner_is_disabled || $closed_cookie){
		echo '<style type="text/css">.simple-banner{display:none;}</style>';
	}

	if (!$banner_is_disabled && !$closed_cookie && get_stripped_option('header_margin') != ""){
		echo '<style id="simple-banner-header-margin" type="text/css">header{margin-top:' . get_stripped_option('header_margin') . ';}</style>';
	}

	if (!$banner_is_disabled && !$closed_cookie && get_stripped_option('header_padding') != ""){
		echo '<style id="simple-banner-header-padding" type="text/css" >header{padding-top:' . get_stripped_option('header_padding') . ';}</style>';
	}

	if (get_stripped_option('simple_banner_position') != ""){
		if (get_stripped_option('simple_banner_position') == 'footer'){
			echo '<style type="text/css">.simple-banner{position:fixed;bottom:0;}</style>';
		} else {
			echo '<style type="text/css">.simple-banner{position:' . get_stripped_option('simple_banner_position') . ';}</style>';
		}
	}

	if (get_stripped_option('simple_banner_font_size') != ""){
		echo '<style type="text/css">.simple-banner .simple-banner-text{font-size:' . get_stripped_option('simple_banner_font_size') . ';}</style>';
	}

	if (get_stripped_option('simple_banner_color') != ""){
		echo '<style type="text/css">.simple-banner{background:' . get_stripped_option('simple_banner_color') . ';}</style>';
	} else {
		echo '<style type="text/css">.simple-banner{background: #b31b1b;}</style>';
	}

	if (get_stripped_option('simple_banner_text_color') != ""){
		echo '<style type="text/css">.simple-banner .simple-banner-text{color:' . get_stripped_option('simple_banner_text_color') . ';}</style>';
	} else {
		echo '<style type="text/css">.simple-banner .simple-banner-text{color: #ffffff;}</style>';
	}

	if (get_stripped_option('simple_banner_link_color') != ""){
		echo '<style type="text/css">.simple-banner .simple-banner-text a{color:' . get_stripped_option('simple_banner_link_color') . ';}</style>';
	} else {
		echo '<style type="text/css">.simple-banner .simple-banner-text a{color:#f16521;}</style>';
	}

	if (get_stripped_option('simple_banner_close_color') != ""){
		echo '<style type="text/css">.simple-banner .simple-banner-button{color:' . get_stripped_option('simple_banner_close_color') . ';}</style>';
	}

	if (get_stripped_option('simple_banner_custom_css') != ""){
		echo '<style type="text/css">.simple-banner{'. get_stripped_option('simple_banner_custom_css') . '}</style>';
	}

	if (get_stripped_option('simple_banner_scrolling_custom_css') != ""){
		echo '<style type="text/css">.simple-banner.simple-banner-scrolling{'. get_stripped_option('simple_banner_scrolling_custom_css') . '}</style>';
	}

	if (get_stripped_option('simple_banner_text_custom_css') != ""){
		echo '<style type="text/css">.simple-banner .simple-banner-text{'. get_stripped_option('simple_banner_text_custom_css') . '}</style>';
	}

	if (get_stripped_option('simple_banner_button_css') != ""){
		echo '<style type="text/css">.simple-banner .simple-banner-button{'. get_stripped_option('simple_banner_button_css') . '}</style>';
	}

	$remove_site_custom_css = ($banner_is_disabled || $closed_cookie) && get_stripped_option('keep_site_custom_css') == "";
	if (!$remove_site_custom_css && get_stripped_option('site_custom_css') != "" && get_stripped_option('pro_version_enabled')) {
		echo '<style id="simple-banner-site-custom-css" type="text/css">'. get_stripped_option('site_custom_css') . '</style>';
	} else {
		// put a dummy element to see if css is being bundled
		echo '<style id="simple-banner-site-custom-css-dummy" type="text/css"></style>';
	}

	$remove_site_custom_js = ($banner_is_disabled || $closed_cookie) && get_stripped_option('keep_site_custom_js') == "";
	if (!$remove_site_custom_js && get_stripped_option('site_custom_js') != "" && get_stripped_option('pro_version_enabled')) {
		echo '<script id="simple-banner-site-custom-js" type="text/javascript">'. get_stripped_option('site_custom_js') . '</script>';
	} else {
		// put a dummy element to see if scripts are being bundled
		echo '<script id="simple-banner-site-custom-js-dummy" type="text/javascript"></script>';
	}
}

add_action('admin_menu', 'simple_banner_menu');
function simple_banner_menu() {
	$manage_simple_banner = 'manage_simple_banner';
	$manage_options = 'manage_options';
	// Add admin access
	$admin = get_role( 'administrator' );
	if ($admin) {
		$admin->add_cap( $manage_simple_banner );
	}

	$permissions_array = get_stripped_option('permissions_array');

	// Add permissions for other roles
	foreach (get_editable_roles() as $role_name => $role_info) {
		if ( $role_name !== 'administrator') {
			if (in_array($role_name, explode(",", $permissions_array))) {
				$add_role = get_role( $role_name );
				$add_role->add_cap( $manage_simple_banner );
				$add_role->add_cap( $manage_options );
			} else {
				$remove_role = get_role( $role_name );
				// only remove capabilities if they were previously added
				if ($remove_role->has_cap( $manage_simple_banner )){
					$remove_role->remove_cap( $manage_simple_banner );
					$remove_role->remove_cap( $manage_options );
				}
			}
		}
	}

	add_menu_page('Simple Banner Settings', 'Simple Banner', $manage_simple_banner, 'simple-banner-settings', 'simple_banner_settings_page', 'dashicons-admin-generic');
}



add_action( 'admin_init', 'simple_banner_settings' );
function simple_banner_settings() {
	register_setting( 'simple-banner-settings-group', 'hide_simple_banner' );
	register_setting( 'simple-banner-settings-group', 'simple_banner_font_size' );
	register_setting( 'simple-banner-settings-group', 'simple_banner_color' );
	register_setting( 'simple-banner-settings-group', 'simple_banner_text_color' );
	register_setting( 'simple-banner-settings-group', 'simple_banner_link_color' );
	register_setting( 'simple-banner-settings-group', 'simple_banner_close_color' );
	register_setting( 'simple-banner-settings-group', 'simple_banner_text' );
	register_setting( 'simple-banner-settings-group', 'simple_banner_custom_css' );
	register_setting( 'simple-banner-settings-group', 'simple_banner_scrolling_custom_css' );
	register_setting( 'simple-banner-settings-group', 'simple_banner_text_custom_css' );
	register_setting( 'simple-banner-settings-group', 'simple_banner_button_css' );
	register_setting( 'simple-banner-settings-group', 'simple_banner_position' );
	register_setting( 'simple-banner-settings-group', 'header_margin' );
	register_setting( 'simple-banner-settings-group', 'header_padding' );
	register_setting( 'simple-banner-settings-group', 'pro_version_activation_code' );
	register_setting( 'simple-banner-settings-group', 'pro_version_enabled' );
	register_setting( 'simple-banner-settings-group', 'disabled_on_posts' );
	register_setting( 'simple-banner-settings-group', 'disabled_pages_array' );
	register_setting( 'simple-banner-settings-group', 'permissions_array' );
	register_setting( 'simple-banner-settings-group', 'site_custom_css' );
	register_setting( 'simple-banner-settings-group', 'keep_site_custom_css' );
	register_setting( 'simple-banner-settings-group', 'site_custom_js' );
	register_setting( 'simple-banner-settings-group', 'keep_site_custom_js' );
	register_setting( 'simple-banner-settings-group', 'debug_mode' );
	register_setting( 'simple-banner-settings-group', 'wp_body_open_enabled' );
	register_setting( 'simple-banner-settings-group', 'close_button_enabled' );
	register_setting( 'simple-banner-settings-group', 'close_button_expiration' );
}

function simple_banner_settings_page() {
	?>
	<?php
		if (esc_attr( get_stripped_option('pro_version_activation_code') ) == "SBPROv1-14315") {
			update_option('pro_version_enabled', true);
		} else {
			update_option('pro_version_enabled', false);
		}
	?>

	<style type="text/css" id="settings_stylesheet">
		.simple-banner-settings-form th {width: 30%;}
	</style>

	<div class="wrap">
		<div style="display: flex;justify-content: space-between;">
			<h2>Simple Banner Preview</h2>
		</div>

		<p>Links in the banner text must be typed in with HTML <code>&lt;a&gt;</code> tags.
		<br />e.g. <code>This is a &lt;a href=&#34;http:&#47;&#47;www.wordpress.com&#34;&gt;Link to Wordpress&lt;&#47;a&gt;</code>.</p>

		<!-- Preview Banner -->
		<div id="preview_banner_outer_container" style="min-height: 40px;">
			<div id="preview_banner_inner_container">
				<div id="preview_banner" class="simple-banner" style="width: 100%;text-align: center;">
					<div id="preview_banner_text" class="simple-banner-text" style="font-weight: normal; padding: 20px;">
						<span>Emergency banner for dismissible messages. You can add or remove this text on the Simple Banner admin page.</span>
					</div>
				</div>
			</div>
		</div>
		<br>

		<!-- Settings Form -->
		<form class="simple-banner-settings-form" method="post" action="options.php">
			<?php settings_fields( 'simple-banner-settings-group' ); ?>
			<?php do_settings_sections( 'simple-banner-settings-group' ); ?>

			<table class="form-table">

				<!-- Close Button -->
				<tr valign="top" style="display: none;">
					<th scope="row">
						Close button enabled
						<br>
						<span style="font-weight:400;">
							This feature uses strictly necessary cookies which do not require consent from users per <a href="https://gdpr.eu/cookies/">GDPR</a> guidelines
						</span>
					</th>
					<td>
						<?php
							$checked = get_stripped_option('close_button_enabled') ? 'checked ' : '';
							echo '<input type="checkbox" id="close_button_enabled" '. $checked . ' name="close_button_enabled" />';
						?>
					</td>
				</tr>
				
				<!-- Close Button -->
				<tr valign="top">
					<th scope="row">
						Simple Banner Text
						<br><span style="font-weight:400;">Leaving this blank removes the banner</span>
					</th>
						<td>
							<textarea id="simple_banner_text" class="large-text code" style="height: 150px;width: 97%;" name="simple_banner_text"><?php echo get_stripped_option('simple_banner_text'); ?></textarea>
						</td>
				</tr>
				
			</table>

			<!-- Save Changes Button -->
			<?php submit_button(); ?>
		</form>
	</div>

	<!-- Script to apply styles to Preview Banner -->
	<script type="text/javascript">
		// Simple Banner Default Stylesheet
		var simple_banner_css = document.createElement('link');
		simple_banner_css.id = 'simple-banner-stylesheet';
		simple_banner_css.rel = 'stylesheet';
		simple_banner_css.href = "<?php echo plugin_dir_url( __FILE__ ) .'simple-banner.css' ?>";
		document.getElementsByTagName('head')[0].appendChild(simple_banner_css);

		// Fixed Preview Banner on scroll
		//window.onscroll = function() {fixedBanner()};
        //function fixedBanner() {			
			//var elementContainer = document.getElementById('preview_banner_outer_container');
			//var elementTarget = document.getElementById('preview_banner_inner_container');
			//if (window.scrollY > (elementContainer.offsetTop)) {
				//elementTarget.style.position = 'fixed';
				//elementTarget.style.width = '83.671%';
				//elementTarget.style.top = '40px';
			//} else {
				//elementTarget.style.position = 'relative';
				//elementTarget.style.width = '100%';
				//elementTarget.style.top = '0';
			//}
        //}

		var style_font_size = document.createElement('style');
		var style_background_color = document.createElement('style');
		var style_link_color = document.createElement('style');
		var style_text_color = document.createElement('style');
		var style_close_color = document.createElement('style');
		var style_custom_css = document.createElement('style');
		var style_custom_text_css = document.createElement('style');
		var style_custom_button_css = document.createElement('style');

		// Banner Text
		document.getElementById('preview_banner_text').innerHTML = document.getElementById('simple_banner_text').value != "" ? 
						'<span>'+document.getElementById('simple_banner_text').value+'</span>' : 
						'<span>This is what your banner will look like with a <a href="/">link</a>.</span>';
		document.getElementById('simple_banner_text').onchange=function(e){
			document.getElementById('preview_banner_text').innerHTML = e.target.value != "" ? '<span>'+e.target.value+'</span>' : '<span>This is what your banner will look like with a <a href="/">link</a>.</span>';
		};

		// Close Button
		var closeButton = '<button id="simple-banner-close-button" class="simple-banner-button" style="float: right; position: absolute; right: 20px; border: 0; height: 100%; background: transparent; top: 28px; font-size: large; color: #ffffff;">✕</button>';
		var closeButtonChecked = document.getElementById('close_button_enabled').checked;
		var closeButtonInitialValue = closeButtonChecked ? closeButton : '';
		document.getElementById('preview_banner').innerHTML = document.getElementById('preview_banner').innerHTML + closeButtonInitialValue;
		document.getElementById('close_button_enabled').onchange=function(e){
			var str = document.getElementById('preview_banner').innerHTML; 
			if (e.target.checked) {
				document.getElementById('preview_banner').innerHTML = str + closeButton;
			} else {
				var res = str.replace(closeButton, '');
				document.getElementById('preview_banner').innerHTML = res;
			}
		};

		// Font Size
		style_font_size.type = 'text/css';
		style_font_size.id = 'preview_banner_font_size'
		style_font_size.appendChild(document.createTextNode('.simple-banner .simple-banner-text{font-size:' + (document.getElementById('simple_banner_font_size').value || '1em') + '}'));
		document.getElementsByTagName('head')[0].appendChild(style_font_size);

		document.getElementById('simple_banner_font_size').onchange=function(e){
			var child = document.getElementById('preview_banner_font_size');
			if (child){child.innerText = "";child.id='';}

			var style_dynamic = document.createElement('style');
			style_dynamic.type = 'text/css';
			style_dynamic.id = 'preview_banner_font_size';
			style_dynamic.appendChild(
				document.createTextNode(
					'.simple-banner .simple-banner-text{font-size:' + (document.getElementById('simple_banner_font_size').value || '1em') + '}'
				)
			);
			document.getElementsByTagName('head')[0].appendChild(style_dynamic);
		};

		// Background Color
		style_background_color.type = 'text/css';
		style_background_color.id = 'preview_banner_background_color'
		style_background_color.appendChild(document.createTextNode('.simple-banner{background:' + (document.getElementById('simple_banner_color').value || '#b31b1b') + '}'));
		document.getElementsByTagName('head')[0].appendChild(style_background_color);

		document.getElementById('simple_banner_color').onchange=function(e){
			document.getElementById('simple_banner_color_show').value = e.target.value || '#b31b1b';
			var child = document.getElementById('preview_banner_background_color');
			if (child){child.innerText = "";child.id='';}

			var style_dynamic = document.createElement('style');
			style_dynamic.type = 'text/css';
			style_dynamic.id = 'preview_banner_background_color';
			style_dynamic.appendChild(
				document.createTextNode(
					'.simple-banner{background:' + (document.getElementById('simple_banner_color').value || '#b31b1b') + '}'
				)
			);
			document.getElementsByTagName('head')[0].appendChild(style_dynamic);
		};
		document.getElementById('simple_banner_color_show').onchange=function(e){
			document.getElementById('simple_banner_color').value = e.target.value;
			document.getElementById('simple_banner_color').dispatchEvent(new Event('change'));
		};

		// Text Color
		style_text_color.type = 'text/css';
		style_text_color.id = 'preview_banner_text_color'
		style_text_color.appendChild(document.createTextNode('.simple-banner .simple-banner-text{color:' + (document.getElementById('simple_banner_text_color').value || '#ffffff') + '}'));
		document.getElementsByTagName('head')[0].appendChild(style_text_color);

		document.getElementById('simple_banner_text_color').onchange=function(e){
			document.getElementById('simple_banner_text_color_show').value = e.target.value || '#ffffff';
			var child = document.getElementById('preview_banner_text_color');
			if (child){child.innerText = "";child.id='';}

			var style_dynamic = document.createElement('style');
			style_dynamic.type = 'text/css';
			style_dynamic.id = 'preview_banner_text_color';
			style_dynamic.appendChild(
				document.createTextNode(
					'.simple-banner .simple-banner-text{color:' + (document.getElementById('simple_banner_text_color').value || '#ffffff') + '}'
				)
			);
			document.getElementsByTagName('head')[0].appendChild(style_dynamic);
		};
		document.getElementById('simple_banner_text_color_show').onchange=function(e){
			document.getElementById('simple_banner_text_color').value = e.target.value;
			document.getElementById('simple_banner_text_color').dispatchEvent(new Event('change'));
		};

		// Link Color
		style_link_color.type = 'text/css';
		style_link_color.id = 'preview_banner_link_color'
		style_link_color.appendChild(document.createTextNode('.simple-banner .simple-banner-text a{color:' + (document.getElementById('simple_banner_link_color').value || '#f16521') + '}'));
		document.getElementsByTagName('head')[0].appendChild(style_link_color);

		document.getElementById('simple_banner_link_color').onchange=function(e){
			document.getElementById('simple_banner_link_color_show').value = e.target.value || '#f16521';
			var child = document.getElementById('preview_banner_link_color');
			if (child){child.innerText = "";child.id='';}

			var style_dynamic = document.createElement('style');
			style_dynamic.type = 'text/css';
			style_dynamic.id = 'preview_banner_link_color';
			style_dynamic.appendChild(
				document.createTextNode(
					'.simple-banner .simple-banner-text a{color:' + (document.getElementById('simple_banner_link_color').value || '#f16521') + '}'
				)
			);
			document.getElementsByTagName('head')[0].appendChild(style_dynamic);
		};
		document.getElementById('simple_banner_link_color_show').onchange=function(e){
			document.getElementById('simple_banner_link_color').value = e.target.value;
			document.getElementById('simple_banner_link_color').dispatchEvent(new Event('change'));
		};

		// Close Color
		style_close_color.type = 'text/css';
		style_close_color.id = 'preview_banner_close_color'
		style_close_color.appendChild(document.createTextNode('.simple-banner .simple-banner-button{color:' + (document.getElementById('simple_banner_close_color').value || 'black') + '}'));
		document.getElementsByTagName('head')[0].appendChild(style_close_color);

		document.getElementById('simple_banner_close_color').onchange=function(e){
			document.getElementById('simple_banner_close_color_show').value = e.target.value || 'black';
			var child = document.getElementById('preview_banner_close_color');
			if (child){child.innerText = "";child.id='';}

			var style_dynamic = document.createElement('style');
			style_dynamic.type = 'text/css';
			style_dynamic.id = 'preview_banner_close_color';
			style_dynamic.appendChild(
				document.createTextNode(
					'.simple-banner .simple-banner-button{color:' + (document.getElementById('simple_banner_close_color').value || 'black') + '}'
				)
			);
			document.getElementsByTagName('head')[0].appendChild(style_dynamic);
		};
		document.getElementById('simple_banner_close_color_show').onchange=function(e){
			document.getElementById('simple_banner_close_color').value = e.target.value;
			document.getElementById('simple_banner_close_color').dispatchEvent(new Event('change'));
		};

		// Custom CSS
		style_custom_css.type = 'text/css';
		style_custom_css.id = 'preview_banner_custom_stylesheet'
		style_custom_css.appendChild(document.createTextNode('.simple-banner{'+document.getElementById('simple_banner_custom_css').value+'}'));
		document.getElementsByTagName('head')[0].appendChild(style_custom_css);

		document.getElementById('simple_banner_custom_css').onchange=function(){
			var child = document.getElementById('preview_banner_custom_stylesheet');
			if (child){child.innerText = "";child.id='';}

			var style_dynamic = document.createElement('style');
			style_dynamic.type = 'text/css';
			style_dynamic.id = 'preview_banner_custom_stylesheet';
			style_dynamic.appendChild(
				document.createTextNode(
					'.simple-banner{'+document.getElementById('simple_banner_custom_css').value+'}'
				)
			);
			document.getElementsByTagName('head')[0].appendChild(style_dynamic);
		};

		// Custom Text CSS
		style_custom_text_css.type = 'text/css';
		style_custom_text_css.id = 'preview_banner_custom_text_stylesheet'
		style_custom_text_css.appendChild(document.createTextNode('.simple-banner .simple-banner-text{'+document.getElementById('simple_banner_text_custom_css').value+'}'));
		document.getElementsByTagName('head')[0].appendChild(style_custom_text_css);

		document.getElementById('simple_banner_text_custom_css').onchange=function(){
			var child = document.getElementById('preview_banner_custom_text_stylesheet');
			if (child){child.innerText = "";child.id='';}

			var style_dynamic = document.createElement('style');
			style_dynamic.type = 'text/css';
			style_dynamic.id = 'preview_banner_custom_text_stylesheet';
			style_dynamic.appendChild(
				document.createTextNode(
					'.simple-banner .simple-banner-text{'+document.getElementById('simple_banner_text_custom_css').value+'}'
				)
			);
			document.getElementsByTagName('head')[0].appendChild(style_dynamic);
		};

		// Custom Button CSS
		style_custom_button_css.type = 'text/css';
		style_custom_button_css.id = 'preview_banner_custom_button_stylesheet'
		style_custom_button_css.appendChild(document.createTextNode('.simple-banner .simple-banner-button{'+document.getElementById('simple_banner_button_css').value+'}'));
		document.getElementsByTagName('head')[0].appendChild(style_custom_button_css);

		document.getElementById('simple_banner_button_css').onchange=function(){
			var child = document.getElementById('preview_banner_custom_button_stylesheet');
			if (child){child.innerText = "";child.id='';}

			var style_dynamic = document.createElement('style');
			style_dynamic.type = 'text/css';
			style_dynamic.id = 'preview_banner_custom_button_stylesheet';
			style_dynamic.appendChild(
				document.createTextNode(
					'.simple-banner .simple-banner-button{'+document.getElementById('simple_banner_button_css').value+'}'
				)
			);
			document.getElementsByTagName('head')[0].appendChild(style_dynamic);
		};

		// Permissions
		document.getElementById('simple_banner_pro_permissions').onclick=function(e){
			let permissionsArray = [];
			Array.from(document.getElementById('simple_banner_pro_permissions').getElementsByTagName('input')).forEach(function(e) {
				if (e.checked) {
					permissionsArray.push(e.value);
				}
			});
			document.getElementById('permissions_array').value = permissionsArray;
		};

		// Disabled Pages
		document.getElementById('simple_banner_pro_disabled_pages').onclick=function(e){
			let disabledPagesArray = [];
			Array.from(document.getElementById('simple_banner_pro_disabled_pages').getElementsByTagName('input')).forEach(function(e) {
				if (e.checked) {
					disabledPagesArray.push(e.value);
				}
			});
			document.getElementById('disabled_pages_array').value = disabledPagesArray;
		};

		// remove banner text newlines on submit
		document.getElementById('submit').onclick=function(e){
			document.getElementById('simple_banner_text').value = document.getElementById('simple_banner_text').value.replace(/\n/g, "");
		};
	</script>
	<?php
}

update_option('close_button_enabled', '');