<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(in_array('ultimate-member/ultimate-member.php', apply_filters('active_plugins', get_option('active_plugins')))){
   class TomSVaptcha_UM {
       public function __construct() {
            add_action( 'TomSVaptchaExtraForms', array($this, 'TomSVaptchaUMOptions'), 11, 2 );
            add_action( 'TomSVaptchaExtraFormsData', array($this, 'TomSVaptchaUMOptionsData'), 11, 2);
            
            if( !empty( esc_textarea( get_option('toms_vaptcha_vid') ) ) ){
                if( esc_textarea( get_option( 'toms_vaptcha_um_login_form') ) == "0" ){
                    add_action( 'um_after_form_fields', array($this, 'toms_vaptcha_um_login_form'), 10, 1);
                    add_action( 'um_submit_form_errors_hook_login', array($this, 'toms_vaptcha_um_login_verification'), 10, 1 );
                }
                if( esc_textarea( get_option( 'toms_vaptcha_um_register_form'), "0" ) == "0" ){
                    add_action( 'um_after_form_fields', array($this, 'toms_vaptcha_um_register_form'), 10, 1);
                    add_action( 'um_submit_form_errors_hook__registration', array($this, 'toms_vaptcha_um_register_verification' ), 10, 1);
                }
                if( esc_textarea( get_option( 'toms_vaptcha_um_lostpassword_form'), "0" ) == "0" ){
                    add_action( 'um_after_password_reset_fields',  array($this, 'toms_vaptcha_um_lostpassword_form'), 10, 1);
                    add_action( 'um_reset_password_errors_hook', array($this, 'toms_vaptcha_um_lostpassword_verification' ), 10, 1);
                }
            }
        }
        /**
         *  Add Ultimate Member Support to TomS Vaptcha settings page 
        */
        function TomSVaptchaUMOptions(){ ?>
            <!--Woocommerce Forms-->
            <div class="toms-vaptcha-wordpress-default-forms"><?php _e('Ultimate Member', 'toms-vaptcha'); ?> : </div>
            <div class="toms-vaptcha-form-list">
                <div class="toms-vaptcha-forms-contents">
                    <label class="toms-label">
                        <input type="checkbox" name="toms_vaptcha_um_login_form" value="0" <?php if( esc_textarea( get_option('toms_vaptcha_um_login_form') ) == "0" )  echo 'checked="checked"'; ?> />
                        <span class="login-text"><?php _e('Login Form', 'toms-vaptcha'); ?></span>
                    </label>
                    <label class="toms-label">
                        <input type="checkbox" name="toms_vaptcha_um_register_form" value="0"  <?php if( esc_textarea( get_option('toms_vaptcha_um_register_form', "0") ) == "0" )  echo 'checked="checked"'; ?> />
                        <span class="register-text"><?php _e('Register Form', 'toms-vaptcha'); ?></span>
                    </label>
                    <label class="toms-label">
                        <input type="checkbox" name="toms_vaptcha_um_lostpassword_form" value="0"  <?php if( esc_textarea( get_option('toms_vaptcha_um_lostpassword_form', "0") ) == "0" )  echo 'checked="checked"'; ?> />
                        <span class="lostpassword-text"><?php _e('Lost Password Form', 'toms-vaptcha'); ?></span>
                    </label>
                    <span class="toms-label">
                        <span class="comment-text"></span>
                    </span>
                </div>
            </div>
        <?php }

        /**
         *  Insert Ultimate Member options to database
        */
        function TomSVaptchaUMOptionsData(){
            update_option('toms_vaptcha_um_login_form', isset($_POST['toms_vaptcha_um_login_form']) ? sanitize_text_field( $_POST['toms_vaptcha_um_login_form'] ) : '' );
            update_option('toms_vaptcha_um_register_form', isset($_POST['toms_vaptcha_um_register_form']) ? sanitize_text_field( $_POST['toms_vaptcha_um_register_form'] ) : '' );
            update_option('toms_vaptcha_um_lostpassword_form', isset($_POST['toms_vaptcha_um_lostpassword_form']) ? sanitize_text_field( $_POST['toms_vaptcha_um_lostpassword_form'] ) : '' );
        }

       /**
        * Add TomS Vaptcha to Ultimate Member Login Form
        *
        * @param um-login   Ultimate Member Login Form id or class name.
        * 
        */
       function toms_vaptcha_um_login_form($args){
            $mode = $args['mode'];

            if( $mode == 'login' ){
                $id_class = '.um-login';
                $html = '';
                $TomSVaptchaFrontend    = new TomSVaptchaFrontend();
                $TomSVaptcha            = new TomSVaptcha();
                $allowed_html           = $TomSVaptcha->TomSVAPTCHA_allow_html();
                $allowed_protocols      = $TomSVaptcha->TomSVAPTCHA_allow_protocols();
                if( esc_textarea( get_option('toms_vaptcha_mode', 'click') ) == 'click'){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Click_Mode_HTML($id_class);
                    $html .= '<input type="hidden" name="toms_vaptcha_um_error" id="toms_vaptcha_um_error" value="true" data-key="toms_vaptcha_um_error" />';
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Click_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                if( esc_textarea( get_option('toms_vaptcha_mode') ) == 'invisible' ){
                    $html .= '<input type="hidden" name="toms_vaptcha_um_error" id="toms_vaptcha_um_error" value="true" data-key="toms_vaptcha_um_error" />';
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Invisible_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                if( esc_textarea( get_option('toms_vaptcha_mode') ) == 'embedded' ){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_HTML($id_class);
                    $html .= '<input type="hidden" name="toms_vaptcha_um_error" id="toms_vaptcha_um_error" value="true" data-key="toms_vaptcha_um_error" />';
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                
                //Display error
                if ( $mode == 'login' && UM()->fields()->is_error( 'toms_vaptcha_um_error' ) ) {
                    echo UM()->fields()->field_error( UM()->fields()->show_error( 'toms_vaptcha_um_error' ) );
                }
            }
       }
       /**
        * Ultimate Member Login Form VAPTCHA verification
        * 
        * @param $_POST['vaptcha_server']
        * @param $_POST['vaptcha_token']
        * 
        */
       function toms_vaptcha_um_login_verification($args){
            $mode = $args['mode'];
            if( $mode == 'login'){
                if ( isset($_POST['vaptcha_token']) && isset($_POST['vaptcha_server']) ) {
                    $vaptcha_token  = isset($_POST['vaptcha_token']) ? sanitize_text_field( $_POST['vaptcha_token'] ) : '';
                    $vaptcha_server = isset($_POST['vaptcha_server']) ? sanitize_text_field( $_POST['vaptcha_server'] ) : '';
                    $TomSVaptcha = new TomSVaptcha();
                    if ( $TomSVaptcha->TomSVaptcha_verification( $vaptcha_server, $vaptcha_token ) == true ){
                        // nothing to do
                    }else{
                        //create an error
                        UM()->form()->add_error( 'toms_vaptcha_um_error', __('Captcha verification failed, please try again.', 'toms-vaptcha') );
                    }
                }else{
                    //create an error
                    UM()->form()->add_error( 'toms_vaptcha_um_error', __('Captcha verification failed, Please click to verify.', 'toms-vaptcha') );
                }
            }
        }
        /**
        * Add TomS Vaptcha to Ultimate Member Register Form
        *
        * @param um-register   Ultimate Member Register Form id or class name.
        * 
        */
        function toms_vaptcha_um_register_form($args){
            $mode = $args['mode'];

            if( $mode == 'register' ){
                $id_class = '.um-register';
                $html = '';
                $TomSVaptchaFrontend    = new TomSVaptchaFrontend();
                $TomSVaptcha            = new TomSVaptcha();
                $allowed_html           = $TomSVaptcha->TomSVAPTCHA_allow_html();
                $allowed_protocols      = $TomSVaptcha->TomSVAPTCHA_allow_protocols();
                if( esc_textarea( get_option('toms_vaptcha_mode', 'click') ) == 'click'){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Click_Mode_HTML($id_class);
                    $html .= '<input type="hidden" name="toms_vaptcha_um_error" id="toms_vaptcha_um_error" value="true" data-key="toms_vaptcha_um_error" />';
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Click_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                if( esc_textarea( get_option('toms_vaptcha_mode') ) == 'invisible' ){
                    $html .= '<input type="hidden" name="toms_vaptcha_um_error" id="toms_vaptcha_um_error" value="true" data-key="toms_vaptcha_um_error" />';
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Invisible_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                if( esc_textarea( get_option('toms_vaptcha_mode') ) == 'embedded' ){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_HTML($id_class);
                    $html .= '<input type="hidden" name="toms_vaptcha_um_error" id="toms_vaptcha_um_error" value="true" data-key="toms_vaptcha_um_error" />';
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                
                //Display error
                if ( $mode == 'register' && UM()->fields()->is_error( 'toms_vaptcha_um_error' ) ) {
                    echo UM()->fields()->field_error( UM()->fields()->show_error( 'toms_vaptcha_um_error' ) );
                }
            }
        }
        /**
        * Ultimate Member Register Form VAPTCHA verification
        * 
        * @param $_POST['vaptcha_server']
        * @param $_POST['vaptcha_token']
        * 
        */
       function toms_vaptcha_um_register_verification($args){
            $mode = $args['mode'];
            if( $mode == 'register'){
                if ( isset($_POST['vaptcha_token']) && isset($_POST['vaptcha_server']) ) {
                    $vaptcha_token  = isset($_POST['vaptcha_token']) ? sanitize_text_field( $_POST['vaptcha_token'] ) : '';
                    $vaptcha_server = isset($_POST['vaptcha_server']) ? sanitize_text_field( $_POST['vaptcha_server'] ) : '';
                    $TomSVaptcha = new TomSVaptcha();
                    if ( $TomSVaptcha->TomSVaptcha_verification( $vaptcha_server, $vaptcha_token ) == true ){
                        // nothing to do
                    }else{
                         //create an error
                        UM()->form()->add_error( 'toms_vaptcha_um_error', __('Captcha verification failed, please try again.', 'toms-vaptcha') );
                    }
                }else{
                    //create an error
                    UM()->form()->add_error( 'toms_vaptcha_um_error', __('Captcha verification failed, Please click to verify.', 'toms-vaptcha') );
                }
            }
        }
        /**
        * Add TomS Vaptcha to Ultimate Member Lostpassword Form
        *
        * @param um-password   Ultimate Member Lostpassword Form id or class name.
        * 
        */
        function toms_vaptcha_um_lostpassword_form($args){
            $mode = $args['mode'];

            if( $mode == 'password' ){
                $id_class = '.um-password';
                $html = '
                <style>
                    .um-password .um-password-toms-vaptcha{
                        margin: 15px 0 0px 0;
                    }
                </style>
                ';
                $TomSVaptchaFrontend    = new TomSVaptchaFrontend();
                $TomSVaptcha            = new TomSVaptcha();
                $allowed_html           = $TomSVaptcha->TomSVAPTCHA_allow_html();
                $allowed_protocols      = $TomSVaptcha->TomSVAPTCHA_allow_protocols();
                if( esc_textarea( get_option('toms_vaptcha_mode', 'click') ) == 'click'){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Click_Mode_HTML($id_class);
                    $html .= '<input type="hidden" name="toms_vaptcha_um_error" id="toms_vaptcha_um_error" value="true" data-key="toms_vaptcha_um_error" />';
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Click_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                if( esc_textarea( get_option('toms_vaptcha_mode') ) == 'invisible' ){
                    $html .= '<input type="hidden" name="toms_vaptcha_um_error" id="toms_vaptcha_um_error" value="true" data-key="toms_vaptcha_um_error" />';
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Invisible_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                if( esc_textarea( get_option('toms_vaptcha_mode') ) == 'embedded' ){
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_HTML($id_class);
                    $html .= '<input type="hidden" name="toms_vaptcha_um_error" id="toms_vaptcha_um_error" value="true" data-key="toms_vaptcha_um_error" />';
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
                
                //Display error
                if ( $mode == 'password' && UM()->fields()->is_error( 'toms_vaptcha_um_error' ) ) {
                    echo UM()->fields()->field_error( UM()->fields()->show_error( 'toms_vaptcha_um_error' ) );
                }
            }
        }
        /**
        * Ultimate Member Lostpassword Form VAPTCHA verification
        * 
        * @param $_POST['vaptcha_server']
        * @param $_POST['vaptcha_token']
        * 
        */
        function toms_vaptcha_um_lostpassword_verification($args){
            $mode = $args['mode'];
            if( $mode == 'password'){
                if ( isset($_POST['vaptcha_token']) && isset($_POST['vaptcha_server']) ) {
                    $vaptcha_token  = isset($_POST['vaptcha_token']) ? sanitize_text_field( $_POST['vaptcha_token'] ) : '';
                    $vaptcha_server = isset($_POST['vaptcha_server']) ? sanitize_text_field( $_POST['vaptcha_server'] ) : '';
                    $TomSVaptcha = new TomSVaptcha();
                    if ( $TomSVaptcha->TomSVaptcha_verification( $vaptcha_server, $vaptcha_token ) == true ){
                        // nothing to do
                    }else{
                         //create an error
                        UM()->form()->add_error( 'toms_vaptcha_um_error', __('Captcha verification failed, please try again.', 'toms-vaptcha') );
                    }
                }else{
                    //create an error
                    UM()->form()->add_error( 'toms_vaptcha_um_error', __('Captcha verification failed, Please click to verify.', 'toms-vaptcha') );
                }
            }
        }
    }
    $TomSVaptcha_UM = new TomSVaptcha_UM();
}