<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

//UPLOAD FILES
function wpvacancylab_insert_attachment($file_handler,$post_id,$setthumb='false') {
    // check to make sure its a successful upload
    
    if ($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK) __return_false();

    require_once(ABSPATH . "wp-admin" . '/includes/image.php');
    require_once(ABSPATH . "wp-admin" . '/includes/file.php');
    require_once(ABSPATH . "wp-admin" . '/includes/media.php');

    $attach_id = media_handle_upload( $file_handler, $post_id );
    
    if ($setthumb) update_post_meta($post_id,'_thumbnail_id',$attach_id);
    
//    return wp_get_attachment_url( $attach_id );
    return $attach_id;
}

//Insert Candidate
function wpvacancylab_insert_candidate($data, $cv){
    global $wpdb;
    
    $arrDOB = explode('/', $data['DateofBirth']);
    return $wpdb->insert(
        'candidates', 
        array(
            'title' => sanitize_text_field($data['Title']), 
            'firstname' => sanitize_text_field($data['FirstName']),
            'lastname' => sanitize_text_field($data['LastName']),
            'email' => sanitize_email($data['E-Mail']),
            'mobile' => sanitize_text_field($data['Mobile']),
            'home' => sanitize_text_field($data['Phone']),
            'positionsought' => sanitize_text_field($data['PositionSought']),
            'dob' => sanitize_text_field(($data['DateofBirth'] ? (checkdate($arrDOB[1], $arrDOB[0], $arrDOB[2]) ? $data['DateofBirth'] : '') : '')),
            'street' => sanitize_text_field($data['Street']),
            'city' => sanitize_text_field($data['City']),
            'county' => sanitize_text_field($data['County']),
            'postcode' => sanitize_text_field($data['PostCode']),
            'country' => sanitize_text_field($data['Country']),
            'salary' => sanitize_text_field($data['Salary'] ? number_format(str_replace(',', '', floatval($data['Salary'])),2,'.','') : ''),
            'permanent' => sanitize_key($data['Permanent'] ? $data['Permanent'] : '0'),
            'contract' => sanitize_key($data['Contract'] ? $data['Contract'] : '0'),
            'preferredloc' => sanitize_text_field($data['Location']),
//            'cv' => esc_url_raw($cv),
            'cv' => sanitize_file_name($cv),
            'message' => sanitize_text_field($data['Message'])
        ), 
        array(
            '%s', 
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%f',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d'
        )
    );
}

//INSERT IF REQUIRED FIELDS ARE MET
if($_POST['Title'] && $_POST['FirstName'] && $_POST['LastName'] && filter_var($_POST['E-Mail'], FILTER_VALIDATE_EMAIL) && $_POST['PositionSought'] && $_POST['declaration']){
    //CHECK NONCE
    if (!isset( $_POST['_wpvacancylab_candidate-registration'] ) || !wp_verify_nonce( $_POST['_wpvacancylab_candidate-registration'], 'insert_candidate-registration' ) ) {
        wp_die('Sorry, your nonce did not verify.');
    } else {
        // process form data
    }
    
    //CHECK IF CANDIDATE ALREADY EXISTS (BY EMAIL)
    $email = filter_input(INPUT_POST, "E-Mail", FILTER_SANITIZE_EMAIL);
    $candidate = $wpdb->get_results($wpdb->prepare("SELECT email from candidates WHERE email = %s", $email), ARRAY_A );
    if($candidate[0]['email']){
        $msg = '<span>Sorry, this email address has already been submitted.</span>';
    } else {
        //UPLOAD FILE
        if ($_FILES) {
            foreach ($_FILES as $file => $array) {
                if($array['size']){
                    $cv = wpvacancylab_insert_attachment($file,$pid);
                } else {
                    $cv = null;
                }
            }
        }
        
        $arrDOB = explode('/', $_POST['DateofBirth']);
        $data = [
            'Title' => sanitize_text_field($_POST['Title']), 
            'FirstName' => sanitize_text_field($_POST['FirstName']),
            'LastName' => sanitize_text_field($_POST['LastName']),
            'E-Mail' => sanitize_email($_POST['E-Mail']),
            'Mobile' => sanitize_text_field($_POST['Mobile']),
            'Phone' => sanitize_text_field($_POST['Phone']),
            'PositionSought' => sanitize_text_field($_POST['PositionSought']),
            'DateofBirth' => sanitize_text_field(($_POST['DateofBirth'] ? (checkdate($arrDOB[1], $arrDOB[0], $arrDOB[2]) ? $_POST['DateofBirth'] : '') : '')),
            'Street' => sanitize_text_field($_POST['Street']),
            'City' => sanitize_text_field($_POST['City']),
            'County' => sanitize_text_field($_POST['County']),
            'PostCode' => sanitize_text_field($_POST['PostCode']),
            'Country' => sanitize_text_field($_POST['Country']),
            'Salary' => sanitize_text_field($_POST['Salary'] ? number_format(str_replace(',', '', floatval($_POST['Salary'])),2,'.','') : ''),
            'Permanent' => sanitize_key($_POST['Permanent'] ? $_POST['Permanent'] : '0'),
            'Contract' => sanitize_key($_POST['Contract'] ? $_POST['Contract'] : '0'),
            'Location' => sanitize_text_field($_POST['Location']),
            'Message' => sanitize_text_field($_POST['Message'])
        ];
        
        $msg = (wpvacancylab_insert_candidate($data, $cv) ? '<span class="success">Thank you for registering with us. One of our consultants will contact you shortly.' : '<span>Sorry, your application could not be sent, please try again or contact us.').'</span>';
    }
}

/*
 * Candidate Application form
 */

$ref = filter_input(INPUT_GET, "ref", FILTER_SANITIZE_STRING);
$content.='<div id="wpvacancylab_candidate">
    <div id="wpvacancylab_msg">'.$msg.'</div>
    <form action="" method="post" enctype="multipart/form-data" name="form1" onSubmit="return wpvacancylab_candidate_form_validate(this);">
        <table id="wpvacancylab_candidate_form">
            <tr class="wpvacancylab_Title">
                <th><label for="wpvacancylab_Title">Title*:</label></th>
                <td>
                    <select name="Title" id="wpvacancylab_Title">
                        <option value="Mr" '.(esc_html($cand['title']) == "Mr" ? 'selected' : '').'>Mr</option>
                        <option value="Mrs" '.(esc_html($cand['title']) == "Mrs" ? 'selected' : '').'>Mrs</option>
                        <option value="Miss" '.(esc_html($cand['title']) == "Miss" ? 'selected' : '').'>Miss</option>
                        <option value="Ms." '.(esc_html($cand['title']) == "Ms." ? 'selected' : '').'>Ms.</option>
                        <option value="Dr" '.(esc_html($cand['title'] )== "Dr" ? 'selected' : '').'>Dr</option>
                        <option value="Prof." '.(esc_html($cand['title']) == "Prof." ? 'selected' : '').'>Prof.</option>
                    </select>
                </td>
            </tr>
            <tr class="wpvacancylab_FirstName">
                <th><label for="wpvacancylab_FirstName">First Name*:</label></th>
                <td><input id="wpvacancylab_FirstName" name="FirstName" Type="text" value="'.esc_html($cand['firstname']).'" size="20" maxlength="255"></td>
            </tr>
            <tr class="wpvacancylab_LastName">
                <th><label for="wpvacancylab_LastName">Last Name*:</label></td>
                <td><input id="wpvacancylab_LastName" name="LastName" type="text" value="'.esc_html($cand['lastname']).'" size="20" maxlength="255"></th>
            </tr>
            <tr class="wpvacancylab_EMail">
                <th><label for="wpvacancylab_EMail">E-mail*:</label></th>
                <td><input id="wpvacancylab_EMail" name="E-Mail" type="text" value="'.esc_html($cand['email']).'" size="20" maxlength="255"></td>
            </tr>
            <tr>
                <th><label for="wpvacancylab_Mobile">Tel (Mobile):</label></th>
                <td><input id="wpvacancylab_Mobile" name="Mobile" type="text" value="'.esc_html($cand['mobile']).'" size="20" maxlength="255"></td>
            </tr>
            <tr>
                <th><label for="wpvacancylab_Phone">Tel (Home):</label></th>
                <td><input id="wpvacancylab_Phone" name="Phone" type="text" value="'.esc_html($cand['home']).'" size="20" maxlength="255"></td>
            </tr>
            <tr class="wpvacancylab_PositionSought">
                <th><label for="wpvacancylab_PositionSought">Position Sought*:</label></th>
                <td><input id="wpvacancylab_PositionSought" name="PositionSought" type="text" value="'.esc_html($ref).'" size="20" maxlength="255"></td>
            </tr>
            <tr>
                <th>&nbsp;</th>
                <td>&nbsp;</td>
            </tr>
            <tr class="wpvacancylab_DateofBirth">
                <th><label for="wpvacancylab_DateofBirth">DOB (dd/mm/yyyy):</label></th>
                <td><input id="wpvacancylab_DateofBirth" name="DateofBirth" type="text" value="'.esc_html($cand['dob']).'" size="20" maxlength="10"></td>
            </tr>
            <tr>
                <th align="right" valign="top" nowrap><label for="wpvacancylab_Street">Street:</label></th>
                <td><textarea id="wpvacancylab_Street" name="Street" cols="22" rows="2">'.esc_html($cand['street']).'</textarea></td>
            </tr>
            <tr>
                <th><label for="wpvacancylab_City">City:</label></th>
                <td><input id="wpvacancylab_City" name="City" type="text" value="'.esc_html($cand['city']).'" size="20" maxlength="255"></td>
            </tr>
            <tr>
                <th><label for="wpvacancylab_County">County:</label></th>
                <td><input id="wpvacancylab_County" name="County" type="text" value="'.esc_html($cand['county']).'" size="20" maxlength="255"></td>
            </tr>
            <tr>
                <th><label for="wpvacancylab_PostCode">Post Code:</label></th>
                <td><input id="wpvacancylab_PostCode" name="PostCode" type="text" value="'.esc_html($cand['postcode']).'" size="20" maxlength="255"></td>
            </tr>
            <tr>
                <th><label for="wpvacancylab_Country">Country:</label></th>
                <td><input id="wpvacancylab_Country" name="Country" type="text" value="'.esc_html($cand['country']).'" size="20" maxlength="255"></td>
            </tr>
            <tr>
                <th>&nbsp;</th>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <th><label for="wpvacancylab_Salary">Salary/Rate Required:</label></th>
                <td><input id="wpvacancylab_Salary" name="Salary" type="text" id="Salary" value="'.esc_html($cand['salary']).'" size="30" maxlength="255"></td>
            </tr>
            <tr>
                <th>
                    <label for="wpvacancylab_Permanent">Permanent:</label>
                </th>
                <td>
                    <input id="wpvacancylab_Permanent" name="Permanent" type="checkbox" id="Permanent" value="1" '.(esc_html($cand['permanent']) ? 'checked' : '').'><br/>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="wpvacancylab_Contract">Contract:</label>
                </th>
                <td>
                    <input id="wpvacancylab_Contract" name="Contract" type="checkbox" id="Contract" value="1" '.(esc_html($cand['contract']) ? 'checked' : '').'>
                </td>
            </tr>
            <tr>
                <th><label for="wpvacancylab_Location">Preferred Location(s):</label></th>
                <td><input id="wpvacancylab_Location" name="Location" type="text" id="Location" value="'.esc_html($cand['preferredloc']).'" size="30" maxlength="255"></td>
            </tr>'
    //        <tr>
    //            <td align="right" valign="top" nowrap>Message:</td>
    //            <td><textarea name="Message" cols="30" rows="4">'.$_SESSION['remember_Message'].'</textarea><br /></td>
    //        </tr>
            .'<tr>
                <th><label for="">Upload CV (max 2MB):</label></th>
                <td>
                    <input name="MAX_FILE_SIZE" type="hidden" id="MAX_FILE_SIZE" value="2097152" />
                    <input id="CV" name="CV" type="file" id="CV" size="25" maxlength="300">
                </td>
            </tr>
            <tr>
                <th align="right" valign="top" nowrap><label for="wpvacancylab_Message">Message:</label></th>
                <td><textarea id="wpvacancylab_Message" name="Message" cols="22" rows="2">'.esc_textarea($cand['message']).'</textarea></td>
            </tr>
            <tr class="wpvacancylab_declaration">
                <td><input type="checkbox" name="declaration" value="declaration"></td>
                <th>I have read and agreed to the Candidate Declaration</th>
            </tr>
                <td></td>
                <td><p><input type="submit" value="Register"></p></td>
            </tr>
        </table>
        <div class="floatClear"></div>
        <p><input type="hidden" name="MM_insert" value="form1"></p>'
        . wp_nonce_field( 'insert_candidate-registration', '_wpvacancylab_candidate-registration' )
    . '</form>
    '.$poweredBy.'
</div>';