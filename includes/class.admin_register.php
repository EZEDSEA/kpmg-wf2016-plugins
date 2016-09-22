<?php

/**
 * File: class.admin_register.php
 * Description of class
 *	Register someone
 *	Update someone's registration information
 *
 * Author: edward <http://ojambo.com>
 * Copyright: 2016
 * Created : 2016-09-18 4:00:38 AM
 * Last Modified : 2016-09-18T08:00:38Z
 */
class KPMG_Admin_Register {

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
		$this->formvariable = "adminregister";
		$this->formaction = "admin_register";

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
		$registerInfoArr = $this->adminData(NULL);
		$entertainmentDataArr = kpmg_entertainnmentOptionsData();
		$entertainmentOptions = kpmg_generateKeySelectOptions($entertainmentDataArr, $registerInfoArr['attend_entertainment_only']);
		$dietaryDataArr = kpmg_dietaryRequirementOptionsData();
		$dietaryOptions = kpmg_generateSelectOptions($dietaryDataArr, $registerInfoArr['employee_dietary_requirements']);
		$bringGuestDataArr = kpmg_yesNoOptionsData();
		$bringGuestOptions = kpmg_generateSelectOptions($bringGuestDataArr, $registerInfoArr['has_guest']);
		$dietaryGuestOptions = kpmg_generateSelectOptions($dietaryDataArr, $registerInfoArr['guest_dietary_requirements']);

		$Form = <<<OJAMBO
			{$Errors}
			<p class="small" id="kpmg-{$formVariable}-ajax-error-area"></p>
			<form id="kpmg-{$formVariable}-form" class="signup-01" method="post" action="">
				<input type="hidden" name="kpmg_formaction" value="{$formAction}" />
				<div class="show register-info">
					<h3 class="sub-heading">Register Someone</h3>
					<p class="requireInd"><span class="yellow">*</span>Indicates a required field</p>
					<div class="required first_name">
						<input type="text" name="{$formVariable}[employee_first_name]" value="{$registerInfoArr['employee_first_name']}" placeholder="First Name" required />
					</div>
					<div class="required last_name">
						<input type="text" name="{$formVariable}[employee_last_name]" value="{$registerInfoArr['employee_last_name']}" placeholder="Last name" required />
					</div>
					<div class="required email">
						<input class="email_address" id="kpmg_{$formVariable}_email_address" type="email" name="{$formVariable}[employee_email_address]" value="{$registerInfoArr['employee_email_address']}" placeholder="Email address" required />
						<div class="results" id="kpmg-{$formVariable}-ajax-results-area"></div>
					</div>
					<div class="passwordContainer">
						<p class="passwordMin">Password must be a minimum of 8 characters.</p>
						<p class="small">Must contain at least one number and a lowercase and uppercase character.</p>
					</div>
					<div class="required pass_one">
						<input type="password" name="{$formVariable}[password_one]" value="{$registerInfoArr['password_one']}" placeholder="Password" required />
					</div>
					<div class="required pass_two">
						<input type="password" name="{$formVariable}[password_two]" value="{$registerInfoArr['password_two']}" placeholder="Re-enter Password" required />
					</div>
				</div>
				<div class="show attend-info">
					<div class="title">Will You Attend?</div>
					<select class="entertainment_only" name="{$formVariable}[attend_entertainment_only]">
						<option value="">Please Select...</option>
						{$entertainmentOptions}
					</select>
					<p>Please note that ID will be required to enter the event and all attendess <b><u>must</u></b> be 19 years or older.</p>
				</div>
				<div class="hide diet-info" data-dietinfo="{$registerInfoArr['attend_entertainment_only']}">
					<div class="title">Dietary Requirements</div>
					<select class="diet-info-select" name="{$formVariable}[employee_dietary_requirements]">
						<option value="">Please Select...</option>
						{$dietaryOptions}
					</select>
					<textarea name="{$formVariable}[employee_dietary_requirements_other]" placeholder="If you would like to add any additional info, please do so here.">{$registerInfoArr['employee_dietary_requirements_other']}</textarea>
				</div>
				<div class="show bring-guest">
					<div class="title">Will You Bring A Guest?</div>
					<select class="has_guest" name="{$formVariable}[has_guest]" value="{$registerInfoArr['has_guest']}">
						<option value="">Please Select...</option>
						{$bringGuestOptions}
					</select>
				</div>
				<div class="hide guest-info">
					<h3 class="sub-heading">ENTER THEIR DETAILS BELOW</h3>
					<div class="required">
						<input type="text" name="{$formVariable}[guest_first_name]" value="{$registerInfoArr['guest_first_name']}" placeholder="First Name" />
					</div>
					<div class="required">
						<input type="text" name="{$formVariable}[guest_last_name]" value="{$registerInfoArr['guest_last_name']}" placeholder="Last name" />
					</div>
				</div>
				<div class="hide guest-diet-info">
					<select class="guest-diet-info-select" name="{$formVariable}[guest_dietary_requirements]">
						<option value="">-- DIETARY REQUIREMENTS --</option>
						{$dietaryGuestOptions}
					</select><span class="yellow">*</span>
					<textarea name="{$formVariable}[guest_dietary_requirements_other]" placeholder="If you would like to add any additional info, please do so here.">{$registerInfoArr['guest_dietary_requirements_other']}</textarea>
				</div>
				<input type="hidden" name="{$formVariable}[step]" value="{$formStep}" />
				<button type="submit" name="{$formVariable}[button]" value="SUBMIT REGISTRATION" >SUBMIT REGISTRATION</button>
			</form>
			{$Thanks}
			<p class="thanks" id="kpmg-{$formVariable}-ajax-thanks-area"></p>

OJAMBO;

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
			'password_one' => 'password',
			'password_two' => 'password',
			'employee_email_address' => 'email',
			'employee_first_name' => 'text',
			'employee_last_name' => 'text',
			'employee_dietary_requirements' => 'text',
			'employee_dietary_requirements_other' => 'text',
			'guest_first_name' => 'text',
			'guest_last_name' => 'text',
			'guest_dietary_requirements' => 'text',
			'guest_dietary_requirements_other' => 'text',
			'has_guest' => 'text',
			'attend_entertainment_only' => 'number'
			//'employee_status' => 'text',
		);
		$updateID = "employee_email_address";

		if ( $this->adminrole != NULL && ($_POST['kpmg_formaction'] == $this->formaction) )
		{
			$saveTableFieldsResult = kpmg_getDatabaseTableColumns($saveInTable);
			$saveTableFieldsArr = array();
			$saveID = NULL;

			foreach($saveTableFieldsResult as $row)
			{
				$fieldName = $row['Field'];
				// Validate
				$dataArr[$fieldName] = isset($_POST[$formVariable][$fieldName]) ? $_POST[$formVariable][$fieldName] : false;
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
								if ( in_array($employeeListInfo['employee_status'], array('declined', 'terminated')) )
								{
									// Check If Email On Employee List
									$this->errors .= "<p class\"smaill\">The {$humanLabel} is not allowed</p>";
								}
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

			// Validate Password
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
			}
			if ( $this->errors == "" )
			{
				// Add Passwords
				$saveArr['password_one'] = $dataArr['password_one'];
				$saveArr['password_two'] = $dataArr['password_two'];
			}

			// Save Data
			if ( $this->errors == "" )
			{
				$saveArr['employee_email_address'] = isset($saveArr['employee_email_address']) ? $saveArr['employee_email_address'] : $saveIDArr['employee_email_address'];
				// Save Registration Step Two In Database
				$userdata = kpmg_generateEmployeeData($saveArr);

				// Save Database Information
				$userID = wp_insert_user($userdata);
				if ( is_wp_error($userID) )
				{
					$this->errors .= "<p class=\"small\">An error occured while creating new user</p>";
				}
				else
				{
					// Save Registration Data After Addition Of Employee ID
					$saveArr['user_id'] = $userID;
					$saveArr['employee_status'] = "registered";
					$registrationdata = kpmg_generateRegistrationData($saveArr);
					$registrationdatafieldtypes = kpmg_generateFieldTypes($registrationdata);
					if ( $wpdb->insert($wpdb->kpmg_registration_details, $registrationdata, $registrationdatafieldtypes) === FALSE )
					{
						$this->errors .= "<p class=\"small\">An error occured while saving registration details</p>";
					}
					else
					{
						$this->thanks .= "<p class=\"thanks\">Thank you. Your registration has now been confirmed.</p>";
					}
				}
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
		$saveTable = $wpdb->kpmg_registration_details;
		$formVariable = $this->formvariable;
		$arr = array();
		$arrTypes = array (
			'password_one' => 'text',
			'password_two' => 'text',
			'employee_email_address' => 'text',
			'employee_first_name' => 'text',
			'employee_last_name' => 'text',
			'employee_dietary_requirements' => 'text',
			'employee_dietary_requirements_other' => 'text',
			'guest_first_name' => 'text',
			'guest_last_name' => 'text',
			'guest_dietary_requirements' => 'text',
			'guest_dietary_requirements_other' => 'text',
			'has_guest' => 'text',
			'attend_entertainment_only' => 'number',
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
				}
			}
			// Password Fields
			$arr['password_one'] =  isset($_POST[$formVariable]['password_one']) ? trim($_POST[$formVariable]['password_one']) : "";
			$arr['password_two'] =  isset($_POST[$formVariable]['password_two']) ? trim($_POST[$formVariable]['password_two']) : "";
		}

		return $arr;
	}

}
