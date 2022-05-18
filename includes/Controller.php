<?php

namespace Mwf\CustomLayouts;

use \Carbon_Fields\Carbon_Fields;
use \Timber\Timber;

class Controller extends Framework {
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
		Subscriber::addAction( 'wp', [$this, 'initLayouts'], 20 );
		Subscriber::addAction( 'after_setup_theme', ['Carbon_Fields', 'boot' ] );
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
		Subscriber::addFilter( 'custom_layouts/template_scope', [$this, 'setPost'] );
	}
	/**
	 * Gets all layouts
	 * @return [type] [description]
	 */
	public function getLayouts() {
		$layouts = get_posts( [
			'posts_per_page' => -1,
			'post_type' => [ 'custom-layout' ],
			'fields' => 'ids'
		]);

		$group = [];

		foreach( $layouts as $id ) {

			$data = [
				'id' => $id,
				'hook' => carbon_get_post_meta( $id, 'cl_action_hook' ),
				'priority' => carbon_get_post_meta( $id, 'cl_action_priority' ),
				'type' => carbon_get_post_meta( $id, 'cl_editor_type' ),
				'replace_content' => carbon_get_post_meta( $id, 'cl_action_disable_all' ),
				'override' => carbon_get_post_meta( $id, 'cl_action_hook_override' ),
				'include' => [],
				'exclude' => [],
			];

			/**
			 * If there's no hook, we don't need to waste time
			 */
			if( empty( $data['hook'] ) ) {
				continue;
			}
			/**
			 * Set inclusion rules
			 */
			$include = carbon_get_post_meta( $id, 'cl_inclusion_conditions' );

			if ( ! empty( $include ) ) {

				foreach ( $include as $index => $values ) {

					if ( ! isset( $values['cl_inclusion_group'] ) ) {
						continue;
					}

					$data['include'][] = $values['cl_inclusion_group'];
				}
			}
			/**
			 * Set Exlusion rules
			 */
			$exlude = carbon_get_post_meta( $id, 'cl_exclusion_conditions' );

			if ( ! empty( $exlude ) ) {

				foreach ( $exlude as $index => $values ) {

					if ( ! isset( $values['cl_exclusion_group'] ) ) {
						continue;
					}

					$data['exclude'][] = $values['cl_exclusion_group'];
				}
			}

			$group[$id] = $data;
		}

		return $group;
	}

	public function getLayoutsForDisplay() {

		$display = [];

		$layouts = $this->getLayouts();

		foreach ( $layouts as $id => $layout ) {
			/**
			 * See if supposed to be exluded
			 */
			if ( $this->validate( $layout['exclude'], 'exclude', $layout['id'] ) === true ) {
				continue;
			}
			/**
			 * See if supposed to be included
			 */
			if ( $this->validate( $layout['include'], 'include', $layout['id'] ) === true ) {
				$display[$layout['id']] = $layout;
			}
		}


		/**
		 * Maybe remove overrides
		 */
		return $display;

	}
	/**
	 * Validate a set of rules for a custom field
	 *
	 * Called recursivly to validate nested rules
	 * @param  array   $groups An array of rules or rule groups
	 * @param  integer $depth  At what depth of the array are we at.
	 * @return bool    $valid  Whether or not all rules are valid in the set
	 * @since 1.0.0
	 */
	private function validate( $groups = [], $type = 'include', $id = 0, $depth = 0 ) {
		/**
		 * Assume false when validating any, true if valuating all
		 * @var boolean
		 */
		$valid = $depth === 1;
		/**
		 * If empty just bail
		 */
		if( empty( $groups ) ) {
			return $valid;
		}

		/**
		 * Maybe do top level
		 */
		if( $depth < 2 ) {

			/**
			 * Increment depth
			 */
			$recursive = $depth + 1;
			/**
			 * If any of the groups are true, it's valid
			 */
			foreach( $groups as $index => $group ) {
				/**
				 * At the groups (top) level, any ruleset can be valid
				 * The first TRUE condition validates the ruleset
				 *
				 * Evaluates an OR condition
				 */

				if ( $depth === 0 && $valid === true ) {
					break;
				}
				/**
				* At the group level, all rules must be valid
				* The first FALSE value invalidates the group
				*
				* Evaluates an AND condition
				*/
				if ( $depth === 1 && $valid === false ) {
					break;
				}
				/**
				 * Re-enter loop recursively
				 */
				$valid = $this->validate( $group, $type, $id, $recursive );
			}

		}
		/**
		 * This level evaluates a single rule
		 */
		elseif ( $depth === 2 ) {
			/**
			 * Merge with defaults
			 */
			$rule = wp_parse_args( $groups, [
				'cl_condition' => '',
				'cl_condition_archive' => '',
				'cl_condition_archive_author' => [],
				'cl_condition_archive_post_type' => [],
				'cl_condition_archive_term' => [],
				'cl_condition_custom' => '',
				'cl_condition_datetime' => '',
				'cl_condition_role' => [],
				'cl_condition_singular' => '',
				'cl_condition_singular_author' => [],
				'cl_condition_singular_page_template' => [],
				'cl_condition_singular_post' => [],
				'cl_condition_singular_post_type' => [],
				'cl_condition_singular_term' => [],
				'cl_rule_type' => $type,
				'cl_layout_id' => $id
			] );
			/**
			 * Bail if no rule
			 */
			if ( empty( $rule['cl_condition'] ) ) {
				$valid = false;
			}
			/**
			 * Maybe call from class method
			 */
			elseif ( method_exists( __CLASS__ , $rule['cl_condition'] ) ) {
				$valid = call_user_func( [ __CLASS__, $rule['cl_condition'] ], $rule );
			}
			/**
			 * Maybe call from inbuilt
			 */
			elseif ( function_exists( $rule['cl_condition'] ) ) {
				$valid = call_user_func( $rule['cl_condition'] );
			}
			/**
			 * Return false otherwise
			 */
			else {
				$valid = false;
			}
		}

		return $valid;
	}
	/**
	 * Determine if singular conditions are met
	 *
	 * @param  array $rule Ruleset for condition
	 * @return bool
	 */
	public static function conditionSingular( $rule ) {

		$valid = false;

		if ( ! is_singular() ) {
			return false;
		}

		switch ( $rule['cl_condition_singular'] ) {
			case '' :
				/**
				 * Display on all singular
				 */
				if ( empty( $rule['cl_condition_singular_post'] ) ) {
					$valid = true;
				}
				/**
				 * Display on some singular
				 */
				else {
					foreach ( $rule['cl_condition_singular_post'] as $post ) {
						if ( get_the_id() == $post['id'] ) {
							$valid = true;
							break;
						}
					}
				}
				break;
			case 'post_type' :
				/**
				 * If empty...
				 */
				if ( empty( $rule['cl_condition_singular_post_type'] ) ) {
					$valid = false;
				}
				/**
				 * Check each post type
				 */
				else {
					foreach ( $rule['cl_condition_singular_post_type'] as $type ) {
						if ( is_singular( $type ) ) {
							$valid = true;
							break;
						}
					}
				}
				break;
			case 'term' :
				/**
				 * If empty...
				 */
				if ( empty( $rule['cl_condition_singular_term'] ) ) {
					$valid = false;
				}
				/**
				 * Check each term
				 */
				else {
					foreach ( $rule['cl_condition_singular_term'] as $term ) {
						if ( has_term( $term['id'], $term['subtype'], get_the_id() ) ) {
							$valid = true;
							break;
						}
					}
				}
				break;
			case 'author' :
				/**
				 * If empty...
				 */
				if ( empty( $rule['cl_condition_singular_author'] ) ) {
					$valid = false;
				}
				/**
				 * Check each author
				 */
				else {

					$page_author = get_post_field( 'post_author', get_the_id() );

					foreach ( $rule['cl_condition_singular_author'] as $author ) {
						if ( $author['id'] == $page_author ) {
							$valid = true;
							break;
						}
					}
				}
				break;
			case 'template' :
				/**
				 * If empty...
				 */
				if ( empty( $rule['cl_condition_singular_page_template'] ) ) {
					$valid = false;
				}
				/**
				 * Check each template
				 */
				else {
					$page_template = get_page_template_slug( get_the_id() );
					foreach ( $rule['cl_condition_singular_page_template'] as $template ) {
						if ( $page_template == $template ) {
							$valid = true;
							break;
						}
					}
				}
				break;

			default:
				$valid = false;
				break;
		}

		return $valid;
	}
	/**
	 * Determine if archive conditions are met
	 *
	 * @param  array $rule Ruleset for condition
	 * @return bool
	 */
	public static function conditionArchive( $rule ) {

		$valid = false;

		if ( ! is_archive() ) {
			return false;
		}

		switch ( $rule['cl_condition_archive'] ) {
			case '':
				$valid = true;
				break;
			case 'post_type':
				$valid = is_post_type_archive( $rule['cl_condition_archive_post_type'] );
				break;
			case 'term':
				if ( ! empty( $rule['cl_condition_archive_term'] ) ) {
					foreach ( $rule['cl_condition_archive_term'] as $term ) {
						/**
						 * Check categories
						 */
						if ( $term['subtype'] == 'category' ) {
							if ( is_category( $term['id'] ) ) {
								$valid = true;
								break;
							}
						}
						/**
						 * Check tags
						 */
						elseif ( $term['subtype'] == 'post_tag' ) {
							if ( is_tag( $term['id'] ) ) {
								$valid = true;
								break;
							}
						}
						/**
						 * Check everything else
						 */
						elseif ( is_tax( $term['id'], $term['subtype'] ) ) {
							$valid = true;
							break;
						}
					}
				}
				break;
			case 'author':
				if ( ! empty( $rule['cl_condition_archive_author'] ) ) {
					foreach ( $rule['cl_condition_archive_author'] as $author ) {
						if ( is_author( $author['id'] ) ) {
							$valid = true;
							break;
						}
					}
				}
				break;
			default:
				$valid = false;
				break;
		}

		return $valid;
	}
	/**
	 * Determine if date conditions are met
	 *
	 * @param  array $rule Ruleset for condition
	 * @return bool
	 */
	public static function conditionDate( $rule ) {
		$valid = false;

		if ( empty( $rule['cl_condition_datetime'] ) ) {
			return true;
		}

		// /**
		//  * If checking to start at a date/time
		//  */
		// if ( $rule['cl_rule_type'] === 'include' ) {
		// 	$valid = strtotime( wp_date( 'm/d/Y h:i:s a' ) ) <= strtotime( $rule['cl_condition_datetime'] );
		// }
		// *
		//  * If checking to stop at a date/time

		// elseif ( $rule['cl_rule_type'] === 'exclude' ) {
		// 	$valid = strtotime( wp_date( 'm/d/Y h:i:s a' ) ) >= strtotime( $rule['cl_condition_datetime'] );
		// 	self::log( $valid );
		// }
		// return $valid;
		return strtotime( wp_date( 'm/d/Y h:i:s a' ) ) >= strtotime( $rule['cl_condition_datetime'] );
	}
	/**
	 * Determine if user role conditions are met
	 *
	 * @param  array $rule Ruleset for condition
	 * @return bool
	 */
	public static function conditionUserRole( $rule ) {
		$valid = false;

		if ( ! empty( $rule['cl_condition_role'] ) ) {
			foreach ( $rule['cl_condition_role'] as $role ) {
				/**
				 * All NOT logged in users
				 */
				if ( $role === 'none' && ! is_user_logged_in() ) {
					$valid = true;
					break;
				}
				/**
				 * All Logged in users
				 */
				elseif ( $role === 'all' && is_user_logged_in() ) {
					$valid = true;
					break;
				}
				/**
				 * Per role
				 */
				else {
					$user = wp_get_current_user();
					if ( in_array( $role, $user->roles ) ) {
						$valid = true;
						break;
					}
				}
			}
		}

		return $valid;
	}
	/**
	 * Determine if custom conditions are met
	 *
	 * @param  array $rule Ruleset for condition
	 * @return bool
	 */
	public static function conditionCustom( $rule ) {

		$valid = apply_filters( 'custom_layouts/conditions/custom', false, $rule );


		$valid = apply_filters( "custom_layouts/conditions/custom/{$rule['cl_layout_id']}", $valid, $rule );

		return $valid;
	}
	/**
	 * Initialize which layouts get displayed where
	 *
	 * @return void
	 */
	public function initLayouts() {

		$layouts = $this->getLayoutsForDisplay();

		foreach ( $layouts as $layout ) {

			if ( empty ( $layout['hook'] ) ) {
				continue;
			}

			$layout['priority'] = ! empty( $layout['priority'] ) ? $layout['priority'] : 10;

			do_action( 'custom_layouts/before_hook', $layout );

			foreach ( $layout['hook'] as $hook ) {
				/**
				 * Pre-process layouts for removing all other actions
				 */
				if ( $layout['replace_content'] ) {

					add_action( 'get_header', function() use ($layout, $hook) {
						/**
						 * If this action has been removed, bail...
						 */
						if ( ! has_action( $hook, ['\\Mwf\\CustomLayouts\\FrontEnd', "render_{$layout['id']}"] ) ) {
							return;
						}
						else {
							/**
							 * Remove all actions
							 */
							remove_all_actions( $hook );
							/**
							 * Re-add our own
							 */
							add_action( $hook, ['\\Mwf\\CustomLayouts\\FrontEnd', "render_{$layout['id']}"], $layout['priority'] );
						}
					}, 0 );
				}
				/**
				 * Pre-process for replacing *some* other items
				 */
				elseif ( ! empty( $layout['override'] ) ) {

					add_action( 'get_header', function() use ($layout, $hook) {
						/**
						 * If this action has been removed, bail...
						 */
						if ( ! has_action( $hook, ['\\Mwf\\CustomLayouts\\FrontEnd', "render_{$layout['id']}"] ) ) {
							return;
						}

						foreach ( $layout['override'] as $override_id ) {

							$override_priority = carbon_get_post_meta( $override_id, 'cl_action_priority' ) ?? 10;

							remove_action( $hook, ['\\Mwf\\CustomLayouts\\FrontEnd', "render_{$override_id }"], $override_priority );
						}

					}, 0 );

				}
				/**
				 * Add the main action
				 */
				add_action( $hook, ['\\Mwf\\CustomLayouts\\FrontEnd', "render_{$layout['id']}"], $layout['priority'] );
			}

			do_action( 'custom_layouts/after_hook', $layout );
		}
	}
	/**
	 * Set the author in the timber context
	 *
	 * @param object/array $_scope The scope/context from Timber::get_context
	 */
	public function setPost( $_scope ) {

		global $post;

		if ( $post ) {

			$post_id = is_object( $post ) ? $post->ID : $post;

		} else {

			$post_id = get_the_id();

		}

		$_scope['post'] = Timber::get_post( $post_id );

		$_scope['post']->author = new \Mwf\CustomLayouts\Timber\Author( get_the_author_ID() );

		return $_scope;
	}
}