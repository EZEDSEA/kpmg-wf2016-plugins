<?php

/**
 * File: data.php
 * Description of data
 *
 * Author: edward <http://ojambo.com>
 * Copyright: 2016  
 * Created : 2016-08-26 1:44:27 PM
 * Last Modified : 2016-08-26T17:44:27Z
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

add_action( 'init', 'createKpmgWinterfestTables' );

	// Create Database Table Names
	function createKpmgWinterfestTables()
	{
		global $wpdb;
		global $charset_collate;

		// Users Table Name
		$users_table_name = "{$wpdb->prefix}users";

		//require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		//require_once( ABSPATH . 'wp-includes/pluggable.php' );

		$wpdb->kpmg_employees = "{$wpdb->prefix}kpmg_employees";
		$wpdb->kpmg_employees_upload = "{$wpdb->prefix}kpmg_employees_upload";
		$wpdb->kpmg_registration_cancellation = "{$wpdb->prefix}kpmg_registration_cancellation";
		$wpdb->kpmg_registration_details = "{$wpdb->prefix}kpmg_registration_details";
		$wpdb->kpmg_employee_status = "{$wpdb->prefix}kpmg_employee_status";
		$wpdb->kpmg_registration_cutoff = "{$wpdb->prefix}kpmg_registration_cutoff";
		$wpdb->kpmg_groups = "{$wpdb->prefix}kpmg_groups";
		$wpdb->kpmg_group_seats = "{$wpdb->prefix}kpmg_group_seats";
		$wpdb->kpmg_tables = "{$wpdb->prefix}kpmg_tables";
		$wpdb->kpmg_table_seats = "{$wpdb->prefix}kpmg_table_seats";

		// Create Employees Table
		//$sql_drop_table = "DROP TABLE IF EXISTS {$wpdb->kpmg_employees};";
		$sql_create_table = "CREATE TABLE IF NOT EXISTS {$wpdb->kpmg_employees} (
				  employee_email_address VARCHAR(255),
				  employee_first_name TEXT,
				  employee_last_name TEXT,
				  employee_designation TEXT,
				  employee_status TEXT,
				  make_admin TEXT,
				  PRIMARY KEY  (employee_email_address)
			 ) $charset_collate; ";

		//$wpdb->query($sql_drop_table);
		$wpdb->query($sql_create_table);

		// Create Employee Upload Table
		$sql_create_table = "CREATE TABLE IF NOT EXISTS {$wpdb->kpmg_employees_upload} (
				  employee_email_address VARCHAR(255),
				  employee_first_name TEXT,
				  employee_last_name TEXT,
				  employee_designation TEXT,
				  employee_status TEXT,
				  make_admin TEXT,
				  PRIMARY KEY  (employee_email_address)
			 ) $charset_collate; ";

		$wpdb->query($sql_create_table);

		// Create Registration Details Table
		$sql_create_table = "CREATE TABLE IF NOT EXISTS {$wpdb->kpmg_registration_cancellation} (
				employee_email_address VARCHAR(255),
				cancellation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (employee_email_address)
			) $charset_collate; ";
		$wpdb->query($sql_create_table);

		// Create Registration Details Table
		$sql_create_table = "CREATE TABLE IF NOT EXISTS {$wpdb->kpmg_registration_details} (
				user_id INT,
				employee_email_address VARCHAR(255),
				employee_first_name TEXT,
				employee_last_name TEXT,
				employee_dietary_requirements TEXT,
				employee_dietary_requirements_other TEXT,
				guest_first_name TEXT,
				guest_last_name TEXT,
				guest_dietary_requirements TEXT,
				guest_dietary_requirements_other TEXT,
				has_guest VARCHAR(10) NOT NULL DEFAULT 'no',
				attend_entertainment_only INT NOT NULL DEFAULT 0,
				employee_status TEXT,
				registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				is_group_host INT NOT NULL DEFAULT 0,
				group_seat INT NOT NULL DEFAULT 0,
				group_id INT NOT NULL DEFAULT 0,
				table_id INT NOT NULL DEFAULT 0,
				PRIMARY KEY  (employee_email_address)
			) $charset_collate; ";
		$wpdb->query($sql_create_table);

		// Alter Registration Details Table
		$row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
			WHERE table_name = ''{$wpdb->kpmg_registration_details}' AND column_name = 'group_seat'"  );
		if(empty($row)){
			$wpdb->query("ALTER TABLE {$wpdb->kpmg_registration_details} ADD group_seat INT NOT NULL DEFAULT 0 AFTER registration_date");
		 }	
		$row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
			WHERE table_name = ''{$wpdb->kpmg_registration_details}' AND column_name = 'is_group_host'"  );
		if(empty($row)){
			$wpdb->query("ALTER TABLE {$wpdb->kpmg_registration_details} ADD is_group_host INT NOT NULL DEFAULT 0 AFTER registration_date");
		 }	
		
		// Create Employee Status Table
		$sql_create_table = "CREATE TABLE IF NOT EXISTS {$wpdb->kpmg_employee_status} (
				employee_status_id INT NOT NULL AUTO_INCREMENT,
				employee_status TEXT,
				PRIMARY KEY  (employee_status_id)
		   ) $charset_collate; ";
		$wpdb->query($sql_create_table);
		
		// Create Status Types Table Data
		$sql_data_arr = array(
			1 => 'unregistered',
			2 => 'registered',
			3 => 'waitinglist',
			4 => 'declined',
			5 => 'terminated',
			6 => 'cancelled'
			);
		$sql_data_table = "INSERT IGNORE INTO {$wpdb->kpmg_employee_status} (employee_status_id, employee_status) VALUES";
		foreach ($sql_data_arr as $key => $val)
		{
			// Via row
			$sql_data_table .= "({$key}, '{$val}'),";
		}
		// Remove Last Comma
		$sql_data_table = substr($sql_data_table, 0, -1);
		$wpdb->query($sql_data_table);
		
		// Create Registration Cut-off Table
		$sql_create_table = "CREATE TABLE IF NOT EXISTS {$wpdb->kpmg_registration_cutoff} (
				registration_cuttoff_id INT NOT NULL AUTO_INCREMENT,
				registration_limit INT NOT NULL DEFAULT 3000,
				waiting_list_limit INT NOT NULL DEFAULT 200,
				table_limit INT NOT NULL DEFAULT 320,
				table_seat_limit INT NOT NULL DEFAULT 10,
				registration_end_date TIMESTAMP NOT NULL DEFAULT '2016-11-02 00:00:00',
				PRIMARY KEY  (registration_cuttoff_id)
		   ) $charset_collate; ";

		$wpdb->query($sql_create_table);
		
		// Create Registration Cut-of Table Data
		$sql_data_table = "INSERT IGNORE INTO {$wpdb->kpmg_registration_cutoff} (registration_cuttoff_id) VALUES (1)";		
		$wpdb->query($sql_data_table);
		
		// Create Groups Table
		$sql_create_table = "CREATE TABLE IF NOT EXISTS {$wpdb->kpmg_groups} (
				  group_id INT NOT NULL AUTO_INCREMENT,
				  null_data TEXT,
				  reserved_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				  PRIMARY KEY  (group_id)
			 ) $charset_collate; ";

		$wpdb->query($sql_create_table);	
		
		// Create Groups Seats Table
		$sql_create_table = "CREATE TABLE IF NOT EXISTS {$wpdb->kpmg_group_seats} (
				  group_seat_id INT NOT NULL AUTO_INCREMENT,
				  group_id INT,
				  host_email_address TEXT,
				  employee_email_address TEXT,
				  is_guest INT NOT NULL DEFAULT 0,
				  seated_position INT,
				  PRIMARY KEY  (group_seat_id)
			 ) $charset_collate; ";

		//$wpdb->query($sql_drop_table);
		$wpdb->query($sql_create_table);
		
		// Create Table Reserved Tables
		$sql_create_table = "CREATE TABLE IF NOT EXISTS {$wpdb->kpmg_tables} (
				  table_id INT NOT NULL AUTO_INCREMENT,
				  null_data TEXT,
				  PRIMARY KEY  (table_id)
			 ) $charset_collate; ";
		$wpdb->query($sql_create_table);	
		
		// Create Groups Seats Table
		$sql_create_table = "CREATE TABLE IF NOT EXISTS {$wpdb->kpmg_table_seats} (
				  table_seat_id INT NOT NULL AUTO_INCREMENT,
				  table_id INT,
				  host_email_address TEXT,
				  employee_email_address TEXT,
				  is_guest INT NOT NULL DEFAULT 0,
				  seated_position INT,
				  PRIMARY KEY  (table_seat_id)
			 ) $charset_collate; ";
		$wpdb->query($sql_create_table);
		
		// Create Registered Admins
		/*$adminsInfo = array(
			array(
				'first_name' => 'Holly',
				'last_name' => 'Greig',
				'email_address' => 'hgreig@kpmg.ca'
			),
			array(
				'first_name' => 'Nalini',
				'last_name' => 'Davies',
				'email_address' => 'njdavies@kpmg.ca'
			),
			array(
				'first_name' => 'Corin',
				'last_name' => 'Toporas',
				'email_address' => 'ctoporas@kpmg.ca'
			),
			array(
				'first_name' => 'Kathy',
				'last_name' => 'Cassar',
				'email_address' => 'kcassar@kpmg.ca'
			)
		);
		$adminsInfo = array();
		if (!empty($adminsInfo))
		{
			foreach ($adminsInfo as $key => $adminInfo)
			{
				// Generate Admin Data
				$admindata = $this->generateAdminData($adminInfo);
				// Check if email address exists
				if ( email_exists($admindata['user_email']) )
				{
					$adminUserData = get_user_by( 'email', $admindata['user_email'] );
					$adminUserID = $adminUserData->ID;
					// Get Generic Results
					$adminUserRegistrationResults = $wpdb->get_results (
						" SELECT * FROM {$wpdb->kpmg_registration_details} WHERE employee_id = {$adminUserID} LIMIT 1 "
						);
					foreach ( $adminUserRegistrationResults as $adminUserRegistrationResult )
					{
						$admindata['email_address'] = $adminUserRegistrationResult->employee_email_address;
						$admindata['first_name'] = $adminUserRegistrationResult->employee_first_name;
						$admindata['last_name'] = $adminUserRegistrationResult->employee_last_name;
						$admindata['dietary_requirements'] = $adminUserRegistrationResult->employee_dietary_requirements;
						$admindata['dietary_requirements_other'] = $adminUserRegistrationResult->employee_dietary_requirements_other;
						$admindata['first_name_guest'] = $adminUserRegistrationResult->guest_first_name;
						$admindata['last_name_guest'] = $adminUserRegistrationResult->guest_last_name;
						$admindata['dietary_requirements_guest'] = $adminUserRegistrationResult->guest_dietary_requirements;
						$admindata['dietary_requirements_guest_other'] = $adminUserRegistrationResult->guest_dietary_requirements_other;
					}
				}
				else
				{
					// Create New Admin User
					$adminUserID = wp_insert_user($admindata);
				}
				// Generate Admin Registration Data
				$admindata['employee_id'] = $adminUserID;
				$adminregistrationdata = $this->generateRegistrationData($admindata);
				$adminregistrationdatafieldtypes = $this->generateFieldTypes($adminregistrationdata);
				// Insert or Replace(with same) Admin Registration Data
				$wpdb->replace($wpdb->kpmg_registration_details, $adminregistrationdata, $adminregistrationdatafieldtypes);


			}
		}*/
		
		// Save Database Version For Future Update Check
		//add_option( "table_db_version", array($this, 'table_db_version') );
	}
	