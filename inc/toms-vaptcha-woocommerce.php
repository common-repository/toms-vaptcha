<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) ){
    class TomSVaptchaWoocommerce {
        public function __construct(){
            add_action( 'TomSVaptchaExtraForms', array($this, 'TomSVaptchaWoocommerceOptions'), 10, 2 );
            add_action( 'TomSVaptchaExtraFormsData', array($this, 'TomSVaptchaWoocommerceOptionsData'), 10, 2);

            if( !empty( esc_textarea( get_option('toms_vaptcha_vid') ) ) ){
                if( esc_textarea( get_option('toms_vaptcha_woo_login_form' ) ) == "0" ){
                    add_action( 'woocommerce_login_form', array($this, 'add_TomSVaptcha_to_woo_login_form'), 10, 2 );
                    add_filter( "woocommerce_process_login_errors", array($this, 'TomSVaptcha_woo_login_form_verification'), 10, 3 );
                }
                if( esc_textarea( get_option('toms_vaptcha_woo_register_form', "0") ) == "0" ){
                    add_action( 'woocommerce_register_form',  array($this, 'add_TomSVaptcha_to_woo_register_form'), 10, 2 );
                    add_filter( 'woocommerce_process_registration_errors', array($this, 'TomSVaptcha_woo_register_form_verification'), 10, 3 );
                }
                if( esc_textarea( get_option('toms_vaptcha_woo_lostpassword_form', "0") ) == "0" ){
                    add_action('woocommerce_lostpassword_form', array($this, 'add_TomSVaptcha_to_woocommerce_lostpassword_form'), 10, 2 );
                    add_filter( 'allow_password_reset', array($this, 'TomSVaptcha_woo_lostpassword_form_verification'), 10, 3);
                }
                if( esc_textarea( get_option('toms_vaptcha_woo_checkout_page', "0") ) == "0" ){
                    add_action( 'woocommerce_review_order_before_payment', array( $this, 'add_TomSVaptcha_to_woocommerce_checkout' ), 10, 2);
                    add_action( 'woocommerce_after_checkout_validation', array( $this, 'TomSVaptcha_woo_checkout_verification' ), 10, 2 );
                }
            }
        }

        /**
         *  Add Woocommerce Support to TomS Vaptcha settings page 
        */
        function TomSVaptchaWoocommerceOptions(){ ?>
            <!--Woocommerce Forms-->
            <div class="toms-vaptcha-wordpress-default-forms"><?php _e('Woocommerce', 'toms-vaptcha'); ?> : </div>
            <div class="toms-vaptcha-form-list">
                <div class="toms-vaptcha-forms-contents">
                    <label class="toms-label">
                        <input type="checkbox" name="toms_vaptcha_woo_login_form" value="0" <?php if( esc_textarea( get_option('toms_vaptcha_woo_login_form') ) == "0" )  echo 'checked="checked"'; ?> />
                        <span class="login-text"><?php _e('Login Form', 'toms-vaptcha'); ?></span>
                    </label>
                    <label class="toms-label">
                        <input type="checkbox" name="toms_vaptcha_woo_register_form" value="0"  <?php if( esc_textarea( get_option('toms_vaptcha_woo_register_form', "0") ) == "0" )  echo 'checked="checked"'; ?> />
                        <span class="register-text"><?php _e('Register Form', 'toms-vaptcha'); ?></span>
                    </label>
                    <label class="toms-label">
                        <input type="checkbox" name="toms_vaptcha_woo_lostpassword_form" value="0"  <?php if( esc_textarea( get_option('toms_vaptcha_woo_lostpassword_form', "0") ) == "0" )  echo 'checked="checked"'; ?> />
                        <span class="lostpassword-text"><?php _e('Lost Password Form', 'toms-vaptcha'); ?></span>
                    </label>
                    <label class="toms-label">
                        <input type="checkbox" name="toms_vaptcha_woo_checkout_page" value="0"  <?php if( esc_textarea( get_option('toms_vaptcha_woo_checkout_page', "0") ) == "0" )  echo 'checked="checked"'; ?> />
                        <span class="checkout-text"><?php _e('Checkout Billing Form', 'toms-vaptcha'); ?></span>
                    </label>
                </div>
            </div>
        <?php }

        /**
         *  Insert Woocommerce options to database
        */
        function TomSVaptchaWoocommerceOptionsData(){
            update_option('toms_vaptcha_woo_login_form', isset($_POST['toms_vaptcha_woo_login_form']) ? sanitize_text_field( $_POST['toms_vaptcha_woo_login_form'] ) : '' );
            update_option('toms_vaptcha_woo_register_form', isset($_POST['toms_vaptcha_woo_register_form']) ? sanitize_text_field( $_POST['toms_vaptcha_woo_register_form'] ) : '' );
            update_option('toms_vaptcha_woo_lostpassword_form', isset($_POST['toms_vaptcha_woo_lostpassword_form']) ? sanitize_text_field( $_POST['toms_vaptcha_woo_lostpassword_form'] ) : '' );
            update_option('toms_vaptcha_woo_checkout_page', isset($_POST['toms_vaptcha_woo_checkout_page']) ? sanitize_text_field( $_POST['toms_vaptcha_woo_checkout_page'] ) : '' );
        }

        /**
         * Woocommerce Login Form
         * 
         * @param woocommerce-form-login  Woocommerce login form class name.
         * 
         * Warnning: Work woocommerce->settings->Advanced->My account page only.
        */
        function add_TomSVaptcha_to_woo_login_form(){
            $woo_account_url    = get_permalink( get_option('woocommerce_myaccount_page_id') );
            $woo_checkout_url    = get_permalink( get_option('woocommerce_checkout_page_id') );
            $current_page_url   = home_url( $_SERVER["REQUEST_URI"]);
            if( esc_url( untrailingslashit($woo_account_url) ) == esc_url( untrailingslashit($current_page_url) ) ||
                esc_url( untrailingslashit($woo_checkout_url) ) ==  esc_url( untrailingslashit($current_page_url) )
            ){
                $id_class = '.woocommerce-form-login';
                $html = '
                <style>
                    .woocommerce-form-login .woocommerce-form-login-toms-vaptcha{
                        margin: 12px 0 10px 0;
                        box-sizing: border-box;
                    }
                </style>
                ';
                $TomSVaptchaFrontend    = new TomSVaptchaFrontend();
                $TomSVaptcha            = new TomSVaptcha();
                $allowed_html           = $TomSVaptcha->TomSVAPTCHA_allow_html();
                $allowed_protocols      = $TomSVaptcha->TomSVAPTCHA_allow_protocols();
                if( esc_textarea( get_option('toms_vaptcha_mode', 'click') ) == 'click'){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Click_Mode_HTML($id_class);
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Click_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                if( esc_textarea( get_option('toms_vaptcha_mode') ) == 'invisible' ){
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Invisible_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                if( esc_textarea( get_option('toms_vaptcha_mode') ) == 'embedded' ){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_HTML($id_class);
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
            }
        }
        /**
         * Woocommerce Login Form VAPTCHA verification
         * 
         * @param $_POST['vaptcha_server']
         * @param $_POST['vaptcha_token']
         * 
         * @param $user  Wordpress login form user data. if the Vaptcha passed, will allow user login, else return ERROR.
         * 
         * Warnning: Work woocommerce->settings->Advanced->My account page only.
         * 
        */
        function TomSVaptcha_woo_login_form_verification($user){
            $woo_account_url    = get_permalink( get_option('woocommerce_myaccount_page_id') );
            $woo_checkout_url    = get_permalink( get_option('woocommerce_checkout_page_id') );
            $current_page_url   = home_url( $_SERVER["REQUEST_URI"]);

            if( esc_url( untrailingslashit($woo_account_url) ) ==  esc_url( untrailingslashit($current_page_url) ) ||
                esc_url( untrailingslashit($woo_checkout_url) ) ==  esc_url( untrailingslashit($current_page_url) )
             ){
                if ( isset($_POST['vaptcha_token']) && isset($_POST['vaptcha_server']) ) {
                    $vaptcha_token  = isset($_POST['vaptcha_token']) ? sanitize_text_field( $_POST['vaptcha_token'] ) : '';
                    $vaptcha_server = isset($_POST['vaptcha_server']) ? sanitize_text_field( $_POST['vaptcha_server'] ) : '';
                    $TomSVaptcha = new TomSVaptcha();
                    if ( $TomSVaptcha->TomSVaptcha_verification( $vaptcha_server, $vaptcha_token ) == true ){
                        return $user;
                    }else{
                        return new WP_Error("Captcha Invalid", __("<strong>ERROR</strong>: Captcha verification failed, please try again.", 'toms-vaptcha'));
                    }
                }else{
                    return new WP_Error("Captcha Invalid", __("<strong>ERROR</strong>: Captcha verification failed, Please click to verify.", 'toms-vaptcha'));
                }
            }
            return $user;
        }

        /**
         * Woocommerce Register Form
         * 
         * @param woocommerce-form-register  Woocommerce register form class name.
         * 
         *  Warnning: Work woocommerce->settings->Advanced->My account page only.
         * 
        */
        function add_TomSVaptcha_to_woo_register_form(){
            global $post;
	        $post_content = isset($post->post_content) ? $post->post_content : '';

            $woo_account_url    = get_permalink( get_option('woocommerce_myaccount_page_id') );
            $current_page_url   = home_url( $_SERVER["REQUEST_URI"]);
            if( esc_url( untrailingslashit($woo_account_url) ) == esc_url( untrailingslashit($current_page_url) ) ||
                has_shortcode($post_content, 'toms_woo_register_form')
            ){
                $id_class = '.woocommerce-form-register';
                $html = '
                <style>
                    .woocommerce-form-register .woocommerce-form-register-toms-vaptcha{
                        margin: 12px 0 10px 0;
                        box-sizing: border-box;
                    }
                </style>
                ';
                $TomSVaptchaFrontend = new TomSVaptchaFrontend();
                $TomSVaptcha            = new TomSVaptcha();
                $allowed_html           = $TomSVaptcha->TomSVAPTCHA_allow_html();
                $allowed_protocols      = $TomSVaptcha->TomSVAPTCHA_allow_protocols();
                if( esc_textarea( get_option('toms_vaptcha_mode', 'click') ) == 'click'){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Click_Mode_HTML($id_class);
                    $html .= '<input type="hidden" name="toms_vaptcha_shortcode" value="true" />';
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    if( 'yes' != esc_textarea( get_option( "woocommerce_enable_myaccount_registration" ) ) ||
                        esc_textarea( get_option('toms_vaptcha_woo_login_form') ) != "0" ||
                        has_shortcode($post_content, 'toms_woo_register_form')
                    ){
                        wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    }
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Click_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                if( esc_textarea( get_option('toms_vaptcha_mode') ) == 'invisible' ){
                    $html .= '<input type="hidden" name="toms_vaptcha_shortcode" value="true" />';
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    if( 'yes' != esc_textarea( get_option( "woocommerce_enable_myaccount_registration" ) ) ||
                        esc_textarea( get_option('toms_vaptcha_woo_login_form') ) != "0" ||
                        has_shortcode($post_content, 'toms_woo_register_form')
                    ){
                        wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    }
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Invisible_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                if( esc_textarea( get_option('toms_vaptcha_mode') ) == 'embedded' ){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_HTML($id_class);
                    $html .= '<input type="hidden" name="toms_vaptcha_shortcode" value="true" />';
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    if( 'yes' != esc_textarea( get_option( "woocommerce_enable_myaccount_registration" ) ) ||
                        esc_textarea( get_option('toms_vaptcha_woo_login_form') ) != "0" ||
                        has_shortcode($post_content, 'toms_woo_register_form')
                    ){
                        wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    }
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
            }
        }

        /**
         * Woocommerce Register Form VAPTCHA verification
         * 
         *  When user passed the Vaptcha and submit the form will get 2 params .
         * @param $_POST['vaptcha_server']
         * @param $_POST['vaptcha_token']
         * 
         * @param $errors  Wordpress login form user data. if the Vaptcha passed, will allow user register, else return ERROR.
         *                 Always need to return $errors even the Vaptcha verify passed or not.
         * 
         *  Warnning: Work woocommerce->settings->Advanced->My account page only.
         * 
        */
        function TomSVaptcha_woo_register_form_verification($errors, $username, $email ){

            $woo_account_url    = get_permalink( get_option('woocommerce_myaccount_page_id') );
            $current_page_url   = home_url( $_SERVER["REQUEST_URI"]);

            if( esc_url( untrailingslashit($woo_account_url) ) == esc_url( untrailingslashit($current_page_url) ) ||
                isset($_POST['toms_vaptcha_shortcode'])
            ){
                if ( isset($_POST['vaptcha_token']) && isset($_POST['vaptcha_server']) ) {
                    $vaptcha_token  = isset($_POST['vaptcha_token']) ? sanitize_text_field( $_POST['vaptcha_token'] ) : '';
                    $vaptcha_server = isset($_POST['vaptcha_server']) ? sanitize_text_field( $_POST['vaptcha_server'] ) : '';
                    $TomSVaptcha = new TomSVaptcha();
                    if ( $TomSVaptcha->TomSVaptcha_verification( $vaptcha_server, $vaptcha_token ) == true ){
                        return $errors;
                    }else{
                        return new WP_Error("Captcha Invalid", __("<strong>ERROR</strong>: Captcha verification failed, please try again.", 'toms-vaptcha'));
                    }
                }else{
                    return new WP_Error("Captcha Invalid", __("<strong>ERROR</strong>: Captcha verification failed, Please click to verify.", 'toms-vaptcha'));
                }
            }
            return $errors;
        }

        /**
         * Woocommerce Lost Password Form
         * 
         * @param lostpasswordform  Woocommerce Lost Password form class name.
         * 
         * Only verify the data from woocommerce lostpassword page 
        */
        function add_TomSVaptcha_to_woocommerce_lostpassword_form(){
            $id_class = '.woocommerce-ResetPassword';
            $html = '
                <style>
                    .woocommerce-ResetPassword .woocommerce-ResetPassword-toms-vaptcha{
                        margin: 12px 0 10px 0;
                        box-sizing: border-box;
                    }
                </style>
                ';
            $TomSVaptchaFrontend = new TomSVaptchaFrontend();

            //Get Woocommerce Lostpassword URL
            $woo_account_url            = untrailingslashit(get_permalink( get_option('woocommerce_myaccount_page_id') ));
            $wp_link_type               = empty( esc_textarea( get_option( 'permalink_structure' ) ) ) ? '&' : '/';
            $woo_lostpasswd_endpoint    = esc_textarea(get_option( 'woocommerce_myaccount_lost_password_endpoint' ));
            $woo_lostpasswd_url         = untrailingslashit( $woo_account_url . $wp_link_type . $woo_lostpasswd_endpoint );

            //Get current page URL
            $current_page_url = untrailingslashit( home_url( $_SERVER["REQUEST_URI"]) );

            if( esc_url( $current_page_url ) == esc_url( $woo_lostpasswd_url ) ) {
                $TomSVaptcha            = new TomSVaptcha();
                $allowed_html           = $TomSVaptcha->TomSVAPTCHA_allow_html();
                $allowed_protocols      = $TomSVaptcha->TomSVAPTCHA_allow_protocols();
                if( esc_textarea( get_option('toms_vaptcha_mode', 'click') ) == 'click'){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Click_Mode_HTML($id_class);
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Click_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                if( esc_textarea( get_option('toms_vaptcha_mode') ) == 'invisible' ){
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Invisible_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                if( esc_textarea( get_option('toms_vaptcha_mode') ) == 'embedded' ){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_HTML($id_class);
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
            }
        }
        /**
         * Woocommerce Lost Password Form VAPTCHA verification
         * 
         *  When user passed the Vaptcha and submit the form will get 2 params .
         * @param $_POST['vaptcha_server']
         * @param $_POST['vaptcha_token']
         * 
         * @param $true  Wordpress Lost Password Form data. if the Vaptcha passed, will allow user register, else return ERROR.
         *                 Always need to return $errors even the Vaptcha verify passed or not.
         * 
         *  Warnning:  Verify the data from woocommerce lostpassword page Only.
        */
        function TomSVaptcha_woo_lostpassword_form_verification($true){
            //Get Woocommerce Lostpassword URL
            $woo_account_url            = untrailingslashit(get_permalink( get_option('woocommerce_myaccount_page_id') ));
            $wp_link_type               = empty( esc_textarea( get_option( 'permalink_structure' ) ) ) ? '&' : '/';
            $woo_lostpasswd_endpoint    = esc_textarea(get_option( 'woocommerce_myaccount_lost_password_endpoint' ));
            $woo_lostpasswd_url         = untrailingslashit( $woo_account_url . $wp_link_type . $woo_lostpasswd_endpoint );

            //Get current page URL
            $current_page_url = untrailingslashit( home_url( $_SERVER["REQUEST_URI"]) );

            if( esc_url( $current_page_url ) == esc_url( $woo_lostpasswd_url ) ) {  
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

         /**
         * Woocommerce checkout page
         * 
         * @param woocommerce-checkout  Woocommerce checkout form class name.
         * 
         * Warnning: Work woocommerce->settings->Advanced->Checkout page only.
         * 
         * Only no login user
        */
        function add_TomSVaptcha_to_woocommerce_checkout(){
            $id_class = '.woocommerce-checkout';
            $html = '
                <style>
                    .woocommerce-checkout .woocommerce-checkout-toms-vaptcha{
                        margin: 12px 0 10px 0;
                        box-sizing: border-box;
                    }
                    @media (min-width: 921px){
                        .woocommerce-checkout .woocommerce-checkout-toms-vaptcha{
                            width: 100% !important;
                            height: auto !important;
                        }
                    }
                </style>
                ';
            $TomSVaptchaFrontend = new TomSVaptchaFrontend();

            $woo_checkout_url    = get_permalink( get_option('woocommerce_checkout_page_id') );
            $current_page_url   = home_url( $_SERVER["REQUEST_URI"]);

            if( !is_user_logged_in() && esc_url( $current_page_url ) == esc_url( $woo_checkout_url ) ) {
                $TomSVaptcha            = new TomSVaptcha();
                $allowed_html           = $TomSVaptcha->TomSVAPTCHA_allow_html();
                $allowed_protocols      = $TomSVaptcha->TomSVAPTCHA_allow_protocols();
                if( esc_textarea( get_option('toms_vaptcha_mode', 'click') ) == 'click'){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Click_Mode_HTML($id_class);
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    if( get_option( 'woocommerce_enable_checkout_login_reminder' ) !== 'yes' || esc_textarea( get_option('toms_vaptcha_woo_login_form') ) != "0"){
                        wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    }
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Click_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                if( esc_textarea( get_option('toms_vaptcha_mode') ) == 'invisible' ){
                    if( get_option( 'woocommerce_enable_checkout_login_reminder' ) !== 'yes' || esc_textarea( get_option('toms_vaptcha_woo_login_form') ) != "0"){
                        wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    }
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Invisible_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                if( esc_textarea( get_option('toms_vaptcha_mode') ) == 'embedded' ){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_HTML($id_class);
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    if( get_option( 'woocommerce_enable_checkout_login_reminder' ) !== 'yes' || esc_textarea( get_option('toms_vaptcha_woo_login_form') ) != "0"){
                        wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    }
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
            }
        }

         /**
         * Woocommerce checkout page
         * 
         * @param woocommerce-checkout  Woocommerce checkout form class name.
         * 
         * Warnning: Work woocommerce->settings->Advanced->Checkout page only.
         * 
         * Only no login user
        */
        function TomSVaptcha_woo_checkout_verification($data, $errors){
            if( !is_user_logged_in() ){
                if ( isset($_POST['vaptcha_token']) && isset($_POST['vaptcha_server']) ) {
                    $vaptcha_token  = isset($_POST['vaptcha_token']) ? sanitize_text_field( $_POST['vaptcha_token'] ) : '';
                    $vaptcha_server = isset($_POST['vaptcha_server']) ? sanitize_text_field( $_POST['vaptcha_server'] ) : '';
                    $TomSVaptcha = new TomSVaptcha();
                    if ( $TomSVaptcha->TomSVaptcha_verification( $vaptcha_server, $vaptcha_token ) == true ){
                        return $errors;
                    }else{
                        $errors->add("Captcha Invalid", __("<strong>ERROR</strong>: Captcha verification failed, please try again.", 'toms-vaptcha'));
                    }
                }else{
                    $errors->add("Captcha Invalid", __("<strong>ERROR</strong>: Captcha verification failed, Please click to verify.", 'toms-vaptcha'));
                }
            }

            return $errors;
        }
    }
    $TomSVaptchaWoocommerce = new TomSVaptchaWoocommerce();
}