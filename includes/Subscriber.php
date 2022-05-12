<?php

namespace Mwf\CustomLayouts;

class Subscriber {
	/**
	 * Instances
	 * @since 1.0.0
	 * @access protected
	 * @var (array) $instances : Collection of instantiated classes
	 */
	protected static $instances = [];
	/**
	 * Constructor
	 * @since 1.0.0
	 * @access protected
	 */
	protected function __construct() {
		// do nothing here
	}
	/**
	 * Get registered instance of THIS class
	 *
	 * @since  1.0.0
	 * @return obj Instance of this class
	 */
	public static function getInstance( $class = '' ) {
		$instance = false;
		/**
		 * If class is empty, return an instance of ourself
		 */
		if ( empty( $class ) ) {
			if ( ! isset( self::$instances[ __CLASS__ ] ) ) {
				self::$instances[ __CLASS__ ] = new self();
			}
			$instance = self::$instances[ __CLASS__ ];
		}
		/**
		 * Else search for an instance
		 */
		else {
			$classname = is_object( $class ) ? get_class( $class ) : $class;

			/**
			 * Check and see if we have an instance of that class to return
			 */
			if ( isset( self::$instances[$classname] ) ) {
				$instance = self::$instances[$classname];
			}
			/**
			 * Else check if we have to add the namespace, and return the instance
			 */
			elseif ( isset( self::$instances[ __NAMESPACE__ . "\\" . $classname ] ) ) {
				$instance = self::$instances[ __NAMESPACE__ . "\\" . $classname ];
			}
			/**
			 * Else see if we were passed on object to add to instances,
			 * & return it as the instance
			 */
			elseif ( is_object( $class ) ) {
				self::$instances[ $classname ] = $class;
				$instance = self::$instances[ $classname ];
			}
			/**
			 * Else see if we were passed a string, and it's an existing class
			 */
			elseif ( is_string( $classname ) && class_exists( $classname ) ) {

				self::$instances[ $classname ] = new $class();

				$instance = self::$instances[ $classname ];
			}
			/**
			 * Else maybe add our own namespace
			 */
			elseif ( is_string( $classname ) && class_exists( __NAMESPACE__ . "\\" . $classname ) ) {

				$ns_classname = __NAMESPACE__ . "\\" . $classname;

				self::$instances[ $classname ] = new $ns_classname();

				$instance = self::$instances[ $classname ];
			}
		}
		return $instance;
	}
	/**
	 * Hooks a function on to a specific action.
	 *
	 * Exactly like wordpress native add_action, which calls add_filter,
	 * this is just a wrapper for addFilter
	 *
	 * @param string $hook : The name of the filter to hook the $function_to_add callback to.
	 * @param callable $function : The callback to be run when the filter is applied.
	 * @param int $priority  :Optional. Used to specify the order in which the functions
	 * @param int $argument_count   Optional. The number of arguments the function accepts. Default 1.
	 *
	 * @see  https://developer.wordpress.org/reference/functions/add_action/
	 */
	public static function addAction( $hook = '', $function = '', $priority = 10, $argument_count = 1 ) {
		self::addFilter( $hook, $function, $priority, $argument_count );
	}
	/**
	 * Removes a function from a specified action hook.
	 *
	 * Exactly like wordpress native remove_action, which calls remove_filter,
	 * this is just a wrapper for removeFilter
	 *
	 * @param string $tag : The action hook to which the function to be removed is hooked.
	 * @param callable $function : The name of the function which should be removed.
	 * @param int $priority : Optional. The priority of the function. Default 10.
	 *
	 * @see  https://developer.wordpress.org/reference/functions/remove_action/
	 */
	public static function removeAction( $hook = '', $function = '', $priority = 10 ) {
		self::removeFilter( $hook, $function, $priority );
	}
	/**
	 * Checks if a specific action has been registered for this hook.
	 *
	 * Wrapper for hasFilter
	 *
	 * @since 1.0.0
	 *
	 * @param string hook The name of the filter hook. Default empty.
	 * @param callable|bool $function Optional. The callback to check for. Default false.
	 * @return bool|int The priority of that hook is returned, or false if the function is not attached.
	 * @see  https://developer.wordpress.org/reference/functions/has_action/
	 */
	public static function hasAction( $hook = '', $function = false ) {
		return self::hasFilter( $hook, $function );
	}
	/**
	 * Hook a function or method to a specific filter action.
	 *
	 * @param string $hook : The name of the filter to hook the $function_to_add callback to.
	 * @param callable $function : The callback to be run when the filter is applied.
	 * @param int $priority  :Optional. Used to specify the order in which the functions
	 * @param int $argument_count   Optional. The number of arguments the function accepts. Default 1.
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_filter/
	 */
	public static function addFilter( $hook = '', $function = '', $priority = 10, $argument_count = 1 ) {

		if( is_array( $function ) ) {

			$instance = self::getInstance( $function[0] );

			/**
			 * If we have the correct instance
			 */
			if( $instance ) {
				add_filter( $hook, [ $instance, $function[1] ], $priority, $argument_count );
			}
			/**
			 * If we are passed an object
			 */
			elseif( is_object( $function[0] ) ) {
				add_filter( $hook, [ $function[0], $function[1] ], $priority, $argument_count );
			}
		}
		/**
		 * Else if we are just passed a string
		 */
		else {
			add_filter( $hook, $function, $priority, $argument_count );
		}
	}
	/**
	 * Removes a function from a specified filter hook.
	 *
	 * This function removes a function attached to a specified filter hook. This
	 * method can be used to remove default functions attached to a specific filter
	 * hook and possibly replace them with a substitute.
	 *
	 * @param string $tag : The action hook to which the function to be removed is hooked.
	 * @param callable $function : The name of the function which should be removed.
	 * @param int $priority : Optional. The priority of the function. Default 10.
	 *
	 * @see  https://developer.wordpress.org/reference/functions/remove_action/
	 */
	public static function removeFilter( $hook = '', $function = '', $priority = 10 ) {

		if( is_array( $function ) ) {

			$instance = self::getInstance( $function[0] );

			/**
			 * If we have the correct instance, remove it
			 */
			if( $instance ) {
				remove_filter( $hook, [ $instance, $function[1] ], $priority );
			}
			/**
			 * If we are passed an object
			 */
			elseif( is_object( $function[0] ) ) {
				remove_filter( $hook, [ $function[0], $function[1] ], $priority );
			}

			/**
			 * If is an active object
			 */
			if( is_object( $function[0] ) ) {
				/**
				 * Either register or get the instance of the object from our own
				 * classlist
				 */
				$instance = self::getInstance( $function[0] );
				/**
				 * If we have an instnce of that object
				 */
				if( $instance ) {
					remove_filter( $hook, [ $instance, $function[1] ], $priority );
				}
				/**
				 * Else just assume we were passed the correct instance
				 */
				else {
					remove_filter( $hook, [ $function[0], $function[1] ], $priority );
				}
			}
			/**
			 * If the function is the string of a classname
			 */
			elseif( is_string( $function[0] ) ) {
				/**
				 * Either register or get the instance of the object from our own
				 * classlist
				 */

				$instance = self::getInstance( $function[0] );
				/**
				 * If we have an instnce of that classname
				 */
				if( $instance ) {

					remove_filter( $hook, [ $instance, $function[1] ], $priority );
				}
				/**
				 * Else remove static function
				 */
				remove_filter( $hook, $function[0] . '::' . $function[1], $priority );
			}
		}
		/**
		 * Else if we are just passed a string
		 */
		else {
			remove_filter( $hook, $function, $priority );
		}
	}
	/**
	 * Checks if a specific action has been registered for this hook.
	 *
	 * @since 1.0.0
	 *
	 * @param string hook The name of the filter hook. Default empty.
	 * @param callable|bool $function Optional. The callback to check for. Default false.
	 * @return bool|int The priority of that hook is returned, or false if the function is not attached.
	 * @see  https://developer.wordpress.org/reference/functions/has_filter/
	 */
	public static function hasFilter( $hook = '', $function = false ) {

		$has_filter = false;

		if( is_array( $function ) ) {
			/**
			 * If is an active object
			 */
			if( is_object( $function[0] ) ) {
				/**
				 * If we have an instnce of that object
				 */
				if( isset( self::$instances[get_class($function[0])] ) ) {
					$has_filter = has_filter( $hook, [ self::$instances[get_class($function[0])], $function[1] ] );
				}
				/**
				 * Else just assume we were passed the correct instance
				 */
				else {
					$has_filter = has_filter( $hook, [ $function[0], $function[1] ] );
				}
			}
			/**
			 * If the function is the string of a classname
			 */
			elseif( is_string( $function[0] ) ) {
				/**
				 * If we have an instnce of that classname
				 */
				if( isset( self::$instances[$function[0]] ) ) {
					$has_filter = has_filter( $hook, [ self::$instances[$function[0]], $function[1] ] );
				}
				/**
				 * Else remove static function
				 */
				else {
					$has_filter = has_filter( $hook, $function[0] . '::' . $function[1], $priority );
				}
			}
		}
		/**
		 * Else if we are just passed a string
		 */
		else {
			$has_filter = has_filter( $hook, $function, $priority );
		}
		return $has_filter;
	}
	/**
	 * Adds a new shortcode.
	 *
	 * Care should be taken through prefixing or other means to ensure that the
	 * shortcode tag being added is unique and will not conflict with other,
	 * already-added shortcode tags. In the event of a duplicated tag, the tag
	 * loaded last will take precedence.
	 *
	 * @param string $tag : Shortcode tag to be searched in post content.
	 * @param callable $callback : The callback function to run when the shortcode is found.
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_shortcode/
	 */
	public static function addShortcode( $hook = '', $function = '' ) {

		if( is_array( $function ) ) {
			/**
			 * If we were passed in instance of an object
			 */
			if( is_object( $function[0] ) ) {
				/**
				 * Either register or get the instance of the object from our own
				 * classlist
				 */
				$instance = self::getInstance( $function[0] );
				/**
				 * Add the filter
				 */
				add_shortcode( $hook, [ $instance, $function[1] ] );
			}
			/**
			 * Else just assume we were passed the correct instance
			 */
			else {
				add_shortcode( $hook, [ $function[0], $function[1] ] );
			}

		}
		/**
		 * Else if we are just passed a string
		 */
		else {
			add_shortcode( $hook, $function );
		}
	}
}