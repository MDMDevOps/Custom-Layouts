<?php

namespace Mwf\CustomLayouts;

use \Carbon_Fields\Carbon_Fields;
use \Carbon_Fields\Container;
use \Carbon_Fields\Field;
use \Carbon_Fields\Block;

class Admin extends Framework {
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
		Subscriber::addAction( 'admin_enqueue_scripts', [$this, 'enqueueStyles'] );
        Subscriber::addAction('admin_enqueue_scripts', [$this, 'enqueueScripts']);
        Subscriber::addAction( 'carbon_fields_register_fields', [$this, 'addFields'] );
        Subscriber::addAction( 'after_setup_theme', [$this, 'bootCarbonFields' ] );
	}

	public function bootCarbonFields() {
		Carbon_Fields::boot();
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
	 * Load post types
	 *
	 * @return array Array of post types
	 */
	private function getPostTypes() {

		$values = [];

		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		foreach( $post_types as $post_type ) {
			if( in_array( $post_type->name, array( 'fl-builder-template' ) ) ) {
				continue;
			}

			$values[$post_type->name] = $post_type->label;
		}
		/**
		 * Filter & Return
		 */
		return apply_filters( 'custom_layouts/fields/post_types', $values );
	}

	public function addFields() {
		/**
		 * General Option
		 */
		Container::make( 'post_meta', 'Layout Options' )
			->set_context( 'normal' )
			->set_classes( 'custom_layout_metabox' )
			->where( 'post_type', 'IN', ['custom-layout'] )
			->add_fields( array_merge(
				$this->getEditorFields(),
				$this->getActionFields(),
			) );
		/**
		 * Inclusion Rules
		 */
		Container::make( 'post_meta', 'Display On' )
			->set_context( 'normal' )
			->set_classes( 'custom_layout_metabox' )
			->where( 'post_type', 'IN', ['custom-layout'] )
			->add_fields(
				$this->getInclusionFields()
			);
		/**
		 * Exclusion Rules
		 */
		Container::make( 'post_meta', 'Hide On' )
			->set_context( 'normal' )
			->set_classes( 'custom_layout_metabox' )
			->where( 'post_type', 'IN', ['custom-layout'] )
			->add_fields(
				$this->getExclusionFields()
			);
		/**
		 * User Meta
		 */
		Container::make( 'user_meta', 'Author Box' )
			->add_fields( $this->getAuthorFields() )
			->set_classes( 'custom_layout_admin_metabox' );
	}

	public function getAuthorFields() {
		$fields = [
			Field::make( 'image', 'cl_author_image', __( ' Image' ) )
				->set_help_text( 'Custom image to use in place of a gravatar' ),
			Field::make( 'rich_text', 'cl_author_content', __( 'Content' ) ),
		];
		/**
		 * Maybe add custom social links
		 */
		if ( ! $this->isPluginActive( 'wordpress-seo/wp-seo.php' ) ) {
			$networks = [
				'facebook',
				'github',
				'googleplus',
				'instagram',
				'linkedin',
				'pinterest',
				'soundcloud',
				'tumblr',
				'twitter',
				'wikipedia',
				'wordpress',
				'youtube',
				'url'
			];

			foreach ( $networks as $network ) {
				$fields[] = Field::make( 'text', "cl_network_{$network}", $network );
			}
		}
		return $fields;

	}
	public function getInclusionFields() {
		$fields = [
			Field::make( 'html', 'cl_exclusion_description', '' )
				->set_html( sprintf( '<p>%s</p>', __( 'Add rules and/or groups of rules to show this element', 'custom_layouts' ) ) ),
			Field::make( 'complex', 'cl_inclusion_conditions', '' )
	        ->set_layout( 'grid' )
	        ->setup_labels(
	        	[
	        		'singular_name' => 'Condition Group',
	        		'plural_name' => 'Condition Groups'
	        	]
	        )
	        ->add_fields(
	        	[
		            Field::make( 'complex', 'cl_inclusion_group', '' )
		                ->set_layout( 'tabbed-horizontal' )
		                ->set_min( 1 )
		                ->setup_labels(
		                	[
		                		'singular_name' => 'Condition',
		                		'plural_name' => 'Conditions'
		                	]
		                )
		                ->add_fields( $this->getConditionalFields( 'include' ) ),
	        	]
	    	)
	    ];
	    return $fields;
	}

	public function getExclusionFields() {
			$fields = [
				Field::make( 'html', 'cl_exclusion_description', '' )
					->set_html( sprintf( '<p>%s</p>', __( 'Add rules and/or groups of rules to hide this element', 'custom_layouts' ) ) ),
				Field::make( 'complex', 'cl_exclusion_conditions', '' )
		        ->set_layout( 'grid' )
		        ->setup_labels(
		        	[
		        		'singular_name' => 'Condition Group',
		        		'plural_name' => 'Condition Groups'
		        	]
		        )
		        ->add_fields(
		        	[
			            Field::make( 'complex', 'cl_exclusion_group', '' )
			                ->set_layout( 'tabbed-horizontal' )
			                ->set_min( 1 )
			                ->setup_labels(
			                	[
			                		'singular_name' => 'Condition',
			                		'plural_name' => 'Conditions'
			                	]
			                )
			                ->add_fields( $this->getConditionalFields( 'exclude' ) ),
		        	]
		    	)
		    ];
		    return $fields;
	}

	public function getConditionalFields( $type = 'include' ) {

		$fields = [];

		$fields[] = Field::make( 'select', 'cl_condition', __( 'Condition' ) )
        	->set_options( [
        		'__return_true' => __( 'Entire Site', 'scaffolding' ),
        		'is_front_page' => __( 'Front Page', 'scaffolding' ),
        		'is_home' => __( 'Blog Page', 'scaffolding' ),
        		'is_404' => __( '404', 'scaffolding' ),
        		'is_search' => __( 'Search Results', 'scaffolding' ),
        		'conditionSingular' => __( 'Singular', 'scaffolding' ),
        		'conditionArchive' => __( 'Archives', 'scaffolding' ),
        		'conditionDate' => __( 'Date/Time', 'scaffolding' ),
        		'conditionUserRole' => __( 'User Role', 'scaffolding' ),
        		'conditionCustom' => __( 'conditionCustom', 'scaffolding' ),
        	]
        );
        /**
         * Singular Fields
         */
        $fields[] = Field::make( 'select', 'cl_condition_singular', '' )
        	->set_options(
        		[
	        		'' => __( 'All', 'scaffolding' ),
	        		'post_type' => __( 'Post Type', 'scaffolding' ),
	        		'term' => __( 'Term', 'scaffolding' ),
	        		'author' => __( 'Author', 'scaffolding' ),
	        		'template' => __( 'Page Template', 'scaffolding' ),
        		]
        	)
        	->set_conditional_logic(
        		[
	    	        'relation' => 'AND',
	    	        [
	    	            'field' => 'cl_condition',
	    	            'value' => 'conditionSingular',
	    	            'compare' => '=',
	    	        ]
	    	    ]
    		);

        $fields[] = Field::make( 'multiselect', 'cl_condition_singular_post_type', '' )
    	    ->add_options( $this->getPostTypes() )
        	->set_conditional_logic(
        		[
	    	        'relation' => 'AND',
	    	        [
	    	            'field' => 'cl_condition',
	    	            'value' => 'conditionSingular',
	    	            'compare' => '=',
	    	        ],
	    	        [
	    	            'field' => 'cl_condition_singular',
	    	            'value' => 'post_type',
	    	            'compare' => '=',
	    	        ]
	    	    ]
    		);
        $fields[] = Field::make( 'multiselect', 'cl_condition_singular_page_template', '' )
    	    ->add_options( $this->getPageTemplates() )
        	->set_conditional_logic(
        		[
	    	        'relation' => 'AND',
	    	        [
	    	            'field' => 'cl_condition',
	    	            'value' => 'conditionSingular',
	    	            'compare' => '=',
	    	        ],
	    	        [
	    	            'field' => 'cl_condition_singular',
	    	            'value' => 'template',
	    	            'compare' => '=',
	    	        ]
	    	    ]
    		);
    	$fields[] = Field::make( 'association', 'cl_condition_singular_post', '' )
    		->set_types(
    			[
    		        [
    		            'type' => 'post',
    		            'post_type' => '',
    		        ]
    		    ]
    		)
        	->set_conditional_logic(
        		[
	    	        'relation' => 'AND',
	    	        [
	    	            'field' => 'cl_condition',
	    	            'value' => 'conditionSingular',
	    	            'compare' => '=',
	    	        ],
	    	        [
	    	            'field' => 'cl_condition_singular',
	    	            'value' => '',
	    	            'compare' => '=',
	    	        ]
	    	    ]
    		);
        $fields[] = Field::make( 'association', 'cl_condition_singular_author', '' )
        	->set_types(
        		[
        	        [
        	            'type' => 'user',
        	        ]
        	    ]
        	)
        	->set_conditional_logic(
        		[
	    	        'relation' => 'AND',
	    	        [
	    	            'field' => 'cl_condition',
	    	            'value' => 'conditionSingular',
	    	            'compare' => '=',
	    	        ],
	    	        [
	    	            'field' => 'cl_condition_singular',
	    	            'value' => 'author',
	    	            'compare' => '=',
	    	        ]
	    	    ]
    		);
        $fields[] = Field::make( 'association', 'cl_condition_singular_term', '' )
        	->set_types( $this->getAssociationTerms() )
        	->set_conditional_logic(
        		[
	    	        'relation' => 'AND',
	    	        [
	    	            'field' => 'cl_condition',
	    	            'value' => 'conditionSingular',
	    	            'compare' => '=',
	    	        ],
	    	        [
	    	            'field' => 'cl_condition_singular',
	    	            'value' => 'term',
	    	            'compare' => '=',
	    	        ]
	    	    ]
    		);
        $fields[] = Field::make( 'html', 'cl_condition_custom' )
            ->set_html( "<p><strong>Custom Conditions :</strong> Use the filter <code>'custom_layouts/conditions/custom'</code></p>" )
        	->set_conditional_logic(
        		[
        	        'relation' => 'AND',
        	        [
        	            'field' => 'cl_condition',
        	            'value' => 'conditionCustom',
        	            'compare' => '=',
        	        ]
    	    	]
    		);
    	/**
    	 * Archive Terms
    	 */
        $fields[] = Field::make( 'select', 'cl_condition_archive', '' )
        	->set_options(
        		[
	        		'' => __( 'All', 'scaffolding' ),
	        		'post_type' => __( 'Post Type', 'scaffolding' ),
	        		'term' => __( 'Term', 'scaffolding' ),
	        		'author' => __( 'Author', 'scaffolding' ),
        		]
        	)
        	->set_conditional_logic(
        		[
	    	        'relation' => 'AND',
	    	        [
	    	            'field' => 'cl_condition',
	    	            'value' => 'conditionArchive',
	    	            'compare' => '=',
	    	        ]
	    	    ]
    		);
        $fields[] = Field::make( 'multiselect', 'cl_condition_archive_post_type', '' )
    	    ->add_options( $this->getPostTypes() )
        	->set_conditional_logic(
        		[
	    	        'relation' => 'AND',
	    	        [
	    	            'field' => 'cl_condition',
	    	            'value' => 'conditionArchive',
	    	            'compare' => '=',
	    	        ],
	    	        [
	    	            'field' => 'cl_condition_archive',
	    	            'value' => 'post_type',
	    	            'compare' => '=',
	    	        ]
	    	    ]
    		);
        $fields[] = Field::make( 'association', 'cl_condition_archive_author', '' )
        	->set_types(
        		[
        	        [
        	            'type' => 'user',
        	        ]
        	    ]
        	)
        	->set_conditional_logic(
        		[
	    	        'relation' => 'AND',
	    	        [
	    	            'field' => 'cl_condition',
	    	            'value' => 'conditionArchive',
	    	            'compare' => '=',
	    	        ],
	    	        [
	    	            'field' => 'cl_condition_archive',
	    	            'value' => 'author',
	    	            'compare' => '=',
	    	        ]
	    	    ]
    		);
        $fields[] = Field::make( 'association', 'cl_condition_archive_term', '' )
        	->set_types( $this->getAssociationTerms() )
        	->set_conditional_logic(
        		[
	    	        'relation' => 'AND',
	    	        [
	    	            'field' => 'cl_condition',
	    	            'value' => 'conditionArchive',
	    	            'compare' => '=',
	    	        ],
	    	        [
	    	            'field' => 'cl_condition_archive',
	    	            'value' => 'term',
	    	            'compare' => '=',
	    	        ]
	    	    ]
    		);
    	/**
    	 * User Role Fields
    	 */
        $fields[] = Field::make( 'multiselect', 'cl_condition_role', '' )
    	    ->add_options( $this->getUserRoles() )
        	->set_conditional_logic(
        		[
	    	        'relation' => 'AND',
	    	        [
	    	            'field' => 'cl_condition',
	    	            'value' => 'conditionUserRole',
	    	            'compare' => '=',
	    	        ],
	    	    ]
    		);
    	/**
    	 * Date / Type Fields
    	 */
    	if ( $type === 'include' ) {
    		$date_time_messages = [
    			'label' => __( 'Display After', 'custom_layouts' ),
    			'help' => __( 'Display on or after this date/time', 'custom_layouts' )
    		];
    	}
    	else {
    		$date_time_messages = [
    			'label' => __( 'Display Until', 'custom_layouts' ),
    			'help' => __( 'Hide on or after this date/time', 'custom_layouts' )
    		];
    	}

    	$fields[] = Field::make( 'date_time', 'cl_condition_datetime', $date_time_messages['label'] )
    	    ->set_help_text( $date_time_messages['help']  )
        	->set_conditional_logic(
        		[
	    	        'relation' => 'AND',
	    	        [
	    	            'field' => 'cl_condition',
	    	            'value' => 'conditionDate',
	    	            'compare' => '=',
	    	        ]
	    	    ]
    		);
    	return $fields;
	}
	/**
	 * Get all the fields related to the editor type
	 *
	 * @return array Array of carbon fields
	 */
	public function getEditorFields() {
		$fields = [];

		$fields[] = Field::make( 'select', 'cl_editor_type', __( 'Type' ) )
			->set_default_value( 'editor' )
			->set_classes( 'editor-type-select' )
			->set_options(
				[
					'editor' => __( 'Editor', 'scaffolding' ),
					'code' => __( 'Code', 'scaffolding' ),
					'part' => __( 'Template Part', 'scaffolding' ),
				]
			);
		$fields[] = Field::make( 'textarea', 'cl_code_editor', __( 'Custom Code' ) )
			->set_rows( 10 )
        	->set_conditional_logic(
        		[
	    	        'relation' => 'AND',
	    	        [
	    	            'field' => 'cl_editor_type',
	    	            'value' => 'code', // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
	    	            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
	    	        ]
	    	    ]
    		);
    	$fields[] = Field::make( 'select', 'cl_template_part', __( 'Template Part' ) )
    		->set_options( apply_filters( 'custom_layouts/template_parts',
	    			[
	    				'author-box' => __( 'Author Box', 'scaffolding' ),
	    				'blog-subscribe' => __( 'Blog Subscribe', 'scaffolding' ),
	    				'social-sharing' => __( 'Social Sharing', 'scaffolding' ),
	    			]
    			)
    		)
        	->set_conditional_logic(
        		[
	    	        'relation' => 'AND',
	    	        [
	    	            'field' => 'cl_editor_type',
	    	            'value' => 'part',
	    	            'compare' => '=',
	    	        ]
	    	    ]
    		);
    	return $fields;
	}

	/**
	 * Get all the fields related to the action hook
	 *
	 * @return array Array of carbon fields
	 */
	public function getActionFields() {
		/**
		 * Step 1 : Define default core hooks
		 */
		$default_hooks = [
			'wp_head' => 'WP Head',
			'wp_body_open' => 'WP Body Open',
			'the_content' => 'The Content',
			'wp_footer'    => 'WP Footer',
		];
		/**
		 * Step 2 : Theme Support
		 *
		 * Allows 3rd party themes to declare theme support, which will override theme hooks
		 */
		$theme_support = get_theme_support( 'mwf-custom-layouts' );
		/**
		 * If we have declared theme support, set them
		 */
		$theme_hooks = isset( $theme_support[0] ) && is_array( $theme_support[0] ) ? $theme_support[0] : [];
		/**
		 * Filter them
		 */
		$theme_hooks = apply_filters( 'custom_layouts/fields/hooks/theme', $theme_hooks );
		/**
		 * Filter for known addons
		 */
		$addon_hooks = apply_filters( 'custom_layouts/fields/hooks/addons', [] );
		/**
		 * Step 3 : Merge Hooks Together, filter, and return
		 */
		$hooks = apply_filters( 'custom_layouts/fields/hooks', array_merge( $default_hooks, $theme_hooks ) );

		$fields = [];

		$fields[] = Field::make( 'multiselect', 'cl_action_hook', __( 'Hook' ) )
			->set_width( 80 )
			->set_options( $hooks );

		$fields[] = Field::make( 'number', 'cl_action_priority', __( 'Priority' ) )
			->set_default_value( 10 )
			->set_width( 20 );

		$layout_args = [
			'posts_per_page' => -1,
			'post_type' => [ 'custom-layout' ],
			'status' => 'publish',
			'fields' => 'ids'
		];

		if ( isset( $_GET['post'] ) ) {
			$layout_args['post__not_in'] = [$_GET['post']];
		}

		$layouts = get_posts( $layout_args );

		$override_options = [];

		foreach ( $layouts as $layout_id ) {
			$override_options[$layout_id] = get_the_title( $layout_id );
		}

		if ( ! empty( $override_options ) ) {
			$fields[] = Field::make( 'multiselect', 'cl_action_hook_override', __( 'Template Override' ) )
				->set_help_text( 'Override (disable) other templates in the same area' )
				->set_options( $override_options );
		}
    	return $fields;
	}

	/**
	 * Register the javascript
	 *
	 * @since 1.0.0
	 */
	public function enqueueScripts() {
		wp_enqueue_script( __NAMESPACE__ . '\admin', MWF_CUSTOMLAYOUTS_URL . '/assets/js/admin' . MWF_CUSTOMLAYOUTS_ASSET_PREFIX . '.js', ['jquery'], MWF_CUSTOMLAYOUTS_VERSION, true );
	}
	/**
	 * Register the css
	 *
	 * @since 1.0.0
	 */
	public function enqueueStyles() {
		wp_enqueue_style(  __NAMESPACE__ . '\admin', MWF_CUSTOMLAYOUTS_URL . '/assets/css/admin' . MWF_CUSTOMLAYOUTS_ASSET_PREFIX . '.css', [], MWF_CUSTOMLAYOUTS_VERSION, 'all' );
	}

    /**
     * Helper function to see if on the edit screen for a custom layout
     *
     * @since 0.1.0
     * @access protected
     * @return boolean
     */
    public function isEditScreen() {
        if ( ! function_exists( 'get_current_screen' ) ) {
            return false;
        }

        $current_screen = get_current_screen();

        if ( $current_screen && $current_screen->id === 'custom-layout' ) {
            return true;
        }

        return false;
    }


    public function getAssociationTerms() {

    	$options = [];

    	$tax_objects = get_taxonomies( [ 'public' => true ], 'objects' );

    	foreach ( $tax_objects as $tax ) {

    		$options[] = [
    			'type' => 'term',
    			'taxonomy' => $tax->name,
    		];
    	}

    	return $options;

    }

    /**
     * Load page template options
     *
     * @return array Array of page templates
     */
    private function getPageTemplates() {

    	include_once ABSPATH . 'wp-admin/includes/theme.php';

    	$values = [
    		'' => 'Default'
    	];

    	$templates = get_page_templates();

    	foreach( $templates as $name => $path ) {
    		$values[$path] = $name;
    	}
    	/**
    	 * Filter & Return
    	 */
    	return apply_filters( 'custom_layouts/fields/page_templates', $values );
    }
    /**
     * Load user roles for ACF User Role select field
     *
     * @return array Array of user roles
     */
    private function getUserRoles() {

    	global $wp_roles;

    	$values = [
    		'none' => 'Not Logged In',
    		'all'  => 'All Logged In',
    	];

    	foreach( $wp_roles->roles as $value => $role ) {
    		$values[$value] = $role['name'];
    	}

    	/**
    	 * Filter & Return
    	 */
    	return apply_filters( 'custom_layouts/fields/user_roles', $values );
    }
}