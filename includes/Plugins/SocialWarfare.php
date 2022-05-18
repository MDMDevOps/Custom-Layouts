<?php

namespace Mwf\CustomLayouts\Plugins;

use Mwf\CustomLayouts\Framework;
use Mwf\CustomLayouts\Subscriber;

class SocialWarfare extends Framework
{

	/**
	 * Construct new instance
	 *
	 */
	public function __construct()
	{
		if (!$this->isPluginActive('social-warfare/social-warfare.php')) {
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
		Subscriber::addFilter('custom_layouts/template_parts', [$this, 'addTemplates']);
		Subscriber::addFilter('custom_layouts/template_scope', [$this, 'templateScope']);
	}

	public function addTemplates($templates)
	{
		return array_merge(
			$templates,
			[
				'core/socialwarfare/sharing' => 'Social Sharing - Social Warfare',
			]
		);
	}

	public function sharing()
	{
		if ( function_exists('social_warfare' ) ) {
			social_warfare();
		}
	}

	public function templateScope($_scope)
	{
		$_scope['social_warfare'] = $this;
		return $_scope;
	}
}
