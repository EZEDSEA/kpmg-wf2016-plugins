<?php

/**
 * File: ajax.php
 * Description of ajax
 *
 * Author: edward <http://ojambo.com>
 * Copyright: 2016  
 * Created : 2016-09-03 7:54:51 PM
 * Last Modified : 2016-09-03T23:54:51Z
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Register with action 'wp_ajax_my_action' to call a specific action for Ajax
add_action( 'wp_ajax_nopriv_kpmgEmployeeCheckAJAX', 'kpmgEmployeeCheckAJAX' );
add_action( 'wp_ajax_kpmgEmployeeCheckAJAX', 'kpmgEmployeeCheckAJAX' );

// Register with action 'wp_ajax_my_action' to call a specific action for Ajax
add_action( 'wp_ajax_nopriv_kpmgEmployeeForGroupCheckAJAX', 'kpmgEmployeeForGroupCheckAJAX' );
add_action( 'wp_ajax_kpmgEmployeeForGroupCheckAJAX', 'kpmgEmployeeForGroupCheckAJAX' );

	// Check KPMG Employee Check Ajax
	function kpmgEmployeeCheckAJAX()
	{
		global $wpdb;
		
		$emailAddress = $_POST['email_address'];
		
		//$emailAddressCheck = filter_var($emailAddress, FILTER_SANITIZE_EMAIL);
		$emailAddressCheck = filter_var($emailAddress, FILTER_SANITIZE_STRING);
		$dataArr = array();
		// Make Sure Email Address is Valid For Auto-Suggestion 2 Characters
		//if( !filter_var($emailAddressCheck, FILTER_VALIDATE_EMAIL) )
		if( strlen(trim($emailAddressCheck)) < 2 )
		{
			$dataArr['error'] = "The email address is invalid";
		}
		else
		{
			$employeeResults = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT emp.employee_email_address FROM {$wpdb->kpmg_employees} emp WHERE emp.employee_email_address LIKE %s ORDER BY emp.employee_email_address ASC"
					, $emailAddressCheck.'%'
				)
				, ARRAY_A
			); 
			
			if( count($employeeResults) > 0 )
			{
				// Get Data
				$dataArr = $employeeResults;

			}
			else
			{
				$dataArr['error'] = "The email address is not allowed";
			}
		}

		echo json_encode($dataArr);
		die();  // Ajax Call must die to avoid trailing 0 to response;
	}
	

	// Check KPMG Employee For Group Check Ajax
	function kpmgEmployeeForGroupCheckAJAX()
	{
		global $wpdb;
		
		$emailAddress = $_POST['email_address'];
		
		//$emailAddressCheck = filter_var($emailAddress, FILTER_SANITIZE_EMAIL);
		$emailAddressCheck = filter_var($emailAddress, FILTER_SANITIZE_STRING);
		$dataArr = array();
		// Make Sure Email Address is Valid For Auto-Suggestion 2 Characters
		//if( !filter_var($emailAddressCheck, FILTER_VALIDATE_EMAIL) )
		if( strlen(trim($emailAddressCheck)) < 2 )
		{
			$dataArr['error'] = "The email address is invalid";
		}
		else
		{
			$employeeResults = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT det.*, emp.*
						FROM {$wpdb->kpmg_employees} emp 
						LEFT JOIN {$wpdb->kpmg_registration_details} det ON det.employee_email_address = emp.employee_email_address
						WHERE emp.employee_email_address LIKE %s ORDER BY emp.employee_email_address ASC"
					, $emailAddressCheck.'%'
				)
				, ARRAY_A
			); 
			
			if( count($employeeResults) > 0 )
			{
				// Get Data
				$dataArr = $employeeResults;

			}
			else
			{
				$dataArr['error'] = "The email address is not allowed";
			}
		}

		echo json_encode($dataArr);
		die();  // Ajax Call must die to avoid trailing 0 to response;
	}
	