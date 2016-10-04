<?php

/**
 * File: class.admin_statistics.php
 * Description of class
 * Count Employees
 * Statistics
 *
 * Author: edward <http://ojambo.com>
 * Copyright: 2016  
 * Created : 2016-09-28 8:56:12 PM
 * Last Modified : 2016-09-29T00:56:12Z
 */
class KPMG_Admin_Statistics {
	
	// Variables
	private $salt;
	private $step;
	private $formaction;
	private $formvariable;
	private $errors;
	private $thanks;
	private $adminrole = NULL;
	private $isAdmin = false;
	
	// Constructor
	public function __construct() 
	{
		$this->salt = KPMGWF_Salt;
		$this->step = 0;
		$this->errors = "";
		$this->thanks = "";
		$this->formvariable = "";
		$this->formaction = "";
		
		global $user;

		$adminRole = KPMGWF_AdminRole;

		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($adminRole, $roles) )
			{
				$this->adminrole = KPMGWF_AdminRole;
				$this->isAdmin = true;
			}
		}	
	}
	
	// Admin Unregistered Employees Count
	public function adminUnregisteredEmployees()
	{
		global $wpdb;
		
		// Variables
		$Count = 0;
		if ( $this->adminrole != NULL )
		{
			// Find All Unregistered Emplopyees
			$result = $wpdb->get_results(
				" SELECT COUNT(emp.employee_email_address) AS table_count 
					FROM {$wpdb->kpmg_employees} emp
					LEFT JOIN {$wpdb->kpmg_registration_details} det ON det.employee_email_address = emp.employee_email_address
					WHERE LOWER(emp.employee_status) = 'unregistered'
						AND det.employee_status IS NULL 
					"	
				, ARRAY_A
			);			
			if( count($result) > 0 ) 
			{
				$Count = $result[0]['table_count'];  // First One only
			}

			return $Count;
		}
		else
		{
			return false;
		}
	}
	
	// Admin Registered Employees Count
	public function adminRegisteredEmployees()
	{
		global $wpdb;
		
		// Variables
		$Count = 0;
		if ( $this->adminrole != NULL )
		{
			// Find All Unregistered Emplopyees
			$result = $wpdb->get_results(
				" SELECT COUNT(det.employee_email_address) AS table_count 
					FROM {$wpdb->kpmg_registration_details} det 
					WHERE LOWER(det.employee_status) = 'registered'
					"	
				, ARRAY_A
			);			
			if( count($result) > 0 ) 
			{
				$Count = $result[0]['table_count'];  // First One only
			}

			return $Count;
		}
		else
		{
			return false;
		}
	}
	
	// Admin Registered Guests Of Employees Count
	public function adminRegisteredGuests()
	{
		global $wpdb;
		
		// Variables
		$Count = 0;
		if ( $this->adminrole != NULL )
		{
			// Find All Unregistered Emplopyees
			$result = $wpdb->get_results(
				" SELECT COUNT(det.employee_email_address) AS table_count 
					FROM {$wpdb->kpmg_registration_details} det 
					WHERE LOWER(det.employee_status) = 'registered'
						AND LOWER(det.has_guest) = 'yes'
					"	
				, ARRAY_A
			);			
			if( count($result) > 0 ) 
			{
				$Count = $result[0]['table_count'];  // First One only
			}

			return $Count;
		}
		else
		{
			return false;
		}
	}
	
	// Admin Declined Employees Count
	public function adminDeclinedEmployees()
	{
		global $wpdb;
		
		// Variables
		$Count = 0;
		if ( $this->adminrole != NULL )
		{
			// Find All Unregistered Emplopyees
			$result = $wpdb->get_results(
				" SELECT COUNT(emp.employee_email_address) AS table_count 
					FROM {$wpdb->kpmg_employees} emp
					WHERE LOWER(emp.employee_status) = 'declined'
					"	
				, ARRAY_A
			);			
			if( count($result) > 0 ) 
			{
				$Count = $result[0]['table_count'];  // First One only
			}

			return $Count;
		}
		else
		{
			return false;
		}
	}	
	
	// Admin Registered Dinner & Entertainment Count
	public function adminDinnerEntertainment()
	{
		global $wpdb;
		
		// Variables
		$Count = 0;
		if ( $this->adminrole != NULL )
		{
			// Find All Unregistered Emplopyees
			$result = $wpdb->get_results(
				" SELECT COALESCE(SUM(CASE WHEN LOWER(det.has_guest) = 'yes' THEN 2 ELSE 1 END), 0) AS table_count 
					FROM {$wpdb->kpmg_registration_details} det 
					WHERE LOWER(det.employee_status) = 'registered'
						AND det.attend_entertainment_only = 0
					"	
				, ARRAY_A
			);			
			if( count($result) > 0 ) 
			{
				$Count = $result[0]['table_count'];  // First One only
			}

			return $Count;
		}
		else
		{
			return false;
		}
	}	
	
	// Admin Registered Only Entertainment Count
	public function adminOnlyEntertainment()
	{
		global $wpdb;
		
		// Variables
		$Count = 0;
		if ( $this->adminrole != NULL )
		{
			// Find All Unregistered Emplopyees
			$result = $wpdb->get_results(
				" SELECT COALESCE(SUM(CASE WHEN LOWER(det.has_guest) = 'yes' THEN 2 ELSE 1 END), 0) AS table_count 
					FROM {$wpdb->kpmg_registration_details} det 
					WHERE LOWER(det.employee_status) = 'registered'
						AND det.attend_entertainment_only = 1
					"	
				, ARRAY_A
			);			
			if( count($result) > 0 ) 
			{
				$Count = $result[0]['table_count'];  // First One only
			}

			return $Count;
		}
		else
		{
			return false;
		}
	}	
	
	// Admin Terminated Employees Count
	public function adminTerminatedEmployees()
	{
		global $wpdb;
		
		// Variables
		$Count = 0;
		if ( $this->adminrole != NULL )
		{
			// Find All Unregistered Emplopyees
			$result = $wpdb->get_results(
				" SELECT COUNT(emp.employee_email_address) AS table_count 
					FROM {$wpdb->kpmg_employees} emp
					WHERE LOWER(emp.employee_status) = 'terminated'
					"	
				, ARRAY_A
			);			
			if( count($result) > 0 ) 
			{
				$Count = $result[0]['table_count'];  // First One only
			}

			return $Count;
		}
		else
		{
			return false;
		}
	}	
	
	// Admin Cancelled Employees Count
	public function adminCancelledEmployees()
	{
		global $wpdb;
		
		// Variables
		$Count = 0;
		if ( $this->adminrole != NULL )
		{
			// Find All Unregistered Emplopyees
			$result = $wpdb->get_results(
				" SELECT COUNT(emp.employee_email_address) AS table_count 
					FROM {$wpdb->kpmg_employees} emp
					LEFT JOIN {$wpdb->kpmg_registration_details} det ON det.employee_email_address = emp.employee_email_address
					WHERE LOWER(emp.employee_status) = 'cancelled'
						AND det.employee_status IS NULL 
					"
				, ARRAY_A
			);			
			if( count($result) > 0 ) 
			{
				$Count = $result[0]['table_count'];  // First One only
			}

			return $Count;
		}
		else
		{
			return false;
		}
	}	
	
	// Admin Waiting List Employees Count
	public function adminWaitingList()
	{
		global $wpdb;
		
		// Variables
		$Count = 0;
		if ( $this->adminrole != NULL )
		{
			// Find All Unregistered Emplopyees
			$result = $wpdb->get_results(
				" SELECT COALESCE(SUM(CASE WHEN LOWER(det.has_guest) = 'yes' THEN 2 ELSE 1 END), 0) AS table_count 
					FROM {$wpdb->kpmg_registration_details} det
					WHERE LOWER(det.employee_status) = 'waitinglist'
					"
				, ARRAY_A
			);			
			if( count($result) > 0 ) 
			{
				$Count = $result[0]['table_count'];  // First One only
			}

			return $Count;
		}
		else
		{
			return false;
		}
	}	
	
	// Admin Registered Group Count
	public function adminRegisteredGroups()
	{
		global $wpdb;
		
		// Variables
		$Count = 0;
		if ( $this->adminrole != NULL )
		{
			// Find All Unregistered Emplopyees
			$result = $wpdb->get_results(
				" SELECT COUNT(DISTINCT det.group_id) AS table_count 
					FROM {$wpdb->kpmg_registration_details} det 
					WHERE det.group_id > 0
					"	
				, ARRAY_A
			);			
			if( count($result) > 0 ) 
			{
				$Count = $result[0]['table_count'];  // First One only
			}

			return $Count;
		}
		else
		{
			return false;
		}
	}	
	
	// Admin Registered Table Count
	public function adminRegisteredTables()
	{
		global $wpdb;
		
		// Variables
		$Count = 0;
		if ( $this->adminrole != NULL )
		{
			// Find All Unregistered Emplopyees
			$result = $wpdb->get_results(
				" SELECT COUNT(DISTINCT det.table_id) AS table_count 
					FROM {$wpdb->kpmg_registration_details} det 
					WHERE det.table_id > 0
					"	
				, ARRAY_A
			);			
			if( count($result) > 0 ) 
			{
				$Count = $result[0]['table_count'];  // First One only
			}

			return $Count;
		}
		else
		{
			return false;
		}
	}	
	
	
	
}
