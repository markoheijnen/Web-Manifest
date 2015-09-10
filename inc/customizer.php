<?php

class Web_Manifest_Customizer {

	public function __construct() {
		add_action( 'customize_register', array( $this, 'register' ) );
	}


	public function register( $wp_customize ) {
		// Add color control for the background of the blocks
		$wp_customize->add_setting( 'webmanifest_shortname', array(
			'default'           => get_bloginfo( 'name' ),
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
			'capability'        => 'manage_options',
			'type'              => 'option',
		) );

		$wp_customize->add_control( 'webmanifest_shortname', array(
			'section'  => 'title_tagline',
			'label'    => __( 'Short name', 'webmanifest' ),
			'type'     => 'text',
			'priority' => 10,
		) );
	}

}
new Web_Manifest_Customizer();