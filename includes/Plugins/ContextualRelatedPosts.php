<?php

namespace Mwf\CustomLayouts\Plugins;

use Mwf\CustomLayouts\Framework;
use Mwf\CustomLayouts\Subscriber;

class ContextualRelatedPosts extends Framework {

	/**
	 * Construct new instance
	 *
	 */
	public function __construct() {

		if ( ! $this->isPluginActive( 'contextual-related-posts/contextual-related-posts.php' ) ) {
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
		Subscriber::addFilter( 'custom_layouts/template_parts', [$this, 'addTemplates'] );
		Subscriber::addFilter( 'custom_layouts/template_scope', [$this, 'templateScope'] );

	}

	public function addTemplates( $templates ) {
		return array_merge(
			$templates,
			[
				'related-posts' => 'Related Posts',
			]
		);
	}

	public function templateScope( $_scope ) {

		$related_raw = get_crp_posts_id();

		if ( empty( $related_raw ) ) {
			$_scope['related_posts'] = [];
			return $_scope;
		}

		$related_ids = [];

		foreach ( $related_raw as $raw_post ) {

			$related_ids[] = $raw_post->ID;
		}

		$args = [
			'ignore_sticky_posts' => true,
			'post__in' => $related_ids,
			'orderby' => 'post__in'
		];

		$posts = new \Timber\PostQuery( $args );

		foreach ( $posts as $rpost ) {

			if ( $rpost ->thumbnail === null ) {

				$rpost ->thumbnail = new \Timber\Image( apply_filters( 'custom_layouts/default_thumbnail', MWF_CUSTOMLAYOUTS_URL . 'assets/images/default-post-thumbnail.webp' ) );
			}
		}

		$_scope['related_posts'] = $posts;

		return $_scope;
	}

	public function render() {
// get_crp_posts_id
	}
}