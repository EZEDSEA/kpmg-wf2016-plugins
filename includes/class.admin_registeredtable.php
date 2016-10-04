<?php

/**
 * File: class.admin_registeredtable.php
 * Description of class
 * Show Table Members
 * 
 *
 * Author: edward <http://ojambo.com>
 * Copyright: 2016  
 * Created : 2016-10-01 1:25:58 AM
 * Last Modified : 2016-10-01T05:25:59Z
 */
class KPMG_Admin_RegisteredTable {
		
	// Variables
	private $salt;
	private $step;
	private $formaction;
	private $formvariable;
	private $errors;
	private $thanks;
	private $adminrole = NULL;
	private $isAdmin = false;
	private $updateemail = NULL;
	
	// Constructor
	public function __construct() 
	{
		$this->salt = KPMGWF_Salt;
		$this->step = 1;
		$this->errors = "";
		$this->thanks = "";
		$this->formvariable = "admintablers";
		$this->formaction = "admin_tablers";
		
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
	
	// Admin Form
	public function adminForm()
	{
		// Variables
		$Errors = $this->errors;
		$Thanks = $this->thanks;
		$formAction = $this->formaction;
		$formVariable = $this->formvariable;
		$formStep = $this->step;
		$InfoArr = $this->adminData($this->updateemail);
		$Inputs = (isset($InfoArr[0]['employee_email_address']) && $InfoArr[0]['employee_email_address'] != "") ? kpmg_generateDragReserveGroupInputs($InfoArr, $formVariable) : "";
		$TableID = isset($InfoArr[0]['table_id']) ? $InfoArr[0]['table_id'] : 0;
		if ( $Thanks != '' )
		{
		$Form = <<<OJAMBO
			<div class="table-information">
				<div class="show">
				{$Inputs}
				</div>
				$Thanks
			</div>	
OJAMBO;
		}
		elseif ( $TableID == 0 )
		{
		$Form = <<<OJAMBO
			<div class="table-information">
				<div class="show">
				{$Inputs}
				</div>
				<p>Not in a table</p>
			</div>	
OJAMBO;
		}
		else
		{
		$Form = <<<OJAMBO
			<div id="kpmg-{$formVariable}-form" class="admingroupform signup-01">
				<div class="show">
					<p class="small table-id">Table ID: {$TableID}</p>
				{$Inputs}
				</div>

			</div>	
			
OJAMBO;
		}

		return $Form;
	}    	
	
	// Admin Process
	public function adminProcess()
	{
		$formVariable = $this->formvariable;
		if ( $this->adminrole != NULL )
		{
			if ( isset($_POST['email_address']) )
			{
				$this->updateemail = filter_var($_POST['email_address'], FILTER_SANITIZE_EMAIL);
			}
			if ( ( $this->updateemail == NULL || !filter_var($this->updateemail, FILTER_VALIDATE_EMAIL) ) )
			{
				return false;
			}
			else
			{
				/*if ( isset($_POST[$formVariable]['step']) && isset($_POST['kpmg_formaction']) )
				{
					return $this->adminFormAction();
				}*/

				return $this->adminForm();
			}
		}
		else
		{
			return false;
		}
		
	}
	
	// Admin Form Action
	public function adminFormAction()
	{
		// Nothing TO DO
	}
	
	
	function adminData($email_address)
	{
		global $wpdb;
		
		// Variables
		$saveTable = $wpdb->kpmg_registration_details;
		$formVariable = $this->formvariable;
		$arr = array();
		$arrTypes = array (
			'employee_email_address' => 'text',
			'employee_first_name' => 'text',
			'employee_last_name' => 'text',
			'guest_first_name' => 'text',
			'guest_last_name' => 'text',
			'has_guest' => 'text',
			'table_id' => 'number',
			//'attend_entertainment_only' => 'number',
		);
		
		$tableColumns = kpmg_getDatabaseTableColumns($saveTable);
		if ( $email_address == NULL || !filter_var($email_address, FILTER_VALIDATE_EMAIL) )
		{
			foreach($tableColumns as $row)
			{
				$fieldName = $row['Field'];
				if ( array_key_exists($fieldName, $arrTypes) )
				{
					$arr[$fieldName] = isset($_POST[$formVariable][$fieldName]) ? $_POST[$formVariable][$fieldName] : "";
				}
			}
		}
		else
		{
			$dataArr = kpmg_getEmployeeTableListByEmail($email_address);
			
			// Employee Group Seats
			for ($i=0; $i<10; $i++)
			{
				foreach($tableColumns as $row)
				{
					$fieldName = $row['Field'];
					if ( array_key_exists($fieldName, $arrTypes) )
					{
						$arr[$i][$fieldName] = isset($_POST[$formVariable][$i][$fieldName]) ? $_POST[$formVariable][$i][$fieldName] : ((isset($dataArr[$i][$fieldName])) ? $dataArr[$i][$fieldName] : "");
					}
				}

				// Deal With Empty 
				if ($arr[$i]['employee_email_address'] == "")
				{
					unset($arr[$i]);  // Remove empty rows
				}
			}
		}
		
		return $arr;
	}	
}
