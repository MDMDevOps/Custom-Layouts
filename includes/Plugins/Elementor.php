<?php

namespace Mwf\CustomLayouts\Plugins;

use Mwf\CustomLayouts\Framework;
use Mwf\CustomLayouts\Subscriber;

class Elementor extends Framework {

	/**
	 * Construct new instance
	 *
	 */
	public function __construct() {

		if ( ! $this->isPluginActive( 'elementor/elementor.php' ) ) {
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

	public function maybeRender( $layout ) {
		if ( ( class_exists( '\\Elementor\\Plugin' ) && \Elementor\Plugin::instance()->db->is_built_with_elementor( $layout->ID ) ) ) {
			add_action( "custom_layout/render/{$layout->ID}", [$this, 'render'] );
		}
	}

	public function render( $layout ) {

		if ( \Elementor\Plugin::$instance->preview->is_preview_mode() && get_the_id() === $layout->ID ) {
			return;
		}

		echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $layout->ID, true );
	}
}