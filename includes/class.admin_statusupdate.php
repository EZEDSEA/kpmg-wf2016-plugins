<?php

/**
 * File: class.admin_statusupdate.php
 * Description of class
 * Change Status Of Any Employee
 *
 * Author: edward <http://ojambo.com>
 * Copyright: 2016  
 * Created : 2016-09-29 7:31:12 AM
 * Last Modified : 2016-09-29T11:31:12Z
 */
class KPMG_Admin_StatusUpdate {
	
	// Variables
	private $salt;
	private $step;
	private $formaction;
	private $formvariable;
	private $errors;
	private $thanks;
	private $adminrole = NULL;
	private $isAdmin = false;
	private $formactionstep1;
	private $updateemail = NULL;
	
	// Constructor
	public function __construct() 
	{
		$this->salt = KPMGWF_Salt;
		$this->step = 1;
		$this->errors = "";
		$this->thanks = "";
		$this->formvariable = "adminstatusup";
		$this->formaction = "admin_statusup";
		$this->formactionstep1 = "admin_status-up2";
		
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
		$registerInfoArr = $this->adminData($this->updateemail);

		$makeAdminDataArr = kpmg_yesNoOptionsData();
		$makeAdminOptions = kpmg_generateSelectOptions($makeAdminDataArr, $registerInfoArr['make_admin']);
		$designationDataArr = kpmg_getAllEmployeeDesignation();
		$designationOptions = kpmg_generateSelectOptions($designationDataArr, $registerInfoArr['employee_designation']);
		$statusDataArr = kpmg_getAllEmployeeStatus();
		$statusOptions = kpmg_generateSelectOptions($statusDataArr, $registerInfoArr['employee_status']);

		if ($this->step == 1 )
		{
		$Form = <<<OJAMBO
			<form id="kpmg-{$formVariable}-get-form" class="signup-01" method="post" action="">
				<div class="errors">{$Errors}
					<p class="small" id="kpmg-{$formVariable}-ajax-error-area"></p>
				</div>
				<input type="hidden" name="kpmg_formaction" value="{$this->formactionstep1}" />
				<h3 class="sub-heading">Update Someone</h3>
				<input type="email" class="email_address" id="kpmg_{$formVariable}_email_address" name="email_address" value="" placeholder="Email" required autocomplete="off" />
				<div class="results" id="kpmg-{$formVariable}-ajax-results-area"></div>
				<input type="hidden" name="{$formVariable}[step]" value="2" />
				<button type="submit" name="{$formVariable}[button]" value="Get Info" >Get Info</button>
			</form>	
			{$Thanks}
			<p class="thanks" id="kpmg-{$formVariable}-ajax-thanks-area"></p>
			
OJAMBO;
		}
		else
		{
		$Form = <<<OJAMBO
			<form id="kpmg-{$formVariable}-form" class="signup-01" method="post" action="">
				<div class="errors">{$Errors}
					<p class="small" id="kpmg-{$formVariable}-ajax-error-area"></p>
				</div>
				<input type="hidden" name="kpmg_formaction" value="{$formAction}" />
				<div class="show register-info"> 
					<h3 class="sub-heading">Update Someone</h3>
					<p><span class="yellow">*</span>Indicates a required field</p>
					<input type="text" name="{$formVariable}[employee_first_name]" value="{$registerInfoArr['employee_first_name']}" placeholder="First Name" required readonly /><span class="yellow">*</span>
					<input type="text" name="{$formVariable}[employee_last_name]" value="{$registerInfoArr['employee_last_name']}" placeholder="Last name" required readonly /><span class="yellow">*</span>
					<input class="email_address"  type="email" name="{$formVariable}[employee_email_address]" value="{$registerInfoArr['employee_email_address']}" placeholder="Email address" required readonly /><span class="yellow">*</span>
				</div>
				<div class="show make-admin-info">
					<div class="title">Make Admin?</div>
					<div class="required">
						<select class="make_admin" name="{$formVariable}[make_admin]">
							<option value="">Please Select...</option>
							{$makeAdminOptions}
						</select>
					</div>
				</div>
				<div class="show designation-info">
					<div class="title">Designation</div>
					<div class="required">
						<select class="employee_designation" name="{$formVariable}[employee_designation]">
							<option value="">Please Select...</option>
							{$designationOptions}
						</select>
					</div>
				</div>					
				<div class="show designation-info">
					<div class="title">Status</div>
					<div class="required">
						<select class="employee_designation" name="{$formVariable}[employee_status]">
							<option value="">Please Select...</option>
							{$statusOptions}
						</select>
					</div>
				</div>					
				<input type="hidden" name="{$formVariable}[step]" value="{$formStep}" />
				<button type="submit" name="{$formVariable}[button]" value="UPDATE EMPLOYEE" onclick="return confirm('Are you sure that you want to update the employee status?')" >UPDATE EMPLOYEE</button>
			</form>	
			{$Thanks}
			<p class="thanks" id="kpmg-{$formVariable}-ajax-thanks-area"></p>
			
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
	
	// Admin Form Action
	public function adminFormAction()
	{
		global $wpdb;
		
		global $KPMG_Email;
		
		// Variables
		//$reportInTable = $wpdb->kpmg_group_seats;
		$saveInTable = $wpdb->kpmg_registration_details;
		$formVariable = $this->formvariable;
		$saveArr = array();
		$saveIDArr = array();
		$dataArr = array();
		$arrTypes = array (
			'make_admin' => 'text',
			'employee_designation' => 'text',			
			'employee_email_address' => 'email',
			'employee_first_name' => 'text',
			'employee_last_name' => 'text',
			'employee_status' => 'text',
		);
		$updateID = "employee_email_address";
		
		
		if ( $this->adminrole != NULL && ($_POST['kpmg_formaction'] == $this->formactionstep1) )
		{
			$fieldName = 'email_address';
			$dataArr[$fieldName] = isset($_POST[$fieldName]) ? $_POST[$fieldName] : false;
			if ( !filter_var($dataArr['email_address'], FILTER_VALIDATE_EMAIL) )
			{
				$this->errors .= "<p class=\"small\">The email address is invalid</p>";
			}
			//elseif ( !email_exists($dataArr['email_address']) || !kpmg_emailOnEmployeeList($dataArr['email_address']) )
			elseif ( !kpmg_emailOnEmployeeList($dataArr['email_address']) )
			{
				// Check If Email On Employee List
				$this->errors .= "<p class\"smaill\">The email address is not allowed</p>";
			}		
			/*$employeeListInfo = kpmg_getemailStatusOnEmployeeList($dataArr['email_address']);
			if ( $employeeListInfo != false )
			{
				if ( in_array($employeeListInfo['employee_status'], array('declined', 'terminated')) )
				{
					// Check If Email On Employee List
					$this->errors .= "<p class\"smaill\">The email address is not allowed</p>";	
				}
				
			}*/
			
			// No Errors
			if ($this->errors == "" )
			{
				$this->updateemail = $dataArr['email_address'];
				$this->step = 2;
			}

			// Show Form
			return $this->adminForm();
		}
		elseif ( $this->adminrole != NULL && ($_POST['kpmg_formaction'] == $this->formaction) )
		{
			$saveTableFieldsResult = kpmg_getDatabaseTableColumns($saveInTable);
			$saveTableFieldsArr = array();
			$saveID = NULL;
			
			foreach($saveTableFieldsResult as $row)
			{
				$fieldName = $row['Field'];
				$dataArr[$fieldName] = isset($_POST[$formVariable][$fieldName]) ? $_POST[$formVariable][$fieldName] : false;
				// Validate
				if ( array_key_exists($fieldName, $arrTypes) )
				{
					$dataType = $arrTypes[$fieldName];
					$humanLabel = kpmg_generateHumanLabel($fieldName);
					
					if ( $dataArr[$fieldName] === false )
					{
						$this->errors .= "<p class=\"small\">Please fill in all required fields</p>";
					}
					elseif ( $dataType == "number" && !is_numeric($dataArr[$fieldName]) )
					{
						$this->errors .= "<p class=\"small\">The {$humanLabel} is invalid</p>";
					}
					elseif ( $dataType == "date" && $dataArr[$fieldName] != date($update_date_format, strtotime($dataArr[$fieldName])) )
					{
						$this->errors .= "<p class=\"small\">The {$humanLabel} is invalid</p>";
					}
					elseif ( $dataType == "email" && !filter_var($dataArr[$fieldName], FILTER_VALIDATE_EMAIL) )
					{
						$this->errors .= "<p class=\"small\">The {$humanLabel} is invalid</p>";
					}
					elseif ( $dataType == "email" && !kpmg_emailOnEmployeeList($dataArr[$fieldName]) )
					{
						// Check If Email On Employee List
						$this->errors .= "<p class\"smaill\">The {$humanLabel} is not allowed</p>";
					}
					else
					{
						if ( $dataType == "email" )
						{
							$employeeListInfo = kpmg_getemailStatusOnEmployeeList($dataArr[$fieldName]);
							if ( $employeeListInfo != false )
							{
								/*if ( in_array($employeeListInfo['employee_status'], array('declined', 'terminated')) )
								{
									// Check If Email On Employee List
									$this->errors .= "<p class\"smaill\">The {$humanLabel} is not allowed</p>";	
								}*/
							}
							
						}
						
						if ($fieldName == $updateID)
						{
							$saveIDArr[$fieldName] = $dataArr[$fieldName];
						}
						else
						{
							$saveArr[$fieldName] = $dataArr[$fieldName];
						}
					}
				}
			}
			
			/*// Validate Password
			$dataArr['password_one'] = isset($_POST[$formVariable]['password_one']) ? $_POST[$formVariable]['password_one'] : false;
			$dataArr['password_two'] = isset($_POST[$formVariable]['password_two']) ? $_POST[$formVariable]['password_two'] : false;
			if ( strlen($dataArr['password_one']) < 8 )
			{
				$this->errors .= "<p class=\"small\">The password must have at least 8 characters</p>";
			}
			if ( preg_match("/[0-9]/", $dataArr['password_one']) === 0 )
			{
				$this->errors .= "<p class=\"small\">The password must have a number</p>";
			}
			if ( preg_match("/[a-z]/", $dataArr['password_one']) === 0 )
			{
				$this->errors .= "<p class=\"small\">The password must have a lowercase character</p>";
			}
			if ( preg_match("/[A-Z]/", $dataArr['password_one']) === 0 )
			{
				$this->errors .= "<p class=\"small\">The password must have an uppercase character</p>";
			}
			if ( $dataArr['password_one'] != $dataArr['password_two'] )
			{
				$this->errors .= "<p class=\"small\">The passwords do not match</p>";
			}*/			

		
			// Save Data
			if ( $this->errors == "" )
			{
				/*$saveArr['employee_email_address'] = isset($saveArr['employee_email_address']) ? $saveArr['employee_email_address'] : $saveIDArr['employee_email_address'];
				// Save Registration Step Two In Database
				$userdata = kpmg_generateEmployeeData($saveArr);
				
				// Save Database Information
				$userID = wp_insert_user($userdata);
				if ( is_wp_error($userID) )
				{
					$this->errors .= "<p class=\"small\">An error occured while creating new user</p>";
				}
				else
				{*/
					// Save Registration Data After Addition Of Employee ID
					//$saveArr['user_id'] = $userID;
					//$saveArr['employee_status'] = "registered";
					if ( isset($saveArr['make_admin']) )
					{
						$employeedetailsbyemail = kpmg_getEmployeeListAndDetailsByEmail($saveIDArr['employee_email_address']);					
						$employeeupdaterole = array(
							'ID' => isset($employeedetailsbyemail['user_id']) ? $employeedetailsbyemail['user_id'] : 0,
							'role' => strtolower($saveArr['make_admin']) == "yes" ? KPMGWF_AdminRole : KPMGWF_EmployeeRole
						);
						if ( $employeeupdaterole['ID'] > 0 )
						{
							$saveroledata = kpmg_generateEmployeeRoleData($employeeupdaterole);
						}
					}
					$saveiddata = $saveIDArr;
					$saveiddatafieldtypes = kpmg_generateFieldTypes($saveiddata);
					$savedata = $saveArr;
					$savedatafieldtypes = kpmg_generateFieldTypes($savedata);
					if ( $wpdb->update($wpdb->kpmg_employees, $savedata, $saveiddata, $savedatafieldtypes, $saveiddatafieldtypes) === FALSE )
					{
						$this->errors .= "<p class=\"small\">An error occured while saving employee changes</p>";
					}
					else
					{
						if ( $wpdb->update($wpdb->kpmg_registration_details, $savedata, $saveiddata, $savedatafieldtypes, $saveiddatafieldtypes) === FALSE )
						{
							$this->errors .= "<p class=\"small\">An error occured while saving employee changes</p>";
						}
						else
						{
							// Update Role
							if ( isset($saveroledata) )
							{
								$userID = wp_update_user( $saveroledata );
								if ( is_wp_error($userID) )
								{
									$this->errors .= "<p class=\"small\">An error occured while changing the make admin role</p>";
								}
								else
								{
									$this->thanks .= "<p class=\"thanks\">Thank you. Your employee changes have now been confirmed.</p>";
								}								
							}
							else
							{
								$this->thanks .= "<p class=\"thanks\">Thank you. Your employee changes have now been confirmed.</p>";
							}
						}
					}
				/*}*/
			}
			
			// Show Form
			return $this->adminForm();
		}
		else
		{
			return false;
		}
		
	}
	
	
	function adminData($email_address)
	{
		global $wpdb;
		
		// Variables
		$saveTable = $wpdb->kpmg_employees;
		$saveTable2 = $wpdb->kpmg_registration_details;
		$formVariable = $this->formvariable;
		$arr = array();
		$arrTypes = array (
			'make_admin' => 'text',
			'employee_designation' => 'text',
			//'password_one' => 'text',
			//'password_two' => 'text',
			'employee_email_address' => 'text',
			'employee_first_name' => 'text',
			'employee_last_name' => 'text',
			'employee_status' => 'text',
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
			// Password Fields
			$arr['password_one'] =  isset($_POST[$formVariable]['password_one']) ? trim($_POST[$formVariable]['password_one']) : "";
			$arr['password_two'] =  isset($_POST[$formVariable]['password_two']) ? trim($_POST[$formVariable]['password_two']) : "";
		}
		else
		{
			$dataArr = kpmg_getEmployeeListAndDetailsByEmail($email_address);
			foreach($tableColumns as $row)
			{
				$fieldName = $row['Field'];
				if ( array_key_exists($fieldName, $arrTypes) )
				{
					$arr[$fieldName] = isset($_POST[$formVariable][$fieldName]) ? $_POST[$fieldName] : ((isset($dataArr[$fieldName])) ? $dataArr[$fieldName] : "");
					if ( $fieldName == 'make_admin' && !isset($_POST[$formVariable][$fieldName]) )
					{
						$arr[$fieldName] = isset($dataArr['registration_admin']) && !empty($dataArr['registration_admin']) ? $dataArr['registration_admin'] : ((isset($dataArr[$fieldName])) ? $dataArr[$fieldName] : "");
					}
					elseif ( $fieldName == 'employee_designation' && !isset($_POST[$formVariable][$fieldName]) )
					{
						$arr[$fieldName] = isset($dataArr['registration_designation']) && !empty($dataArr['registration_designation']) ? $dataArr['registration_designation'] : ((isset($dataArr[$fieldName])) ? $dataArr[$fieldName] : "");
					}
					elseif ( $fieldName == 'employee_status' && !isset($_POST[$formVariable][$fieldName]) )
					{
						$arr[$fieldName] = isset($dataArr['registration_status']) && !empty($dataArr['registration_status']) ? $dataArr['registration_status'] : ((isset($dataArr[$fieldName])) ? $dataArr[$fieldName] : "");
					}
				}
			}
			// Password Fields
			$arr['password_one'] =  isset($_POST[$formVariable]['password_one']) ? trim($_POST[$formVariable]['password_one']) : "";
			$arr['password_two'] =  isset($_POST[$formVariable]['password_two']) ? trim($_POST[$formVariable]['password_two']) : "";
		}
		
		return $arr;
	}
	
}
