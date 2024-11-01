<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( in_array('user-registration/user-registration.php', apply_filters('active_plugins', get_option('active_plugins'))) ){
    class TomSVaptchaUR {
        public function __construct(){
            add_action( 'TomSVaptchaExtraForms', array($this, 'toms_vaptcha_ur_options'), 15, 2 );
            add_action( 'TomSVaptchaExtraFormsData', array($this, 'toms_vaptcha_ur_options_data'), 15, 2);

            if( !empty( esc_textarea( get_option('toms_vaptcha_vid') ) ) ){
                if( esc_textarea( get_option( 'toms_vaptcha_ur_login_form') ) == "0" ){
                    add_action('user_registration_login_form', array($this, 'toms_vaptcha_for_ur_login_form'), 10, 2);
                    add_filter('user_registration_process_login_errors', array($this, 'toms_vaptcha_ur_login_form_verification'), 10, 3);
                }
                if( esc_textarea( get_option( 'toms_vaptcha_ur_register_form', "0" ) ) == "0" ){
                    add_action('user_registration_after_form_fields', array($this, 'toms_vaptcha_for_ur_register_form'), 10, 2);
                    add_action('user_registration_after_submit_buttons', array($this, 'toms_vaptcha_ur_register_form_after_submit_button'), 10, 2);
                }
                if( esc_textarea( get_option( 'toms_vaptcha_ur_lostpassword_form', "0" ) ) == "0" ){
                    add_action('user_registration_lostpassword_form', array($this, 'toms_vaptcha_ur_lostpassword_form'), 10, 2);
                // add_filter( 'allow_password_reset', array($this, 'toms_vaptcha_ur_lostpassword_form_verification'), 10, 3);
                }
            }

        }

        /**
         *  Add User Registration Support to TomS Vaptcha settings page
         * 
         * block data-type      user-registration/form-selector
        */
        function toms_vaptcha_ur_options(){ ?>
            <!--User Registration Forms-->
            <div class="toms-vaptcha-wordpress-default-forms"><?php _e('User Registration', 'toms-vaptcha'); ?> : </div>
            <div class="toms-vaptcha-form-list">
                <div class="toms-vaptcha-forms-contents">
                    <label class="toms-label">
                        <input type="checkbox" name="toms_vaptcha_ur_login_form" value="0" <?php if( esc_textarea( get_option('toms_vaptcha_ur_login_form' ) ) == "0" )  echo 'checked="checked"'; ?> />
                        <span class="login-text"><?php _e('Login Form', 'toms-vaptcha'); ?></span>
                    </label>
                    <label class="toms-label">
                        <input type="checkbox" name="toms_vaptcha_ur_register_form" value="0"  <?php if( esc_textarea( get_option('toms_vaptcha_ur_register_form', "0" ) ) == "0" )  echo 'checked="checked"'; ?> />
                        <span class="register-text"><?php _e('Register Form', 'toms-vaptcha'); ?></span>
                    </label>
                    <label class="toms-label">
                        <input type="checkbox" name="toms_vaptcha_ur_lostpassword_form" value="0"  <?php if( esc_textarea( get_option('toms_vaptcha_ur_lostpassword_form', "0" ) )  == "0" )  echo 'checked="checked"'; ?> />
                        <span class="lostpassword-text"><?php _e('Lost Password Form', 'toms-vaptcha'); ?></span>
                    </label>
                    <span class="toms-label">
                        <span class="comment-text"></span>
                    </span>
                </div>
            </div>
        <?php }

        /**
         *  Insert User Registration options to database
        */
        function toms_vaptcha_ur_options_data(){
            update_option('toms_vaptcha_ur_login_form', isset($_POST['toms_vaptcha_ur_login_form']) ? sanitize_text_field( $_POST['toms_vaptcha_ur_login_form'] ) : '' );
            update_option('toms_vaptcha_ur_register_form', isset($_POST['toms_vaptcha_ur_register_form']) ? sanitize_text_field( $_POST['toms_vaptcha_ur_register_form'] ) : '' );
            update_option('toms_vaptcha_ur_lostpassword_form', isset($_POST['toms_vaptcha_ur_lostpassword_form']) ? sanitize_text_field( $_POST['toms_vaptcha_ur_lostpassword_form'] ) : '' );
        }

        /**
         * User Registration Login Form
         * 
         * @param user-registration-form-login  User Registration login form class name.
         * 
         *  Warnning: Work  User Registration->settings->General Options->My account page only.
        */
        function toms_vaptcha_for_ur_login_form(){
            global $post;
	        $post_content = isset($post->post_content) ? $post->post_content : '';

            $ur_account_url = get_permalink( get_option( 'user_registration_myaccount_page_id' ));
            $ur_current_url = home_url( $_SERVER["REQUEST_URI"]);
            if( untrailingslashit($ur_account_url) == untrailingslashit($ur_current_url) || 
                has_block('user-registration/form-selector') ||
                has_shortcode($post_content, 'user_registration_my_account')
            ){
                $id_class = '.user-registration-form-login';
                $html = '';
                $TomSVaptchaFrontend    = new TomSVaptchaFrontend();
                $TomSVaptcha            = new TomSVaptcha();
                $allowed_html           = $TomSVaptcha->TomSVAPTCHA_allow_html();
                $allowed_protocols      = $TomSVaptcha->TomSVAPTCHA_allow_protocols();
                if( esc_textarea( get_option('toms_vaptcha_mode', 'click') ) == 'click'){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Click_Mode_HTML($id_class);
                    if( has_block('user-registration/form-selector') || has_shortcode($post_content, 'user_registration_my_account')){
                        $html .= '<input type="hidden" name="toms_vaptcha_shortcode" value="true" />';
                    }
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Click_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                if( esc_textarea( get_option('toms_vaptcha_mode') ) == 'invisible' ){
                    if( has_block('user-registration/form-selector') || has_shortcode($post_content, 'user_registration_my_account')){
                        $html .= '<input type="hidden" name="toms_vaptcha_shortcode" value="true" />';
                    }
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Invisible_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                if( esc_textarea( get_option('toms_vaptcha_mode') ) == 'embedded' ){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_HTML($id_class);
                    if( has_block('user-registration/form-selector') || has_shortcode($post_content, 'user_registration_my_account')){
                        $html .= '<input type="hidden" name="toms_vaptcha_shortcode" value="true" />';
                    }
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
            }
        }

         /**
         * User Registration Login Form VAPTCHA verification
         * 
         * @param $_POST['vaptcha_server']
         * @param $_POST['vaptcha_token']
         * 
         * @param $user  Wordpress login form user data. if the Vaptcha passed, will allow user login, else return ERROR.
         * 
         *  Warnning: Work  User Registration->settings->General Options->My account page only.
        */
        function toms_vaptcha_ur_login_form_verification($errors){
            $ur_account_url    = get_permalink( get_option('user_registration_myaccount_page_id') );
            $current_page_url   = home_url( $_SERVER["REQUEST_URI"]);

            if( untrailingslashit($ur_account_url) == untrailingslashit($current_page_url) || isset($_POST['toms_vaptcha_shortcode']) ){
                if ( isset($_POST['vaptcha_token']) && isset($_POST['vaptcha_server']) ) {
                    $vaptcha_token  = isset($_POST['vaptcha_token']) ? sanitize_text_field( $_POST['vaptcha_token'] ) : '';
                    $vaptcha_server = isset($_POST['vaptcha_server']) ? sanitize_text_field( $_POST['vaptcha_server'] ) : '';
                    $TomSVaptcha = new TomSVaptcha();
                    if ( $TomSVaptcha->TomSVaptcha_verification( $vaptcha_server, $vaptcha_token ) == true ){
                        return $errors;
                    }else{
                        return new WP_Error("Captcha Invalid", __(" Captcha verification failed, please try again.", 'toms-vaptcha'));
                    }
                }else{
                    return new WP_Error("Captcha Invalid", __(" Captcha verification failed, Please click to verify.", 'toms-vaptcha'));
                }
            }
            return $errors;
        }

        /**
         * User Registration Register Form
         * 
         * @param register  User Registration login form class name.
         * 
        */
        function toms_vaptcha_for_ur_register_form(){
            global $post;
	        $post_content = isset($post->post_content) ? $post->post_content : '';

            $ur_account_url = get_permalink( get_option( 'user_registration_myaccount_page_id' ));
            $ur_current_url = home_url( $_SERVER["REQUEST_URI"]);

            if( untrailingslashit($ur_account_url) == untrailingslashit($ur_current_url) || 
                has_block('user-registration/form-selector') || 
                has_shortcode($post_content, 'user_registration_form') ){
                $id_class = '.register';
                $html = '';
                $TomSVaptchaFrontend    = new TomSVaptchaFrontend();
                $TomSVaptcha            = new TomSVaptcha();
                $allowed_html           = $TomSVaptcha->TomSVAPTCHA_allow_html();
                $allowed_protocols      = $TomSVaptcha->TomSVAPTCHA_allow_protocols();
                if( esc_textarea( get_option('toms_vaptcha_mode', 'click') ) == 'click'){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Click_Mode_HTML($id_class);
                    if( has_block('user-registration/form-selector') || has_shortcode($post_content, 'user_registration_my_account')){
                        $html .= '<input type="hidden" name="toms_vaptcha_shortcode" value="true" />';
                    }
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    //wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Click_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                if( esc_textarea( get_option('toms_vaptcha_mode') ) == 'invisible' ){
                    if( has_block('user-registration/form-selector') || has_shortcode($post_content, 'user_registration_my_account')){
                        $html .= '<input type="hidden" name="toms_vaptcha_shortcode" value="true" />';
                    }
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    //wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Invisible_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                if( esc_textarea( get_option('toms_vaptcha_mode') ) == 'embedded' ){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_HTML($id_class);
                    if( has_block('user-registration/form-selector') || has_shortcode($post_content, 'user_registration_my_account')){
                        $html .= '<input type="hidden" name="toms_vaptcha_shortcode" value="true" />';
                    }
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    //wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
            }
        }
        /**
         * User Registration Register Form after submit button
         * 
         * @param register  User Registration login form class name.
         * 
        */
        function toms_vaptcha_ur_register_form_after_submit_button(){
            global $post;
	        $post_content = isset($post->post_content) ? $post->post_content : '';

            $ur_account_url = get_permalink( get_option( 'user_registration_myaccount_page_id' ));
            $ur_current_url = home_url( $_SERVER["REQUEST_URI"]);

            if( untrailingslashit($ur_account_url) == untrailingslashit($ur_current_url) || 
                has_block('user-registration/form-selector') || 
                has_shortcode($post_content, 'user_registration_form') ){
                $id_class       = '.register';
                $submit_class   = 'ur-submit-button';
                $nonce_name     = 'ur_frontend_form_nonce';
                
                $TomSVaptchaFrontend = new TomSVaptchaFrontend();
                $TomSVaptcha            = new TomSVaptcha();
                $allowed_html           = $TomSVaptcha->TomSVAPTCHA_allow_html();
                $allowed_protocols      = $TomSVaptcha->TomSVAPTCHA_allow_protocols();
                $html = '';
                $html .= $TomSVaptchaFrontend->TomSVaptcha_Verify_Frontend_Only_style($id_class, $submit_class, $nonce_name);
                echo wp_kses( $html, $allowed_html, $allowed_protocols );
                wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Verify_Frontend_Only_JS($id_class, $submit_class, $nonce_name), [ 'type' => 'text/javascript' ] );
            }
        }

        /**
         * User Registration Lostpassword Form
         * 
         * @param user-registration-ResetPassword  User Registration login form class name.
         * 
         *  Warnning: Work User Registration->settings->General Options->Lost password page only.
        */
        function toms_vaptcha_ur_lostpassword_form(){
            //Get User Registration Lostpassword URL
            $ur_account_url            = untrailingslashit( get_permalink( get_option('user_registration_myaccount_page_id') ) );
            $wp_link_type              = empty( get_option( 'permalink_structure' ) ) ? '&' : '/';
            $ur_lostpasswd_endpoint    = esc_textarea( get_option( 'user_registration_myaccount_lost_password_endpoint', 'lost-password' ) );
            $ur_lostpasswd_url         = untrailingslashit( $ur_account_url . $wp_link_type . $ur_lostpasswd_endpoint );

            //Get current page URL
            $current_page_url = untrailingslashit( home_url( $_SERVER["REQUEST_URI"]) );

            if( $current_page_url == $ur_lostpasswd_url ) {
                $id_class       = '.user-registration-ResetPassword';
                $submit_class   = 'user-registration-Button';
                $nonce_name     = '_wpnonce';
                $html = '';
                $TomSVaptchaFrontend = new TomSVaptchaFrontend();
                $TomSVaptcha            = new TomSVaptcha();
                $allowed_html           = $TomSVaptcha->TomSVAPTCHA_allow_html();
                $allowed_protocols      = $TomSVaptcha->TomSVAPTCHA_allow_protocols();
                if( esc_textarea( get_option('toms_vaptcha_mode', 'click') ) == 'click'){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Click_Mode_HTML($id_class);
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Verify_Frontend_Only_style($id_class, $submit_class, $nonce_name);
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Verify_Frontend_Only_JS($id_class, $submit_class, $nonce_name), [ 'type' => 'text/javascript' ] );
                }
                if( esc_textarea( get_option('toms_vaptcha_mode') ) == 'invisible' ){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Verify_Frontend_Only_style($id_class, $submit_class, $nonce_name);
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Verify_Frontend_Only_JS($id_class, $submit_class, $nonce_name), [ 'type' => 'text/javascript' ] );
                }
                if( esc_textarea( get_option('toms_vaptcha_mode') ) == 'embedded' ){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_HTML($id_class);
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Verify_Frontend_Only_style($id_class, $submit_class, $nonce_name);
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Verify_Frontend_Only_JS($id_class, $submit_class, $nonce_name), [ 'type' => 'text/javascript' ] );
                }
            }
        }

        /**
         * User Registration Lostpassword Form VAPTCHA verification
         * 
         * @param $_POST['vaptcha_server']
         * @param $_POST['vaptcha_token']
         * 
         * @param $user  Wordpress login form user data. if the Vaptcha passed, will allow user login, else return ERROR.
         * 
         *  Warnning: Work User Registration->settings->General Options->Lost password page only.
        */
        function toms_vaptcha_ur_lostpassword_form_verification($true){
            //Get User Registration Lostpassword URL
            $ur_account_url            = untrailingslashit( get_permalink( get_option('user_registration_myaccount_page_id') ) );
            $wp_link_type               = empty( get_option( 'permalink_structure' ) ) ? '&' : '/';
            $ur_lostpasswd_endpoint    = esc_textarea( get_option( 'user_registration_myaccount_lost_password_endpoint', 'lost-password' ) );
            $ur_lostpasswd_url         = untrailingslashit( $ur_account_url . $wp_link_type . $ur_lostpasswd_endpoint );

            //Get current page URL
            $current_page_url = untrailingslashit( home_url( $_SERVER["REQUEST_URI"]) );

            if( $current_page_url == $ur_lostpasswd_url ) {  
                if ( isset($_POST['vaptcha_token']) && isset($_POST['vaptcha_server']) ) {
                    $vaptcha_token  = isset($_POST['vaptcha_token']) ? sanitize_text_field( $_POST['vaptcha_token'] ) : '';
                    $vaptcha_server = isset($_POST['vaptcha_server']) ? sanitize_text_field( $_POST['vaptcha_server'] ) : '';
                    $TomSVaptcha    = new TomSVaptcha();
                    if ( $TomSVaptcha->TomSVaptcha_verification( $vaptcha_server, $vaptcha_token ) == true ){
                        return $true;
                    }else{
                        return new WP_Error("Captcha Invalid", __("<strong>ERROR</strong>: Captcha verification failed, please try again.", 'toms-vaptcha'));
                    }
                }else{
                    return new WP_Error("Captcha Invalid", __("<strong>ERROR</strong>: Captcha verification failed, Please click to verify.", 'toms-vaptcha'));
                }
            }
            return $true;
        }
    }
    $TomSVaptchaUR = new TomSVaptchaUR();
}