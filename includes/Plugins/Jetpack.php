<?php

namespace Mwf\CustomLayouts\Plugins;

use Mwf\CustomLayouts\Framework;
use Mwf\CustomLayouts\Subscriber;

class Jetpack extends Framework {

	/**
	 * Construct new instance
	 *
	 */
	public function __construct()
	{
		if ( ! $this->isPluginActive( 'jetpack/jetpack.php' ) ) {
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
	public function addActions()
	{
		Subscriber::addAction( 'loop_start', [$this, 'removeSharingInject'] );
	}
	/**
	 * Register filters
	 *
	 * Uses the subscriber class to ensure only actions of this instance are added
	 * and the instance can be referenced via subscriber
	 *
	 * @since 1.0.0
	 */
	public function addFilters()
	{
		Subscriber::addFilter( 'custom_layouts/template_parts', [$this, 'addTemplates'] );
		Subscriber::addFilter( 'custom_layouts/template_scope', [$this, 'templateScope'] );

	}

	public function addTemplates( $templates )
	{
		return array_merge(
			$templates,
			[
				'core/jetpack/sharing' => 'Social Sharing - Jetpack',
			]
		);
	}

	public function removeSharingInject()
	{
		remove_filter( 'the_content', 'sharing_display', 19 );
		remove_filter( 'the_excerpt', 'sharing_display', 19 );

		if ( class_exists( 'Jetpack_Likes' ) ) {
			remove_filter( 'the_content', [ Jetpack_Likes::init(), 'post_likes' ], 30, 1 );
		}
	}

	public function sharing()
	{
		if ( function_exists( 'sharing_display' ) ) {
			sharing_display( '', true );
		}

		if ( class_exists( 'Jetpack_Likes' ) ) {
			$custom_likes = new Jetpack_Likes;
			echo $custom_likes->post_likes( '' );
		}
	}

	public function templateScope( $_scope )
	{
		$_scope['jetpack'] = $this;
		return $_scope;
	}
}