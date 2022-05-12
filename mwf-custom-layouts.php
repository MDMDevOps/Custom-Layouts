<?php
/**
 * The plugin bootstrap file
 * This file is read by WordPress to generate the plugin information in the plugin admin area.
 * This file also defines plugin parameters, registers the activation and deactivation functions, and defines a function that starts the plugin.
 * @link    https://www.midwestfamilymadison.com
 * @since   1.0.0
 * @package mwf
 *
 * @wordpress-plugin
 * Plugin Name: Mid-West Family Custom Layouts
 * Plugin URI:  https://www.midwestfamilymadison.com
 * GitHub Plugin URI:https://github.com/MDMDevOps/Custom-Layouts
 * Description: Custom layouts for (almost) any site
 * Version:     0.1.0
 * Author:      Mid-West Family
 * Author URI:  https://www.midwestfamilymadison.com
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: mwf
 */

namespace Mwf\CustomLayouts;

/**
 * If this file is called directly, abort
 */
if ( !defined( 'WPINC' ) ) {
	die( 'Abort' );
}

if ( ! class_exists( '\Mwf\CustomLayouts\Plugin' ) ) {

	require_once __DIR__ . '/vendor/autoload.php';

	class Plugin extends Framework {

		public function __construct() {
			/**
			 * Create plugin constants
			 */
			define( 'MWF_CUSTOMLAYOUTS_VERSION', '0.1.0' );
			define( 'MWF_CUSTOMLAYOUTS_ASSET_PREFIX', $this->isDev() ? '' : '.min' );
			define( 'MWF_CUSTOMLAYOUTS_URL', plugin_dir_url( __FILE__ ) );
			define( 'MWF_CUSTOMLAYOUTS_PATH', plugin_dir_path( __FILE__ ) );
			/**
			 * Register the text domain
			 */
			load_plugin_textdomain( 'mwf', false, basename( dirname( __FILE__ ) ) . '/languages' );
			/**
			 * Register activation hook
			 */
			register_activation_hook( __FILE__, [$this, 'activate'] );
			/**
			 * Register deactivation hook
			 */
			register_deactivation_hook( __FILE__, [$this, 'deactivate'] );
			/**
			 * Kickoff the plugin
			 */
			$this->burnBabyBurn();
			/**
			 * Construct parent
			 */
			parent::__construct();
		}

		/**
		 * Register actions
		 *
		 * Uses the subscriber class to ensure only actions of this instance are added
		 * and the instance can be referenced via subscriber
		 *
		 * @since 1.0.0
		 */
		public function addActions() {
			Subscriber::addAction( 'init', [$this, 'registerPostTypes'] );
			Subscriber::addAction( 'init', [$this, 'registerTaxonomies'] );
			Subscriber::addAction( 'widgets_init', [$this, 'registerWidgets'] );
		}

		private function burnBabyBurn() {
			/**
			 * Register the admin functions
			 */
			new Admin();
			/**
			 * Register the front end functions
			 */
			new FrontEnd();
			/**
			 * Register controller functions
			 */
			new Controller();
			/**
			 * Register theme addons
			 */
			$themes = $this->getClasses( 'includes/Themes' );

			foreach ( $themes as $theme ) {

				$class = __NAMESPACE__ . '\\Themes\\' . $theme;

				new $class;
			}
			/**
			 * Register plugin addons
			 */
			$plugins = $this->getClasses( 'includes/Plugins' );

			foreach ( $plugins as $plugin ) {

				$class = __NAMESPACE__ . '\\Plugins\\' . $plugin;

				new $class;
			}
			// foreach( $widgets as $widget_name ) {

			// 	$widget = __NAMESPACE__ . '\\Widgets\\' . $widget_name;

			// 	register_widget( $widget );
			// }
			/**
			 * Theme support
			 */
			new Themes\Astra();
			new Themes\Genesis();
			new Themes\Divi();
			/**
			 * Page builder support
			 */
			// new Plugins\FLBuilder();
			// new Plugins\Elementor();
		}

		/**
		 * Activate Plugin
		 *
		 * Register Post Types, Register Taxonomies, and Flush Permalinks
		 * @since 1.0.0
		 */
		public function activate() {
			/**
			 * Register custom post types
			 */
			$this->registerPostTypes();
			/**
			 * Register custom taxonomies
			 */
			$this->registerTaxonomies();
			/**
			 * Flush permalinks
			 */
			$this->flushPermalinks();
		}
		/**
		 * Deactivate Plugin
		 *
		 * Remove unecessary data from database
		 * @since 1.0.0
		 */
		public function deactivate() {
			/**
			 * Flush permalinks
			 */
			$this->flushPermalinks();
		}

		/**
		 * Flush permalinks
		 */
		private function flushPermalinks() {
			global $wp_rewrite;
			$wp_rewrite->init();
			$wp_rewrite->flush_rules();
		}

		/**
		 * Register custom post types
		 */
		public function registerPostTypes() {

			$post_types = $this->getClasses( 'PostTypes' );

			foreach( $post_types as $post_type_name ) {

				$post_type = __NAMESPACE__ . '\\PostTypes\\' . $post_type_name;

				$post_type = new $post_type();

				$post_type->register();

			}
		}
		/**
		 * Register custom taxonomies
		 */
		public function registerTaxonomies() {

			$taxonomies = $this->getClasses( 'Taxonomies' );

			foreach( $taxonomies as $taxonomy_name ) {

				$taxonomy =  __NAMESPACE__ . '\\Taxonomies\\' . $taxonomy_name;

				$taxonomy = new $taxonomy();

				$taxonomy->register();
			}
		}
		/**
		 * Register custom widgets
		 */
		public function registerWidgets() {

			$widgets = $this->getClasses( 'Widgets' );

			foreach( $widgets as $widget_name ) {

				$widget = __NAMESPACE__ . '\\Widgets\\' . $widget_name;

				register_widget( $widget );
			}
		}
	}
	new \Mwf\CustomLayouts\Plugin();
}