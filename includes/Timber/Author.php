<?php

namespace Mwf\CustomLayouts\Timber;

use \Timber\User;
use \Timber\Image;

use \Mwf\CustomLayouts\Framework;

class Author extends User {

	private $_links = [];

	/**
	 * @param object|int|bool $uid
	 */
	public function __construct( $uid = false ) {

		parent::__construct( $uid );

		$custom_description = carbon_get_user_meta( $this->ID, 'cl_author_content' );

		if ( ! empty( $custom_description ) ) {
			$this->description = $custom_description;
		}
	}

	public function links() {

		if ( empty( $this->_links ) ) {

			$networks = [
				'url' => [ 'icon' => 'cl-icon cl-icon-sphere' ],
				'facebook' => [ 'icon' => 'cl-icon cl-icon-facebook' ],
				'github' => [ 'icon' => 'cl-icon cl-icon-github' ],
				'googleplus' => [ 'icon' => 'cl-icon cl-icon-google' ],
				'instagram' => [ 'icon' => 'cl-icon cl-icon-instagram' ],
				'linkedin' => [ 'icon' => 'cl-icon cl-icon-linkedin2' ],
				'pinterest' => [ 'icon' => 'cl-icon cl-icon-pinterest' ],
				'soundcloud' => [ 'icon' => 'cl-icon cl-icon-soundcloud' ],
				'twitter' => [ 'icon' => 'cl-icon cl-icon-twitter'],
				'wikipedia' => [ 'icon' => 'cl-icon cl-icon-wikipedia' ],
				'wordpress' => [ 'icon' => 'cl-icon cl-icon-wordpress' ],
				'youtube' => [ 'icon' => 'cl-icon cl-icon-youtube1' ],
			];

			$networks = apply_filters( 'custom_layouts/author/networks', $networks );

			$yoast = $this->isPluginActive( 'wordpress-seo/wp-seo.php' );

			foreach ( $networks as $name => $args ) {

				$args = wp_parse_args( $args, [
					'url' => '',
					'pattern' => '',
					'icon' => '',
				] );

				if ( $yoast ) {
					/**
					 * If url is set from filter, don't override
					 */
					$args['url'] = empty( $args['url'] ) ? get_the_author_meta( $name ) : $args['url'];
					/**
					 * Set the twitter pattern
					 */
					if ( $name === 'twitter' ) {
						$args['pattern'] = 'https://twitter.com/@%s';
					}
				} else {
					$args['url'] = empty( $args['url'] ) ? carbon_get_user_meta( $this->ID, 'cl_network_' . $name ) : $args['url'];
				}
				/**
				 * Bail if empty at this point
				 */
				if ( empty( $args['url'] ) ) {
					continue;
				}
				/**
				 * Set URL
				 */
				$this->_links[$name]['url'] = empty( $args['pattern'] ) ? esc_url_raw( $args['url'] ) : esc_url_raw( sprintf( $args['pattern'], $args['url'] ) );
				/**
				 * Set Icon
				 */
				$this->_links[$name]['icon'] = $args['icon'];
			}

		}

		return $this->_links;

	}

	public function avatar( $args = null ) {
		if ( $this->avatar_override ) {
			return $this->avatar_override;
		}
		else {
			$args = wp_parse_args( $args, [ 'size' => 80 ] );
			$custom_image_id = carbon_get_user_meta( $this->ID, 'cl_author_image' );
			if ( $custom_image_id ) {
				return new Image( wp_get_attachment_image_url( $custom_image_id, [ $args['size'], $args['size'] ] ) );
			}
			else {
				return new Image( get_avatar_url( $this->id, $args ) );
			}
		}
	}
}