<?php

/**
 * File: class.employee_groupdisplay.php
 * Description of class
 *	Group employee
 *	Display employee's group information
 * 
 * Author: edward <http://ojambo.com>
 * Copyright: 2016  
 * Created : 2016-09-22 6:53:46 AM
 * Last Modified : 2016-09-22T10:53:46Z
 */
class KPMG_Employee_GroupDisplay {
		
		
	// Variables
	private $salt;
	private $step;
	private $formaction;
	private $formvariable;
	private $errors;
	private $thanks;
	private $page;
	private $pagethankyou;
	private $pagemyinfo;
	private $role = NULL;
	private $isEmployee = false;
	private $formactionstep1;
	private $updateemail = NULL;
	
	// Constructor
	public function __construct() 
	{
		$this->salt = KPMGWF_Salt;
		$this->step = 2;
		$this->errors = "";
		$this->thanks = "";
		$this->page = KPMGWF_Group;
		$this->pagethankyou = KPMGWF_GroupTY;
		$this->pagemyinfo = KPMGWF_Info;
		$this->formvariable = "employeegroup";
		$this->formaction = "employee_group";
		$this->formactionstep1 = "employeegroup";
		
		global $user;

		$employeeRole = KPMGWF_EmployeeRole;
		$adminRole = KPMGWF_AdminRole;

		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			$user_email = $current_user->user_email;
			$this->updateemail = $user_email;
			if ( in_array($employeeRole, $roles) || in_array($adminRole, $roles) )
			{
				$this->role = in_array($adminRole, $roles) ? $adminRole : $employeeRole;
				$this->isEmployee = true;
			}
		}	
	}
	
	// Employee Form
	public function employeeForm()
	{
		// Variables
		$Errors = $this->errors;
		$Thanks = $this->thanks;
		$formAction = $this->formaction;
		$formVariable = $this->formvariable;
		$formStep = $this->step;
		$InfoArr = $this->employeeData($this->updateemail);
		$editCreateGroupLink = "";
		$Inputs = "";
		if ( $InfoArr )
		{
			$tempData = array();
			$tempData = kpmg_generateReserveGroupSeats($InfoArr);
			$Inputs = (isset($InfoArr[0]['employee_email_address']) && $InfoArr[0]['employee_email_address'] != "") ? kpmg_generateDragReserveGroupInputs($InfoArr, $formVariable) : "";
			$GroupID = isset($InfoArr[0]['group_id']) ? $InfoArr[0]['group_id'] : 0;
			if ( $InfoArr[0]['employee_email_address'] == $this->updateemail )
			{
				$editCreateGroupLink = "<a href='{$this->page}'>EDIT THIS GROUP</a>";
			}
			else
			{
				// Do Nothing
			}
		}
		else
		{
			$editCreateGroupLink = "<a href='{$this->page}'>CREATE A GROUP</a>";
		}
		
		$Form = <<<OJAMBO
			<div class="group-information">
				<div class="show">
				{$Inputs}
				</div>
				{$editCreateGroupLink}
			</div>	
OJAMBO;

		return $Form;

	}    	
	
	// Employee Process
	public function employeeProcess()
	{
		$formVariable = $this->formvariable;
		if ( $this->role != NULL )
		{
			return $this->employeeForm();
		}
		else
		{
			return false;
		}
		
	}
	
	function employeeData($email_address)
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
			'group_id' => 'number',
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
			$dataArr = kpmg_getEmployeeGroupListByEmail($email_address);			
			if ($dataArr === false)
			{
				$dataArr[0] = kpmg_getEmployeeListAndDetailsByEmail($email_address);
			}
			elseif ( $dataArr[0]['employee_email_address'] != $email_address )
			{
				// If Already Reserved And Not Host
				$currentPage = kpmg_getCurrentPageSlug();
				if ($currentPage != "my-info")
				{
					// Redirect To My Info 
					$new_url = add_query_arg( 'alreadyreserved', 1, $this->pagemyinfo ); // alreadyreserved Var
					wp_redirect( $new_url, 303 );  // Allow Response Cache Only
				}
			}

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
