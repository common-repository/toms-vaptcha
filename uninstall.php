<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

if( esc_textarea( get_option('toms_vaptcha_clear_data') ) == "0" ){

    global $wpdb;

    // Delete options.
    $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'toms\_vaptcha\_%';" );
    
    // Clear any cached data that has been removed.
    wp_cache_flush();
}