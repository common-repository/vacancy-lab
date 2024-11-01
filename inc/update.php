<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

global $wpdb;

$options = get_option('wpvacancylab');
$upload_dir = wp_upload_dir();

/*
 * Various required functions of the Vacancy Lab plugin
 */
$userVL = filter_input(INPUT_POST, "user", FILTER_SANITIZE_STRING);
$passVL = filter_input(INPUT_POST, "pass", FILTER_SANITIZE_STRING);
$operation = filter_input(INPUT_POST, "op", FILTER_SANITIZE_STRING);

//Validation
$userWP = hash('sha256', $options['wpvacancylab_user']);
$passWP = hash('sha256', $options['wpvacancylab_password']);

//Vacancy data
$data['WebLocation'] = filter_input(INPUT_POST, "WebLocation", FILTER_SANITIZE_STRING);
$data['Reference'] = filter_input(INPUT_POST, "Reference", FILTER_SANITIZE_STRING);
$data['Position'] = filter_input(INPUT_POST, "Position", FILTER_SANITIZE_STRING);
$data['Salary'] = filter_input(INPUT_POST, "Salary", FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$data['SalaryPeriod'] = filter_input(INPUT_POST, "SalaryPeriod", FILTER_SANITIZE_STRING);
$data['SalaryCurrency'] = filter_input(INPUT_POST, "SalaryCurrency", FILTER_SANITIZE_STRING);
$data['Consultant'] = filter_input(INPUT_POST, "Consultant", FILTER_SANITIZE_STRING);
$data['Email'] = filter_input(INPUT_POST, "Email", FILTER_SANITIZE_EMAIL);
$data['Skills'] = filter_input(INPUT_POST, "Skills", FILTER_SANITIZE_STRING);
$data['Location'] = filter_input(INPUT_POST, "Location", FILTER_SANITIZE_STRING);
$data['Type'] = filter_input(INPUT_POST, "Type", FILTER_SANITIZE_STRING);
$data['Description'] = sanitize_text_field(urldecode(stripslashes($_POST['Description'])));
$data['CreDate'] = filter_input(INPUT_POST, "CreDate", FILTER_SANITIZE_STRING);

if($userVL == $userWP && $passVL == $passWP){
    //CHECK UPDATES HAVE BEEN APPLIED
    switch($operation){
        case 'add_vac_temp': //add individual vacancy to temp table
        case 'add_vac': //add individual vacancy to vacancy table
        case 'update_vac': //updates specified vacancy
            //CHECK IF 'SalaryCurrency' COLUMN EXISTS IN tblvacancies
            $arrSalaryCurrency = ($wpdb->get_results($wpdb->prepare("SHOW COLUMNS FROM `tblvacancies` LIKE 'SalaryCurrency'",""), ARRAY_A ));
            
            if(!empty($wpdb->last_error)){
                echo $wpdb->print_error();
                exit;
            } else {
                if($arrSalaryCurrency[0]['Field'] != 'SalaryCurrency'){
                    $wpdb->query("ALTER TABLE `tblvacancies` ADD `SalaryCurrency` varchar(9)");

                    if(!empty($wpdb->last_error)){
                        echo $wpdb->print_error();
                        exit;
                    }
                }
            }

            //CHECK IF 'SalaryCurrency' COLUMN EXISTS IN tblvacancies
            $arrSalaryCurrencyTemp = ($wpdb->get_results($wpdb->prepare("SHOW COLUMNS FROM `tblvacancies_temp` LIKE 'SalaryCurrency'",""), ARRAY_A ));
            if(!empty($wpdb->last_error)){
                echo $wpdb->print_error();
                exit;
            } else {
                if($arrSalaryCurrencyTemp[0]['Field'] != 'SalaryCurrency'){
                    $wpdb->query("ALTER TABLE `tblvacancies_temp` ADD `SalaryCurrency` varchar(9)");

                    if(!empty($wpdb->last_error)){
                        echo $wpdb->print_error();
                        exit;
                    }
                }
            }
        break;
    }
    
    switch($operation){
        case 'add_vac_temp': //add individual vacancy to temp table
            echo wpvacancylab_insert_vacancy_temp($data);
        break;
        case 'add_vac': //add individual vacancy to vacancy table
            echo wpvacancylab_insert_vacancy($data);
        break;
        case 'get_vac': //get vacancy
            $arrResult = $wpdb->get_results($wpdb->prepare("SELECT * from tblvacancies WHERE Reference = %s", sanitize_text_field($data['Reference'])), ARRAY_A );
            if(!empty($wpdb->last_error)){
                echo json_encode(array('error'=>$wpdb->print_error()));
                exit;
            } else {
                echo json_encode($arrResult);
            }
        break;
        case 'complete_vac': //overwrite tblvacancies with tempvacancies (after 'add_vac' has completed
            $wpdb->get_results("TRUNCATE TABLE tblvacancies"); //empty tblvacancies table
            if(!empty($wpdb->last_error)){
                echo $wpdb->print_error();
                exit;
            } else {
                $wpdb->query("INSERT INTO tblvacancies(SELECT * FROM tblvacancies_temp)"); //copy tmp to live
                if(!empty($wpdb->last_error)){
                    echo $wpdb->print_error();
                    exit;
                } else {
                    $wpdb->get_results("TRUNCATE TABLE tblvacancies_temp"); //empty tblvacancies_temp table
                    if(!empty($wpdb->last_error)){
                        echo $wpdb->print_error();
                        exit;
                    } else {
                        echo 'success';
                    }
                }
            }
        break;
        case 'update_vac': //updates specified vacancy
            //returns 1 for success, 0 for error
            echo wpvacancylab_update_vacancy($data);
        break;
        case 'remove_vac': //removes specific vacancy
            //returns 1 for success, 0 for error
            $wpdb->query($wpdb->prepare("DELETE FROM tblvacancies WHERE Reference = %s", sanitize_text_field($data['Reference'])), ARRAY_A );
            
            if(!empty($wpdb->last_error)){
                echo $wpdb->print_error();
            } else {
                echo 'success';
            }
        break;
    
        case 'get_cand_emails': //gets list of candidate emails
            $emails = $wpdb->get_col( 'SELECT email FROM candidates', 0 );
            echo json_encode($emails);
        break;
        case 'get_cand': //get candidate by email address
            $candidate = $wpdb->get_results($wpdb->prepare("SELECT * from candidates WHERE email = %s", sanitize_email($data['Email'])), ARRAY_A );
            $CV = get_post_meta($candidate[0]['cv'],'_wp_attached_file',true);
            $candidate[0]['cv'] = ($CV ? $upload_dir['baseurl'].'/'.$CV : '');
            echo json_encode($candidate);
        break;
        case 'remove_cand': //removes specific candiate
            //GETS POST ID OF CANDIDATE, DELETES ANY ATTACHMENTS AND THEN REMOVES CANDIDATE ROW FROM TABLE
            $candidate = $wpdb->get_results($wpdb->prepare("SELECT * from candidates WHERE email = %s", sanitize_email($data['Email'])), ARRAY_A );
            if($candidate[0]['cv']){
                wp_delete_attachment( $candidate[0]['cv'], true );
            }
            echo $wpdb->query($wpdb->prepare("DELETE FROM candidates WHERE email = %s", sanitize_email($data['Email'])), ARRAY_A );
        break;
    }
} else {
    die('Validation failed');
}

//Insert Vacancy Temp
function wpvacancylab_insert_vacancy_temp($data){
    global $wpdb;
    $wpdb->show_errors();
    
//    return $data['Description'];exit;
    $wpdb->insert(
        'tblvacancies_temp', 
        array(
            'WebLocation' => sanitize_text_field($data['WebLocation']), 
            'Reference' => sanitize_text_field($data['Reference']),
            'Position' => sanitize_text_field($data['Position']),
            'Salary' => sanitize_text_field($data['Salary']),
            'SalaryPeriod' => sanitize_text_field($data['SalaryPeriod']),
            'SalaryCurrency' => sanitize_text_field($data['SalaryCurrency']),
            'Consultant' => sanitize_text_field($data['Consultant']),
            'Email' => sanitize_email($data['Email']),
            'Skills' => sanitize_text_field($data['Skills']),
            'Location' => sanitize_text_field($data['Location']),
            'Type' => sanitize_text_field($data['Type']),
            'Description' => sanitize_text_field($data['Description']),
            'CreDate' => sanitize_text_field(date('Y-m-d H:i:s',strtotime($data['CreDate'])))
        ), 
        array(
            '%s', 
            '%s',
            '%s',
            '%f',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s'
        )
    );
    if(!empty($wpdb->last_error)){
        return $wpdb->print_error();
    } else {
        return 'success';
    }
}

//Insert Vacancy
function wpvacancylab_insert_vacancy($data){
    global $wpdb;
    $wpdb->show_errors();
    
    //DELETE ANY EXISTING
    $wpdb->query($wpdb->prepare("DELETE FROM tblvacancies WHERE Reference = %s", sanitize_text_field($data['Reference'])), ARRAY_A );
    if(!empty($wpdb->last_error)){
        return $wpdb->print_error();
    } else {
        $wpdb->insert(
            'tblvacancies', 
            array(
                'WebLocation' => sanitize_text_field($data['WebLocation']), 
                'Reference' => sanitize_text_field($data['Reference']),
                'Position' => sanitize_text_field($data['Position']),
                'Salary' => sanitize_text_field($data['Salary']),
                'SalaryPeriod' => sanitize_text_field($data['SalaryPeriod']),
                'SalaryCurrency' => sanitize_text_field($data['SalaryCurrency']),
                'Consultant' => sanitize_text_field($data['Consultant']),
                'Email' => sanitize_email($data['Email']),
                'Skills' => sanitize_text_field($data['Skills']),
                'Location' => sanitize_text_field($data['Location']),
                'Type' => sanitize_text_field($data['Type']),
                'Description' => sanitize_text_field($data['Description']),
                'CreDate' => sanitize_text_field(date('Y-m-d H:i:s',strtotime($data['CreDate'])))
            ), 
            array(
                '%s', 
                '%s',
                '%s',
                '%f',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );
        if(!empty($wpdb->last_error)){
            return $wpdb->print_error();
        } else {
            return 'success';
        }
    }
}

//Update Vacancy
function wpvacancylab_update_vacancy($data){
    global $wpdb;
    $wpdb->update( 
        'tblvacancies', 
        array( 
            'WebLocation' => sanitize_text_field($data['WebLocation']),
            'Position' => sanitize_text_field($data['Position']),
            'Salary' => sanitize_text_field($data['Salary']),
            'SalaryPeriod' => sanitize_text_field($data['SalaryPeriod']),
            'SalaryCurrency' => sanitize_text_field($data['SalaryCurrency']),
            'Consultant' => sanitize_text_field($data['Consultant']),
            'Email' => sanitize_text_field($data['Email']),
            'Skills' => sanitize_text_field($data['Skills']),
            'Location' => sanitize_text_field($data['Location']),
            'Type' => sanitize_text_field($data['Type']),
            'Description' => sanitize_text_field($data['Description']),
            'CreDate' => sanitize_text_field(date('Y-m-d H:i:s',strtotime($data['CreDate'])))
        ), 
        array( 'Reference' => sanitize_text_field($data['Reference']) ), 
        array(
            '%s', 
            '%s',
            '%f',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s'
        ), 
        array( '%s' ) 
    );
    
    if(!empty($wpdb->last_error)){
        return $wpdb->print_error();
    } else {
        return 'success';
    }
}