<?php
/*
 * Vacancy page including search function and search results
 * if $_GET['Ref'] is passed to the page and is valid, that vacancy will be displayed
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

// Checks if a specific vacancy ref has been request
if($_GET['ref']){
    $ref = filter_input(INPUT_GET, "ref", FILTER_SANITIZE_STRING);
    $vacancy = $wpdb->get_results($wpdb->prepare("select * from tblvacancies WHERE Reference = %s", $ref), ARRAY_A );
    $title = esc_html($vacancy[0]['Reference']).' - '.esc_html($vacancy[0]['Position']);
} else {
    $title = 'Vacancy Search';
}