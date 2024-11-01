<?php
/* 
 * Plugin Name: Vacancy Lab
 * Plugin URI: https://www.vacancylab.com/wordpress-plugin/
 * Description: Add Vacancy Search and Candidate submissions that are powered by Vacancy Lab onto your Wordpress website
 * Version: 0.7
 * Author: Jarrett & Lam
 * Author URI: https://www.vacancylab.com
 * License: GPL2
 * Text Domain: vacancy-lab
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

$plugin_url = WP_PLUGIN_URL . '/wpvacancylab';
$options = array();

function wpvacancylab_plugin(){
    add_options_page(
        'Vacancy Lab',
        'Vacancy Lab',
        'manage_options',
        'wpvacancylab_plugin',
        'wpvacancylab_plugin_options_page'
    );
}
add_action('admin_menu', 'wpvacancylab_plugin');

//GET POST DATA IF APPLICABLE
function wpvacancylab_getpostdata() {
    if ( isset( $_POST['user'] ) && isset( $_POST['pass'] ) && isset( $_POST['op'] )) {
        require 'inc/update.php';
        exit;
    } // end if

} // end my_theme_send_email
add_action( 'init', 'wpvacancylab_getpostdata' );

function wpvacancylab_plugin_options_page(){
    if(!current_user_Can('manage_options')){
        wp_die('You do not have permission to access this page');
    }
    
    //CHECK NONCE
    // if this fails, check_admin_referer() will automatically print a "failed" page and die.
    if ( ! empty( $_POST ) && check_admin_referer( 'save_wpvacancylab_options', '_wpvacancylab_options' ) ) {
       // process form data
    }
    
    global $plugin_url;
    global $options;
    
    if(isset($_POST['vacancylab_form_submitted'])){
        $hidden_field = sanitize_text_field($_POST['vacancylab_form_submitted']);
        if($hidden_field == "Y"){
            $wpvacancylab_user = sanitize_text_field($_POST['wpvacancylab_user']);
            $wpvacancylab_password = sanitize_text_field($_POST['wpvacancylab_password']);
            $wpvacancylab_vac_pageid = sanitize_key($_POST['wpvacancylab_vac_pageid']);
            $wpvacancylab_s_text = sanitize_key($_POST['wpvacancylab_s_text']);
            $wpvacancylab_s_location = sanitize_key($_POST['wpvacancylab_s_location']);
            $wpvacancylab_s_position = sanitize_key($_POST['wpvacancylab_s_position']);
            $wpvacancylab_can_pageid = sanitize_key($_POST['wpvacancylab_can_pageid']);
            
            $options['wpvacancylab_user'] = $wpvacancylab_user;
            $options['wpvacancylab_password'] = $wpvacancylab_password;
            $options['wpvacancylab_vac_pageid'] = $wpvacancylab_vac_pageid;
            $options['wpvacancylab_s_text'] = $wpvacancylab_s_text;
            $options['wpvacancylab_s_location'] = $wpvacancylab_s_location;
            $options['wpvacancylab_s_position'] = $wpvacancylab_s_position;
            $options['wpvacancylab_can_pageid'] = $wpvacancylab_can_pageid;
            $options['last_updated'] = time();
            update_option('wpvacancylab', $options);
        }
    }
    $options = get_option('wpvacancylab');
    
    if($options != ''){
        $wpvacancylab_user = $options['wpvacancylab_user'];
        $wpvacancylab_password = $options['wpvacancylab_password'];
        $wpvacancylab_vac_pageid = $options['wpvacancylab_vac_pageid'];
        $wpvacancylab_s_text = $options['wpvacancylab_s_text'];
        $wpvacancylab_s_location = $options['wpvacancylab_s_location'];
        $wpvacancylab_s_position = $options['wpvacancylab_s_position'];
        $wpvacancylab_can_pageid = $options['wpvacancylab_can_pageid'];
    }
    require('inc/options-page-wrapper.php');
}


/*
 * Widget
 */

class Wpvacancylab_Widget extends WP_Widget {
    function wpvacancylab_Widget() {
        // Instantiate the parent object
        parent::__construct( false, 'Vacancy Lab Widget' );
    }

    function widget( $args, $instance ) {
        global $wpdb;
        // Widget output
        extract($args);
        $title = apply_filters('widgets_title', $instance['title']);

        $options = get_option('wpvacancylab');
        if($options != ''){
            $wpvacancylab_user = $options['wpvacancylab_user'];
            $wpvacancylab_password = $options['wpvacancylab_password'];
            $wpvacancylab_vac_pageid = $options['wpvacancylab_vac_pageid'];
            $wpvacancylab_s_text = $options['wpvacancylab_s_text'];
            $wpvacancylab_s_location = $options['wpvacancylab_s_location'];
            $wpvacancylab_s_position = $options['wpvacancylab_s_position'];
            $wpvacancylab_can_pageid = $options['wpvacancylab_can_pageid'];
        }

        require('inc/widget.php');
    }

    function update( $new_instance, $old_instance ) {
        // Save widget options
    }

    function form( $instance ) {
        // Output admin widget options form
    }
}

function vacancylab_register_widgets() {
    register_widget( 'Wpvacancylab_Widget' );
}
add_action( 'widgets_init', 'vacancylab_register_widgets' );

/*
 * JS & Stylesheets
 */
function wpvacancylab_scripts() {
    wp_enqueue_script('wpvacancylab-script',plugins_url('/js/wpvacancylab-script.js', __FILE__),array( 'jquery' ));
    wp_enqueue_style('wpvacancylab',plugins_url( '/css/wpvacancylab.css', __FILE__ ));    
}
// Adds the plugin css files
add_action( 'wp_enqueue_scripts', 'wpvacancylab_scripts' );

/*
 * Front-end
 */
function Wpvacancylab_Front_End( $content ) {
    global $wpdb;
    // Loads the plugin options
    $options = get_option('wpvacancylab');
    
    if($options != ''){
        $wpvacancylab_user = $options['wpvacancylab_user'];
        $wpvacancylab_password = $options['wpvacancylab_password'];
        $wpvacancylab_vac_pageid = $options['wpvacancylab_vac_pageid'];
        $wpvacancylab_s_text = $options['wpvacancylab_s_text'];
        $wpvacancylab_s_location = $options['wpvacancylab_s_location'];
        $wpvacancylab_s_position = $options['wpvacancylab_s_position'];
        $wpvacancylab_can_pageid = $options['wpvacancylab_can_pageid'];
        $wpvacancylab_pwd_by = $options['wpvacancylab_pwd_by'];
    }

    // Variable to store powered by link
    if($wpvacancylab_pwd_by != 'off'){
        $poweredBy = '<p style="float:right;padding-top:25px;font-size:0.6em;">Powered By <a href="https://www.vacancylab.com" target="_blank">Vacancy Lab</a></p>';
    }
    
    // Checks if this is the vacancies page
    if(get_the_ID() == $wpvacancylab_vac_pageid){
        require('inc/front-end-vac.php');
    }
    if(get_the_ID() == $wpvacancylab_can_pageid){
        require('inc/front-end-cand.php');
    }
    
    // Returns the updated content
    return $content;
}
// Adds the updated content as it is loaded into the page
add_filter('the_content', 'Wpvacancylab_Front_End');