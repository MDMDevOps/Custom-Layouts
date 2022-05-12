<?php

namespace Mwf\CustomLayouts;

use \Timber\Timber;

class FrontEnd extends Framework {

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
		Subscriber::addAction( 'custom_layouts/before_hook', [$this, 'maybeEnqueueScripts'] );
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
		Subscriber::addFilter( 'custom_layout/the_content', 'do_blocks' );
		Subscriber::addFilter( 'custom_layout/the_content', 'wptexturize' );
		Subscriber::addFilter( 'custom_layout/the_content', 'convert_smilies' );
		Subscriber::addFilter( 'custom_layout/the_content', 'convert_chars' );
		Subscriber::addFilter( 'custom_layout/the_content', 'shortcode_unautop' );
		Subscriber::addFilter( 'custom_layout/the_content', 'do_shortcode' );
		Subscriber::addFilter( 'custom_layout/the_content', 'wp_make_content_images_responsive' );
		Subscriber::addFilter( 'custom_layout/the_content', 'prepend_attachment' );
		// Subscriber::addFilter( 'get_avatar_data', [$this, 'customGravitar'], 100, 2 );
	}
	/**
	 * Check if scripts / styles need enqueed
	 */
	public function maybeEnqueueScripts( $layout ) {
		if ( $layout['type'] === 'part' ) {
			add_action( 'wp_enqueue_scripts', function() {
				if ( ! wp_script_is( __NAMESPACE__ . '\frontend', 'enqueued' ) ) {
					$this->enqueueScripts();
					$this->enqueueStyles();
				}
			}, 20 );
		}
	}
	/**
	 * Register the javascript
	 *
	 * @since 1.0.0
	 */
	public function enqueueScripts() {
		wp_enqueue_script( __NAMESPACE__ . '\frontend', MWF_CUSTOMLAYOUTS_URL . '/assets/js/frontend' . MWF_CUSTOMLAYOUTS_ASSET_PREFIX . '.js', ['jquery'], MWF_CUSTOMLAYOUTS_VERSION, true );
		wp_enqueue_script( __NAMESPACE__ . '\fontawesome', MWF_CUSTOMLAYOUTS_URL . '/assets/js/fontawesome.js', [], '5.0.0',true );
	}
	/**
	 * Register the css
	 *
	 * @since 1.0.0
	 */
	public function enqueueStyles() {
		wp_enqueue_style( __NAMESPACE__ . '\frontend', MWF_CUSTOMLAYOUTS_URL . '/assets/css/frontend' . MWF_CUSTOMLAYOUTS_ASSET_PREFIX . '.css', [], MWF_CUSTOMLAYOUTS_VERSION, 'all' );
	}
	/**
	 * Global callback to run wp functions
	 */
	public static function __callStatic( $function, $args ) {

		$id = explode( '_', $function );

		if ( $id[0] !== 'render' || ! isset( $id[1] ) ) {
			return false;
		}

		$layout = get_post( $id[1] );

		if ( ! $layout ) {
			return false;
		}

		$self = new self;

		$self->render( $layout );


	}
	private function render( $layout ) {

		$type = carbon_get_post_meta( $layout->ID, 'cl_editor_type' );

		$current_action = current_action();

		if ( $current_action === 'the_content' ) {

			$priority = carbon_get_post_meta( $layout->ID, 'cl_action_priority' );

			ob_start();
		}

		do_action( 'custom_layout/before_render', $layout );

		switch ( $type ) {
			case 'code':
				$this->renderCode( $layout );
				break;
			case 'part':
				$this->renderTemplatePart( $layout );
				break;
			default:
				$this->renderEditor( $layout );
				break;
		}

		do_action( 'custom_layout/after_render', $layout );

		if ( $current_action === 'the_content' ) {
			$output = ob_get_clean();

			if ( intval( $priority ) < 5 ) {
				return $output . $args[0];
			}

			else {
				return $args[0] . $output;
			}
		}
	}
	/**
	 * Render regular WP Editor
	 */
	private function renderEditor( $layout ) {

		if ( has_action( "custom_layout/render/{$layout->ID}" ) ) {
			do_action( "custom_layout/render/{$layout->ID}", $layout );
		}

		else {
			echo apply_filters( 'custom_layout/the_content', get_the_content( null, true, $layout ) );
		}

	}
	/**
	 * Render custom code content type
	 */
	private function renderCode( $layout ) {
		echo carbon_get_post_meta( $layout->ID, 'cl_code_editor' );
	}
	/**
	 * Render a timber/php template
	 *
	 * @param  array  $template name of template to render
	 * @param  array  $data     data to merge with scope (context)
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function renderTemplatePart( $layout ) {

		$template_name = carbon_get_post_meta( $layout->ID, 'cl_template_part' );

		$template = $this->getTemplatePart( $template_name );

		if ( empty( $template ) ) {
			return;
		}

		if ( in_array( pathinfo( $template, PATHINFO_EXTENSION ), [ 'twig', 'html' ] ) ) {
			/**
			 * Attempt to get from cache
			 */
			$_scope = wp_cache_get( 'template_scope', 'custom_layouts' );
			/**
			 * Maybe get from timber if not cached
			 */
			$_scope = ! empty( $_scope ) ? $_scope : Timber::context();
			/**
			 * Allow filtering
			 */
			$_scope = apply_filters( 'custom_layouts/template_scope', $_scope );
			/**
			 * Set cache
			 */
			wp_cache_set( 'template_scope', $_scope, 'custom_layouts' );
			/**
			 * Send flyin'
			 */
			Timber::render( [$template], $_scope );
		} else {
			require $template;
		}
	}
	/**
	 * Generate all the path combinations for locating templates
	 */
	public function generatePaths( $slug, $name, $views, $post_type ) {
		$paths = [];

		$patterns = [
			[
				'%s/%s',
				'%s-%s',
			],
			[
				'%s/%s-%s',
				'%s-%s-%s',
			],
			[
				'%s/%s/%s-%s',
				'%s/%s-%s-%s',
				'%s-%s/%s-%s',
				'%s-%s-%s-%s',
			]

		];

		$temp = [];

		if ( ! empty( $name ) ) {

			foreach ( $views as $view ) {
				foreach ( $patterns[2] as $pattern ) {
					$paths[0][] = sprintf( $pattern, $slug, $name, $view, $post_type );
					$paths[0][] = sprintf( $pattern, $slug, $name, $post_type, $view );
				}
				foreach ( $patterns[1] as $pattern ) {
					$paths[1][] = sprintf( $pattern, $slug, $name, $view );
					$paths[1][] = sprintf( $pattern, $slug, $name, $post_type );
				}
			}
			foreach ( $patterns[0] as $pattern ) {
				$paths[2][] = sprintf( $pattern, $slug, $name );
			}

		} else {
			foreach ( $views as $view ) {
				foreach ( $patterns[1] as $pattern ) {

					$paths[0][] = sprintf( $pattern, $slug, $view, $post_type );
					$paths[0][] = sprintf( $pattern, $slug, $post_type, $view );

				}

				foreach ( $patterns[0] as $pattern ) {

					$paths[1][] = sprintf( $pattern, $slug, $view );
					$paths[1][] = sprintf( $pattern, $slug, $post_type );

				}
			}

			$paths[2][] = $slug;
		}

		$paths = array_unique( array_reduce( $paths, 'array_merge', [] ) );

		return $paths;
	}
	/**
	 * Wrapper for get_template_part
	 *
	 * Expand get template part to incude post types and views
	 *
	 * @param  string $modifier : string used to utilize a context specific filter
	 * @return [string]           The context string
	 */
	public function getTemplatePart( $slug = '', $name = '', $scope = [], $force = false ) {

		if( empty( $slug ) ) {
			return;
		}

		$slug = apply_filters( "custom_layouts/template/{$slug}", $slug );

		$name = apply_filters( "custom_layouts/template/{$slug}/name", $name );

		$views = $this->getViews();

		$posttype = get_post_type();

		$cache_key = md5( 'template' . $slug . $name . join( '_', $views ) . $posttype . intval( $force ) );

		$cache = wp_cache_get( $cache_key, 'custom_layout_templates' );

		if ( $cache ) {
			return $cache;
		}
		/**
		 * Generate paths to search
		 */
		if ( $force ) {
			$paths = [ $slug ];
		} else {
			$paths = apply_filters( 'custom_layouts/templates/paths', $this->generatePaths(
				$slug,
				$name,
				$views,
				$posttype
			) );
		}
		/**
		 * Allow child themes to specify search directory
		 */
		$directory = apply_filters( 'custom_layouts/template/directory', 'template-parts' );

		$template = false;
		/**
		 * Loop through and look for first template
		 *
		 * Templates are most specific => least specific
		 */
		foreach ( $paths as $path ) {

			$template = $this->locateTemplate( $directory, $path );

			if ( $template ) {
				break;
			}
		}
		/**
		 * Look in a different directory, maybe
		 */
		if ( empty( $template) && $directory !== 'template-parts' ) {

			foreach ( $paths as $path ) {

				$template = $this->locateTemplate( 'template-parts', $path );

				if ( $template ) {
					break;
				}
			}
		}
		/**
		 * Finally, load plugin version
		 */
		if ( empty ( $template ) ) {
			foreach ( $paths as $path ) {
				if ( file_exists( MWF_CUSTOMLAYOUTS_PATH .'template-parts/' . $path . '.twig') ) {
					$template = MWF_CUSTOMLAYOUTS_PATH .'template-parts/' . $path . '.twig';
					break;
				}
			}
		}

		wp_cache_set( $cache_key, $template, 'custom_layout_templates' );

		return $template;
	}
	/**
	 * Search for templates in the theme
	 */
	public function locateTemplate( $base, $path ) {
		/**
		 * First try twig files
		 */
		$template = locate_template( "{$base}/{$path}.twig", false, false );

		if ( $template ) {
			return $template;
		}
		/**
		 * Then HTML
		 */
		$template = locate_template( "{$base}/{$path}.html", false, false );

		if ( $template ) {
			return $template;
		}
		/**
		 * Then PHP
		 */
		$template = locate_template( "{$base}/{$path}.php", false, false );

		return $template;
	}
	/**
	 * Get specific views
	 */
	public function getViews( $context = '' ) {

		if ( ! empty( $this->views ) ) {
			return $this->views;
		}

		if ( is_singular() ) {

			if ( is_front_page() ) {
				$this->views[] = 'frontpage';
			}

			$this->views[] = 'single';
		}

		else if ( is_home() ) {
			$this->views = [ 'blog', 'archive' ];
		}

		else if ( is_search() ) {
			$this->views = [ 'search', 'archive' ];
		}

		else if ( is_archive() ) {

			if ( is_category() ) {
				$this->views[] = 'archive/category';
			}

			else if ( is_tag() ) {
				$this->views[] = 'archive/tag';
			}

			else if ( is_author() ) {
				$this->views[] = 'archive/author';
			}

			else if ( is_author() ) {
				$this->views[] = 'archive/author';
			}

			else if ( is_date() ) {
				$this->views[] = 'archive/date';
			}

			else if ( is_post_type_archive() ) {
				$this->views[] = 'archive/posttype';
			}

			else if ( is_tax() ) {
				$this->views[] = 'archive/tax';
			}

			$this->views[] = 'archive';
		}

		else if ( is_404() ) {
			$this->views[] = 'error404';
		}
		/**
		 * Apply generic filter
		 */
		$this->views = apply_filters( 'custom_layouts/views', $this->views, $context );
		/**
		 * Apply context specific filter
		 */
		if ( ! empty( $context ) ) {
			$this->views = apply_filters( "custom_layouts/views/{$context}", $this->views );
		}
		return $this->views;
	}
	/**
	 * Filter the gravatar for custom author box
	 */
	function customGravitar( $args, $id_or_email ) {

		$user = $id_or_email;
		$custom_image_id = carbon_get_user_meta( $user, 'cl_author_image' );

		if ( $custom_image_id ) {
			$custom_image = wp_get_attachment_image_url( $custom_image_id, [100, 100] );
			$args['url'] = $custom_image;
		}

		return $args;
	}

}