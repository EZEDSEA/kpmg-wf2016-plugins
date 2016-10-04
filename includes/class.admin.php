<?php

/**
 * File: class.admin.php
 * Description of class.admin.php
 *
 * Author: edward <http://ojambo.com>
 * Copyright: 2016  
 * Created : 2016-09-13 6:57:30 AM
 * Last Modified : 2016-09-13T10:57:30Z
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class KPMG_Admin {
	
	// Variables
	private $salt;
	private $registrationerrors;
	private $reserveagrouperrors;
	private $errorsupdateinfo;
	private $thanksupdateinfo;
	private $errorschangetable;
	private $thankschangetable;
	private $errorsreportreservations;
	private $thanksreportreservations;
	private $errorsreportdietary;
	private $thanksreportdietary;
	private $errorsreportmaster;
	private $thanksreportmaster;
	private $adminrole = NULL;
	private $admincutoffformaction;
	private $admincutofferrors;
	
	// Constructor
	public function __construct() 
	{
		 $this->salt = KPMGWF_Salt;
		 $this->registrationstep = 0;
		 $this->registrationerrors = "";
		 $this->reserveagrouperrors = "";
		 $this->cancelregistrationerrors = "";
		 $this->cancelthanks = "";
		 $this->updateattenderrors = "";
		 $this->updatedieterrors = "";
		 $this->updateattendthanks = "";
		 $this->updatedietthanks = "";
		 $this->admincutofferrors = "";
		 $this->updateguesterrors = "";
		 $this->updateguesthanks = "";
		 $this->admincutoffformaction = "admin_cutoff_date";

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
			 }
		 }
	}
   
	// Admin Regsiter Someone Form
	public function adminRegisterSomeoneForm()
	{
		 // Variables
		 $registerInfoArr = $this->employeeRegisterData();
		 $registerErrors = $this->registrationerrors;
		 $entertainmentDataArr = kpmg_entertainnmentOptionsData();
		 $entertainmentOptions = kpmg_generateKeySelectOptions($entertainmentDataArr, $registerInfoArr['entertainment_only']);
		 $dietaryDataArr = kpmg_dietaryRequirementOptionsData();
		 $dietaryOptions = kpmg_generateSelectOptions($dietaryDataArr, $registerInfoArr['dietary_requirements']);
		 $bringGuestDataArr = kpmg_yesNoOptionsData();
		 $bringGuestOptions = kpmg_generateSelectOptions($bringGuestDataArr, $registerInfoArr['has_guest']);
		 $dietaryGuestOptions = kpmg_generateSelectOptions($dietaryDataArr, $registerInfoArr['dietary_requirements_guest']);	   

		 $employeeRegisterForm = <<<OJAMBO
			 <form id="kpmg-registration-form" class="signup-01" method="post" action="">
				<div class="errors">
					{$registerErrors}
					<p class="small" id="kpmg-registration-ajax-error-area"></p>
				</div>
				 div class="show register-info"> 
					 <h3>Register Someone</h3>
					 <p><span class="yellow">*</span>Indicates a required field</p>
					 <input type="text" name="register[first_name]" value="{$registerInfoArr['first_name']}" placeholder="First Name" required /><span class="yellow">*</span>
					 <input type="text" name="register[last_name]" value="{$registerInfoArr['last_name']}" placeholder="Last name" required /><span class="yellow">*</span>
					 <input class="email_address" id="kpmg_registration_email_address" type="email" name="register[email_address]" value="{$registerInfoArr['email_address']}" placeholder="Email address" required /><span class="yellow">*</span>
					 <div class="results" id="kpmg-registration-ajax-results-area"></div>
					 <p>Password must be a minimum of 8 characters.</p>
					 <p>Must contain at least one number and a lowercase and uppercase character.</p>
					 <input type="password" name="register[password_one]" value="{$registerInfoArr['password_one']}" placeholder="Password" required /><span class="yellow">*</span>
					 <input type="password" name="register[password_two]" value="{$registerInfoArr['password_two']}" placeholder="Re-enter Password" required /><span class="yellow">*</span>
					 </div>
				 <div class="show attend-info"> 
					 <h3>Will You Attend:</h3>
					 <p><span class="yellow">*</span>Indicates a required field</p>
					 <select name="register[entertainment_only]">
						 <option value="">Please Select...</option>
						 {$entertainmentOptions}
					 </select>
				 </div>
				 <p>Please note that ID will be required to enter the event and all attendess <b><u>must</u></b> be 19 years or older.</p>
				 <div class="show diet-info" data-dietinfo="{$registerInfoArr['entertainment_only']}"> 
					 <h3>Dietary Requirements</h3>
					 <select name="register[dietary_requirements]">
						 <option value="">Please Select...</option>
						 {$dietaryOptions}
					 </select>
					 <textarea name="register[dietary_requirements_other]" placeholder="If you would like to add any additional info, please do so here.">{$registerInfoArr['dietary_requirements_other']}</textarea>
				 </div>
				 <div class="show bring-guest">
					 <h3>Will You Bring A Guest?</h3>
					 <select class="has_guest" name="register[has_guest]" value="{$registerInfoArr['has_guest']}">
						 <option value="">Please Select...</option>
						 {$bringGuestOptions}
					 </select>
				 </div>
				 <div class="show guest-info">
					 <h3>ENTER THEIR DETAILS BELOW</h3>
					 <input type="text" name="register[first_name_guest]" value="{$registerInfoArr['first_name_guest']}" placeholder="First Name" /><span class="yellow">*</span>
					 <input type="text" name="register[last_name_guest]" value="{$registerInfoArr['last_name_guest']}" placeholder="Last name" /><span class="yellow">*</span>
				 </div>
				 <div class="show guest-diet-info">
					 <select name="register[dietary_requirements_guest]">
						 <option value="">-- DIETARY REQUIREMENTS --</option>
						 {$dietaryGuestOptions}
					 </select><span class="yellow">*</span>
					 <textarea name="register[dietary_requirements_other_guest]" placeholder="If you would like to add any additional info, please do so here.">{$registerInfoArr['dietary_requirements_other_guest']}</textarea>
				 </div>
			 </form>	
OJAMBO;

		 return $employeeRegisterForm;
	}
   
	// Admin Update Someone Form
	public function adminUpdateSomeoneForm()
	{
		 // Variables
		 $registerInfoArr = $this->employeeRegisterData();
		 $registerErrors = $this->registrationerrors;
		 $entertainmentDataArr = kpmg_entertainnmentOptionsData();
		 $entertainmentOptions = kpmg_generateKeySelectOptions($entertainmentDataArr, $registerInfoArr['entertainment_only']);
		 $dietaryDataArr = kpmg_dietaryRequirementOptionsData();
		 $dietaryOptions = kpmg_generateSelectOptions($dietaryDataArr, $registerInfoArr['dietary_requirements']);
		 $bringGuestDataArr = kpmg_yesNoOptionsData();
		 $bringGuestOptions = kpmg_generateSelectOptions($bringGuestDataArr, $registerInfoArr['has_guest']);
		 $dietaryGuestOptions = kpmg_generateSelectOptions($dietaryDataArr, $registerInfoArr['dietary_requirements_guest']);	   

		 $employeeRegisterForm = <<<OJAMBO
			 <form id="kpmg-registration-form" class="signup-01" method="post" action="">
				<div class="errors">
					{$registerErrors}
					<p class="small" id="kpmg-registration-ajax-error-area"></p>
				</div>					 
				 <div class="show attend-info"> 
					 <h3>Will You Attend:</h3>
					 <p><span class="yellow">*</span>Indicates a required field</p>
					 <select name="register[entertainment_only]">
						 <option value="">Please Select...</option>
						 {$entertainmentOptions}
					 </select>
				 </div>
				 <p>Please note that ID will be required to enter the event and all attendess <b><u>must</u></b> be 19 years or older.</p>
				 <div class="show diet-info" data-dietinfo="{$registerInfoArr['entertainment_only']}"> 
					 <h3>Dietary Requirements</h3>
					 <select name="register[dietary_requirements]">
						 <option value="">Please Select...</option>
						 {$dietaryOptions}
					 </select>
					 <textarea name="register[dietary_requirements_other]" placeholder="If you would like to add any additional info, please do so here.">{$registerInfoArr['dietary_requirements_other']}</textarea>
				 </div>
				 <div class="show bring-guest">
					 <h3>Will You Bring A Guest?</h3>
					 <select class="has_guest" name="register[has_guest]" value="{$registerInfoArr['has_guest']}">
						 <option value="">Please Select...</option>
						 {$bringGuestOptions}
					 </select>
				 </div>
				 <div class="show guest-info">
					 <h3>ENTER THEIR DETAILS BELOW</h3>
					 <input type="text" name="register[first_name_guest]" value="{$registerInfoArr['first_name_guest']}" placeholder="First Name" /><span class="yellow">*</span>
					 <input type="text" name="register[last_name_guest]" value="{$registerInfoArr['last_name_guest']}" placeholder="Last name" /><span class="yellow">*</span>
				 </div>
				 <div class="show guest-diet-info">
					 <select name="register[dietary_requirements_guest]">
						 <option value="">-- DIETARY REQUIREMENTS --</option>
						 {$dietaryGuestOptions}
					 </select><span class="yellow">*</span>
					 <textarea name="register[dietary_requirements_other_guest]" placeholder="If you would like to add any additional info, please do so here.">{$registerInfoArr['dietary_requirements_other_guest']}</textarea>
				 </div>
			 </form>	
OJAMBO;

		 return $employeeRegisterForm;
	}
   
	// Admin Reserve Someone Form
	public function adminReserveGroupForm()
	{
		// Variables
		$reserveInfoArr = $this->employeeReserveAGroupData();
		$reserveGroupInputs = kpmg_generateReserveGroupInputs($reserveInfoArr);
		$reserveGroupInputs = kpmg_generateReserveGroupInputs($reserveInfoArr);
		$reserveErrors = $this->reserveagrouperrors;
		$formActionURL = ""; // WAS $this->pagegroup;

		$employeeReserveGroupForm = <<<OJAMBO
			<div id="addtogroupparent">
				<input id="kpmg-create-group-input" placeholder="Enter a kpmg email address" data-ajax="kpmg_seat_ajax-results-area" autocomplete="off" />
				<div id="kpmg_seat_ajax-results-area"></div>
			</div>
			<button id="kpmg-add-to-group-button" class="add_to_grp_btn">Add to my group</button>
			<p>Please note that photo ID will be required to enter the event and all attendees must be 19 years or older.  The name on the printed ticket will have to match the photo ID.</p>
			<form id="kpmg-reserve-a-group-form" class="signup-01" method="post" action="{$formActionURL}">
				<div class="errors">
					{$reserveErrors}
					<p class="small" id="kpmg-reserve-a-group-ajax-error-area"></p>
				</div>
				<div class="show">
				{$reserveGroupInputs}
				</div>
				<input type="hidden" name="reserveagroup[step]" value="2" />
				<input type="submit" name="reserveagroup[button]" value="SUBMIT" />
			</form>	
OJAMBO;

		return $employeeReserveGroupForm;
	}   
   
	// Admin Cutoff Date Form
	public function adminCutoffDateForm()
	{
		// Variables
		$InfoArr = kpmg_getRegistrationCutoff();
		$Inputs = kpmg_generateRegistrationCutoff($InfoArr);
		$Errors = $this->admincutofferrors;
		$formAction = $this->admincutoffformaction;

		$Form = <<<OJAMBO
			<form id="kpmg-reserve-a-group-form" class="signup-01" method="post" action="">
				<div class="errors">
					{$Errors}
					<p class="small" id="kpmg-admincutoff-ajax-error-area"></p>
				</div>
				<input type="hidden" name="kpmg_formaction" value="{$formAction}" />
				<div class="show">
				{$Inputs}
				</div>
				<input type="hidden" name="admincutoff[step]" value="2" />
				<input type="submit" name="admincutoff[button]" value="SUBMIT" />
			</form>	
OJAMBO;

		return $Form;
	}   
   
	// Admin Upload Employees Form
	public function adminUploadEmployeesForm()
	{
		// Variables
		$InfoArr = kpmg_getRegistrationCutoff();
		$Inputs = kpmg_generateRegistrationCutoff($InfoArr);
		$Errors = $this->admincutofferrors;
		$formAction = $this->admincutoffformaction;

		$Form = <<<OJAMBO
			<form id="kpmg-reserve-a-group-form" class="signup-01" method="post" action="" enctype="multipart/form-data">
				<div class="errors">
					{$Errors}
					<p class="small" id="kpmg-admincutoff-ajax-error-area"></p>
				</div>
				<input type="hidden" name="kpmg_formaction" value="{$formAction}" />
				<div class="show">
					<input type="file" name="uploadEmployees" id="uploadEmployees">
				</div>
				<input type="hidden" name="admincutoff[step]" value="2" />
				<input type="submit" name="admincutoff[button]" value="Upload" />
			</form>	
OJAMBO;

		return $Form;
	}   
   
	// Admin Report Master Form
	public function adminReportMasterForm()
	{
		global $user;
		
			
		$adminRole = KPMGWF_AdminRole;
		
		// Variable
		$errorMsg = $this->errorsreportreservations;
		$thanksMsg = $this->thanksreportreservations;
		$adminReportDietaryForm = "";
		
		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($adminRole, $roles) )
			{
				$editDetails = array();
				$editDetails['email_address'] = "";
				// Update if Posted
				if ( isset($_POST['adminreportmaster']) )
				{
					if ( isset($_POST['adminreportmaster']['email_address']) )
					{
						$editDetails['email_address'] = trim($_POST['adminreportmaster']['email_address']);
					}
					
				}
				
				$adminReportDietaryForm = <<<OJAMBO
			<form id="admin-report-master-form" class="signup-01" method="post" action="{$_SERVER['REQUEST_URI']}">
				<div class="errors">
					{$errorMsg}
					<p class="small" id="admin-report-master-ajax-error-area"></p>
				</div>
				{$thanksMsg}
				<a class="button" href="?download=adminreportmaster">CSV</a>
				<input type="email" class="email_address" data-key="admin_change_table" name="adminreportmaster[email_address]" value="{$editDetails['email_address']}" placeholder="Email" required />
				<input type="hidden" name="adminreportmaster[step]" value="2" />
				<input type="submit" name="adminreportmaster[button]" value="SEND" />
			</form>	
OJAMBO;
				
			}
		}

		return $adminReportDietaryForm;
	}
	
	// Admin Report Dietary Form
	public function adminReportDietaryForm()
	{
		global $user;
		
		
			
		$adminRole = KPMGWF_AdminRole;
		
		// Variables
		$errorMsg = $this->errorsreportreservations;
		$thanksMsg = $this->thanksreportreservations;
		$adminReportDietaryForm = "";
		
		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($adminRole, $roles) )
			{
				$editDetails = array();
				$editDetails['email_address'] = "";
				// Update if Posted
				if ( isset($_POST['adminreportdietary']) )
				{
					if ( isset($_POST['adminreportdietary']['email_address']) )
					{
						$editDetails['email_address'] = trim($_POST['adminreportreservations']['email_address']);
					}
					
				}
				
				$adminReportDietaryForm = <<<OJAMBO
			<form id="admin-report-dietary-form" class="signup-01" method="post" action="{$_SERVER['REQUEST_URI']}">
				<div class="errors">
					{$errorMsg}
					<p class="small" id="admin-report-dietary-ajax-error-area"></p>
				</div>
				{$thanksMsg}
				<a class="button" href="?download=adminreportdietary">CSV</a>
				<input type="email" class="email_address" data-key="admin_change_table" name="adminreportdietary[email_address]" value="{$editDetails['email_address']}" placeholder="Email" required />
				<input type="hidden" name="adminreportdietary[step]" value="2" />
				<input type="submit" name="adminreportdietary[button]" value="SEND" />
			</form>	
OJAMBO;
				
			}
		}

		return $adminReportDietaryForm;
	}	
	
	// Admin Report Reservations Form
	public function adminReportReservationsForm()
	{
		global $user;
		
		
			
		$adminRole = KPMGWF_AdminRole;
		
		// Variables
		$errorMsg = $this->errorsreportreservations;
		$thanksMsg = $this->thanksreportreservations;
		$adminReportReservationsForm = "";
		
		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($adminRole, $roles) )
			{
				$editDetails = array();
				$editDetails['email_address'] = "";
				// Update if Posted
				if ( isset($_POST['adminreportreservations']) )
				{
					if ( isset($_POST['adminreportreservations']['email_address']) )
					{
						$editDetails['email_address'] = trim($_POST['adminreportreservations']['email_address']);
					}
					
				}
				
				$adminReportReservationsForm = <<<OJAMBO
			<form id="admin-report-reservations-form" class="signup-01" method="post" action="{$_SERVER['REQUEST_URI']}">
				<div class="errors">
					{$errorMsg}
					<p class="small" id="admin-report-reservations-ajax-error-area"></p>
				</div>
				{$thanksMsg}
				<a class="button" href="?download=adminreportreservations">CSV</a>
				<input type="email" class="email_address" data-key="admin_change_table" name="adminreportreservations[email_address]" value="{$editDetails['email_address']}" placeholder="Email" required />
				<input type="hidden" name="adminreportreservations[step]" value="2" />
				<input type="submit" name="adminreportreservations[button]" value="SEND" />
			</form>	
OJAMBO;
				
			}
		}

		return $adminReportReservationsForm;
	}	
	
	// Admin Change Table Form
	public function adminChangeTableForm()
	{
		global $user;
		
		
			
		$adminRole = KPMGWF_AdminRole;
		
		// Variables
		$errorMsg = $this->errorsupdateinfo;
		$thanksMsg = $this->thanksupdateinfo;
		$adminChangeTableForm = "";
		
		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($adminRole, $roles) )
			{
				$editDetails = array();
				$editDetails['employee_id'] = 0;
				$editDetails['email_address'] = "";
				// Update if Posted
				if ( isset($_POST['adminchangetable']) )
				{
					if ( isset($_POST['adminchangetable']['employee_id']) )
					{
						$editDetails['employee_id'] = trim($_POST['adminchangetable']['employee_id']);
					}
					if ( isset($_POST['adminchangetable']['email_address']) )
					{
						$editDetails['email_address'] = trim($_POST['adminchangetable']['email_address']);
					}
					
				}
				
				$adminChangeTableForm = <<<OJAMBO
			<form id="admin-change-table-form" class="signup-01" method="post" action="{$_SERVER['REQUEST_URI']}">
				<div class="errors">
					{$errorMsg}
					<p class="small" id="admin-change-table-ajax-error-area"></p>
				</div>
				{$thanksMsg}
				<input type="hidden" id="admin_change_table_employee_id" name="adminchangetable[employee_id]" value="{$editDetails['employee_id']}" />
				<input type="email" id="admin_change_table_email_address" class="email_address" data-key="admin_change_table" name="adminchangetable[email_address]" value="{$editDetails['email_address']}" placeholder="Enter Email" required />
				<input type="hidden" name="adminchangetable[step]" value="2" />
				<input type="submit" name="adminchangetable[button]" value="EDIT" />
			</form>	
OJAMBO;
				
			}
		}

		return $adminChangeTableForm;
	}
	
	// Admin Update Info Form
	public function adminUpdateInfoForm()
	{
		global $user;
		
		
			
		$adminRole = KPMGWF_AdminRole;
		
		// Variables
		$errorMsg = $this->errorsupdateinfo;
		$thanksMsg = $this->thanksupdateinfo;
		$adminUpdateInfoForm = "";
		
		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($adminRole, $roles) )
			{
				$editDetails = array();
				$editDetails['employee_id'] = 0;
				$editDetails['email_address'] = "";
				// Update if Posted
				if ( isset($_POST['adminupdateinfo']) )
				{
					if ( isset($_POST['adminupdateinfo']['employee_id']) )
					{
						$editDetails['employee_id'] = trim($_POST['adminupdateinfo']['employee_id']);
					}
					if ( isset($_POST['adminupdateinfo']['email_address']) )
					{
						$editDetails['email_address'] = trim($_POST['adminupdateinfo']['email_address']);
					}
					
				}
				
				$adminUpdateInfoForm = <<<OJAMBO
			<form id="admin-update-info-form" class="signup-01" method="post" action="{$_SERVER['REQUEST_URI']}">
				<div class="errors">
					{$errorMsg}
					<p class="small" id="admin-update-info-ajax-error-area"></p>
				</div>
				{$thanksMsg}
				<input type="hidden" id="admin_update_info_employee_id" name="adminupdateinfo[employee_id]" value="{$editDetails['employee_id']}" />
				<input type="email" id="admin_update_info_email_address" class="email_address" data-key="admin_update_info" name="adminupdateinfo[email_address]" value="{$editDetails['email_address']}" placeholder="Enter Email" required />
				<input type="hidden" name="adminupdateinfo[step]" value="2" />
				<input type="submit" name="adminupdateinfo[button]" value="EDIT" />
			</form>	
OJAMBO;
				
			}
		}

		return $adminUpdateInfoForm;
	}

	// Admin Change Table Authorization
	public function adminChangeTableAuthorization( ) 
	{
		global $wpdb;
		global $user;
		
		
		
		$adminRole = KPMGWF_AdminRole;

		
		/*	if ( in_array($adminRole, $roles) )
			{
				$userID = isset($_SESSION['adminupdateinfo']['employee_id']) ? $_SESSION['adminupdateinfo']['employee_id'] : $userID;
			}		
		
		*/
		// Check if Employee Id Is Correct
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($adminRole, $roles) )
			{
				$admindata = array();
				if (isset($_POST['adminchangetable']['employee_id']) )
				{
					 $employee_id = intval($_POST['adminchangetable']['employee_id']);
					 
					 // Check if Valid Employee ID
					 $employeeDetails = $KPMGWinterFestFunctions->getEmployeeDetails($employee_id);
					 if ( isset($employeeDetails['employee_first_name']) )
					 {
						$_SESSION['adminchangetable']['employee_id'] = $employee_id;
						wp_redirect($this->pagereservation);
					 }
					 else
					 {
						 $this->errorsupdateinfo = "<p class=\"small\">The user does not exist.</p>";
					 }
				}
				else
				{
					$this->errorsupdateinfo = "<p class=\"small\">You must select a valid user to edit.</p>";
				}
			}
			else
			{
				$this->errorsupdateinfo .= "<p class=\"small\">You must login as an admin to edit.</p>";
			}
		}
		else
		{
			$this->errorsupdateinfo .= "<p class=\"small\">You must login as an admin to edit.</p>";
		}
		
		// Display Errors & Form
		return $this->adminChangeTableForm();
	}		
	
	// Admin Update Info Authorization
	public function adminUpdateInfoAuthorization( ) 
	{
		global $wpdb;
		global $user;
		
		
		
		$adminRole = KPMGWF_AdminRole;	
		
		
		// Check if Employee Id Is Correct
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($adminRole, $roles) )
			{
				$admindata = array();
				if (isset($_POST['adminupdateinfo']['employee_id']) )
				{
					 $employee_id = intval($_POST['adminupdateinfo']['employee_id']);
					 
					 // Check if Valid Employee ID
					 $employeeDetails = $KPMGWinterFestFunctions->getEmployeeDetails($employee_id);
					 if ( isset($employeeDetails['employee_first_name']) )
					 {
						$_SESSION['adminupdateinfo']['employee_id'] = $employee_id;
						wp_redirect($this->pagemyinfo);
					 }
					 else
					 {
						 $this->errorsupdateinfo = "<p class=\"small\">The user does not exist.</p>";
					 }
				}
				else
				{
					$this->errorsupdateinfo = "<p class=\"small\">You must select a valid user to edit.</p>";
				}
			}
			else
			{
				$this->errorsupdateinfo .= "<p class=\"small\">You must login as an admin to edit.</p>";
			}
		}
		else
		{
			$this->errorsupdateinfo .= "<p class=\"small\">You must login as an admin to edit.</p>";
		}
		
		// Display Errors & Form
		return $this->adminUpdateInfoForm();
	}	
	
	// Admin Report Reservations Authorization
	public function adminReportReservationsAuthorization( ) 
	{
		global $wpdb;
		global $user;
		
		
		
		$adminRole = KPMGWF_AdminRole;	
		
		
		// Check if Employee Id Is Correct
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($adminRole, $roles) )
			{
				$admindata = array();
				if ( isset($_POST['adminreportreservations']['email_address']) )
				{
					 $email_address = $_POST['adminreportreservations']['email_address'];
					 
					 // Check if Valid Email Address
					 if ( !filter_var($email_address, FILTER_VALIDATE_EMAIL) )
					{
						$this->errorsreportreservations .= "<p class=\"small\">The email address is invalid</p>";
					}
					else
					{
						// Get Data
						$reportData = $this->adminReportReservationsData();
						$csvFile = $this->adminGenerateCSVString($reportData);
						$sendReport = $KPMGWinterFestFunctions->sendCSVEmail($csvFile, $email_address);
						if ( $sendReport )
						{
							$this->thanksreportreservations .= "<p class=\"small\"The Report was sent to the provided email</p>";
						}
					}
				}
				else
				{
					$this->errorsreportreservations = "<p class=\"small\">You must enter an email adress to send the file.</p>";
				}
			}
			else
			{
				$this->errorsreportreservations .= "<p class=\"small\">You must login as an admin to send file.</p>";
			}
		}
		else
		{
			$this->errorsreportreservations .= "<p class=\"small\">You must login as an admin to send file.</p>";
		}
		
		// Display Errors & Form
		return $this->adminReportReservationsForm();
	}	
	
	
	
	// Admin Report Master Process
	public function adminReportMasterProcess()
	{
		global $wp_roles;
		
		
		$adminRole = KPMGWF_AdminRole;
		
		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($adminRole, $roles) )
			{
				if ( isset($_POST['adminreportmaster']['step']) )
				{
					return $this->adminReportMasterAuthorization();
				}
				elseif ( isset($_GET['download']) )
				{
					if ( $_GET['download'] == 'adminreportmaster' )
					{
						$this->adminDownloadReports();
					}
				}

				return $this->adminReportMasterForm();
			}
		}
		else
		{
			return false;
		}
		
	}	
	
	// Admin Report Dietary Process
	public function adminReportDietaryProcess()
	{
		global $wp_roles;
		
		
		$adminRole = KPMGWF_AdminRole;
		
		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($adminRole, $roles) )
			{
				if ( isset($_POST['adminreportdietary']['step']) )
				{
					return $this->adminReportDietaryAuthorization();
				}
				elseif ( isset($_GET['download']) )
				{
					if ( $_GET['download'] == 'adminreportdietary' )
					{
						$this->adminDownloadReports();
					}
				}

				return $this->adminReportDietaryForm();
			}
		}
		else
		{
			return false;
		}
		
	}	

	// Admin Report Reservations Process
	public function adminReportReservationsProcess()
	{
		global $wp_roles;
		
		
		$adminRole = KPMGWF_AdminRole;
		
		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($adminRole, $roles) )
			{
				if ( isset($_POST['adminreportreservations']['step']) )
				{
					return $this->adminReportReservationsAuthorization();
				}
				elseif ( isset($_GET['download']) )
				{
					if ( $_GET['download'] == 'adminreportreservations' )
					{
						$this->adminDownloadReports();
					}
				}

				return $this->adminReportReservationsForm();
			}
		}
		else
		{
			return false;
		}
		
	}	
	
	// Admin Change Table Process
	public function adminChangeTableProcess()
	{
		global $wp_roles;
		
		
		$adminRole = KPMGWF_AdminRole;
		
		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($adminRole, $roles) )
			{
				if ( isset($_POST['adminchangetable']['step']) )
				{
					return $this->adminChangeTableAuthorization();
				}

				return $this->adminChangeTableForm();
			}
		}
		else
		{
			return false;
		}
		
	}	
	
	// Admin Update Information Process
	public function adminUpdateInfoProcess()
	{
		global $wp_roles;
		
		
		$adminRole = KPMGWF_AdminRole;
		
		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($adminRole, $roles) )
			{
				if ( isset($_POST['adminupdateinfo']['step']) )
				{
					return $this->adminUpdateInfoAuthorization();
				}

				return $this->adminUpdateInfoForm();
			}
		}
		else
		{
			return false;
		}
		
	}
	
	// Admin Registration Cut Off Process
	public function adminRegsiterCutoffProcess()
	{
		if ( $this->adminrole != NULL )
		{
			
			if ( isset($_POST['admincutoff']['step']) )
			{
				return $this->adminFormAction();
			}

			return $this->adminUploadEmployeesForm();
		}
		else
		{
			return false;
		}
		
	}
	
	// Admin Upload Employees Process
	public function adminUploadEmployeesProcess()
	{
		if ( $this->adminrole != NULL )
		{
			
			if ( isset($_POST['admincutoff']['step']) )
			{
				return $this->adminFormAction();
			}

			return $this->adminCutoffDateForm();
		}
		else
		{
			return false;
		}
		
	}
	
	// Admin Create Registration Process
	public function adminCreateRegisterProcess()
	{
		if ( $this->adminrole != NULL )
		{
			
			if ( isset($_POST['admincutoff']['step']) )
			{
				return $this->adminFormAction();
			}

			return $this->adminRegisterSomeoneForm();
		}
		else
		{
			return false;
		}
		
	}
	
	// Admin Create Group Process
	public function adminCreateGroupProcess()
	{
		if ( $this->adminrole != NULL )
		{
			
			if ( isset($_POST['admincutoff']['step']) )
			{
				return $this->adminFormAction();
			}

			return $this->adminReserveGroupForm();
		}
		else
		{
			return false;
		}
		
	}
	
	// Admin Download Reports
	public function adminDownloadReports()
	{	
		global $wp_roles;
		
		
		$adminRole = KPMGWF_AdminRole;
		
		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($adminRole, $roles) )
			{
				if ( isset($_GET['download']) )
				{
					$timestamp = date("ymd_his");
					$chosendownload = $_GET['download'];
					if ( $chosendownload == 'adminreportreservations' )
					{
						// Report of table host and names of all people at table
						$reportData = $this->adminReportReservationsData();
						$filename = "reservations_{$timestamp}.csv";
						// Generate CSV File
						$this->adminDownloadReportsSCV($reportData, $filename);
					}
					elseif ( $chosendownload == 'adminreportdietary' )
					{
						// Report of dietary requirements for registrants and guests
						$reportData = $this->adminReportDietaryData();
						$filename = "dietary_{$timestamp}.csv";
						// Generate CSV File
						$this->adminDownloadReportsSCV($reportData, $filename);
					}
					elseif ( $chosendownload == 'adminreportmaster' )
					{
						// Report of dietary requirements for registrants and guests
						$reportData = $this->adminReportMasterData();
						$filename = "dietary_{$timestamp}.csv";
						// Generate CSV File
						$this->adminDownloadReportsSCV($reportData, $filename);					
					}
				}
			}
		}	
		
	}
	
	// Admin Download Reports CSV
	public function adminDownloadReportsSCV($data, $filename)
	{
		if ( $data )
		{
			// Clean Output Buffer
			ob_clean();
			
			// Create File Pointer Connected To Output Stream
			$output = fopen("php://output", "w");
			
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
			
			// Get File Contents
			$csvFile = stream_get_contents($output);
			
			// Close File Pointer
			fclose($output);
			
			// Output headers so that file is downloaded
			header("Content-type: text/csv; charset=utf-8");
			header('Content-Length: '.strlen($csvFile));
			header("Content-Disposition: attachment; filename={$filename}");
			
			exit($csvFile);
		}
	}
	
	// Admin Generate CSV String
	public function adminGenerateCSVString($data)
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
	
	
	// Admin Report Reservations Data
	public function adminReportReservationsData()
	{
		global $wpdb;
		
		$reportData = $wpdb->get_results(
			" SELECT p1.employee_first_name AS host_first_name "
			. " ,p1.employee_last_name AS host_last_name "
			. " ,p1.guest_first_name AS host_guest_first_name "
			. " ,p1.guest_last_name AS host_guest_first_name "
			. " ,p2.employee_first_name AS seat2_first_name "
			. " ,p2.employee_last_name AS seat2_last_name "
			. " ,p2.guest_first_name AS seat2_guest_first_name "
			. " ,p2.guest_last_name AS seat2_guest_first_name "
			. " ,p3.employee_first_name AS seat3_first_name "
			. " ,p3.employee_last_name AS seat3_last_name "
			. " ,p3.guest_first_name AS seat3_guest_first_name "
			. " ,p3.guest_last_name AS seat3_guest_first_name "
			. " ,p4.employee_first_name AS seat4_first_name "
			. " ,p4.employee_last_name AS seat4_last_name "
			. " ,p4.guest_first_name AS seat4_guest_first_name "
			. " ,p4.guest_last_name AS seat4_guest_first_name "
			. " ,p5.employee_first_name AS seat5_first_name "
			. " ,p5.employee_last_name AS seat5_last_name "
			. " ,p5.guest_first_name AS seat5_guest_first_name "
			. " ,p5.guest_last_name AS seat5_guest_first_name "
			. " FROM {$wpdb->kpmg_table_seats} AS ts "
			. " LEFT JOIN {$wpdb->kpmg_registration_details} AS p1 ON ts.employee_first_id = p1.employee_id "
			. " LEFT JOIN {$wpdb->kpmg_registration_details} AS p2 ON ts.employee_second_id = p2.employee_id "
			. " LEFT JOIN {$wpdb->kpmg_registration_details} AS p3 ON ts.employee_third_id = p3.employee_id "
			. " LEFT JOIN {$wpdb->kpmg_registration_details} AS p4 ON ts.employee_fourth_id = p4.employee_id"
			. " LEFT JOIN {$wpdb->kpmg_registration_details} AS p5 ON ts.employee_fifth_id = p5.employee_id  ",
				ARRAY_A
		);
			
		if ( count($reportData) > 0 )
		{
			return $reportData;
		}
		
		return false;
	}
	
	// Admin Report Dietary Data
	public function adminReportDietaryData()
	{
		global $wpdb;
		
		$reportData = $wpdb->get_results(
			" SELECT * "
			. " FROM {$wpdb->kpmg_registration_details} ",
				ARRAY_A
		);
			
		if ( count($reportData) > 0 )
		{
			return $reportData;
		}
		
		return false;
	}	
	
	// Admin Report Master Data
	public function adminReportMasterData()
	{
		global $wpdb;
		
		$reportData = $wpdb->get_results(
			" SELECT pr.* "
			. " ,p1.employee_first_name AS host_first_name "
			. " ,p1.employee_last_name AS host_last_name "
			. " ,p1.guest_first_name AS host_guest_first_name "
			. " ,p1.guest_last_name AS host_guest_first_name "
			. " ,p2.employee_first_name AS seat2_first_name "
			. " ,p2.employee_last_name AS seat2_last_name "
			. " ,p2.guest_first_name AS seat2_guest_first_name "
			. " ,p2.guest_last_name AS seat2_guest_first_name "
			. " ,p3.employee_first_name AS seat3_first_name "
			. " ,p3.employee_last_name AS seat3_last_name "
			. " ,p3.guest_first_name AS seat3_guest_first_name "
			. " ,p3.guest_last_name AS seat3_guest_first_name "
			. " ,p4.employee_first_name AS seat4_first_name "
			. " ,p4.employee_last_name AS seat4_last_name "
			. " ,p4.guest_first_name AS seat4_guest_first_name "
			. " ,p4.guest_last_name AS seat4_guest_first_name "
			. " ,p5.employee_first_name AS seat5_first_name "
			. " ,p5.employee_last_name AS seat5_last_name "
			. " ,p5.guest_first_name AS seat5_guest_first_name "
			. " ,p5.guest_last_name AS seat5_guest_first_name "
			. " FROM {$wpdb->kpmg_registration_details} AS pr "
			. " LEFT JOIN {$wpdb->kpmg_table_seats} AS ts ON ts.employee_first_id = pr.employee_id "
			. " LEFT JOIN {$wpdb->kpmg_registration_details} AS p1 ON ts.employee_first_id = p1.employee_id "
			. " LEFT JOIN {$wpdb->kpmg_registration_details} AS p2 ON ts.employee_second_id = p2.employee_id "
			. " LEFT JOIN {$wpdb->kpmg_registration_details} AS p3 ON ts.employee_third_id = p3.employee_id "
			. " LEFT JOIN {$wpdb->kpmg_registration_details} AS p4 ON ts.employee_fourth_id = p4.employee_id"
			. " LEFT JOIN {$wpdb->kpmg_registration_details} AS p5 ON ts.employee_fifth_id = p5.employee_id  ",
				ARRAY_A
		);
			
		if ( count($reportData) > 0 )
		{
			return $reportData;
		}
		
		return false;
	}
	
	
	// Admin Form Action Handler
	public function adminFormAction()
	{
		
	}
	
	
		
	// Employee Register Form Data
	public function employeeRegisterData() 
	{
		// Variables
		$registerInfoArr = array();
		
		// Registration Form Step One
		$registerInfoArr['first_name'] = isset($_POST['register']['first_name']) ? trim($_POST['register']['first_name']) : ((isset($_SESSION['kpmg_userdata']['first_name'])) ? $_SESSION['kpmg_userdata']['first_name'] : "");
		$registerInfoArr['last_name'] = isset($_POST['register']['last_name']) ? trim($_POST['register']['last_name']) : ((isset($_SESSION['kpmg_userdata']['last_name'])) ? $_SESSION['kpmg_userdata']['last_name'] : "");
		$registerInfoArr['email_address'] = isset($_POST['register']['email_address']) ? strtolower(trim($_POST['register']['email_address'])) : ((isset($_SESSION['kpmg_userdata']['email_address'])) ? $_SESSION['kpmg_userdata']['email_address'] : "");
		$registerInfoArr['password_one'] = isset($_POST['register']['password_one']) ? trim($_POST['register']['password_one']) : ((isset($_SESSION['kpmg_userdata']['password_one'])) ? $_SESSION['kpmg_userdata']['password_one'] : "");
		$registerInfoArr['password_two'] = isset($_POST['register']['password_two']) ? trim($_POST['register']['password_two']) : ((isset($_SESSION['kpmg_userdata']['password_two'])) ? $_SESSION['kpmg_userdata']['password_two'] : "");
		
		// Registration Form Step Two
		$registerInfoArr['entertainment_only'] = isset($_POST['register']['entertainment_only']) ? trim($_POST['register']['entertainment_only']) : ((isset($_SESSION['kpmg_userdata']['entertainment_only'])) ? $_SESSION['kpmg_userdata']['entertainment_only'] : "");
		$registerInfoArr['dietary_requirements'] = isset($_POST['register']['dietary_requirements']) ? trim($_POST['register']['dietary_requirements']) : ((isset($_SESSION['kpmg_userdata']['dietary_requirements'])) ? $_SESSION['kpmg_userdata']['dietary_requirements'] : "");
		$registerInfoArr['dietary_requirements_other'] = isset($_POST['register']['dietary_requirements_other']) ? trim($_POST['register']['dietary_requirements_other']) : ((isset($_SESSION['kpmg_userdata']['dietary_requirements_other'])) ? $_SESSION['kpmg_userdata']['dietary_requirements_other'] : "");
		
		$registerInfoArr['has_guest'] = isset($_POST['register']['has_guest']) ? trim($_POST['register']['has_guest']) : ((isset($_SESSION['kpmg_userdata']['has_guest'])) ? $_SESSION['kpmg_userdata']['has_guest'] : "");
		$registerInfoArr['first_name_guest'] = isset($_POST['register']['first_name_guest']) ? trim($_POST['register']['first_name_guest']) : ((isset($_SESSION['kpmg_userdata']['first_name_guest'])) ? $_SESSION['kpmg_userdata']['first_name_guest'] : "");
		$registerInfoArr['last_name_guest'] = isset($_POST['register']['last_name_guest']) ? trim($_POST['register']['last_name_guest']) : ((isset($_SESSION['kpmg_userdata']['last_name_guest'])) ? $_SESSION['kpmg_userdata']['last_name_guest'] : "");
		$registerInfoArr['dietary_requirements_guest'] = isset($_POST['register']['dietary_requirements_guest']) ? trim($_POST['register']['dietary_requirements_guest']) : ((isset($_SESSION['kpmg_userdata']['dietary_requirements_guest'])) ? $_SESSION['kpmg_userdata']['dietary_requirements_guest'] : "");
		$registerInfoArr['dietary_requirements_other_guest'] = isset($_POST['register']['dietary_requirements_other_guest']) ? trim($_POST['register']['dietary_requirements_other_guest']) : ((isset($_SESSION['kpmg_userdata']['dietary_requirements_other_guest'])) ? $_SESSION['kpmg_userdata']['dietary_requirements_other_guest'] : "");
		
		

		// Return
		return $registerInfoArr;
		
	}	
		
	// Employee Reserve A Group Data
	public function employeeReserveAGroupData() 
	{
		global $wpdb;
		
		// Variables
		$reserveAGroupInfoArr = array();
		
		// Reserve A Group Form Step One Registrating User Logged In Only
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
		$user_email = $current_user->user_email;
		
		// Create Session If Applicable
		if ( !isset($_SESSION['kpmg_userreservedata']) )
		{
			// First One First Is Host
			$_SESSION['kmpg_userreservedata']['group_seat'][0] = kpmg_getEmployeeDetailsByUserID($user_id);	
			// Check If Guest Exists
			if ( $_SESSION['kmpg_userreservedata']['group_seat'][0]['has_guest'] )
			{
				$_SESSION['kmpg_userreservedata']['group_seat'][1] = $_SESSION['kmpg_userreservedata']['group_seat'][0];
			}
			
			// Find Reserved Table If Available
			$reservedGroupResults = $wpdb->get_results(
				$wpdb->prepare(
					" SELECT * FROM {$wpdb->kpmg_group_seats} 
						WHERE group_id IN 
						( SELECT group_id FROM {$wpdb->kpmg_group_seats} WHERE employee_email_address = %s )
					"
				, $user_email			
				)
				, ARRAY_A
			);
			
			if ( count($reservedGroupResults) > 0 )
			{
				// If Already Reserved And Not Host
				if ( $reservedGroupResults[0]['host_email_address'] != $user_email )
				{
					// Redirect To My Info 
					$new_url = add_query_arg( 'alreadyreserved', 1, $this->pagemyinfo ); // alreadyreserved Var
					wp_redirect( $new_url, 303 );  // Allow Response Cache Only
				}
				else
				{
					foreach ($reservedGroupResults as $key => $row)
					{
						$_SESSION['kmpg_userreservedata']['group_seat'][$key] = $row;
					}
				}
			}
		}
		
		// Employee Group Seats
		for ($i=0; $i<10; $i++)
		{
			// Host
			if ( isset($_POST['reserveagroup']['group_seat'][$i]['host_email_address']) )
			{
				$reserveAGroupInfoArr['group_seat'][$i]['host_email_address'] = trim($_POST['reserveagroup']['group_seat'][0]['host_email_address']);
			}
			elseif ( !isset($_POST['reserveagroup']['group_seat']) && isset($_SESSION['kmpg_userreservedata']['group_seat'][0]['employee_email_address']) )
			{
				$reserveAGroupInfoArr['group_seat'][$i]['host_email_address'] = trim($_SESSION['kmpg_userreservedata']['group_seat'][0]['employee_email_address']);
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
			elseif ( !isset($_POST['reserveagroup']['group_seat']) && isset($_SESSION['kmpg_userreservedata']['group_seat'][$i]['employee_email_address']) )
			{
				$reserveAGroupInfoArr['group_seat'][$i]['email_address'] = trim($_SESSION['kmpg_userreservedata']['group_seat'][$i]['employee_email_address']);
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
			elseif ( !isset($_POST['reserveagroup']['group_seat'])  && isset($_SESSION['kmpg_userreservedata']['group_seat'][$i]['has_guest']) && $i > 0 )
			{
				$reserveAGroupInfoArr['group_seat'][$i]['is_guest'] = ( trim($_SESSION['kmpg_userreservedata']['group_seat'][$i]['has_guest']) == "yes" ) ? 1 : 0;
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
}
