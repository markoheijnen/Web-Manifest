<?php
/*
Plugin Name: Web Manifest
Plugin URI:  http://github.com/markoheijnen/web-manifest
Description: 
Version:     0.1
Author:      Marko Heijnen
Author URI:  http://markoheijnen.com
License:     GPL2
Text Domain: web-manifest
Domain Path: /languages
*/

/*  Copyright 2015 Web Manifest  (email : info@markoheijnen.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


class Web_Manifest {

	public function __construct() {
		add_action( 'wp_head', array( $this, 'output_link_wp_head' ), 10, 0 );
		add_action( 'init', array( $this, 'add_rewrite_rule' ) );
		add_action( 'parse_request', array( $this, 'show_manifest' ) );
	}

	public function output_link_wp_head() {
		echo '<link rel="manifest" href="' . esc_url( home_url('manifest.json') ) . '" />' . PHP_EOL;
	}

	public function add_rewrite_rule() {
		add_rewrite_rule( 'manifest\.json$', 'index.php?webmanifest=1', 'top' );

		global $wp;
		$wp->add_query_var( 'webmanifest' );
	}

	public function show_manifest() {
		if ( empty( $GLOBALS['wp']->query_vars['webmanifest'] ) ) {
			return;
		}

		$manifest = $this->get_manifest();

		echo wp_json_encode( $manifest );
		exit;
	}

	public function get_manifest() {
		$data = array(
			'name'        => get_bloginfo( 'name' ),
			'short_name'  => '',
			'icons'       => array(),

			'display'     => $this->get_display(),
			'orientation' => $this->get_orientation(),
		);

		$site_icon_id = get_option( 'site_icon' );
		if ( $site_icon_id ) {
			$site_icon    = get_post( $site_icon_id );
			$icons        = $GLOBALS['wp_site_icon']->intermediate_image_sizes();
			$meta_data    = wp_get_attachment_metadata( $site_icon_id );

			// Merge icons and Meta data sizes
			$icons = array_unique( array_merge( array('full'), $icons, array_keys($meta_data['sizes']) ) );

			foreach ( $icons as $icon ) {
				$url_data = wp_get_attachment_image_src( $site_icon_id, $icon );
				$image = array(
					'file'      => $url_data[0],
					'width'     => $url_data[1],
					'height'    => $url_data[2]
				);

				if ( isset( $meta_data['sizes'][$icon] ) ) {
					$image['mime-type'] = $meta_data['sizes'][$icon]['mime-type'];
				}
				else {
					$image['mime-type'] = $site_icon->post_mime_type;
				}

				$data['icons'][] = array(
					'src'     => $image['file'],
					'sizes'   => $image['width'] . 'x' . $image['height'],
					'type'    => $image['mime-type']
				);
			}
		}

		return $data;
	}

	public function get_display() {
		$default = 'standalone';
		$display = apply_filters( 'webmanifest_display', $default );

		if ( ! in_array( $display, array( 'fullscreen', 'minimal-ui', 'browser' ) ) ) {
			$display = 'standalone';
		}

		return $display;
	}

	public function get_orientation() {
		$default     = 'landscape';
		$orientation = apply_filters( 'webmanifest_orientation', $default );

		return $orientation;
	}

}

$GLOBALS['webmanifest'] = new Web_Manifest;
