<?php

namespace Mwf\CustomLayouts;

class Widget extends \WP_Widget {
	/**
	 * Root ID for all widgets of this type.
	 *
	 * @since 1.0.0
	 * @var mixed|string
	 */
	public $id_base = '';
	/**
	 * Name for this widget type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $name = '';
	/**
	 * Option array passed to wp_register_sidebar_widget().
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $widget_options = [];
	/**
	 * Option array passed to wp_register_widget_control().
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $control_options = [];
	/**
	 * Constructor, initialize the widget
	 * @method __construct
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct( $this->id_base, $this->name, $this->widget_options, $this->control_options );
	}
	/**
	 * Create back end form for specifying image and content
	 * @param $instance
	 * @see https://codex.wordpress.org/Function_Reference/wp_parse_args
	 * @since 1.0.0
	 */
	public function form( $instance ) {

		printf( '<div class="%s_widget_form" style="padding-top: 10px; padding-bottom: 10px;">', $this->id_base );

		foreach ( $this->getFields() as $field => $args ) {
			/**
			 * Correct field structure
			 */
			$args = wp_parse_args( $args, [ 'id' => $field, 'type' => '', 'label' => '', 'description' => '', 'value' => '' ] );
			/**
			 * Set the value from instance
			 */
			$args['value'] = isset( $instance[$field] ) ? $instance[$field] : $args['value'];
			/**
			 * Output the form field
			 */
			echo '<div class="field" style="margin-bottom: 14px;">';

				$this->input( $args );

				if ( !empty( $args['description'] ) ) {
					printf( '<p class="description">%s</p>', esc_attr( $args['description'] ) );
				}

			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * Update form values
	 * @param $new_instance, $old_instance
	 * @since 1.0.0
	 */
	public function update( $new_instance, $old_instance ) {
		/**
		 * Loop through each field and sanitize
		 */
		foreach( $this->getFields() as $field => $args ) {
			/**
			 * Hande for group fields
			 */
			if( is_array( $new_instance[ $field ] ) ) {

				foreach( $new_instance[ $field ] as $index => $value ) {

					if( isset( $args['sanitize'] ) && function_exists( $args['sanitize'] ) ) {

						$instance[ $field ][$index] = call_user_func( $args['sanitize'], $value );

					}

					else {

						$instance[ $field ][$index] = sanitize_text_field( $value  );

					}

				}

			}
			/**
			 * Handline for singular fields
			 */
			else {
				if( isset( $args['sanitize'] ) && function_exists( $args['sanitize'] ) ) {

					$instance[$field] = call_user_func( $args['sanitize'], $new_instance[$field] );

				}

				else {

					$instance[$field] = sanitize_text_field( $new_instance[$field] );

				}
			}

		}

		return $instance;
	}

	/**
	 * Output widget on the front end
	 * @param $args, $instance
	 * @since 1.0.0
	 */
	public function widget( $args, $instance ) {
		// Display before widget args
		echo $args['before_widget'];
		// Display Title
		if( !empty( $instance['title'] ) ) {
			$instance['title']  = apply_filters( 'widget_title', $instance['title'], $instance, $this->widget_id_base );
			// Again check if filters cleared name, in the case of 'dont show titles' filter or something
			$instance['title']  = ( !empty( $instance['title']  ) ) ? $args['before_title'] . $instance['title']  . $args['after_title'] : '';
			// Display Title
			echo $instance['title'];
		}

		/**
		 * DO WIDGETY STUFF
		 */
		echo '<ul>';
		foreach( $this->getFields() as $field => $field_args ) {
			if( isset( $instance[$field_args['id']] ) ) {
				printf( '<li><strong>%s:</strong> %s</li>', $field_args['id'], esc_attr( $instance[$field_args['id']] ) );
			}
		}
		echo '</ul>';

		// Display after widgets args
		echo $args['after_widget'];
	}
	/**
	 * Get widget form fields
	 * @return array of form fields
	 * @since  1.0.0
	 */
	public function getFields() {
		return [];
	}

	/**
	 * Orchastrate widget form inputs
	 *
	 * @param  [array] $input : the input arguments from our field setting
	 * @param  [string] $value : the value of the input
	 */
	private function input( $input ) {
		switch ( $input['type'] ) {
			case 'text' :
				$this->textInput( $input );
				break;
			case 'textarea' :
				$this->textareaInput( $input );
				break;
			case 'radio' :
				$this->radioInput( $input );
				break;
			case 'checkbox' :
				$this->checkboxInput( $input );
				break;
			case 'select' :
				$this->selectInput( $input );
				break;
			default:
				// Nothing to do here...
				break;
		}
	}
	/**
	 * Output text input for widget forms
	 *
	 * @param  [array] $input : the input arguments from our field setting
	 * @param  [string] $value : the value of the input
	 */
	private function textInput( $input ) {
		/**
		 * Normalize the arguments required for this field type
		 */
		$defaults = array(
			'label' => '',
			'class' => 'widefat',
		);
		$input = array_merge( $defaults, $input );
		/**
		 * Do label
		 */
		printf( '<label for="%s" style="margin-bottom: 5px; display: block;">%s</label>',
			$this->get_field_name( $input['id'] ),
			esc_attr( $input['label'] )
		);
		/**
		 * Do Input
		 */
		printf( '<input name="%s" id="%s" class="%s" type="text" value="%s"/>',
			$this->get_field_name( $input['id'] ),
			$this->get_field_id( $input['id'] ),
			$input['class'],
			esc_attr( $input['value'] )
		);
	}
	/**
	 * Output textarea for widget forms
	 *
	 * @param  [array] $input : the input arguments from our field setting
	 * @param  [string] $value : the value of the input
	 */
	private function textareaInput( $input ) {
		/**
		 * Normalize the arguments required for this field type
		 */
		$defaults = array(
			'label' => '',
			'class' => 'widefat',
			'rows'   => 10,
			'cols'  => 30,
		);
		$input = array_merge( $defaults, $input );
		/**
		 * Do label
		 */
		printf( '<label for="%s" style="margin-bottom: 5px; display: block;">%s</label>',
			$this->get_field_name( $input['id'] ),
			esc_attr( $input['label'] )
		);
		/**
		 * Do Input
		 */
		printf( '<textarea name="%s" id="%s" class="%s" rows="%d" cols="%d">%s</textarea>',
			$this->get_field_name( $input['id'] ),
			$this->get_field_id( $input['id'] ),
			$input['class'],
			$input['rows'],
			$input['cols'],
			esc_attr( $input['value'] )
		);
	}
	/**
	 * Output radio input for widget forms
	 *
	 * @param  [array] $input : the input arguments from our field setting
	 * @param  [string] $value : the value of the input
	 */
	private function radioInput( $input ) {
		/**
		 * Normalize the arguments required for this field type
		 */
		$defaults = array(
			'label'   => '',
			'class'   => 'widefat',
			'default' => '1',
			'options' => array(
				'1' => __( 'Option 1', 'plugin_scaffolding' ),
				'2' => __( 'Option 2', 'plugin_scaffolding' ),
			),
		);
		$input = array_merge( $defaults, $input );
		/**
		 * Open group
		 */
		echo '<radiogroup>';
		/**
		 * do legend
		 */
		printf( '<legend style="margin-bottom: 5px; display: block;">%s</legend>',
			$input['label']
		);
		/**
		 * Do Options
		 */
		foreach( $input['options'] as $input_value => $label ) {
			printf( '<input name="%s" id="%s" class="%s" type="radio" value="%s"%s/>',
				$this->get_field_name( $input['id'] ),
				$this->get_field_id( $input['id'] ),
				$input['class'],
				$input_value,
				checked( $input['value'], $input_value, false )
			);
			printf( '<label for="%s">%s</label>',
				$this->get_field_name( $input['id'] ),
				esc_attr( $label )
			);
			echo '</br>';
		}
		/**
		 * Close group
		 */
		echo '</radiogroup>';
	}
	/**
	 * Output checkbox input for widget forms
	 *
	 * @param  [array] $input : the input arguments from our field setting
	 * @param  [string] $value : the value of the input
	 */
	private function checkboxInput( $input ) {
		/**
		 * Normalize the arguments required for this field type
		 */
		$defaults = array(
			'label' => '',
			'class' => '',
			'value' => '1'
		);
		$input = array_merge( $defaults, $input );
		/**
		 * Do Input
		 */
		printf( '<input name="%s" id="%s" class="%s" type="checkbox" value="1" %s/>',
			$this->get_field_name( $input['id'] ),
			$this->get_field_id( $input['id'] ),
			$input['class'],
			checked( $input['value'], '1', false )
		);
		/**
		 * Do label
		 */
		printf( '<label for="%s">%s</label>',
			$this->get_field_name( $input['id'] ),
			esc_attr( $input['label'] )
		);
	}

	/**
	 * Output select input for widget forms
	 *
	 * @param  [array] $input : the input arguments from our field setting
	 * @param  [string] $value : the value of the input
	 */
	private function selectInput( $input ) {
		/**
		 * Normalize the arguments required for this field type
		 */
		$defaults = array(
			'label' => '',
			'class' => 'widefat',
			'options' => array(
				'' => __( 'Select Option', 'plugin_scaffolding' ),
			),
		);
		$input = array_merge( $defaults, $input );
		/**
		 * Do label
		 */
		printf( '<label for="%s" style="margin-bottom: 5px; display: block;">%s</label>',
			$this->get_field_name( $input['id'] ),
			esc_attr( $input['label'] )
		);
		/**
		 * Open select
		 */
		printf( '<select name="%s" id="%s" class="%s">',
			$this->get_field_name( $input['id'] ),
			$this->get_field_id( $input['id'] ),
			$input['class']
		);
		/**
		 * Do Options
		 */
		foreach( $input['options'] as $option_value => $label ) {
			printf( '<option value="%s"%s>%s</option>',
				$option_value,
				selected( $input['value'], $option_value, false ),
				$label
			);
		}
		/**
		 * Close Select
		 */
		echo '</select>';
	}

}