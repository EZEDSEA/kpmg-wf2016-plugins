<?php

/**
 * File: functions.php
 * Description of functions
 *
 * Author: edward <http://ojambo.com>
 * Copyright: 2016  
 * Created : 2016-08-26 1:10:46 PM
 * Last Modified : 2016-08-26T17:10:46Z
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Registers
// Start Session
add_action('init','startKPMGSession');

// Register User Roles
add_action( 'after_setup_theme', 'createKPMGUserRoles' );
// Register Remove Admin Bar For Specific User Roles
add_action( 'after_setup_theme', 'removeKPMGAdminBar' );

// Register front end CSS and JavaScript
add_action( 'wp_enqueue_scripts', 'addKPMGScriptsStyles' );	

// Restrict Content TO Logged In Users
add_action( 'template_redirect', 'redirectForNonLogin' );

// Run Front-end Auto Login First To Prevent Headers Already Sent Errors
//add_action('wp_loaded','kpmg_autoLogin');

// Start KPMG Sesssion
function startKPMGSession()
{
	if( !session_id() )
	{
		session_start();
	}
}
	
// Create New User Roles
function createKPMGUserRoles()
{
	// Add Winterfest Manager
	add_role( KPMGWF_AdminRole, 'Winterfest Admin', array(KPMGWF_AdminRole) );

	// Add Winterfest Employee
	add_role( KPMGWF_EmployeeRole, 'Winterfest Employee', array(KPMGWF_EmployeeRole) );
}

// Remove Admin Bar
function removeKPMGAdminBar()
{
	// Remove Admin Bar For Winterfest Manager
	if ( current_user_can(KPMGWF_AdminRole) && !is_admin() )
	{
		show_admin_bar(false);
	}

	// Remove Admin Bar For Winterfest Employee
	if ( current_user_can(KPMGWF_EmployeeRole) && !is_admin() )
	{
		show_admin_bar(false);
	}
}

// Add Scripts And Styles
function addKPMGScriptsStyles()
{
	global $post;
	
	// CSS Stylesheet in Head
	wp_enqueue_style( 'kpmg-winterfest.css', KPMGWF_URL."/css/kpmg-winterfest.css"  );
	// Javascript In Footer
	wp_register_script( 'datetimepicker_css.js', KPMGWF_URL."/js/datetimepicker_css.js", array(), '1.0.0', TRUE  );
	wp_register_script( 'kpmg-admin-winterfest.js', KPMGWF_URL."/js/kpmg-admin-winterfest.js", array('jquery'), '1.0.0', TRUE  );
	wp_enqueue_script( 'kpmg-winterfest.js', KPMGWF_URL."/js/kpmg-winterfest.js", array('jquery'), '1.0.0', TRUE  );

	// Localize Ajax URL Variables To Specific JavaScript Files
	wp_localize_script( 'kpmg-winterfest.js', 'ajaxregisteremployeecheck', 
		array('ajaxurl' => admin_url('admin-ajax.php'))); 
	
	// Specific Pages
    if( is_page() || is_single() )
    {
        switch($post->post_name) 
        {
            case 'admin':
                wp_enqueue_script('datetimepicker_css.js');
                wp_enqueue_script('kpmg-admin-winterfest.js');
                break;
        }
    } 	
}

// REdirect If Not Logged In
function redirectForNonLogin()
{
			// Page Array
		$pageArr = array(
			'thank-you',
			'thanks',
			'group',
			'my-info',
			'adminstration',
			'admin'
		);
	if ( is_page($pageArr) && ! is_user_logged_in() ) 
	{

		wp_redirect( KPMGWF_Site, 303 );  // Allow Response Cache Only
		  exit;
    }
}

// Generate Employee User Role For Database Insert & Update
function kpmg_generatedEmployeeRole($useremail)
{
	$employeeRole = KPMGWF_EmployeeRole; // Employee Role
	$employeListInfo = kpmg_getEmployeeListInfo($useremail);
	if (isset($employeListInfo['make_admin']))
	{
		if ( strtolower($employeListInfo['make_admin']) == 'yes')
		{
			$employeeRole = KPMGWF_AdminRole; // Admin Role
		}
	}
	
	return $employeeRole;
}

// Generate Employee User Data For Database Insert & Update
function kpmg_generateEmployeeData($dataArr)
{
	$employeeEmail = isset($dataArr['email_address']) ? $dataArr['email_address'] : ((isset($dataArr['employee_email_address'])) ? $dataArr['employee_email_address'] : $dataArr['user_email']);
	$userdata = array (
		'user_login' => isset($dataArr['email_address']) ? $dataArr['email_address'] : ((isset($dataArr['employee_email_address'])) ? $dataArr['employee_email_address'] : $dataArr['user_login']),
		'user_email' => isset($dataArr['email_address']) ? $dataArr['email_address'] : ((isset($dataArr['employee_email_address'])) ? $dataArr['employee_email_address'] : $dataArr['user_email']),
		'user_pass' => isset($dataArr['password_one']) ? $dataArr['password_one'] : $dataArr['user_pass'],
		'first_name' => isset($dataArr['employee_first_name']) ? $dataArr['employee_first_name'] : $dataArr['first_name'],
		'last_name' => isset($dataArr['employee_last_name']) ? $dataArr['employee_last_name'] : $dataArr['last_name'],
		'role' => kpmg_generatedEmployeeRole($employeeEmail)
	);


	return $userdata;
}

// Generate Registration Data For Database Insert & Update
function kpmg_generateRegistrationData($dataArr)
{
	// Reserve Some Data
	$dataArr['entertainment_only'] =  isset($dataArr['attend_entertainment_only']) ? $dataArr['attend_entertainment_only'] : $dataArr['entertainment_only'];
	$dataArr['dietary_requirements'] =  isset($dataArr['employee_dietary_requirements']) ? $dataArr['employee_dietary_requirements'] : $dataArr['dietary_requirements'];
	$dataArr['dietary_requirements_other'] =  isset($dataArr['employee_dietary_requirements_other']) ? $dataArr['employee_dietary_requirements_other'] : $dataArr['dietary_requirements_other'];
	$dataArr['first_name_guest'] =  isset($dataArr['guest_first_name']) ? $dataArr['guest_first_name'] : $dataArr['first_name_guest'];
	$dataArr['last_name_guest'] =  isset($dataArr['guest_last_name']) ? $dataArr['guest_last_name'] : $dataArr['last_name_guest'];
	$dataArr['dietary_requirements_guest'] =  isset($dataArr['guest_dietary_requirements']) ? $dataArr['guest_dietary_requirements'] : $dataArr['dietary_requirements_guest'];
	$dataArr['dietary_requirements_other_guest'] =  isset($dataArr['guest_dietary_requirements_other']) ? $dataArr['guest_dietary_requirements_other'] : $dataArr['dietary_requirements_other_guest'];
	// ((isset($dataArr['dietary_requirements_other_guest'])) ? $dataArr['dietary_requirements_other_guest'] : "")
	
	$registrationdata = array (
		'user_id' => isset($dataArr['user_id']) ? $dataArr['user_id'] : 0,
		'employee_email_address' => isset($dataArr['email_address']) ? $dataArr['email_address'] :((isset($dataArr['employee_email_address'])) ? $dataArr['employee_email_address'] : $dataArr['user_email']),
		'employee_first_name' => isset($dataArr['employee_first_name']) ? $dataArr['employee_first_name'] : $dataArr['first_name'],
		'employee_last_name' => isset($dataArr['employee_last_name']) ? $dataArr['employee_last_name'] : $dataArr['last_name'], 
		'attend_entertainment_only' => isset($dataArr['attend_entertainment_only']) ? $dataArr['attend_entertainment_only'] : $dataArr['entertainment_only'], 
		'has_guest' => strtolower($dataArr['has_guest']), 
		'employee_status' => strtolower($dataArr['employee_status']), 
		'employee_dietary_requirements' => ($dataArr['entertainment_only'] !=1 && isset($dataArr['dietary_requirements']) ) ? $dataArr['dietary_requirements'] : "",
		'employee_dietary_requirements_other' => ($dataArr['entertainment_only'] !=1 && isset($dataArr['dietary_requirements_other']) ) ? $dataArr['dietary_requirements_other'] : "", 
		'guest_first_name' => (strtolower($dataArr['has_guest']) == "yes" && isset($dataArr['first_name_guest']) ) ? $dataArr['first_name_guest'] : "",
		'guest_last_name' => (strtolower($dataArr['has_guest']) == "yes" && isset($dataArr['last_name_guest']) ) ? $dataArr['last_name_guest'] : "", 
		'guest_dietary_requirements' => (strtolower($dataArr['has_guest']) == "yes" && $dataArr['entertainment_only'] !=1 && isset($dataArr['dietary_requirements_guest']) ) ? $dataArr['dietary_requirements_guest'] : "",
		'guest_dietary_requirements_other' => (strtolower($dataArr['has_guest']) == "yes" &&  $dataArr['entertainment_only'] !=1 && isset($dataArr['dietary_requirements_other_guest']) ) ? $dataArr['dietary_requirements_other_guest'] : ""
	);

	return $registrationdata;
}

// Generate Field Types For Database Insert & Update
function kpmg_generateFieldTypes($dataArr)
{
	$fieldTypesArr = array();

	foreach( $dataArr as $key => $value )
	{
		if ( is_int($value) )
		{
			// Integer Whole Number
			$fieldTypesArr[] = '%d';
		}
		elseif ( is_float($value) )
		{
			// Float
			$fieldTypesArr[] = '%f';
		}
		else
		{
			// Assume String
			$fieldTypesArr[] = '%s';
		}
	}

	return $fieldTypesArr;
}

// Generate CSV String
function kpmg_generateCSVString($data)
{
		// Create File Pointer Connected To Output Stream
		$output = fopen("php://temp", "w");

		$fieldsArr = array();
		$counter = 0;
		foreach ( $data as $row => $dataArr )
		{
			foreach ($dataArr as $key => $value)
			{
				// Condition to generate headers
				if ( $counter == 0 )
				{
					// Get Field Names
					$fieldsArr[] = $key;
				}
			}

			// Condition to generate headers
			if ( $counter == 0 )
			{
				// Output Column Headings
				fputcsv($output, $fieldsArr);
			}

			// Ouput Data Values
			fputcsv($output, $dataArr);

			$counter++; // Increment
		}

		// Place Stream Pointer At Beginning
		rewind($output);

		// Get File Contents
		$csvFile = stream_get_contents($output);

		// Close File Pointer
		//fclose($output);

		return $csvFile;
}

// Generate Human Label
function kpmg_generateHumanLabel($field)
{
	$human_label = strtolower($field);  // Lowercase
	$human_label = str_replace('_', ' ',$human_label);  // Replace underscore with space.
	$human_label = ucwords($human_label);  // Uppercase first character per word.
	return $human_label;
}

// Get Database Table Columns
function kpmg_getDatabaseTableColumns($table)
{
	global $wpdb;
	$result = $wpdb->get_results(
		" SHOW COLUMNS FROM {$table}"
		, ARRAY_A
	);
	if (count($result) > 0 )
	{
		return $result;
	}
	else
	{
		return false;
	}
}

// Check if Email On Employee List
function kpmg_emailOnEmployeeList($useremail)
{
	global $wpdb;
	$employee_email_address = strtolower($useremail);
	$result = $wpdb->get_var(
		$wpdb->prepare(
		" SELECT count(employee_email_address) 
			FROM {$wpdb->prefix}kpmg_employees
			WHERE LOWER(employee_email_address) = %s"
		, $employee_email_address
		)
	);
	if ($result > 0 )
	{
		return true;
	}
	else
	{
		return false;
	}
}

// Employee List Information
function kpmg_getEmployeeListInfo($useremail)
{
	global $wpdb;
	$employee_email_address = strtolower($useremail);
	$result = $wpdb->get_results(
		$wpdb->prepare(
		" SELECT * 
			FROM {$wpdb->kpmg_employees}
			WHERE LOWER(employee_email_address) = %s LIMIT 1"
		, $employee_email_address
		)
		, ARRAY_A
	);
	if ( count($result) > 0  )
	{
		return $result[0];  // First One only
	}
	else
	{
		return false;
	}
}

// Get Email On Employee List
function kpmg_getemailStatusOnEmployeeList($useremail)
{
	global $wpdb;
	$employee_email_address = strtolower($useremail);
	$result = $wpdb->get_results(
		$wpdb->prepare(
		" SELECT * 
			FROM {$wpdb->kpmg_employees}
			WHERE LOWER(employee_email_address) = %s LIMIT 1"
		, $employee_email_address
		)
		, ARRAY_A
	);
	if ( count($result) > 0 )
	{
		return $result[0];  // First One only
	}
	else
	{
		return false;
	}
}

// Get Employee Details
function kpmg_getEmployeeDetailsByUserID($user_id)
{
	global $wpdb;
	$result = $wpdb->get_results(
		$wpdb->prepare(
		" SELECT * 
			FROM {$wpdb->kpmg_registration_details}
			WHERE user_id = %d LIMIT 1"
		, $user_id
		)
		, ARRAY_A
	);
	if ( count($result) > 0 )
	{
		return $result[0];  // First One only
	}
	else
	{
		return false;
	}
}

// Get Employee List And Details
function kpmg_getEmployeeListAndDetailsByEmail($useremail)
{
	global $wpdb;
	$employee_email_address = strtolower($useremail);
	$result = $wpdb->get_results(
		$wpdb->prepare(
		" SELECT det.*, det.employee_status AS registration_status, emp.* 
			FROM {$wpdb->kpmg_employees} emp
			LEFT JOIN {$wpdb->kpmg_registration_details} det ON det.employee_email_address = emp.employee_email_address
			WHERE LOWER(emp.employee_email_address) = %s LIMIT 1"
		, $employee_email_address
		)
		, ARRAY_A
	);

	if ( count($result) > 0 )
	{
		return $result[0];  // First One only
	}
	else
	{
		return false;
	}
}

// Get Employee Group List By Email
function kpmg_getEmployeeGroupListByEmail($useremail)
{
	global $wpdb;
	$return_arr = array();
	$employee_email_address = strtolower($useremail);
	$result = $wpdb->get_results(
		$wpdb->prepare(
		" SELECT det.*, det.employee_status AS registration_status, emp.*, @rownum := @rownum + 1 as row_number
			, CASE WHEN LOWER(det.has_guest) = 'yes' THEN @seatnum := @seatnum + 2 ELSE @seatnum := @seatnum + 1 END AS seat_number
			FROM wp_kpmg_registration_details det
			INNER JOIN wp_kpmg_employees emp ON emp.employee_email_address = det.employee_email_address
			INNER JOIN wp_kpmg_registration_details det2 ON det2.group_id = det.group_id
			CROSS JOIN (SELECT @rownum := 0) r
			CROSS JOIN (SELECT @seatnum := 0) s
			WHERE det2.employee_email_address = %s
				AND det.group_id > 0 
			ORDER BY det.group_id ASC, det.group_seat ASC
			"
		, $employee_email_address
		)
		, ARRAY_A
	);

	if ( count($result) > 0 )
	{
		foreach ($result as $key => $row)
		{
			$return_arr[$key] = $row;
			$return_arr[$key]['host_first_name'] = $result[0]['employee_first_name'];
			$return_arr[$key]['host_last_name'] = $result[0]['employee_last_name'];
			$return_arr[$key]['host_email_address'] = $result[0]['employee_email_address'];
		}
		return $return_arr;  // All Results
	}
	else
	{
		return false;
	}
}

// Get Employee Group List Details By Email
function kpmg_getEmployeeGroupListDetailsByEmail($useremail)
{
	global $wpdb;
	$return_arr = array();
	$employee_email_address = strtolower($useremail);
	$result = $wpdb->get_results(
		$wpdb->prepare(
		" SELECT grp.*, det.*, det.employee_status AS registration_status, emp.*	
			FROM {$wpdb->kpmg_group_seats} grp
			LEFT JOIN {$wpdb->kpmg_employees} emp ON emp.employee_email_address = grp.employee_email_address
			LEFT JOIN {$wpdb->kpmg_registration_details} det ON det.employee_email_address = emp.employee_email_address
			WHERE grp.group_id IN 
				( SELECT group_id FROM {$wpdb->kpmg_group_seats} WHERE employee_email_address = %s )
			"
		, $employee_email_address
		)
		, ARRAY_A
	);

	if ( count($result) > 0 )
	{
		foreach ($result as $key => $row)
		{
			$return_arr[$key] = $row;
			$return_arr[$key]['host_first_name'] = $result[0]['employee_first_name'];
			$return_arr[$key]['host_last_name'] = $result[0]['employee_last_name'];
			$return_arr[$key]['host_email_address'] = $result[0]['host_email_address'];
		}
		return $return_arr;  // All Results
	}
	else
	{
		return false;
	}
}

// Get Current Page Slug
function kpmg_getCurrentPageSlug()
{
	$current_page = sanitize_post( $GLOBALS['wp_the_query']->get_queried_object() );
	// Get the page slug
	$slug = $current_page->post_name;
	return $slug;
}

// Check If In Seated Group
function kpmg_reserveGroupSeat($useremail)
{
	global $wpdb;
	$employee_email_address = strtolower($useremail);
	$result = $wpdb->get_results(
		$wpdb->prepare(
		" SELECT * FROM {$wpdb->kpmg_table_seats}
			WHERE employee_email_address = %s LIMIT 1"	
		, $employee_email_address
		)
		, ARRAY_A
	);
	if( count($result) > 0 ) 
	{
		return $result[0];  // First One only
	}

	return false;

}	

// AutoLogin
function kpmg_autoLogin($useremail)
{
	// Make Sure Not Already Logged In
	if ( !is_user_logged_in() )
	{
		$user = get_user_by('email', $useremail);
		$userlogin = $useremail;

		// Login Without Redirecting
		if ( !is_wp_error($user)  )
		{
			wp_clear_auth_cookie();
			wp_set_current_user($user->ID);
			wp_set_auth_cookie($user->ID);
			do_action('wp_login', $userlogin);
		}
	}
}

// Generate Select Options
function kpmg_generateSelectOptions($data, $selected)
{
	$selectOptions = "";
	foreach ($data as $key => $value)
	{
		$selectOptions .= "<option value=\"{$value}\"";
		if ($value == $selected )
		{
			$selectOptions .= " selected ";
		}
		$selectOptions .= ">{$value}</option>";
	}

	return $selectOptions;
}

// Generate Key Select Options
function kpmg_generateKeySelectOptions($data, $selected)
{
	$selectOptions = "";
	foreach ($data as $key => $value)
	{
		$selectOptions .= "<option value=\"{$value}\"";
		if ($value == $selected )
		{
			$selectOptions .= " selected ";
		}
		$selectOptions .= ">{$key}</option>";
	}

	return $selectOptions;
}

// Entertainment Only Options
function kpmg_entertainnmentOptionsData()
{
	$dataArr = array(
		'Entertainment only' => 1,
		'Dinner and entertainment' => 0
	);

	return $dataArr;
}

// Yes No Options
function kpmg_yesNoOptionsData()
{
	$dataArr = array(
		'Yes',
		'No'
	);

	return $dataArr;
}

// Dietary Requirement Options
function kpmg_dietaryRequirementOptionsData()
{
	$dataArr = array(
		'Halaal',
		'Kosher',
		'Gluten Free',
		'Lactose Allergy',
		'Nut Allergy',
		'Vegan',
		'Vegetarian',
		'Seafood Allergy',
		'Other'
	);

	return $dataArr;
}


// Generate Reserve Group Seats
function kpmg_generateReserveGroupSeats($reserveData)
{
	// Variables
	$reserveAGroupInfoArr = array();
	
	for ($i=0; $i<10; $i++)
	{
		// Host
		if ( isset($_POST['reserveagroup']['group_seat'][$i]['host_email_address']) )
		{
			$reserveAGroupInfoArr['group_seat'][$i]['host_email_address'] = trim($_POST['reserveagroup']['group_seat'][0]['host_email_address']);
		}
		elseif ( !isset($_POST['reserveagroup']['group_seat']) && isset($reserveData[0]['employee_email_address']) )
		{
			$reserveAGroupInfoArr['group_seat'][$i]['host_email_address'] = trim($reserveData[0]['employee_email_address']);
		}
		else
		{
			$reserveAGroupInfoArr['group_seat'][$i]['host_email_address'] = "";
		}

		// Employee
		if ( isset($_POST['reserveagroup']['group_seat'][$i]['email_address']) )
		{
			$reserveAGroupInfoArr['group_seat'][$i]['email_address'] = trim($_POST['reserveagroup']['group_seat'][$i]['email_address']);
		}
		elseif ( !isset($_POST['reserveagroup']['group_seat']) && isset($reserveData[$i]['employee_email_address']) )
		{
			$reserveAGroupInfoArr['group_seat'][$i]['email_address'] = trim($reserveData[$i]['employee_email_address']);
		}
		else
		{
			$reserveAGroupInfoArr['group_seat'][$i]['email_address'] = "";
		}

		// Employee Guest
		if ( isset($_POST['reserveagroup']['group_seat'][$i]['is_guest']) )
		{
			$reserveAGroupInfoArr['group_seat'][$i]['is_guest'] = trim($_POST['reserveagroup']['group_seat'][$i]['is_guest']);
		}
		elseif ( !isset($_POST['reserveagroup']['group_seat'])  && isset($reserveData[$i]['has_guest']) && $i > 0 )
		{
			$reserveAGroupInfoArr['group_seat'][$i]['is_guest'] = ( trim($reserveData[$i]['has_guest']) == "yes" ) ? 1 : 0;
		}
		else
		{
			$reserveAGroupInfoArr['group_seat'][$i]['is_guest'] = 0;
		}						

		if ($reserveAGroupInfoArr['group_seat'][$i]['email_address'] == "")
		{
			unset($reserveAGroupInfoArr['group_seat'][$i]);  // Remove empty rows
		}
	}
			// Return
		return $reserveAGroupInfoArr;
}

// Generate Draggable Reserve Group Inputs
function kpmg_generateDragReserveGroupInputs($reserveData, $variable)
{
	$formInputs = "";
	$formCounter = 0;
	$formInputEmployeeID = 0;
	$maximumGroupSeats = KPMGWF_MaxGroupSeats;
	$seatsRemaining = $maximumGroupSeats;

	foreach ( $reserveData as $key => $row )
	{
		$formInputEmployeeClick = "";
		$formInputEmployeeReadOnly = "";
		$formInputGuestClick = "";
		$formInputGuestReadOnly = "";
		$formCounter++; // Increment
		$formCounterPrevious = ($formCounter - 1) ;
		$seatsRemaining--; // Decrement
		
		if ( strtolower($row['has_guest']) == "yes" )
		{
			$seatsRemaining--; // Decrement
		}

		$hostEmailAddress = $reserveData[0]['employee_email_address'];
		$employeeEmailAddress = $reserveData[$key]['employee_email_address'];
		$isHost = ($hostEmailAddress == $employeeEmailAddress) ? 1 : 0;
		$classIsHost = ($isHost == 1) ? "is_host" : "";
		$employeeName = "{$row['employee_first_name']} {$row['employee_last_name']}";
		$employeeGuestName = "{$row['guest_first_name']} {$row['guest_last_name']}";
		$employeeGuestDisplayEmail = (strtolower($row['has_guest'] == "yes")) ? "Guest" : "";
		$linkRemoveEmployee = ( ($isHost != 1) ) ? "<a href='#' data-remove='added_field{$formCounter}' class='remove_field remove_group_fields'>Remove</a>" : "";
		$classaddedField = "added_field{$formCounter}";
		
		if ( !empty($employeeEmailAddress) )
		{
			// Cannot Edit 
			$formInputEmployeeReadOnly = " readonly ";
		}

		$formInputs .= ($formCounter == 1) ? "<div class='host-area'>" : ""; // Host Area
		$formInputs .= <<<OJAMBO
			<div id="reserveagroupparent{$formCounter}" class="{$classIsHost} {$classaddedField} draggable_group_item" data-is-host=""  draggable="true">
				<span class="display-name">{$employeeName}</span>
				<span class="display-email">{$employeeEmailAddress}</span>
				<span class="guest-name">{$employeeGuestName}</span>
				<span class="guest-email">{$employeeGuestDisplayEmail}</span>
				<input type="hidden" id="group_seat_{$key}_host_email_address" class="host_email_address" name="{$variable}[{$key}][host_email_address]" value="{$hostEmailAddress}" />
				<input type="hidden" class="email_address" id="group_seat_{$key}_display_name" name="{$variable}[{$key}][email_address]" value="{$employeeEmailAddress}" {$formInputEmployeeReadOnly} placeholder="Enter Email Address" data-ajax="group_seat_{$key}_ajax-results-area" data-seatnum="{$key}" />
				<div class="results" id="group_seat_{$key}_ajax-results-area"></div>
				{$linkRemoveEmployee}
			</div>
OJAMBO;
		$formInputs .= ($formCounter == 1) ? "<span class='host'>HOST</span>" : "";  // Host Only
		$formInputs .= ($formCounter == 1) ? "</div>" : "";  // Host Only

	}

	// Add People Area
	$formInputs .= "<div class='reserveagroupparent_add_area'></div>";
	// Count Seats Remaining
	$formInputs .= "<p class='remaining-people'>You can add <span id='remaining-group-seats' data-seatsremaining='{$seatsRemaining}' data-seatlast='{$formCounter}'>{$seatsRemaining}</span> more people to this group</p>";
	
	return $formInputs;
}

// Generate Reserve Group Inputs
function kpmg_generateReserveGroupInputs($reserveData)
{
	$formInputs = "";
	$formCounter = 0;
	$formInputEmployeeID = 0;
	$maximumGroupSeats = KPMGWF_MaxGroupSeats;
	$seatsRemaining = $maximumGroupSeats;

	foreach ( $reserveData['group_seat'] as $key => $row )
	{
		$formInputEmployeeClick = "";
		$formInputEmployeeReadOnly = "";
		$formInputGuestClick = "";
		$formInputGuestReadOnly = "";
		$formCounter++; // Increment
		$formCounterPrevious = ($formCounter - 1) ;
		$seatsRemaining--; // Decrement

		$hostEmailAddress = $reserveData['group_seat'][0]['email_address'];
		$employeeEmailAddress = $reserveData['group_seat'][$key]['email_address'];
		$isHost = ($hostEmailAddress == $employeeEmailAddress) ? 1 : 0;
		$isGuest = $reserveData['group_seat'][$key]['is_guest'];
		$classIsHost = ($isHost == 1) ? "is_host" : "";
		$classIsGuest = ($isGuest == 1) ? "is_guest" : "";
		$classIsEmployee = ($isGuest == 1) ? "" : "is_employee";
		$employeeDetails = kpmg_getEmployeeListAndDetailsByEmail($employeeEmailAddress);	
		$employeeName = "{$employeeDetails['employee_first_name']} {$employeeDetails['employee_last_name']}";
		$employeeGuestName = "{$employeeDetails['guest_first_name']} {$employeeDetails['guest_last_name']}";
		$employeeDisplayName = ($isGuest) ? $employeeGuestName : $employeeName;
		$employeeDisplayEmail = ($isGuest) ? "Guest" : $employeeEmailAddress;
		$linkRemoveEmployee = ( ($isHost != 1 && $isGuest == 0 )|| ($isGuest == 0 && $formCounter > 2) ) ? "<a href='#' data-remove='added_field{$formCounter}' class='remove_field remove_group_fields'>Remove</a>" : "";
		$classaddedField = ($isGuest) ? "added_field{$formCounter} added_field{$formCounterPrevious}" : "added_field{$formCounter}";
		
		/*$employeeFirstName = $reserveData['group_seat'][$key]['first_name'];
		$employeeLastName = $reserveData['group_seat'][$key]['last_name'];
		$displayEmployeeName = trim("{$employeeFirstName} {$employeeLastName}");
		$displayEmployeeName = trim($displayEmployeeName);
		if ( !empty($displayEmployeeName) )
		{
			$formInputEmployeeReadOnly = " readonly ";
			if ( $key == 0 )
			{
				$formInputEmployeeClick = "";
			}
		}*/
		if ( !empty($employeeEmailAddress) )
		{
			// Cannot Edit 
			$formInputEmployeeReadOnly = " readonly ";
		}


		$formInputs .= <<<OJAMBO
			<div id="reserveagroupparent{$formCounter}" class="{$classIsHost} {$classIsEmployee} {$classIsGuest} {$classaddedField}" data-is-host="" data-is-guest="{$isGuest}">
				<span class="display-name">{$employeeDisplayName}</span>
				<span class="display-email">{$employeeDisplayEmail}</span>
				<input type="hidden" id="group_seat_{$key}_host_email_address" name="reserveagroup[group_seat][{$key}][host_email_address]" value="{$hostEmailAddress}" />
				<input type="hidden" id="group_seat_{$key}_is_guest" name="reserveagroup[group_seat][{$key}][is_guest]" value="{$isGuest}" />
				<input type="hidden" class="email_address" id="group_seat_{$key}_display_name" name="reserveagroup[group_seat][{$key}][email_address]" value="{$employeeEmailAddress}" {$formInputEmployeeReadOnly} placeholder="Enter Email Address" data-ajax="group_seat_{$key}_ajax-results-area" data-seatnum="{$key}" />
				<div class="results" id="group_seat_{$key}_ajax-results-area"></div>
				{$linkRemoveEmployee}
			</div>
OJAMBO;

	}

	// Add People Area
	$formInputs .= "<div class='reserveagroupparent_add_area'></div>";
	// Count Seats Remaining
	$formInputs .= "<p class='remaining-people'>You can add <span id='remaining-group-seats' data-seatsremaining='{$seatsRemaining}' data-seatlast='{$formCounter}'>{$seatsRemaining}</span> more people to this group</p>";
	
	return $formInputs;
}
	
// Get Registration Cutoff Information
function kpmg_getRegistrationCutoff()
{
	global $wpdb;

	$result = $wpdb->get_results(
		$wpdb->prepare(
		" SELECT * FROM {$wpdb->kpmg_registration_cutoff}
			WHERE registration_cuttoff_id = %d LIMIT 1"	
		, 1
		)
		, ARRAY_A
	);
	if( count($result) > 0 ) 
	{
		return $result[0];  // First One only
	}

	return false;
}

// Generate Registration Cutoff
function kpmg_generateRegistrationCutoff($data)
{
	$formInputs = "";
	foreach ($data as $key => $row)
	{
		$inputType = kpmg_getDataType($key);
		$humanLabel = kpmg_generateHumanLabel($key);
		$calendarPickIcon = KPMFWF_CalendarPickIcon;
		$formInputs .= ($inputType == "hidden") ? "" : "<label for='{$key}'>{$humanLabel}</label>";
		$input_id = ($inputType == "date") ? $key : "";
		$input_placeholder = ($inputType == "date") ? "YYYY-MM-DD HH:MM:SS" : "";
		$formInputs .= "<input id='{$input_id}' placeholder='{$input_placeholder}' type='{$inputType}' name='{$key}' value='{$row}' />";
		$formInputs .= ($inputType == "date") ? "<img class='calendar-picker-icon' src='{$calendarPickIcon}' onclick=\"javascript:NewCssCal('{$input_id}', 'yyyyMMdd', 'arrow', true, '24', true, 'future');\" />" : "";
	}

	return $formInputs;
}

// Get Data Type
function kpmg_getDataType($input)
{
	$dataType = "";
	$arrTypes = array (
		'registration_limit' => 'number',
		'waiting_list_limit' => 'number',
		'table_limit' => 'number',
		'table_seat_limit' => 'number',
		'registration_end_date' => 'date',
		'registration_cuttoff_id' => 'hidden',
	);

	if ( array_key_exists($input, $arrTypes) )
	{
		$dataType = $arrTypes[$input];
	}
	else
	{
		$dataType = "text";
	}
	return $dataType;
}