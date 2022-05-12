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
	 * Get all the action hooks for this specific theme
	 *
	 * @param  array $hooks Action hooks that the theme can display - default is empty
	 * @return array $hooks
	 */
	public function getHooks( $hooks ) {
		/**
		 * Bail early if the theme already handled their own hooks
		 */
		if( ! empty( $hooks ) ) {
			return $hooks;
		}
		/**
		 * Add our known defaults
		 */
		$hooks = [
			'et_before_main_content',
			'et_after_main_content',
			'et_header_top',
			'et_before_post',
			'et_before_content',
			'et_after_post',
			'et_fb_before_comments_template',
			'et_fb_after_comments_template',
			'et_block_template_canvas_main_content',
		];
		return $hooks;
	}
}