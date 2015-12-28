<?php

class EDD_Sample_Plugin {
    private $vars;

    function __construct( $vars ) {
        $this->vars = $vars;

        add_action( 'admin_menu', array( $this, 'license_menu' ), 99 );
        add_action( 'plugins_loaded', array( $this, 'init' ) );
        add_action( 'admin_init', array( $this, 'register_option' ) );
        add_action( 'admin_init', array( $this, 'activate_license' ) );
        add_action( 'admin_init', array( $this, 'deactivate_license' ) );
        add_action( 'admin_init', array( $this, 'check_license' ) );
        add_action( 'admin_init', array( $this, 'sl_updater'), 0 );
    }

    function get_var( $var ) {
        if ( isset( $this->vars[ $var ] ) )
            return $this->vars[ $var ];
        return false;
    }

	/**
	 * Show an error message that license needs to be activated
	 */
    function init() {
        if ( 'valid' != get_option( $this->get_var( 'license_status' ) ) ) {
            if ( ( ! isset( $_GET['page'] ) or $this->get_var( 'admin_page_slug' ) != $_GET['page'] ) ) {
                add_action( 'admin_notices', function() {
                    echo '<div class="error"><p>' .
                         sprintf( __( 'The %s license needs to be activated. %sActivate Now%s', 'edd-sample' ), $this->get_var( 'plugin_title' ), '<a href="' . admin_url( 'admin.php?page=' . $this->get_var( 'admin_page_slug' ) ) . '">', '</a>' ) .
                         '</p></div>';
                } );
            } else {
                add_action( 'admin_notices', function() {
                    echo '<div class="notice"><p>' .
                         sprintf( __( 'License key invalid. Need a license? %sPurchase Now%s', 'edd-sample' ), '<a target="_blank" href="' . $this->get_var( 'purchase_url' ) . '">', '</a>' ) .
                         '</p></div>';
                } );
            }
        }

        /**
         * If your plugin depends on another plugin, adds a condition to verify
         * if that plugin is installed.
         */
        if ( ( $this->get_var( 'dependent_class_to_check' ) and ! class_exists( $this->get_var( 'dependent_class_to_check' ) ) ) or
             ( $this->get_var( 'dependent_plugin' ) and ! is_plugin_active( $this->get_var( 'dependent_plugin' ) ) ) ) {
            add_action( 'admin_notices', function() {
                echo '<div class="error"><p>' .
                     sprintf( __( 'The %s plugin requires %s to be installed and activated', 'edd-sample' ), $this->get_var( 'plugin_title' ), $this->get_var( 'dependent_plugin_title' ) ) .
                     '</p></div>';
            } );
        }

    }

    function sl_updater() {
        // retrieve our license key from the DB
        $license_key = trim( get_option( $this->get_var( 'license_key' ) ) );
        $license_status = get_option( $this->get_var( 'license_status' ) );

        // setup the updater
        new EDD_SL_Plugin_Updater( $this->get_var( 'store_url' ), $this->get_var( 'plugin_file' ), array(
                'version' => $this->get_var( 'version' ),
                'license' => $license_key,
                'item_name' => $this->get_var( 'item_name' ),
                'author' => $this->get_var( 'author' )
            ),
	        array(
		        'license_status' => $license_status,
		        'admin_page_url' => admin_url( 'admin.php?page=' . $this->get_var( 'admin_page_slug' ) ),
		        'purchase_url' => $this->get_var( 'purchase_url' ),
		        'plugin_title' => $this->get_var( 'plugin_title' )
	        )
        );
    }

    function license_menu() {
        /**
         * Modify this depending on where you would like your settings page to appear
         */
	    if ( $this->get_var( 'parent_menu_slug' ) ) {
	        add_submenu_page(
	            $this->get_var( 'parent_menu_slug' ),
	            $this->get_var( 'plugin_title' ),
	            $this->get_var( 'settings_page_title' ),
	            'manage_options',
	            $this->get_var( 'admin_page_slug' ),
	            array( $this, 'license_page' )
	        );
	    } else {
		    add_menu_page(
			    $this->get_var( 'plugin_title' ),
		        $this->get_var( 'plugin_title' ),
			    'manage_options',
		        $this->get_var( 'admin_page_slug' ),
			    array( $this, 'license_page' ),
			    null,
			    41
		    );
	    }
    }

    function license_page() {
        $license = get_option( $this->get_var( 'license_key' ) );
        $status = get_option( $this->get_var( 'license_status' ) );
        ?>
        <div class="wrap">
        <h2><?php echo esc_html( $this->get_var( 'plugin_title' ) ) ?></h2>
	    <?php settings_errors(); ?>
        <form method="post" action="options.php">

            <?php settings_fields( $this->get_var( 'option_group' ) ); ?>

	        <?php if ( 'valid' != get_option( $this->get_var( 'license_status' ) ) ): ?>
                <p><?php echo esc_html( sprintf( __( 'Thank you for purchasing %s!  Please enter your license key below.', 'edd-sample' ), $this->get_var( 'plugin_title' ) ) ); ?></p>
            <?php endif; ?>

            <table class="form-table">
                <tbody>
	                <tr valign="top">
	                    <th scope="row" valign="top">
	                        <?php _e( 'License Key' ); ?>
	                    </th>
	                    <td>
	                        <input id="<?= esc_attr( $this->get_var( 'license_key' ) ) ?>" name="<?= $this->get_var( 'license_key' ) ?>" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
	                        <label class="description" for="<?= esc_attr( $this->get_var( 'license_key' ) ) ?>"><?php _e('Enter your license key'); ?></label>
	                    </td>
	                </tr>
	                <tr valign="top">
	                    <th scope="row" valign="top">
	                        <?php _e( 'Activate License' ); ?>
	                    </th>
	                    <td>
	                        <?php if ( $status !== false && $status == 'valid' ) { ?>
	                            <span style="color:green;"><?php _e( 'active' ); ?></span>
	                            <?php wp_nonce_field( $this->get_var( 'option_group' ) . '_nonce', $this->get_var( 'option_group' ) . '_nonce' ); ?>
	                            <input type="submit" class="button-secondary" name="<?= $this->get_var( 'option_group' ) ?>_deactivate" value="<?php _e('Deactivate License'); ?>"/>
	                        <?php } else {
	                            wp_nonce_field( $this->get_var( 'option_group' ) . '_nonce', $this->get_var( 'option_group' ) . '_nonce' ); ?>
	                            <input type="submit" class="button-secondary" name="<?= $this->get_var( 'option_group' ) ?>_activate" value="<?php _e('Activate License'); ?>"/>
	                        <?php } ?>
	                    </td>
	                </tr>
                </tbody>
            </table>

            <p><?php echo sprintf( esc_html( __( 'Any questions or problems with your license? %sContact us%s!', 'edd-sample' ) ), '<a href="' . $this->get_var( 'contact_url' ) . '">', '</a>' ); ?></p>
        </form>
    <?php
    }

    function register_option() {
        // creates our settings in the options table
        register_setting( $this->get_var( 'option_group' ), $this->get_var( 'license_key' ), array( $this, 'sanitize_license' ) );
    }

    function sanitize_license( $new ) {
        $old = get_option( $this->get_var( 'license_key' ) );
        if ( $old && $old != $new ) {
            delete_option( $this->get_var( 'license_status' ) ); // new license has been entered, so must reactivate
        }
        return $new;
    }

    function activate_license() {
        // listen for our activate button to be clicked
        if ( isset( $_POST[ $this->get_var( 'option_group' ) . '_activate' ] ) ) {
            // run a quick security check
            if ( ! check_admin_referer( $this->get_var( 'option_group' ) . '_nonce', $this->get_var( 'option_group' ) . '_nonce' ) )
                return; // get out if we didn't click the Activate button

            // save the license key to the database
            update_option( $this->get_var( 'license_key' ), $_POST[$this->get_var( 'license_key' )] );

            // retrieve the license from the database
            $license = trim( get_option( $this->get_var( 'license_key' ) ) );

            // data to send in our API request
            $api_params = array(
                'edd_action'=> 'activate_license',
                'license' 	=> $license,
                'item_name' => urlencode( $this->get_var( 'item_name' ) ), // the name of our product in EDD
                'url'       => home_url()
            );

            // Call the custom API.
            $response = wp_remote_post( $this->get_var( 'store_url' ), array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

            // make sure the response came back okay
            if ( is_wp_error( $response ) ) {
	            add_settings_error(
		            $this->get_var( 'option_group' ),
		            'activate',
		            __( 'There was an error activating the license, please verify your license is correct and try again or contact support.', 'edd-sample' )
	            );
	            return false;
            }

            // decode the license data
            $license_data = json_decode( wp_remote_retrieve_body( $response ) );

            // $license_data->license will be either "valid" or "invalid"
            update_option( $this->get_var( 'license_status' ), $license_data->license );
	        if ( 'valid' != $license_data->license ) {
		        add_settings_error(
			        $this->get_var( 'option_group' ),
			        'activate',
			        __( 'There was an error activating the license, please verify your license is correct and try again or contact support.', 'edd-sample' )
		        );
	        }
        }
    }

    function deactivate_license() {
        // listen for our activate button to be clicked
        if ( isset( $_POST[ $this->get_var( 'option_group' ) . '_deactivate'] ) ) {
            // run a quick security check
            if( ! check_admin_referer( $this->get_var( 'option_group' ) . '_nonce', $this->get_var( 'option_group' ) . '_nonce' ) )
                return; // get out if we didn't click the Activate button

            // retrieve the license from the database
            $license = trim( get_option( $this->get_var( 'license_key' ) ) );

            // data to send in our API request
            $api_params = array(
                'edd_action'=> 'deactivate_license',
                'license' 	=> $license,
                'item_name' => urlencode( $this->get_var( 'item_name' ) ), // the name of our product in EDD
                'url'       => home_url()
            );

            // Call the custom API.
            $response = wp_remote_post( $this->get_var( 'store_url' ), array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

            // make sure the response came back okay
            if ( is_wp_error( $response ) ) {
	            add_settings_error(
		            $this->get_var( 'option_group' ),
		            'deactivate',
		            __( 'There was an error deactivating the license, please try again or contact support.', 'edd-sample' )
                );
                return false;
            }

            // decode the license data
            $license_data = json_decode( wp_remote_retrieve_body( $response ) );

            // $license_data->license will be either "deactivated" or "failed"
	        if ( 'deactivated' == $license_data->license ) {
		        add_settings_error(
			        $this->get_var( 'option_group' ),
			        'deactivate',
			        __( 'License deactivated', 'edd-sample' )
		        );
		        delete_option( $this->get_var( 'license_status' ) );
	        } else {
		        add_settings_error(
			        $this->get_var( 'option_group' ),
			        'deactivate',
			        __( 'Unable to deactivate license, please try again or contact support.', 'edd-sample' )
		        );
	        }
        }
    }

    function check_license() {
        if ( get_transient( $this->get_var( 'license_status' ) . '_checking' ) )
            return;

        $license = trim( get_option( $this->get_var( 'license_key' ) ) );

        $api_params = array(
            'edd_action' => 'check_license',
            'license' => $license,
            'item_name' => urlencode( $this->get_var( 'item_name' ) ),
            'url'       => home_url()
        );

        // Call the custom API.
        $response = wp_remote_post(
            $this->get_var( 'store_url' ),
            array(
                'timeout' => 15,
                'sslverify' => false,
                'body' => $api_params
            )
        );

        if ( is_wp_error( $response ) )
            return false;

        $license_data = json_decode(
            wp_remote_retrieve_body( $response )
        );

        if ( $license_data->license != 'valid' ) {
            delete_option( $this->get_var( 'license_status' ) );
        }

        // Set to check again in 12 hours
        set_transient(
            $this->get_var( 'license_status' ) . '_checking',
            $license_data,
            ( 60 * 60 * 12 )
        );
    }
}
