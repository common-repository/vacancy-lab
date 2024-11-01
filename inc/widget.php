<?php
/*
 * Widget for sidebar if required - 
 * Once you activate the plugin, go to Appearance » Widgets. Next, drag and drop the widgets you want to display in your post or page into the Shortcodes sidebar. That's it. Now you can add WordPress widgets in your post and page content.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if($wpvacancylab_s_text || $wpvacancylab_s_location || $wpvacancylab_s_position){ //ONLY SHOW SEARCH FORM IF THERE ARE ANY FILTERS TO SHOW
    $locations = $wpdb->get_results("select DISTINCT Location from tblvacancies WHERE Reference <> '01234567890123456789' AND Location != '' ORDER BY Location");
    $positions = $wpdb->get_results("select DISTINCT Position from tblvacancies WHERE Reference <> '01234567890123456789' AND Position != '' ORDER BY Position");

    //Locations
    $strLocations="<select id='wpvacancylab_location' name='wpvacancylab_location'>"
        . "<option value='' selected='selected'></option>";
    foreach ($locations as $obj){
       $strLocations.="<option value='".$obj->Location."'>".$obj->Location."</option>";
    }
    $strLocations.="</select>";

    //Positions
    $strPositions="<select id='wpvacancylab_position' name='wpvacancylab_position'>"
        . "<option value='' selected='selected'></option>";
    foreach ($positions as $obj){
       $strPositions.="<option value='".$obj->Position."'>".$obj->Position."</option>";
    }
    $strPositions.="</select>";

    echo '<div class="widget widget_vacancylab">'
        . '<form method="get" action="">'
            . '<h2 class="widget-title">Vacancy Search</h2>'
            . ($wpvacancylab_s_text ? '<p><label for="wpvacancylab_search">Search:</label><br/><input type="text" id="wpvacancylab_search" name="wpvacancylab_search" /></p>' : '')
            . ($wpvacancylab_s_location ? '<p><label for="wpvacancylab_location">Location:</label><br/>'.$strLocations.'</p>' : '')
            . ($wpvacancylab_s_position ? '<p><label for="wpvacancylab_position">Position:</label><br/>'.$strPositions.'</p>' : '')
            . '<input type="hidden" value="' . $wpvacancylab_vac_pageid . '" name="page_id" />'
            . '<p><input type="submit" name="Search" value="Search" name="wpvacancylab_search_submit" /></p>'
            . wp_nonce_field( 'vacancy-search' )
        . '</form>'
    . '</div>';
}