<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( class_exists('TomSVaptchaHome') && !class_exists('TomSVaptcha') ){

    class TomSVaptcha extends TomSVaptchaHome {
    
        public function __construct(){
           
           add_action( 'init', array($this, 'TomSVaptchaInit'), 10, 2);
           add_action( 'admin_menu', array($this, 'add_TomSVaptcha_menu_to_TomS'), 10, 2);

           if( !empty( esc_textarea( get_option('toms_vaptcha_vid') ) ) ){
                if( esc_textarea( get_option('toms_vaptcha_login_form') ) == "0"){
                        add_action( 'login_form', array($this, 'add_TomSVaptcha_to_login_form'), 10, 2);
                        add_filter( 'wp_authenticate_user',  array($this, 'TomSVaptcha_login_form_verification'), 10, 3);
                }
                if( esc_textarea( get_option('toms_vaptcha_register_form', "0") ) == "0" ){
                        add_action( 'register_form', array($this, 'add_TomSVaptcha_to_register_form'), 10, 2);
                        add_filter( 'registration_errors',  array($this, 'TomSVaptcha_register_form_verification'), 10, 3);
                }
                if( esc_textarea( get_option('toms_vaptcha_lostpassword_form', "0") )  == "0" ){
                        add_action( 'lostpassword_form', array($this, 'add_TomSVaptcha_to_lostpassword_form'), 10, 2);
                        add_filter( 'allow_password_reset', array($this, 'TomSVaptcha_lostpassword_form_verification'), 10, 3);
                }
                if( esc_textarea( get_option('toms_vaptcha_comment_form', "0") ) == "0" ){
                        add_action( 'comment_form', array($this, 'add_TomSVaptcha_to_comment_form'), 10, 2);
                        add_filter( 'preprocess_comment', array($this, 'TomSVaptcha_comment_form_verification'), 10, 3);
                }
            }
           
           //add settings button to Installed plugin page
           add_filter('plugin_action_links', array($this, 'plugin_page_setting_button'), 10, 3);
        }

        public function add_TomSVaptcha_menu_to_TomS(){
            add_submenu_page( "toms-wp", __('TomS Vaptcha Settings', 'toms-vaptcha'), '<span class="toms-menu-item"><span class="toms-vaptcha"></span><span class="toms-menu-text">'. __('TomS Vaptcha', 'toms-vaptcha').'</span></span>', 'manage_options', 'toms-vaptcha-settings', array($this, 'TomSVaptchaSettings'), );
            add_action( "admin_enqueue_scripts", array($this, 'TomSVaptcha_global_load_style') );
            add_action( "toms-wp_page_toms-vaptcha-settings", array($this, 'TomSVaptcha_load_style') );
        }
    
        //TomS Vaptcha backend Global style
        public function TomSVaptcha_global_load_style() {
            wp_enqueue_style( 'TomSVaptchaGlobalStyle', plugin_dir_url( __FILE__ ) . 'assets/css/toms-vaptcha-admin.css' );
        }
        //TomS Vaptcha backend setting page style
        public function TomSVaptcha_load_style() {
            wp_enqueue_style( 'TomSVaptchaStyle', plugin_dir_url( __FILE__ ) . 'assets/css/toms-vaptcha.css' );
        }
        //TomS Vaptcha Languages
        public function TomSVaptchaInit(){
            load_plugin_textdomain( 'toms-vaptcha', false, rtrim(dirname(plugin_basename( __FILE__ )), '/inc') . '/languages' );
        }

        /**
         * Submit data to database.
        */
        private function TomSVaptchaHandleForm(){
            //check nonce
            if( wp_verify_nonce( $_POST['toms_vaptcha_nonce'], 'save_toms_vaptcha_nonce' ) AND current_user_can( 'manage_options' ) ) {
                //update_option() insert data to database.
                update_option('toms_vaptcha_vid', sanitize_text_field( $_POST['vid'] ) );
                update_option('toms_vaptcha_key', sanitize_text_field( $_POST['key'] ) );

                update_option('toms_vaptcha_mode', sanitize_text_field( $_POST['toms_vaptcha_mode'] ) );

                update_option('toms_vaptcha_login_form', isset($_POST['toms_vaptcha_login_form']) ? sanitize_text_field( $_POST['toms_vaptcha_login_form'] ) : '' );
                update_option('toms_vaptcha_register_form', isset($_POST['toms_vaptcha_register_form']) ? sanitize_text_field( $_POST['toms_vaptcha_register_form'] ) : '' );
                update_option('toms_vaptcha_lostpassword_form', isset($_POST['toms_vaptcha_lostpassword_form']) ? sanitize_text_field( $_POST['toms_vaptcha_lostpassword_form'] ) : '' );
                update_option('toms_vaptcha_comment_form', isset($_POST['toms_vaptcha_comment_form']) ? sanitize_text_field( $_POST['toms_vaptcha_comment_form'] ) : '' );

                update_option('toms_vaptcha_https', sanitize_text_field( $_POST['toms_vaptcha_https'] ) );

                update_option('toms_vaptcha_guide', sanitize_text_field( $_POST['toms_vaptcha_guide'] ) );

                update_option('toms_vaptcha_online_url', sanitize_text_field( $_POST['toms_vaptcha_online_url'] ) );

                update_option('toms_vaptcha_button_style', sanitize_text_field( $_POST['toms_vaptcha_button_style'] ) );

                update_option('toms_vaptcha_button_color', sanitize_text_field( $_POST['toms_vaptcha_button_color'] ) );

                update_option('toms_vaptcha_area', sanitize_text_field( $_POST['toms_vaptcha_area'] ) );

                update_option('toms_vaptcha_language', sanitize_text_field( $_POST['toms_vaptcha_language'] ) );

                // Create an action for extra form data
                $extra_forms = do_action( 'TomSVaptchaExtraFormsData');
                
                update_option('toms_vaptcha_clear_data', isset($_POST['toms_vaptcha_clear_data']) ? sanitize_text_field( $_POST['toms_vaptcha_clear_data'] ) : '' );
                   
            ?>
                <div class="updated notice notice-success settings-error is-dismissible">
                    <p><strong><?php _e('Settings saved.', 'toms-vaptcha'); ?></strong></p>
                </div>
            <?php } else { ?>
                <div class="error notice notice-success settings-error is-dismissible">
                    <p><strong><?php _e('ERROR : Settings save failed.', 'toms-vaptcha'); ?></strong></p>
                    <p class="description"><?php _e('Sorry, you don\'t have permission to perform this action.', 'toms-vaptcha'); ?> </p>
                </div>
            <?php }
        }
        
        //TomS Vaptcha setting page contents
        public function TomSVaptchaSettings() { ?>
            <div class="wrap">
                <h1>
                    <span class="toms-vaptcha-heading">
                        <span class="toms-vaptcha-icon"></span>
                        <span class="toms-vaptcha-heading-text"><?php _e('TomS Vaptcha Settings', 'toms-vaptcha'); ?></span>
                    </span>
                </h1>
                <?php if( !wp_is_mobile() ) : ?>
                <p class="description"><a href="https://en.vaptcha.com/" target="_blank" ><strong><?php _e('VAPTCHA', 'toms-vaptcha'); ?></strong></a> <?php _e('is a new type of human-machine verification solution based on artificial intelligence and big data. Through comprehensive analysis of user behavior characteristics, biological characteristics, network environment, etc., VAPTCHA\'s efficient and evolving intelligent risk control engine can accurately identify and intercept attack requests including manual coding. Compared with traditional verification codes, VAPTCHA has significant advantages in terms of security and user experience.', 'toms-vaptcha'); ?></p>
                <?php endif; ?>
    
                <?php if( isset($_POST['justsubmitted']) && $_POST['justsubmitted'] == "true") $this->TomSVaptchaHandleForm(); ?>
                <form method="post" class="toms-vaptcha-form">
                    <input type="hidden" name="justsubmitted" value="true" />
                    <?php if ( function_exists('wp_nonce_field') ){ wp_nonce_field('save_toms_vaptcha_nonce', 'toms_vaptcha_nonce'); }  //create a nonce to confirm the user submit from current page. ?>
    
                    <!--Vid and Key-->
                    <div class="toms-vaptcha-vid-key">
                        <p class="toms-vaptcha-vid-key-title"><?php _e('VID and Secret KEY', 'toms-vaptcha'); ?></p>
                        <div class="description"><?php _e('To get the Vaptcha <strong>VID</strong> and <strong>Key</strong>, click ', 'toms-vaptcha'); ?> <a href="https://user-en.vaptcha.com/manage" target="_blank"> <?php _e('here', 'toms-vaptcha'); ?></a>.</div>
                        
                        <!--Vid-->
                        <label for="toms_vaptcha_settings"><strong><?php _e('VID ', 'toms-vaptcha'); ?></strong>:</label>
                        <input type="text" name="vid" id="vid" value="<?php echo esc_textarea( get_option('toms_vaptcha_vid') ); // Get vid from database ?>" />
                        <!--Key-->
                        <label for="toms_vaptcha_settings"><strong><?php _e('KEY ', 'toms-vaptcha'); ?></strong>:</label>
                        <input type="password" name="key" id="key" value="<?php echo esc_textarea( get_option('toms_vaptcha_key') ); //Get key from database ?>" onfocus="this.type='text'" onblur="this.type='password'" />
                    </div>
                    
                    <!--Vaptcha Mode-->
                    <div class="toms-vaptcha-mode">
                        <p class="toms-vaptcha-mode-title"><?php _e('Display Mode', 'toms-vaptcha'); ?></p>
                        <div class="toms-vaptcha-mode-items">
                            <?php $toms_vaptcha_mode = !empty( get_option('toms_vaptcha_mode') ) ? esc_textarea( get_option('toms_vaptcha_mode', "click") ) : "click" ;?>
                            <label class="toms-label">
                                <input type="radio" name="toms_vaptcha_mode" value="click" <?php if( $toms_vaptcha_mode == 'click' || empty($toms_vaptcha_mode) ) echo 'checked="checked"'; ?> />
                                <span class="click-text"><?php _e('Click', 'toms-vaptcha'); ?></span>
                            </label>
                            <label class="toms-label">
                                <input type="radio" name="toms_vaptcha_mode" value="invisible" <?php if( $toms_vaptcha_mode == 'invisible') echo 'checked="checked"'; ?> />
                                <span class="invisible-text"><?php _e('Invisible', 'toms-vaptcha'); ?></span>
                            </label>
                            <label class="toms-label">
                                <input type="radio" name="toms_vaptcha_mode" id="toms_vaptcha_mode_embedded" value="embedded" <?php if( $toms_vaptcha_mode == 'embedded') echo 'checked="checked"'; ?> />
                                <span class="embedded-text"><?php _e('Embedded', 'toms-vaptcha'); ?> ( <?php _e('Enterprise User Only.', 'toms-vaptcha'); ?> )</span>
                            </label>
                            <div class="toms-vaptcha-mode__embedded-notice" ></div>
                        </div>
                    </div>

                    <!--Support Verification Form Lists-->
                    <div class="toms-vaptcha-support-forms">
                        <p class="toms-vaptcha-support-forms-title"><?php _e('Support Forms', 'toms-vaptcha'); ?></p>
                        <!--Wordpress Default Forms-->
                        <div class="toms-vaptcha-wordpress-default-forms"><?php _e('WordPress Default', 'toms-vaptcha'); ?> : </div>
                        <div class="toms-vaptcha-form-list">
                            <div class="toms-vaptcha-forms-contents">
                                <label class="toms-label">
                                    <input type="checkbox" name="toms_vaptcha_login_form" value="0" <?php if( esc_textarea( get_option('toms_vaptcha_login_form') ) == "0" )  echo 'checked="checked"'; ?> />
                                    <span class="login-text"><?php _e('Login Form', 'toms-vaptcha'); ?></span>
                                </label>
                                <label class="toms-label">
                                    <input type="checkbox" name="toms_vaptcha_register_form" value="0"  <?php if( esc_textarea( get_option('toms_vaptcha_register_form', '0') ) == "0" )  echo 'checked="checked"'; ?> />
                                    <span class="register-text"><?php _e('Register Form', 'toms-vaptcha'); ?></span>
                                </label>
                                <label class="toms-label">
                                    <input type="checkbox" name="toms_vaptcha_lostpassword_form" value="0"  <?php if( esc_textarea( get_option('toms_vaptcha_lostpassword_form', '0') ) == "0" )  echo 'checked="checked"'; ?> />
                                    <span class="lostpassword-text"><?php _e('Lost Password Form', 'toms-vaptcha'); ?></span>
                                </label>
                                <label class="toms-label">
                                    <input type="checkbox" name="toms_vaptcha_comment_form" value="0"  <?php if( esc_textarea( get_option('toms_vaptcha_comment_form', '0') ) == "0" )  echo 'checked="checked"'; ?> />
                                    <span class="comment-text"><?php _e('Comment Form', 'toms-vaptcha'); ?></span>
                                </label>
                            </div>
                        </div>
                        <?php
                            //Create a action for extra forms
                            $extra_forms = do_action( 'TomSVaptchaExtraForms');
                            ?>
                    </div>

                    <!--Vaptcha Optional Settings-->
                    <div class="toms-vaptcha-options">
                        <p class="toms-vaptcha-options-title"><?php _e('Optional Settings', 'toms-vaptcha'); ?></p>
                        <div class="toms-vaptcha-options-items">
                            <div class="toms-https">
                                <label class="toms-https-text"><?php _e('Https', 'toms-vaptcha'); ?> : </label>
                                <div class="toms-label">
                                    <?php $toms_vaptcha_https = !empty( esc_textarea( get_option('toms_vaptcha_https') ) ) ? esc_textarea( get_option('toms_vaptcha_https') ) : "0" ;?>
                                    <label class="toms-true-label">
                                        <input type="radio" name="toms_vaptcha_https" value="0" <?php if( $toms_vaptcha_https == "0" || empty($toms_vaptcha_https) ) echo 'checked="checked"'; ?> />
                                        <span class="true-text"><?php _e('Enabled', 'toms-vaptcha'); ?></span>
                                    </label>
                                    <label class="toms-false-label">
                                        <input type="radio" name="toms_vaptcha_https" value="1" <?php if( $toms_vaptcha_https == "1" ) echo 'checked="checked"'; ?> />
                                        <span class="false-text"><?php _e('Disabled', 'toms-vaptcha'); ?></span>
                                    </label>
                                </div>
                            </div>
                            
                            <div  class="toms-guide">
                                <span class="toms-guide-title"><?php _e('Operation Guide', 'toms-vaptcha'); ?></span><span>( <?php _e('<strong>Embedded</strong> mode only', 'toms-vaptcha'); ?> )</span> : 
                                <?php $toms_vaptcha_guide = !empty( esc_textarea( get_option('toms_vaptcha_guide') ) ) ? esc_textarea( get_option('toms_vaptcha_guide', "0") ) : "0" ;?>
                                <div class="toms-guide-label">
                                    <label class="toms-label">
                                        <input type="radio" name="toms_vaptcha_guide" value="0" <?php if( $toms_vaptcha_guide == "0" || empty($toms_vaptcha_guide) ) echo 'checked="checked"'; ?> />
                                        <span class="true-text"><?php _e('Enabled', 'toms-vaptcha'); ?></span>
                                    </label>
                                    <label class="toms-label">
                                        <input type="radio" name="toms_vaptcha_guide" value="1" <?php if( $toms_vaptcha_guide == "1" ) echo 'checked="checked"'; ?> />
                                        <span class="false-text"><?php _e('Disabled', 'toms-vaptcha'); ?></span>
                                    </label>
                                </div>
                            </div>

                            <div class="toms-button-style">
                                <span class="toms-button-style-text"><?php _e('Style', 'toms-vaptcha'); ?></span><span>( <?php _e('<strong>Click</strong> mode only', 'toms-vaptcha'); ?> )</span> : 
                                <div class="toms-button-style-label">
                                    <?php $toms_vaptcha_button_style = !empty( esc_textarea( get_option('toms_vaptcha_button_style') ) ) ? esc_textarea( get_option('toms_vaptcha_button_style') ) : "0" ;?>
                                    <label class="toms-label">
                                        <input type="radio" name="toms_vaptcha_button_style" value="0" <?php if( $toms_vaptcha_button_style == "0" || empty($toms_vaptcha_button_style) ) echo 'checked="checked"'; ?> />
                                        <span class="dark-text"><?php _e('Dark', 'toms-vaptcha'); ?></span>
                                    </label>
                                    <label class="toms-label">
                                        <input type="radio" name="toms_vaptcha_button_style" value="1" <?php if( $toms_vaptcha_button_style == "1" ) echo 'checked="checked"'; ?> />
                                        <span class="light-text"><?php _e('Light', 'toms-vaptcha'); ?></span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="toms-color">
                                <label class="toms-label"><?php _e('Button color', 'toms-vaptcha'); ?><span class="toms-color-span">( <?php _e('<strong>Click</strong> Mode only', 'toms-vaptcha'); ?> )</span> : </label>
                                <input type="text" id="toms-vaptcha-color" name="toms_vaptcha_button_color" value="<?php echo esc_textarea( get_option('toms_vaptcha_button_color', '#57ABFF') ); ?>">
                            </div>

                            <div class="toms-custom-resource-url">
                                <label class="toms-label"><?php _e('Custom Resource Url', 'toms-vaptcha'); ?> : </label>
                                <input type="url" id="toms-vaptcha-online-url" name="toms_vaptcha_online_url" value="<?php echo esc_textarea( get_option('toms_vaptcha_online_url') ); ?>" />
                            </div>

                            <div class="toms-area">
                                <label class="toms-label"><?php _e('Area', 'toms-vaptcha'); ?> : </label>
                                <select name="toms_vaptcha_area">
                                    <option value="0" <?php selected( esc_textarea( get_option('toms_vaptcha_area') ), "0" ); ?> ><?php _e('Auto', 'toms-vaptcha'); ?></option>
                                    <option value="1" <?php selected( esc_textarea( get_option('toms_vaptcha_area') ), "1" ); ?> ><?php _e('Southeast Asia', 'toms-vaptcha'); ?></option>
                                    <option value="2" <?php selected( esc_textarea( get_option('toms_vaptcha_area') ), "2" ); ?> ><?php _e('North America', 'toms-vaptcha'); ?></option>
                                    <option value="3" <?php selected( esc_textarea( get_option('toms_vaptcha_area') ), "3" ); ?> ><?php _e('China', 'toms-vaptcha'); ?></option>
                                </select>
                            </div>

                            <div class="toms-languages">
                                <label class="toms-label"><?php _e('Language', 'toms-vaptcha'); ?> : </label>
                                <select name="toms_vaptcha_language">
                                    <option value="0" <?php selected( esc_textarea( get_option('toms_vaptcha_language') ), "0" ); ?> ><?php _e('Auto Detect', 'toms-vaptcha'); ?></option>
                                    <option value="1" <?php selected( esc_textarea( get_option('toms_vaptcha_language') ), "1" ); ?> ><?php _e('English', 'toms-vaptcha'); ?></option>
                                    <option value="2" <?php selected( esc_textarea( get_option('toms_vaptcha_language') ), "2" ); ?> ><?php _e('Chinese Simplified', 'toms-vaptcha'); ?></option>
                                    <option value="3" <?php selected( esc_textarea( get_option('toms_vaptcha_language') ), "3" ); ?> ><?php _e('Chinese Traditional', 'toms-vaptcha'); ?></option>
                                    <option value="4" <?php selected( esc_textarea( get_option('toms_vaptcha_language') ), "4" ); ?> ><?php _e('Japanese', 'toms-vaptcha'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!--Clear all data option-->
                    <div class="toms-vaptcha-clear-data">
                        <div class="toms-vaptcha-clear-data-contents">
                            <div class="toms-label">
                                <input type="checkbox" name="toms_vaptcha_clear_data" value="0"  <?php if( esc_textarea( get_option('toms_vaptcha_clear_data') ) == "0" )  echo 'checked="checked"'; ?> />
                                <span class="delete-text"><?php _e('Delete all data of this plugin', 'toms-vaptcha'); ?></span>
                                <div class="delete-warning-text"><span class="delete-warning-title"><?php _e('Warning: ', 'toms-vaptcha'); ?></span> <?php _e('If you are only upgrading this plugin, please do not check this option, otherwise the data will be lost when the plugin is deleted.', 'toms-vaptcha'); ?></div>
                            </div>
                        </div>
                    </div>

                    <!--Submit Button-->
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'toms-vaptcha'); ?>" />
                </form>
            </div>
        <?php }

        /**
         *  TomS VAPTCHA Allowed HTML
         * @return array
        */
        function TomSVAPTCHA_allow_html(){
            return $allowed_html =[
                'style' => [
                    'id'        => [],
                    'class'     => [],
                    'name'      => [],
                    '@media'    => [],
                    'max-width' => []
                ],
                'div' => [
                    'class' => [],
                    'id'    => [],
                    'name'  => []
                ],
                'span' => [
                    'class' => [],
                    'id'    => [],
                    'name'  => []
                ],
                'img'   => [
                    'title' => [],
                    'src' => [],
                    'alt' => []
                ],
                'input' => [
                    'id'    => [],
                    'class' => [],
                    'type'  => [],
                    'name'  => [],
                    'value' => [],
                    'data-key' => []
                ]
            ];
        }
        /**
         *  TomS VAPTCHA Allowed protocols
         * @return array
        */
        function TomSVAPTCHA_allow_protocols(){
            return $protocols = array( 'data', 'http', 'https' );
        }

        /**
         * Login Form
         * 
         * @param loginform  Wordpress login form id.
         * 
        */
        public function add_TomSVaptcha_to_login_form(){
            if( $GLOBALS['pagenow'] === 'wp-login.php' ) {
                $id_class = 'loginform';
                $html = '';
                $TomSVaptchaFrontend = new TomSVaptchaFrontend();
                $allowed_html       = $this->TomSVAPTCHA_allow_html();
                $allowed_protocols  = $this->TomSVAPTCHA_allow_protocols();
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
                    $html .= '<style>
                                #loginform-toms-vaptcha{
                                    width: 100% !important;
                                    height: auto !important;
                                }
                    </style>
                    ';
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_HTML($id_class);
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }

            }
        }

         /**
         * Login Form VAPTCHA verification
         * 
         *  When user passed the Vaptcha and submit the form will get 2 params .
         * @param $_POST['vaptcha_server']
         * @param $_POST['vaptcha_token']
         * 
         * @param $user  Wordpress login form user data. if the Vaptcha passed, will allow user login, else return ERROR.
         * 
        */
        public function TomSVaptcha_login_form_verification($user){
            if( $GLOBALS['pagenow'] === 'wp-login.php' ) {
                if ( isset($_POST['vaptcha_token']) && isset($_POST['vaptcha_server']) ) {
                    $vaptcha_token  = isset($_POST['vaptcha_token']) ? sanitize_text_field( $_POST['vaptcha_token'] ) : '';
                    $vaptcha_server = isset($_POST['vaptcha_server']) ? sanitize_text_field( $_POST['vaptcha_server'] ) : '';
                    
                    if ( $this->TomSVaptcha_verification( $vaptcha_server, $vaptcha_token ) == true ){
                        return $user;
                    }else{
                        return new WP_Error("Captcha Invalid", __("<strong>ERROR</strong>: Captcha verification failed, please try again.", 'toms-vaptcha'));
                    }
                }else{
                    return new WP_Error("Captcha Invalid", __("<strong>ERROR</strong>: Captcha verification failed, Please click to verify.", 'toms-vaptcha'));
                }
            }else{
                return $user;
            }
            
        }

        /**
         * Register Form
         * 
         * @param registerform  Wordpress register form id.
         * 
        */
        public function add_TomSVaptcha_to_register_form(){
            if( $GLOBALS['pagenow'] === 'wp-login.php' && !empty($_REQUEST['action']) && $_REQUEST['action'] === 'register' ) {
                $id_class = 'registerform';
                $html = '';
                $TomSVaptchaFrontend = new TomSVaptchaFrontend();
                $allowed_html       = $this->TomSVAPTCHA_allow_html();
                $allowed_protocols  = $this->TomSVAPTCHA_allow_protocols();
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
                    $html .= '<style>
                                #registerform-toms-vaptcha{
                                    width: 100% !important;
                                    height: auto !important;
                                }
                    </style>
                    ';
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_HTML($id_class);
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
            }
        }
        
         /**
         * Register Form VAPTCHA verification
         * 
         *  When user passed the Vaptcha and submit the form will get 2 params .
         * @param $_POST['vaptcha_server']
         * @param $_POST['vaptcha_token']
         * 
         * @param $errors  Wordpress login form user data. if the Vaptcha passed, will allow user register, else return ERROR.
         *                 Always need to return $errors even the Vaptcha verify passed or not.
         * 
        */
        public function TomSVaptcha_register_form_verification($errors, $sanitized_user_login, $user_email){
            if( $GLOBALS['pagenow'] === 'wp-login.php' && !empty($_REQUEST['action']) && $_REQUEST['action'] === 'register' ) {
                if ( isset($_POST['vaptcha_token']) && isset($_POST['vaptcha_server']) ) {
                    $vaptcha_token  = isset($_POST['vaptcha_token']) ? sanitize_text_field( $_POST['vaptcha_token'] ) : '';
                    $vaptcha_server = isset($_POST['vaptcha_server']) ? sanitize_text_field( $_POST['vaptcha_server'] ) : '';
                    
                    if ( $this->TomSVaptcha_verification( $vaptcha_server, $vaptcha_token ) == true ){
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
         * Lost Password Form
         * 
         * @param lostpasswordform  Wordpress Lost Password form id.
         * 
        */
        public function add_TomSVaptcha_to_lostpassword_form(){
            if( $GLOBALS['pagenow'] === 'wp-login.php' && !empty($_REQUEST['action']) && $_REQUEST['action'] === 'lostpassword' ) {
                $id_class = 'lostpasswordform';
                $html = '';
                $TomSVaptchaFrontend = new TomSVaptchaFrontend();
                $allowed_html       = $this->TomSVAPTCHA_allow_html();
                $allowed_protocols  = $this->TomSVAPTCHA_allow_protocols();
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
                    $html .= '<style>
                                #lostpasswordform-toms-vaptcha{
                                    width: 100% !important;
                                    height: auto !important;
                                }
                    </style>
                    ';
                    $html .= $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_HTML($id_class);
                    echo wp_kses( $html, $allowed_html, $allowed_protocols );
                    wp_print_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Frontend_JS() );
                    wp_print_inline_script_tag( $TomSVaptchaFrontend->TomSVaptcha_Embedded_Mode_JS($id_class), [ 'type' => 'text/javascript' ] );
                }
            }
        }

        /**
         * Lost Password Form VAPTCHA verification
         * 
         *  When user passed the Vaptcha and submit the form will get 2 params .
         * @param $_POST['vaptcha_server']
         * @param $_POST['vaptcha_token']
         * 
         * @param $true  Default is true. if the Vaptcha passed, return $true, else return ERROR.
         * 
        */
        public function TomSVaptcha_lostpassword_form_verification($true){
            if( $GLOBALS['pagenow'] === 'wp-login.php' && !empty($_REQUEST['action']) && $_REQUEST['action'] === 'lostpassword' ) {
                if ( isset($_POST['vaptcha_token']) && isset($_POST['vaptcha_server']) ) {
                    $vaptcha_token  = isset($_POST['vaptcha_token']) ? sanitize_text_field( $_POST['vaptcha_token'] ) : '';
                    $vaptcha_server = isset($_POST['vaptcha_server']) ? sanitize_text_field( $_POST['vaptcha_server'] ) : '';
                    
                    if ( $this->TomSVaptcha_verification( $vaptcha_server, $vaptcha_token ) == true ){
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
         * Comment Form
         * 
         * @param comment-form  Wordpress comment form classname.
         * 
        */
        public function add_TomSVaptcha_to_comment_form(){
            $id_class = '.comment-form';
            $html = '';
            $TomSVaptchaFrontend = new TomSVaptchaFrontend();
            $allowed_html       = $this->TomSVAPTCHA_allow_html();
            $allowed_protocols  = $this->TomSVAPTCHA_allow_protocols();
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
            
            if( esc_textarea( get_option('toms_vaptcha_mode', 'click') ) == 'click' ||
                esc_textarea( get_option('toms_vaptcha_mode') ) == 'embedded' ){
                echo <<<HTML
                    <script>
                        //add Vaptcha before the comment submit button.
                        var commentFormBtn  = document.getElementsByClassName('comment-form-toms-vaptcha')[0];
                        var commentForm     = document.getElementsByClassName('comment-form')[0];
                        commentForm.insertBefore(commentFormBtn, document.getElementsByClassName('form-submit')[0]);
                    </script>
                HTML;
            }
        }

         /**
         * Comment Form VAPTCHA verification
         * 
         *  When user passed the Vaptcha and submit the form will get 2 params .
         * @param $_POST['vaptcha_server']
         * @param $_POST['vaptcha_token']
         * 
         * @param $user  Wordpress login form user data. if the Vaptcha passed, will allow user login, else return ERROR.
         * 
        */
        public function TomSVaptcha_comment_form_verification($commentdata){

                if ( isset($_POST['vaptcha_token']) && isset($_POST['vaptcha_server']) ) {
                    $vaptcha_token  = isset($_POST['vaptcha_token']) ? sanitize_text_field( $_POST['vaptcha_token'] ) : '';
                    $vaptcha_server = isset($_POST['vaptcha_server']) ? sanitize_text_field( $_POST['vaptcha_server'] ) : '';
                    
                    if ( $this->TomSVaptcha_verification( $vaptcha_server, $vaptcha_token ) == true ){
                        return $commentdata;
                    }else{
                        wp_die( __("<strong>ERROR</strong>: Challenge failed!!! Too many attempts, Please try again later.", 'toms-vaptcha').'<br/><br/> <a href="javascript:history.back()">« '.__('Back', 'toms-vaptcha').'</a>');
                    }
                }else{
                    wp_die(  __('<strong>ERROR</strong>: Captcha challenge failed!!! Bots are not allowed to submit comments.', 'toms-vaptcha').'<br/><br/> <a href="javascript:history.back()">« '.__('Back', 'toms-vaptcha').'</a>');
                }

                return $commentdata;
        }

        /**
         * VAPTCHA verification
         * 
         * Call this function need 2 args: $_POST['vaptcha_server'] and $_POST['vaptcha_token']
         * 
         * @param $server   Vaptcha server url. => $_POST['vaptcha_server']
         * @param $token    Vaptcha token  => $_POST['vaptcha_token']
         * 
         * @return bool true | false
         * 
         */
        public function TomSVaptcha_verification($server, $token) {

            $vid         = esc_textarea( get_option('toms_vaptcha_vid') );
            $secretkey   = esc_textarea( get_option('toms_vaptcha_key') );
            $scene       = 0;
            $ip          = $_SERVER['REMOTE_ADDR'];

            $vaptcha_token  = !empty($token) ? $token : '';
            $vaptcha_server = !empty($server) ? $server : '';

            $args = 'id=' . $vid . '&scene=' . $scene . '&secretkey=' . $secretkey . '&token=' . $vaptcha_token . '&ip=' . $ip;

            $verify_url = $vaptcha_server;

            $response = wp_remote_post( $verify_url, array(
                'body' => $args
            ));

            $vaptcha_json = $response['body']; 

            $result = json_decode($vaptcha_json, true);

            if ( $result['success'] == 1 ) {
                return true;
            }else{
                return false;
            }
            
        }

        /**
         * Add settings link to plugin actions
         *
         * @param  array  $plugin_actions
         * @param  string $plugin_file
         * @since  1.0
         * @return array
         */
        public function plugin_page_setting_button( $plugin_actions, $plugin_file ){
 
            if ( 'toms-vaptcha/toms-vaptcha-home.php' === $plugin_file ) {
                $plugin_actions[] = sprintf( __( '<a href="%s">Settings</a>', 'toms-vaptcha' ), esc_url( admin_url( 'admin.php?page=toms-vaptcha-settings' ) ) );
            }
            return $plugin_actions;
        }
    }
    
    $TomSVaptcha = new TomSVaptcha();
    
}