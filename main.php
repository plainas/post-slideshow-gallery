 <?php
/*
Plugin Name: Post Slideshow Gallery
Plugin URI: http://lamehacks.net
Description: A barebones Plugin to create slideshows with the pictures attached to a post. This plugin is suposed to be simple to use and to do one thing and do it well. It uses the awesome jquery based galleria (http://galleria.aino.se/). Capable of rendering more than one gallery per page.
Version: 0.1
Author: Pedro
Author URI: http://lamehacks.net
License: MIT
.
There are many slideshow gallery plugins for wordpress, unfortunately most of them are buggy (no offense) and uncapable of rendering more than one gallery per page. Often they're too bloated with features of questionable utility.

With this plugin you can create a gallery per post and they can show in the same page.

This is a very barebones plugin, it aims to do one thing well and nothing else. There are no fancy extra features. Just insert [postslideshow] on your post content where you want the slideshow to appear and you're good to go. All the pictures of the current post will be included in the slideshow. 
.
*/

$postgalconf["canvas_size"]	= array(800,600);
$postgalconf["thumbnail_size"]	= array(75,75);


$css_url = plugins_url('psgoverlay.css',__FILE__);

wp_register_style('overlay_style_sheet', $css_url);
wp_enqueue_style( 'overlay_style_sheet');


wp_enqueue_script('jquery');
add_action('wp_head', 'psg_head_includes');
add_shortcode( 'postslideshow', 'psg_output_slideshow' );




function psg_head_includes(){
	echo '<script src="'.plugins_url().'/post-slideshow-gallery/galleria/galleria-1.2.5.min.js" type="text/javascript"></script>';
	echo '<script src="'.plugins_url().'/post-slideshow-gallery/galleria/themes/classic/galleria.classic.js" type="text/javascript"></script>';
	echo '<link rel="stylesheet" href="'.plugins_url().'/post-slideshow-gallery/galleria/themes/classic/galleria.classic.css" /> ';
}

function psg_output_slideshow(){
	
	$args = array( 	'post_type'   	 	=> 'attachment',
					'post_mime_type' 	=> 'image',
					'post_parent' 	 	=> get_the_ID(),	
					'numberposts'    	=> -1,
					'orderby'	  		=> 'menu_order',
					'order'           	=> 'ASC',
					'post_status' 		=> null ); 
	
	$images  = get_posts($args);
	
	$prepared_images = array_map("prepare_images_array", $images);
	return get_slideshow_html($prepared_images);
}

function prepare_images_array($image){
	global $postgalconf;
	$iaux = wp_get_attachment_image_src($image->ID, $postgalconf["canvas_size"]);
	$taux = wp_get_attachment_image_src($image->ID, $postgalconf["thumbnail_size"]);
	
	//$url_pieces = parse_url($iaux[0]);
	
	$return_image["image"]	= $iaux[0];
	$return_image["thumb"]	= $taux[0];
	//TODO: sanitize these fields... is it necessary?
	$return_image["layer"]	= get_layer_html($image);
	return $return_image;
}

function get_slideshow_html($images){
	ob_start();
	include "gallery-template.php";
	$html = ob_get_clean();
	return $html;

}

function get_layer_html($data){

	$out = <<<ltemplate
			<div class="psgwrapper">
			<h4 class="psgtitle">{$data->post_title}</h4>
			<p class="psgdescription">{$data->post_content}</p>
			</div>
ltemplate;

	//carregar aqui um template como diz em get_slideshow_html
	//return print_r($data, true);
	return $out;
}

//**********************************************************************
// add the admin options page


add_action('admin_menu', 'my_plugin_menu');

function my_plugin_menu() {
	add_options_page('Post slideshow Gallery Options', 'Post slideshow Gallery', 'manage_options', 'post-gallery-slideshow', 'post_gallery_slideshow_options');
}

function post_gallery_slideshow_options() {
	?>
	<div class="wrap">
		<h2>Post Galelry Slideshow</h2>
		<form action="options.php" method="post">
			<?php settings_fields('post-slideshow-options'); ?>
			<?php do_settings_sections('plugin'); ?>	
			<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
		</form>
	</div>
	<?php
}

// add the admin settings and such
add_action('admin_init', 'plugin_admin_init');

function plugin_admin_init(){
	register_setting( 'post-slideshow-options', 'post-slideshow-options', 'slideshow_options_validate' );
	add_settings_section('slideshow_dimensions', 'Dimensions', 'gallery_dimensions_text', 'plugin');
	add_settings_field('width', 'width', 'width_output_callback', 'plugin', 'slideshow_dimensions');
	add_settings_field('height', 'height', 'height_output_callback', 'plugin', 'slideshow_dimensions');
}


function gallery_dimensions_text(){
	?>
	<p>Define the side of your slideshows here.<br />
	Dimensions can be set in pixels or in percentage. Example of valid values '30px', '200px', '80%'.<br />
	Make sure you include the units and no space in between the number and 'px' or '%' </p>
	<?php
}

function width_output_callback() {
	$options = get_option('post-slideshow-options');
	echo "<input id='width' name='post-slideshow-options[width]' size='10' type='text' value='{$options['width']}' />";
}

function height_output_callback() {
	$options = get_option('post-slideshow-options');
	echo "<input id='height' name='post-slideshow-options[height]' size='10' type='text' value='{$options['height']}' />";
}


function slideshow_options_validate($input) {
	$input['width'] = trim($input['width']);
	$input['height'] = trim($input['height']);
	
	if(!preg_match('/^[0-9]{1,3}(px|%)$/i', $input['width'])) {
		$input['width'] = '100%'; 
	}
	$input['height'] = trim($input['height']);
	if(!preg_match('/^[0-9]{1,3}(px|%)$/i', $input['height'])) {
		$input['height'] = '360px';
	}
	return $input;
}
?>




