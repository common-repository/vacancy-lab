<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

global $wpdb;

//CREATE tblvacancies if it doesnt exist
$wpdb->query("
    CREATE TABLE IF NOT EXISTS `tblvacancies` (
        `WebLocation` varchar(50) DEFAULT NULL,
        `Reference` varchar(20) NOT NULL DEFAULT '',
        `Position` varchar(100) DEFAULT NULL,
        `Salary` decimal(10,2) DEFAULT NULL,
        `SalaryPeriod` varchar(9) DEFAULT NULL,
        `SalaryCurrency` varchar(9) DEFAULT NULL,
        `Consultant` varchar(50) DEFAULT NULL,
        `Email` varchar(50) DEFAULT NULL,
        `Skills` text,
        `Location` text,
        `Type` varchar(10) DEFAULT NULL,
        `Description` text,
        `CreDate` datetime DEFAULT NULL,
        PRIMARY KEY (`Reference`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");

//CREATE tblvacancies_temp if it doesnt exist
$wpdb->query("
    CREATE TABLE IF NOT EXISTS `tblvacancies_temp` (
        `WebLocation` varchar(50) DEFAULT NULL,
        `Reference` varchar(20) NOT NULL DEFAULT '',
        `Position` varchar(100) DEFAULT NULL,
        `Salary` decimal(10,2) DEFAULT NULL,
        `SalaryPeriod` varchar(9) DEFAULT NULL,
        `SalaryCurrency` varchar(9) DEFAULT NULL,
        `Consultant` varchar(50) DEFAULT NULL,
        `Email` varchar(50) DEFAULT NULL,
        `Skills` text,
        `Location` text,
        `Type` varchar(10) DEFAULT NULL,
        `Description` text,
        `CreDate` datetime DEFAULT NULL,
        PRIMARY KEY (`Reference`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");

//CREATE candidates table if it doesnt exist
$wpdb->query("
    CREATE TABLE IF NOT EXISTS `candidates` (
        `title` varchar(5) NOT NULL DEFAULT '',
        `firstname` varchar(32) NOT NULL DEFAULT '',
        `lastname` varchar(32) NOT NULL DEFAULT '',
        `email` varchar(255) NOT NULL DEFAULT '',
        `mobile` varchar(32) NOT NULL DEFAULT '',
        `home` varchar(32) NOT NULL DEFAULT '',
        `positionsought` varchar(32) NOT NULL DEFAULT '',
        `dob` varchar(10) NOT NULL DEFAULT '',
        `street` varchar(255) NOT NULL DEFAULT '',
        `city` varchar(32) NOT NULL DEFAULT '',
        `county` varchar(32) NOT NULL DEFAULT '',
        `postcode` varchar(32) NOT NULL DEFAULT '',
        `country` varchar(32) NOT NULL DEFAULT '',
        `salary` varchar(32) NOT NULL DEFAULT '',
        `permanent` set('0','1') NOT NULL DEFAULT '',
        `contract` set('0','1') NOT NULL DEFAULT '',
        `preferredloc` varchar(32) NOT NULL DEFAULT '',
        `cv` varchar(255),
        `message` text,
        UNIQUE KEY `email` (`email`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");

//CHECK IF 'cv' COLUMN EXISTS
$arrCV = ($wpdb->get_results($wpdb->prepare("SHOW COLUMNS FROM `candidates` LIKE 'cv'",""), ARRAY_A ));
if($arrCV[0]['Field'] != 'cv'){
    if($wpdb->query("ALTER TABLE `candidates` ADD `cv` varchar(255)")){
        echo "<br/>DB Table 'candidates' updated with 'cv' column";
    }
}

//CHECK IF 'message' COLUMN EXISTS
$arrMessage = ($wpdb->get_results($wpdb->prepare("SHOW COLUMNS FROM `candidates` LIKE 'message'",""), ARRAY_A ));
if($arrMessage[0]['Field'] != 'message'){
    if($wpdb->query("ALTER TABLE `candidates` ADD `message` text")){
        echo "<br/>DB Table 'candidates' updated with 'message' column";
    }
}

//CHECK IF 'SalaryCurrency' COLUMN EXISTS IN tblvacancies
$arrSalaryCurrency = ($wpdb->get_results($wpdb->prepare("SHOW COLUMNS FROM `tblvacancies` LIKE 'SalaryCurrency'",""), ARRAY_A ));
if($arrSalaryCurrency[0]['Field'] != 'SalaryCurrency'){
    if($wpdb->query("ALTER TABLE `tblvacancies` ADD `SalaryCurrency` varchar(9)")){
        echo "<br/>DB Table 'tblvacancies' updated with 'SalaryCurrency' column";
    }
}

//CHECK IF 'SalaryCurrency' COLUMN EXISTS IN tblvacancies
$arrSalaryCurrencyTemp = ($wpdb->get_results($wpdb->prepare("SHOW COLUMNS FROM `tblvacancies_temp` LIKE 'SalaryCurrency'",""), ARRAY_A ));
if($arrSalaryCurrencyTemp[0]['Field'] != 'SalaryCurrency'){
    if($wpdb->query("ALTER TABLE `tblvacancies_temp` ADD `SalaryCurrency` varchar(9)")){
        echo "<br/>DB Table 'tblvacancies_temp' updated with 'SalaryCurrency' column";
    }
}
?>

<div class="wrap">

	<h1><?php esc_attr_e( 'Vacancy Lab Plugin Admin', 'wp_admin_style' ); ?></h1>
	<div id="col-container">
            <form name="vacancylab_form" method="post" action="">
                <input type="hidden" name="vacancylab_form_submitted" value="Y" />
		<div id="col-right" style="width:50%">

			<div class="col-wrap">
				<h2 class="hndle"><span><?php esc_attr_e('Connection Details', 'wp_admin_style'); ?></span></h2>
				<div class="inside">
                                    <div class="postbox">
					<table class="form-table">
                                                <tr valign="top">
                                                        <td scope="row" style="width:200px"><label for="wpvacancylab_url"><?php esc_attr_e('URL for Vacancy Lab', 'wp_admin_style'); ?></label></td>
                                                        <td><input type="text" id="wpvacancylab_url" readonly="readonly" value="<?php echo trailingslashit(get_site_url()); ?>" class="all-options" /></td>
                                                </tr>
                                                <tr valign="top" class="alternate">
                                                        <td scope="row"><label for="wpvacancylab_user"><?php esc_attr_e('Username', 'wp_admin_style'); ?></label></td>
                                                        <td><input type="text" id="wpvacancylab_user" name="wpvacancylab_user" value="<?php echo esc_html($wpvacancylab_user); ?>" class="all-options" /></td>
                                                </tr>
                                                <tr valign="top">
                                                        <td scope="row"><label for="wpvacancylab_password"><?php esc_attr_e('Password', 'wp_admin_style'); ?></label></td>
                                                        <td><input type="password" id="wpvacancylab_password" name="wpvacancylab_password" value="<?php echo esc_html($wpvacancylab_password); ?>" class="all-options" /></td>
                                                </tr>
                                        </table>
                                    </div>
				</div>

			</div>
			<!-- /col-wrap -->

		</div>
		<!-- /col-right -->

		<div id="col-left" style="width:50%">

			<div class="col-wrap">
				<h2 class="hndle"><span><?php esc_attr_e( 'Vacancies Settings', 'wp_admin_style' ); ?></span></h2>
				<div class="inside">
                                    <div class="postbox">
					<table class="form-table">
                                                <tr valign="top">
                                                        <td scope="row" style="width:200px"><label for="wpvacancylab_vac_pageid"><?php esc_attr_e('Vacancies Listing Page', 'wp_admin_style'); ?></label></td>
                                                        <td><?php wp_dropdown_pages(array('selected' => intval($wpvacancylab_vac_pageid),'name' => 'wpvacancylab_vac_pageid', 'show_option_none' => '[none]', 'option_none_value' => '')); ?></td>
                                                </tr>
                                                <tr valign="top" class="alternate">
                                                        <td scope="row"><label for="wpvacancylab_s_text"><?php esc_attr_e('Search on Text', 'wp_admin_style'); ?></label></td>
                                                        <td><input type="checkbox" value="1" name="wpvacancylab_s_text" id="wpvacancylab_s_text" <?php checked( $wpvacancylab_s_text, '1', TRUE ); ?> /></td>
                                                </tr>
                                                <tr valign="top">
                                                        <td scope="row"><label for="wpvacancylab_s_location"><?php esc_attr_e('Search on Location', 'wp_admin_style'); ?></label></td>
                                                        <td><input type="checkbox" value="1" name="wpvacancylab_s_location" id="wpvacancylab_s_location" <?php checked( $wpvacancylab_s_location, '1', TRUE ); ?> /></td>
                                                </tr>
                                                <tr valign="top" class="alternate">
                                                        <td scope="row"><label for="wpvacancylab_s_position"><?php esc_attr_e('Search on Position', 'wp_admin_style'); ?></label></td>
                                                        <td><input type="checkbox" value="1" name="wpvacancylab_s_position" id="wpvacancylab_s_position" <?php checked( $wpvacancylab_s_position, '1', TRUE ); ?> /></td>
                                                </tr>
                                        </table>
                                    </div>
				</div>
                                <p>&nbsp</p>
				<h2 class="hndle"><span><?php esc_attr_e( 'Candidates Settings', 'wp_admin_style' ); ?></span></h2>
				<div class="inside">
                                    <div class="postbox">
					<table class="form-table">
                                                <tr valign="top">
                                                        <td scope="row" style="width:200px"><label for="wpvacancylab_can_pageid"><?php esc_attr_e('Candidate Application Page', 'wp_admin_style'); ?></label></td>
                                                        <td><?php wp_dropdown_pages(array('selected' => intval($wpvacancylab_can_pageid),'name' => 'wpvacancylab_can_pageid', 'show_option_none' => '[none]', 'option_none_value' => '')); ?></td>
                                                </tr>
                                        </table>
                                    </div>
				</div>
                                <p>
                                    <input class="button-primary" type="submit" name="Save" value="Save" />
                                </p>
			</div>
			<!-- /col-wrap -->

		</div>
		<!-- /col-left -->
                <?php wp_nonce_field( 'save_wpvacancylab_options', '_wpvacancylab_options' ); ?>
            </form>
	</div>
	<!-- /col-container -->

</div> <!-- .wrap -->
