<?php

/**
 * File: class.admin_tables.php
 * Description of class
 * Count Tables
 * Put Groups In Tables
 * 
 * Author: edward <http://ojambo.com>
 * Copyright: 2016  
 * Created : 2016-09-22 6:13:21 AM
 * Last Modified : 2016-09-22T10:13:21Z
 */
class KPMG_Admin_Tables {
	
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
	
	// Admin Unregistered Count
	public function adminUnregisteredTables()
	{
		global $wpdb;
		
		// Variables
		$InfoArr = kpmg_getRegistrationCutoff();
		$Count = 0;
		if ( $this->adminrole != NULL )
		{
			// Find All Unregistered Tables
			$registered_tables = kpmg_getRegistrationTableCount();
			if ( $registered_tables > 0 )
			{
				$Count = $InfoArr['registration_limit'] - $registered_tables['table_count'];
			}
			return $Count;
		}
		else
		{
			return false;
		}
		
	}
	
	// Admin Registered Count
	public function adminRegisteredTables()
	{
		global $wpdb;
		
		// Variables
		$InfoArr = kpmg_getRegistrationCutoff();
		$Count = 0;
		
		if ( $this->adminrole != NULL )
		{
			// Find All Unregistered Tables
			$registered_tables = kpmg_getRegistrationTableCount();
			if ( $registered_tables > 0 )
			{
				$Count = $registered_tables['table_count'];
			}
			return $Count;
		}
		else
		{
			return false;
		}
		
	}
	
	// Admin Process
	public function adminProcess()
	{
		$formVariable = $this->formvariable;
		
		if ( $this->adminrole != NULL )
		{
			
			if ( isset($_POST[$formVariable]['step']) && isset($_POST['kpmg_formaction']) )
			{
				return $this->adminFormAction();
			}

			return $this->adminForm();
		}
		else
		{
			return false;
		}
		
	}

	
}
