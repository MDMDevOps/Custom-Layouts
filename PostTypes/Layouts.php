<?php

/**
 * Sample Post Type
 *
 * @link    https://www.wpcodelabs.com
 * @since   1.0.0
 * @package plugin_scaffolding
 */

namespace Mwf\CustomLayouts\PostTypes;

use \Mwf\CustomLayouts\Framework;
use \Mwf\CustomLayouts\Subscriber;

class Layouts extends Framework {
	/**
	 * Name of the custom taxonomy
	 * @since 1.0.0
	 */
	const NAME = 'custom-layout';
	/**
	 * Register actions
	 *
	 * Uses the subscriber class to ensure only actions of this instance are added
	 * and the instance can be referenced via subscriber
	 *
	 * @since 1.0.0
	 */
	public function addActions() {
		Subscriber::addAction( 'save_post_custom-layout', [$this, 'invalidateTransient'] );
		Subscriber::addAction( 'add_meta_boxes-layout', [$this, 'removeYoastMetabox'], 999 );
	}
	/**
	 * Register custom post type
	 *
	 * I recommend using a tool such as GenerateWP to easily generate post type arguments
	 *
	 * @see https://generatewp.com/post-type/
	 * @since 1.0.0
	 */
	public function register() {
		$labels = [
			'name'                  => _x( 'Custom Layouts', 'Post Type General Name', 'mwf_custom_layouts' ),
			'singular_name'         => _x( 'Custom Layout', 'Post Type Singular Name', 'mwf_custom_layouts' ),
			'menu_name'             => __( 'Custom Layouts', 'mwf_custom_layouts' ),
			'name_admin_bar'        => __( 'Custom Layouts', 'mwf_custom_layouts' ),
			'parent_item_colon'     => __( 'Parent Layout:', 'mwf_custom_layouts' ),
			'all_items'             => __( 'Custom Layouts', 'mwf_custom_layouts' ),
			'add_new_item'          => __( 'Add New Layout', 'mwf_custom_layouts' ),
			'add_new'               => __( 'Add New', 'mwf_custom_layouts' ),
			'new_item'              => __( 'New Layout', 'mwf_custom_layouts' ),
			'edit_item'             => __( 'Edit Layout', 'mwf_custom_layouts' ),
			'update_item'           => __( 'Update Layout', 'mwf_custom_layouts' ),
			'view_item'             => __( 'View Layout', 'mwf_custom_layouts' ),
			'search_items'          => __( 'Search Layouts', 'mwf_custom_layouts' ),
			'not_found'             => __( 'Not found', 'mwf_custom_layouts' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'mwf_custom_layouts' ),
			'items_list'            => __( 'Layout list', 'mwf_custom_layouts' ),
			'items_list_navigation' => __( 'Layout list navigation', 'mwf_custom_layouts' ),
			'filter_items_list'     => __( 'Filter block list', 'mwf_custom_layouts' ),
        ];
		$rewrite = [
			'slug'                  => 'custom-layout',
			'with_front'            => true,
			'pages'                 => true,
			'feeds'                 => true,
        ];
		$args = [
			'label'                 => __( 'Custom Layout', 'mwf_custom_layouts' ),
			'description'           => __( 'Custom Layouts', 'mwf_custom_layouts' ),
			'labels'                => $labels,
			'supports'              => [ 'title', 'editor', 'revisions' ],
			'hierarchical'          => true,
			'public'                => is_admin(),
			'show_ui'               => true,
			'show_in_menu'          => 'themes.php',
			// 'show_in_menu'          => true,
			'menu_position'         => 20,
			'menu_icon'             => 'dashicons-text',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_frommwf_custom_layoutsearch'   => true,
			'publicly_queryable'    => is_user_logged_in(),
			'capability_type'       => 'page',
			'show_in_rest'          => true,
			'rewrite'               => $rewrite,
        ];
		register_post_type( self::NAME, $args );
	}
	/**
	 * Invalidates the display transient
	 *
	 * @return void
	 */
	public function invalidateTransient() {
		delete_transient( 'custom_layouts_for_display' );
	}
	/**
	 * Remove the yoast metabox, it's unneeded
	 */
	public function removeYoastMetabox() {
		remove_meta_box( 'wpseo_meta', 'custom-layout', 'normal' );
	}
}