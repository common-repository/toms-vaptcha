<?php
/**
 * Plugin Name:       TomS Vaptcha
 * Description:       Gesture captcha —— Easy for human, hard for robots. Protect the login, register, lostpassword and comment forms, support woocommerce, ultimate member, user registration and more popular forms.
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           1.1.2
 * Author:            Tom Sneddon
 * Author URI:        https://TomSneddon.org
 * License:           GPLv3 or later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.htmlnditional-logic
 * Text Domain:       toms-vaptcha
 * Domain Path:		  /languages
 */

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( !class_exists('TomSVaptchaHome') ){

    class TomSVaptchaHome {

        public function __construct() {
            add_action( 'admin_menu', array($this, 'TomSAdminMenu') );
        }
        
        public function TomSAdminMenu() {
            if( 'toplevel_page_toms-wp' != get_plugin_page_hook('toms-wp', '') ){
                $TomSIcon =  plugin_dir_url( __FILE__ ) . 'assets/img/TomSWP.svg';
                //Add menu to admin
                $TomSWP = add_menu_page( __('TomS Plugins For Wordpress', 'toms-vaptcha'), '<span class="toms-menu-item"><span class="toms-wp-header"><span><span class="toms-menu-title-text">&nbsp;&nbsp;&nbsp;' . __('TomS WP', 'toms-vaptcha') . '</span></span>', 'manage_options', 'toms-wp', array($this, 'TomSWordpress'), $TomSIcon, null );
                add_submenu_page( 'toms-wp', __('TomS Dashboard', 'toms-vaptcha'), '<span class="toms-menu-item"><span class="toms-dashboard"></span><span class="toms-menu-text">' . __('Dashboard', 'toms-vaptcha') . '</span></span>' , 'manage_options', 'toms-wp', array($this, 'TomSWordpress'), null );
                    
                //Add Admin global style
                add_action( "admin_enqueue_scripts", array($this, 'TomSWP_global_load_style') );
                //Add TomS WP style
                add_action( "load-{$TomSWP}", array($this, 'TomSWP_load_style') );
                //Add TomS admin footer
                add_action( 'admin_footer_text', array($this, 'TomSWP_admin_footer_text') );
            }
        }

        //TomS Admin global style
        public function TomSWP_global_load_style() {
            wp_enqueue_style( 'TomSWPGlobalStyle', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css' );
        }
        
        //TomS WP style
        public function TomSWP_load_style() {
            wp_enqueue_style( 'TomSWPStyle', plugin_dir_url( __FILE__ ) . 'assets/css/tomswp.css' );
        }
        //TomS admin footer
        public function TomSWP_admin_footer_text($text) {
            if ( is_admin() && isset($_GET['page']) && strpos($_GET['page'], 'toms-') !== false){
                return sprintf( __( '<i>Thank you for using <a href="%s" target="_blank"><strong>TomS WordPress Plugin</strong></a></i>', 'toms-vaptcha' ) , 'https://tomsneddon.org' );
            }
            return $text;
        }

        //TomS WP Page contents
        public function TomSWordpress() {

            $TOMSLOGO = plugin_dir_url( __FILE__ ) . 'assets/img/TomS.svg';
            $TOMSURL = 'https://tomsneddon.org/';
            $TOMSWPURL = 'https://wordpress.org/plugins/';
            
            $TOMS_OBJECT    = file_get_contents(plugin_dir_url( __FILE__ ) . 'toms.json');
            $TOMS_JSON      = json_decode($TOMS_OBJECT, true);
        
            ?>
            
            <div id="toms-wp-dashboard" class="toms-wp-dashboard">
                <div class="toms-header">
                    <span class="toms-logo" ><img src="<?php echo esc_attr( $TOMSLOGO ); ?>" /></span>
                    <h1 class="toms-header-text"><?php _e('TomS DashBoard', 'toms-vaptcha'); ?></h1>
                </div>

                <?php if( !wp_is_mobile() ) : ?>
                <h2><?php _e('Welcome to TomS !', 'toms-vaptcha');?></h2>
                <p class="description"><?php _e('Thank you for choosing TomS plugins. We provide easy, useful and secure plugins for you.', 'toms-vaptcha'); ?></p>
                <?php endif;
                    //get all plugins
                    $all_plugins = get_plugins(''); ?>

                    <div class="current-installed">
                    <h3><?php _e('Current Installed', 'toms-vaptcha' ); ?></h3>
                    <div class="toms-current-activated">
                <?php

                    foreach ($all_plugins as $key => $value) {
                        //Get plugin slug
                        $slug = dirname($key);

                        //Check $key contains 'toms.php'
                        $toms_check = strpos($key, $slug.'toms.php');

                        if(isset($TOMS_JSON[$slug]['Status']) && $TOMS_JSON[$slug]['Status'] == 'published'){
                            $TOMS_URL = $TOMSWPURL;
                        }else{
                            $TOMS_URL = $TOMSURL;
                        }

                        //Installed plugins
                        if( $value['AuthorName'] == 'Tom Sneddon' && isset($TOMS_JSON[$slug]['Slug']) && $TOMS_JSON[$slug]['Slug'] == $slug ){ ?>
                                <div class="toms-items toms-wp-dashboard-<?php echo esc_attr( $slug ); ?>">
                                    <div class="toms-item-contents">
                                        <div class="<?php echo esc_attr( $slug ); ?> toms-plugins-logo">
                                            <?php echo '<img src="data:image/svg+xml;base64,' . esc_attr( $TOMS_JSON[$slug]['Logo'] ) . '" />' ?>
                                        </div>
                                        <div class="<?php echo esc_attr( $slug ); ?>-details toms-plugins-details" >
                                            <div class="<?php echo  esc_attr( $slug ); ?>-text toms-plugins-text">
                                                <?php echo esc_html( $value['Name'] ); ?>
                                                <?php echo $TOMS_JSON[$slug]['Type'] == 'block' ? ' <span style="color: #ff0000;">[<span style="color: #006600;">Block</span>]</span>' : ''; ?>
                                                <?php echo $TOMS_JSON[$slug]['Type'] == 'add-on' ? ' <span style="color: #ff0000;">[<span style="color: #006600;">Add-on</span>]</span>' : ''; ?>
                                            </div>
                                            <div class="<?php echo esc_attr( $slug ); ?>-description toms-plugins-description"><?php esc_html_e( $value['Description'] ); ?></div>
                                        </div>
                                    </div>
                                    <div class="toms-item-button">
                                        <div class="<?php echo esc_attr( $slug ); ?>-version toms-plugins-version"><?php echo __('Version:', 'toms-vaptcha' ) .' '. esc_html( $value['Version'] ); ?></div>
                                        
                                        <?php if( is_plugin_active( $toms_check == true ? $slug .'/'. $slug . '-home.php' : $key ) == true ) { 
                                                 if( $TOMS_JSON[$slug]['Type'] != 'block' && $TOMS_JSON[$slug]['Type'] != 'add-on'){ ?>
                                                 <div>
                                                    <a class="<?php echo  esc_attr( $slug ); ?>-config toms-plugins-config button activate-now button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page='.$slug.'-settings' ) ); ?>" >
                                                        <?php _e('Settings', 'toms-vaptcha' ); ?>
                                                    </a>
                                                    <a href="<?php echo esc_attr( $TOMS_URL.$TOMS_JSON[$slug]['Slug'] )?>" class="update-now button toms-active button-hidden" target="_blank">
                                                        <?php _e('View Details', 'toms-vaptcha'); ?>
                                                    </a>
                                                 </div>
                                            <?php }else{ ?>
                                                    <a href="<?php echo esc_attr( $TOMS_URL.$TOMS_JSON[$slug]['Slug'] )?>" class="update-now button" target="_blank">
                                                        <?php _e('View Details', 'toms-vaptcha'); ?>
                                                    </a>
                                            <?php } ?>
                                        <?php }else{ ?>
                                             <div>
                                                <a class="<?php echo  esc_attr( $slug ); ?>-update toms-plugins-update button activate-now button-primary" href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'activate', 'plugin' => $key ), admin_url( 'plugins.php' ) ), 'activate-plugin_'.$key ); ?>" >
                                                    <?php _e('Activate', 'toms-vaptcha' ); ?>
                                                </a>
                                                <a href="<?php echo esc_attr( $TOMS_URL.$TOMS_JSON[$slug]['Slug'] )?>" class="update-now button toms-active button-hidden" target="_blank">
                                                    <?php _e('View Details', 'toms-vaptcha'); ?>
                                                </a>
                                             </div>
                                        <?php } ?>
                                    </div>
                                </div>

                            <?php }
                        } ?>
                    </div>
                </div>
                <div class="our-more-plugins">
                <h3><?php _e('Our other popular plugins', 'toms-vaptcha' ); ?></h3>
                <div class="toms-more-plugins">
                <?php
                    foreach ($TOMS_JSON as $key => $value) {
                        //plugin name
                        $slug = $key;

                        if(isset($TOMS_JSON[$slug]['Status']) && $TOMS_JSON[$slug]['Status'] == 'published'){
                            $TOMS_URL = $TOMSWPURL;
                        }else{
                            $TOMS_URL = $TOMSURL;
                        }
                        //Our more plugins
                        if(isset($TOMS_JSON[$slug]['AuthorName']) && $TOMS_JSON[$slug]['AuthorName'] == 'Tom Sneddon'){ 
                            if(empty($all_plugins[ $key.'/'.$key.'-home.php']['TextDomain']) && empty($all_plugins[ $key.'/'.$key.'.php']['TextDomain'])){
                            ?>
                                <div class="toms-items toms-wp-dashboard-<?php echo esc_attr( $slug ); ?>">
                                    <div class="toms-item-contents">
                                        <div class="<?php echo esc_attr( $slug ); ?> toms-plugins-logo">
                                            <?php echo '<img src="data:image/svg+xml;base64,' . esc_attr( $TOMS_JSON[$slug]['Logo'] ) . '" />' ?>
                                        </div>
                                        <div class="<?php echo esc_attr( $slug ); ?>-details toms-plugins-details" >
                                            <div class="<?php echo  esc_attr( $slug ); ?>-text toms-plugins-text">
                                                <?php echo esc_html( $value['Name'] ); ?>
                                                <?php echo $value['Type'] == 'block' ? ' <span style="color: #ff0000;">[<span style="color: #006600;">Block</span>]</span>' : ''; ?>
                                                <?php echo $TOMS_JSON[$slug]['Type'] == 'add-on' ? ' <span style="color: #ff0000;">[<span style="color: #006600;">Add-on</span>]</span>' : ''; ?>
                                            </div>
                                            <div class="<?php echo esc_attr( $slug ); ?>-description toms-plugins-description"><?php esc_html_e( $value['Description'] ); ?></div>
                                        </div>
                                    </div>
                                    <div class="toms-item-button <?php echo empty($value['Version']) ? 'toms-version-empty' : ''; ?>">
                                        <?php if( !empty($value['Version']) ){ ?>
                                            <div class="<?php echo esc_attr( $slug ); ?>-version toms-plugins-version"><?php echo __('Version:', 'toms-vaptcha') . ' '. esc_html( $value['Version'] ); ?></div> 
                                        <?php } ?>
                                        <?php if( $TOMS_JSON[$slug]['Status'] == 'extented' ){ ?>
                                            <a href="<?php echo esc_attr( $TOMS_URL.$TOMS_JSON[$slug]['Slug'] )?>" class="update-now button" target="_blank">
                                                <?php _e('View Details', 'toms-vaptcha'); ?>
                                            </a>
                                        <?php }elseif( $TOMS_JSON[$slug]['Status'] == 'comming-soon' ){ ?>
                                            <a href="<?php echo esc_attr( $TOMS_URL.$TOMS_JSON[$slug]['Slug'] )?>" class="update-now button" target="_blank">
                                                <?php _e('Comming Soon', 'toms-vaptcha'); ?>
                                            </a>
                                        <?php }else{ ?>
                                            <a href="<?php echo esc_url( $TOMS_URL.$TOMS_JSON[$slug]['Slug'] ); ?>" class="update-now button" target="_blank">
                                                <?php _e('View Details', 'toms-vaptcha'); ?>
                                            </a>
                                        <?php } ?>
                                    </div>
                                </div>
                        <?php }
                        }
                    } ?>
                    </div>
                </div>
            </div>

        </div>
        <?php }

    }

    $TomSVaptchaHome = new TomSVaptchaHome();

    //Include TomS Plugins main php file. glob() make the path as array.
    $toms_include_files_array = glob( plugin_dir_path( __FILE__ ) . "inc/*.php" );
    
    foreach ( $toms_include_files_array as $file ) {
        include_once $file;
    }

}