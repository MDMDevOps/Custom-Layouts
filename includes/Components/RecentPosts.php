<?php

namespace Mwf\CustomLayouts\Plugins;

use Mwf\CustomLayouts\Framework;
use Mwf\CustomLayouts\Subscriber;

class RecentPosts extends Framework {
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

		// Subscriber::addAction( 'custom_layout/before_render', [$this, 'maybeRender'] );
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

	// public function addTemplates( $templates ) {
	// 	return array_merge(
	// 		$templates,
	// 		[
	// 			'related-posts' => 'Related Posts',
	// 		]
	// 	);
	// }
}