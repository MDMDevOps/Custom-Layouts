<?php

namespace Mwf\CustomLayouts\Themes;

use Mwf\CustomLayouts\Framework;
use Mwf\CustomLayouts\Subscriber;

class Astra extends Framework {

	/**
	 * Construct new instance
	 *
	 */
	public function __construct() {

		$theme = wp_get_theme();

		if ( ! is_object( $theme ) || ! isset( $theme->template ) || $theme->template !== 'astra' ) {
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
			'astra_html_before' => 'Astra Html Before',
			'astra_head_top' => 'Astra Head Top',
			'astra_head_bottom' => 'Astra Head Bottom',
			'astra_body_top' => 'Astra Body Top',
			'astra_header_before' => 'Astra Header Before',
			'astra_masthead_top' => 'Astra Masthead Top',
			'astra_main_header_bar_top' => 'Astra Main Header Bar Top',
			'astra_masthead_content' => 'Astra Masthead Content',
			'astra_masthead_toggle_buttons_before' => 'Astra Masthead Toggle Buttons Before',
			'astra_masthead_toggle_buttons_after' => 'Astra Masthead Toggle Buttons After',
			'astra_main_header_bar_bottom' => 'Astra Main Header Bar Bottom',
			'astra_masthead_bottom' => 'Astra Masthead Bottom',
			'astra_header_after' => 'Astra Header After',
			'astra_content_before' => 'Astra Content Before',
			'astra_content_top' => 'Astra Content Top',
			'astra_primary_content_top' => 'Astra Primary Content Top',
			'astra_content_loop' => 'Astra Content Loop',
			'astra_template_parts_content_none' => 'Astra Template Parts Content None',
			'astra_content_while_before' => 'Astra Content While Before',
			'astra_template_parts_content_top' => 'Astra Template Parts Content Top',
			'astra_template_parts_content' => 'Astra Template Parts Content',
			'astra_entry_before' => 'Astra Entry Before',
			'astra_entry_top' => 'Astra Entry Top',
			'astra_single_header_before' => 'Astra Single Header Before',
			'astra_single_header_top' => 'Astra Single Header Top',
			'astra_single_post_title_after' => 'Astra Single Post Title After',
			'astra_single_header_bottom' => 'Astra Single Header Bottom',
			'astra_single_header_after' => 'Astra Single Header After',
			'astra_entry_content_before' => 'Astra Entry Content Before',
			'astra_entry_content_404_page' => 'Astra 404 Entry Content',
			'astra_entry_content_after' => 'Astra Entry Content After',
			'astra_entry_bottom' => 'Astra Entry Bottom',
			'astra_entry_after' => 'Astra Entry After',
			'astra_template_parts_content_bottom' => 'Astra Template Parts Content Bottom',
			'astra_primary_content_bottom' => 'Astra Primary Content Bottom',
			'astra_content_while_after' => 'Astra Content While After',
			'astra_content_bottom' => 'Astra Content Bottom',
			'astra_content_after' => 'Astra Content After',
			'astra_comments_before' => 'Astra Comments Before',
			'astra_comments_after' => 'Astra Comments After',
			'astra_sidebars_before' => 'Astra Sidebars Before',
			'astra_sidebars_after' => 'Astra Sidebars After',
			'astra_footer_before' => 'Astra Footer Before',
			'astra_footer_content_top' => 'Astra Footer Content Top',
			'astra_footer_inside_container_top' => 'Astra Footer Inside Container Top',
			'astra_footer_inside_container_bottom' => 'Astra Footer Inside Container Bottom',
			'astra_footer_content_bottom' => 'Astra Footer Content Bottom',
			'astra_footer_after' => 'Astra Footer After',
			'astra_body_bottom' => 'Astra Body Bottom',
			'astra_404_content_template' => '404 Content',
		];
		return $hooks;
	}
}