<?php

namespace Mwf\CustomLayouts;

class Timber extends Framework {

	public $views;
	/**
	 * Register actions
	 *
	 * Uses the subscriber class to ensure only actions of this instance are added
	 * and the instance can be referenced via subscriber
	 *
	 * @since 1.0.0
	 * @see  https://developer.wordpress.org/reference/functions/add_action/
	 */
	public function addActions() {

	}
	/**
	 * Register shortcodes
	 *
	 * Uses the subscriber class to ensure only shortcodes of this instance are added
	 * and the instance can be referenced via subscriber
	 *
	 * @since 1.0.0
	 */
	public function addShortcodes() {

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

	}

	/**
	 * Get the context / scope
	 */
	public static function getScope() {
		/**
		 * Attempt to get from cache
		 */
		$_scope = wp_cache_get( 'template_scope', 'custom_layouts' );

		if ( empty ( $_scope ) ) {
			/**
			 * Maybe get from timber if not cached
			 */
			$_scope = ! empty( $_scope ) ? $_scope : \Timber\Timber::context();
			/**
			 * Allow filtering
			 */
			$_scope = apply_filters( 'custom_layouts/template_scope', $_scope );
			/**
			 * Set cache
			 */
			wp_cache_set( 'template_scope', $_scope, 'custom_layouts' );
		}
		/**
		 * Send flyin'
		 */
		return apply_filters( 'custom_layouts/template_scope/nocache', $_scope );
	}

}