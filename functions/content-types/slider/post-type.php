<?php

// Slider content type
function cptui_register_my_cpts_slider() {

	$labels = [
		"name" => __( "Slider", "cwd_cornell" ),
		"singular_name" => __( "Slider", "cwd_cornell" ),
	];

	$args = [
		"label" => __( "Slider", "cwd_cornell" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => false,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => [ "slug" => "slider", "with_front" => true ],
		"query_var" => true,
		"menu_icon" => "dashicons-format-gallery",
		"supports" => [ "title", "thumbnail", "revisions" ],
	];

	register_post_type( "slider", $args );
}
add_action( 'init', 'cptui_register_my_cpts_slider' );