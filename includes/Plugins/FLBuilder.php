<?php

namespace Mwf\CustomLayouts\Plugins;

use Mwf\CustomLayouts\Framework;
use Mwf\CustomLayouts\Subscriber;

class FLBuilder extends Framework {

	/**
	 * Construct new instance
	 *
	 */
	public function __construct() {

		if ( ! $this->isPluginActive( 'beaver-builder-lite-version/fl-builder.php' ) && ! $this->isPluginActive( 'bb-plugin/fl-builder.php' ) ) {
			return false;
		}
		parent::__construct();
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
		// Subscriber::addFilter( 'custom_layouts/fields/hooks/theme', [$this, 'getHooks'] );
	}

	public function maybeRender( $layout ) {
		if ( class_exists( 'FLBuilderModel' ) && \FLBuilderModel::is_builder_enabled( $layout->ID ) ) {
			add_action( "custom_layout/render/{$layout->ID}", [$this, 'render'] );
		}
	}

	public function render( $layout ) {
		/**
		 * Don't render on single edit screen
		 */
		if ( get_the_id() === $layout->ID ) {
			return false;
		}
		/**
		 * Render content
		 */
		\FLBuilder::render_query( [
			'post_type' => 'custom-layout',
			'p' => $layout->ID,
		] );
	}
}