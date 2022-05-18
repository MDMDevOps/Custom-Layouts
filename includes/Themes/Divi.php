<?php

namespace Mwf\CustomLayouts\Themes;

use Mwf\CustomLayouts\Framework;
use Mwf\CustomLayouts\Subscriber;

class Divi extends Framework {

	/**
	 * Construct new instance
	 *
	 */
	public function __construct() {

		$theme = wp_get_theme();

		if ( ! is_object( $theme ) || ! isset( $theme->template ) || strtolower( $theme->template ) !== 'divi' ) {
			return false;
		}

		parent::__construct();
	}
	/**
	 * Register filters
	 *
	 * Uses the subscriber class to ensure only actions of this instance are added
	 * and the instance can be referenced via subscriber
	 *
	 * @since 1.0.0
	 */
	public function addFilters() {
		Subscriber::addFilter( 'custom_layouts/fields/hooks/theme', [$this, 'getHooks'] );
	}
	/**
	 * Register actions
	 *
	 * Uses the subscriber class to ensure only actions of this instance are added
	 * and the instance can be referenced via subscriber
	 *
	 * @since 1.0.0
	 * @see  https://developer.wordpress.org/reference/functions/add_filter/
	 */
	public function addActions() {
		Subscriber::addAction( 'custom_layout/before_render', [$this, 'maybeRender'] );
		Subscriber::addAction( 'save_post_custom-layout', [$this, 'clearCache'] );
		Subscriber::addAction( 'wp_enqueue_scripts', [$this, 'enqueueEditStyles'] );
	}
	/**
	 * Get all the action hooks for this specific theme
	 *
	 * @param  array $hooks Action hooks that the theme can display - default is empty
	 * @return array $hooks
	 */
	public function getHooks( $hooks ) {
		/**
		 * Add our known defaults
		 */
		$default_hooks = [
			'et_before_main_content' => 'Before main Content',
			'et_after_main_content' => 'After main Content',
			'et_header_top' => 'Header Top',
			'et_before_post' => 'Before Post',
			'et_before_content' => 'Before Content',
			'et_after_post' => 'After Post',
			'et_fb_before_comments_template' => 'Before Comments Template',
			'et_fb_after_comments_template' => 'After Comments Template',
			'et_block_template_canvas_main_content' => 'Canvas Main Content',
		];
		return wp_parse_args($hooks, $default_hooks);
	}

	public function enqueueEditStyles() {
		if ( isset( $_GET['et_fb'] ) && $_GET['et_fb'] == 1 && ! wp_script_is( __NAMESPACE__ . '\editlink', 'enqueued' ) ) {
			wp_enqueue_style( __NAMESPACE__ . '\editlink', MWF_CUSTOMLAYOUTS_URL . '/assets/css/edit-link' . MWF_CUSTOMLAYOUTS_ASSET_PREFIX . '.css', [], MWF_CUSTOMLAYOUTS_VERSION, 'all' );
			wp_enqueue_script( __NAMESPACE__ . '\editlink', MWF_CUSTOMLAYOUTS_URL . '/assets/js/edit-link.min.js', ['jquery'], MWF_CUSTOMLAYOUTS_VERSION,true );
		}
	}

	public function maybeRender( $layout ) {
		if ( function_exists( 'et_pb_is_pagebuilder_used' ) && et_pb_is_pagebuilder_used( $layout->ID ) ) {
			add_action( "custom_layout/render/{$layout->ID}", [$this, 'render'] );
		}
	}

	public function clearCache() {
		if ( class_exists( 'ET_Core_PageResource' ) ) {
			\ET_Core_PageResource::remove_static_resources( 'all', 'all' );
		}
	}

	public function render( $layout ) {
		/**
		 * See if we are currently editing a page
		 */
		if ( isset( $_GET['et_fb'] ) && $_GET['et_fb'] == 1 && get_the_id() !== $layout->ID ) {

			$edit_link = get_post_permalink( $layout->ID ) . '?et_fb=1&PageSpeed=off';

			printf( '<a href="%s" class="custom-layout-edit" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13.89 3.39l2.71 2.72c.46.46.42 1.24.03 1.64l-8.01 8.02-5.56 1.16 1.16-5.58s7.6-7.63 7.99-8.03c.39-.39 1.22-.39 1.68.07zm-2.73 2.79l-5.59 5.61 1.11 1.11 5.54-5.65zm-2.97 8.23l5.58-5.6-1.07-1.08-5.59 5.6z"></path></svg></a>', $edit_link );
		}
		/**
		 * Output content
		 */
		echo et_builder_render_layout( get_the_content( null, true, $layout ) );
	}
}