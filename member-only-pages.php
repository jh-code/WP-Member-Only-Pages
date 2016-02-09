<?php
/*
Plugin Name: Member Only Pages
Plugin URI: http://wphax.com
Description: Creates a meta box on posts and pages allowing the admin to select if non-members get redirected to the registration page or not.
Version: 0.1b
Author: Jared Helgeson
Author URI: http://wphax.com
Text Domain: jh_mop
*/

if( !class_exists( 'JH_MOP' ) ) {
	class JH_MOP {

		function __construct() {
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
			add_action( 'save_post', array( $this, 'save_meta_box' ) );
			add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		}

		function template_redirect() {
			global $post;

			if( !is_user_logged_in() && !is_home() && !is_front_page() ) {
				$redirect = get_post_meta( $post->ID, '_jh_mop_redirect', true );

				if( (int)$redirect === 1 )
					wp_redirect( wp_registration_url() );
			}
		}

		function add_meta_boxes( $post_type ) {
			$post_types = array( 'post', 'page' );

			if( in_array( $post_type, $post_types ) )
		    	add_meta_box( 'jh-mop', __( 'Member Only Pages', 'jh_mop' ), array( $this, 'meta_box_display' ), $post_type, 'side' );
		}

		function meta_box_display( $post ) {
        	wp_nonce_field( 'jh_mop_meta', 'jh_mop_meta_nonce' );

        	$value = get_post_meta( $post->ID, '_jh_mop_redirect', true ); ?>

			<input type="checkbox" id="jh_mop_redirect" name="jh_mop_redirect" value="1"<?php echo ( $value == 1 ? ' checked' : '' ); ?>> 

	        <label for="jh_mop_redirect"><?php _e( 'Redirect non-members to registration page?', 'jh_mop' ); ?> </label>
	        
	    <?php }
		 
		function save_meta_box( $post_id ) {
	        if( !isset( $_POST[ 'jh_mop_meta_nonce' ] ) )
	            return $post_id;
	 
	        $nonce = $_POST[ 'jh_mop_meta_nonce' ];
	 
	        if( !wp_verify_nonce( $nonce, 'jh_mop_meta' ) )
	            return $post_id;
	 
	        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
	            return $post_id;
	 
	        // Check the user's permissions.
	        if ( 'page' == $_POST['post_type'] ) {
	            if ( ! current_user_can( 'edit_page', $post_id ) ) {
	                return $post_id;
	            }
	        } else {
	            if ( ! current_user_can( 'edit_post', $post_id ) ) {
	                return $post_id;
	            }
	        }
	 
	        /* OK, it's safe for us to save the data now. */
	 
	        // Sanitize the user input.
	        $data = sanitize_text_field( $_POST[ 'jh_mop_redirect' ] );
	 
	        // Update the meta field.
	        update_post_meta( $post_id, '_jh_mop_redirect', $data );
		}
	}
}

global $jh_mop;
$jh_mop = new JH_MOP();