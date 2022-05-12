<?php

namespace Mwf\CustomLayouts\Themes;

use Mwf\CustomLayouts\Framework;
use Mwf\CustomLayouts\Subscriber;

class Genesis extends Framework {

	/**
	 * Construct new instance
	 *
	 */
	public function __construct() {

		$theme = wp_get_theme();

		if ( ! is_object( $theme ) || ! isset( $theme->template ) || $theme->template !== 'genesis' ) {
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
			'genesis_before' => 'Genesis Before',
			'genesis_before_header' => 'Genesis Before header',
			'genesis_header_right' => 'Genesis Header Right',
			'genesis_after_header' => 'Genesis After Header',
			'genesis_before_content_sidebar_wrap' => 'Gensis Before Content Sidebar Wrap',
			'genesis_before_content' => 'Genesis Before Content',
			'genesis_before_loop' => 'Genesis Before Loop',
			'genesis_loop' => 'Genesis Loop',
			'genesis_before_while' => 'Genesis Before While',
			'genesis_after_endwhile' => 'Genesis After While',
			'genesis_after_loop' => 'Genesis After Loop',
			'genesis_after_content' => 'Genesis After Content',
			'genesis_after_content_sidebar_wrap' => 'Gensis After Content Sidebar Wrap',
			'genesis_before_entry' => 'Genesis Before Entry',
			'genesis_entry_header' => 'Genesis Entry Header',
			'genesis_entry_content' => 'Genesis Entry Content',
			'genesis_404_entry_content' => 'Genesis 404 Entry Content',
			'genesis_entry_footer' => 'Genesis Entry Footer',
			'genesis_after_entry' => 'Genesis After Entry',
			'genesis_before_sidebar_widget_area' => 'Genesis Before Sidebar Widget Area',
			'genesis_sidebar' => 'Genesis Sidebar',
			'genesis_after_sidebar_widget_area' => 'Genesis After Sidebar Widget Area',
			'genesis_before_sidebar_alt_widget_area' => 'Genesis Before Sidebar Alt Widget Area',
			'genesis_sidebar_alt' => 'Genesis Sidebar Alt',
			'genesis_after_sidebar_alt_widget_area' => 'Genesis Before Sidebar Widget Area After Sidebar Alt Widget Area',
			'genesis_before_footer' => 'Genesis Before Footer',
			'genesis_footer' => 'Genesis Footer',
			'genesis_after_footer' => 'Genesis After Footer',
			'genesis_after' => 'Genesis After',
		];
		return $hooks;
	}
}