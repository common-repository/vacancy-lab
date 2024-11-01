<?php
/*
 * Vacancy page including search function and search results
 * if $_GET['Ref'] is passed to the page and is valid, that vacancy will be displayed
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

$locations = $wpdb->get_results("select DISTINCT Location from tblvacancies WHERE Reference <> '01234567890123456789' AND Location != '' ORDER BY Location");
$positions = $wpdb->get_results("select DISTINCT Position from tblvacancies WHERE Reference <> '01234567890123456789' AND Position != '' ORDER BY Position");

//Search variables
$search = filter_input(INPUT_GET, "wpvacancylab_search", FILTER_SANITIZE_STRING);
$location = filter_input(INPUT_GET, "wpvacancylab_location", FILTER_SANITIZE_STRING);
$position = filter_input(INPUT_GET, "wpvacancylab_position", FILTER_SANITIZE_STRING);
$search_submit = filter_input(INPUT_GET, "wpvacancylab_search_submit", FILTER_SANITIZE_STRING);

//CHECK IF FORM SUBMITTED - IF SO, CHECK FOR NONCE
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($search_submit)){
    check_admin_referer('vacancy-search');
}

//Locations
$strLocations="<select name='wpvacancylab_location'>"
    . "<option value=''".(!$location ? " selected='selected'" : "").">Location</option>";
foreach ($locations as $obj){
   $strLocations.="<option value='".$obj->Location."'".($location == $obj->Location ? " selected='selected'" : "").">".$obj->Location."</option>";
}
$strLocations.="</select>";

//Positions
$strPositions="<select name='wpvacancylab_position'>"
    . "<option value=''".(!$position ? " selected='selected'" : "").">Position</option>";
foreach ($positions as $obj){
   $strPositions.="<option value='".$obj->Position."'".($position == $obj->Position ? "selected='selected'" : "").">".$obj->Position."</option>";
}
$strPositions.="</select>";

// Checks if a specific vacancy ref has been request
if($_GET['ref']){
    $ref = filter_input(INPUT_GET, "ref", FILTER_SANITIZE_STRING);
    $vacancy = $wpdb->get_results($wpdb->prepare("select * from tblvacancies WHERE Reference = %s", $ref), ARRAY_A );
    if($vacancy[0]['Reference']){
//        $header = '<header class="entry-header">Vacancy Ref: '.$vacancy[0]['Reference'].'</header><!-- .entry-header -->';
        $content= 
            '<div id="wpvacancylab_vacancy_details">
                <div class="wpvacancylab_vacancy_details_wrapper">'
                    . (trim($vacancy[0]['Reference']) ? '<label>Vacancy Ref:</label><div>'.$vacancy[0]['Reference'].'</div>' : '')
                    . (trim($vacancy[0]['Position']) ? '<label>Position:</label><div>'.$vacancy[0]['Position'].'</div>' : '')
                    . (trim($vacancy[0]['Salary']) ? '<label>Salary:</label><div>'.$vacancy[0]['Salary'].'</div>' : '')
                    . (trim($vacancy[0]['SalaryPeriod']) ? '<label>SalaryPeriod:</label><div>'.$vacancy[0]['SalaryPeriod'].'</div>' : '')
                    . (trim($vacancy[0]['Consultant']) ? '<label>Consultant:</label><div>'.$vacancy[0]['Consultant'].'</div>' : '')
                    . (trim($vacancy[0]['Email']) ? '<label>Email:</label><div>'.$vacancy[0]['Email'].'</div>' : '')
                    . (trim($vacancy[0]['Skills']) ? '<label>Skills:</label><div>'.$vacancy[0]['Skills'].'</div>' : '')
                    . (trim($vacancy[0]['Location']) ? '<label>Location:</label><div>'.$vacancy[0]['Location'].'</div>' : '')
                    . (trim($vacancy[0]['Type']) ? '<label>Type:</label><div>'.$vacancy[0]['Type'].'</div>' : '')
                . '</div>'
                . '<div class="wpvacancylab_vacancy_details_wrapper">'
                    . (trim($vacancy[0]['Description']) ? '<label class="wpvacancylab_vacancy_details_long">Description:</label><div class="wpvacancylab_vacancy_details_long">'.str_replace('Â¬', '<br/>', $vacancy[0]['Description']).'</div>' : '')
                . '</div>'
            . '</div>'
           
            . '<div id="wpvacancylab_vacancy_details_buttons">'
                . '<a class="wpvacancylab_vacancy_details_button" href="'.esc_url( get_permalink($wpvacancylab_vac_pageid) ).'">Back</a>'
                . '<a class="wpvacancylab_vacancy_details_button" href="'.esc_url( get_permalink($wpvacancylab_can_pageid) ) . (strpos(get_permalink($wpvacancylab_vac_pageid), '?') !== false ? '&' : '?') . 'ref='.$ref.'">Apply Now</a>'
            . '</div>'
            . '<div class="wpvacancylab_floatclear"></div>';
    } else {
        $content= 'Sorry, no vacancy found for \''.$ref.'\'';
    }
} else {
    if($wpvacancylab_s_text || $wpvacancylab_s_location || $wpvacancylab_s_position){ //ONLY SHOW SEARCH FORM IF THERE ARE ANY FILTERS TO SHOW
        // Adds the search filter interface
        $content.= '<form method="get" action="" id="wpvacancylab_form">'
            . ($wpvacancylab_s_text ? '<input type="text" id="wpvacancylab_search" name="wpvacancylab_search" placeholder="Search" value="'.$search.'">' : '')
            . ($wpvacancylab_s_location ? $strLocations : '')
            . ($wpvacancylab_s_position ? $strPositions : '')
            . wp_nonce_field( 'vacancy-search' )
            . '<input type="hidden" value="' . $wpvacancylab_vac_pageid . '" name="page_id" />'
            . '<input type="submit" value="Update Results" name="wpvacancylab_search_submit" />'
        . '</form>';
    }
    
    //RESULTS LIST
    $qry = "select * from tblvacancies WHERE Reference <> '01234567890123456789'";
    if($search){ $qry.= " AND (Reference LIKE '%$search%' OR Position LIKE '%$search%' OR Salary LIKE '%$search%' OR Consultant LIKE '%$search%' OR Email LIKE '%$search%' OR Skills LIKE '%$search%' OR Location LIKE '%$search%' OR Type LIKE '%$search%' OR Description LIKE '%$search%')"; }
    if($location){ $qry.= " AND Location = '$location'"; }
    if($position){ $qry.= " AND Position = '$position'"; }
    
    $vacancies = $wpdb->get_results($qry);
    $content.="<table>"
        . "<thead>"
            . "<tr>"
                . "<th>Reference</th>"
                . "<th>Position</th>"
                . "<th>Location</th>"
                . "<th>Salary</th>"
            . "</td>"
        . "</thead>"
        . "<tbody>";
    foreach ($vacancies as $obj){
        $content.="<tr>"
            . "<th><a class='wpvacancylab_ref_button' href='".esc_url( get_permalink($wpvacancylab_vac_pageid) ) . (strpos(get_permalink($wpvacancylab_vac_pageid), '?') !== false ? '&' : '?') . "ref={$obj->Reference}'>{$obj->Reference}</a></th>"
            . "<td>{$obj->Position}</td>"
            . "<td>{$obj->Location}</td>"
            . "<td>{$obj->Salary}</td>"
        . "</td>";
    }
    $content.="</tbody>"
    . "</table>";
}
    
//Adds the powered by link to the content
$content.= $poweredBy;