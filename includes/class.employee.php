<?php

/**
 * File: class.employee.php
 * Description of class.employee.php
 *
 * Author: edward <http://ojambo.com>
 * Copyright: 2016
 * Created : 2016-08-27 10:09:49 PM
 * Last Modified : 2016-08-28T02:09:49Z
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class KPMG_Employee {

   // Variables
   private $salt;
	private $registrationstep;
	private $registrationerrors;
	private $reserveagrouperrors;
	private $cancelregistrationerrors;
	private $updateattenderrors;
	private $updatedieterrors;
	private $updateguesterrors;
	private $cancelthanks;
	private $updateattendthanks;
	private $updatedietthanks;
	private $updateguesthanks;
	private $pagereserveagroup;
	private $pagemyinfo;
	private $pageregistration;
	private $pageregistrationstep1;
	private $pageregistrationstep2;
	private $pageregistrationstep3;
	private $pageregistrationthankyou;
	private $pagereservationthankyou;

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
		$this->updateguesterrors = "";
		$this->updateguesthanks = "";
		$this->pagegroup = KPMGWF_Group;
		$this->pagemyinfo = KPMGWF_Info;
		$this->pageregistration = KPMGWF_Register;
		$this->pageregistrationstep1 = KPMGWF_Register1;
		$this->pageregistrationstep2 = KPMGWF_Register2;
		$this->pageregistrationstep3 = KPMGWF_Register3;
		$this->pageregistrationthankyou = KPMGWF_RegisterTY;
		$this->pagegroupthankyou = KPMGWF_GroupTY;
   }

	// Employee Register Form
	public function employeeRegisterForm()
	{

		if ( isset($_SESSION['registrationerrors']) )
		{
			$this->registrationerrors = $_SESSION['registrationerrors'];
			unset($_SESSION['registrationerrors']);
		}

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

		$hideShowDiet = ($registerInfoArr['entertainment_only'] == 1) ? "hide": "show";
		$hideShowGuestDiet = ($registerInfoArr['entertainment_only'] == 1 || strtolower($registerInfoArr['has_guest']) == "no") ? "hide": "show";
		$hideShowGuest = ( strtolower($registerInfoArr['has_guest']) == "no") ? "hide": "show";

		$registrationPage = $this->pageregistrationstep1;  // Default
		// Redirect Fix
		$current_page = kpmg_getCurrentPageSlug();
		if (stripos(substr(strrchr(rtrim($this->pageregistrationstep1, '/'), '/'), 1), $current_page) !== false)
		{
			$this->registrationstep = 1;
		}
		elseif (stripos(substr(strrchr(rtrim($this->pageregistrationstep2, '/'), '/'), 1), $current_page) !== false)
		{
			$this->registrationstep = 2;
		}
		elseif (stripos(substr(strrchr(rtrim($this->pageregistrationstep3, '/'), '/'), 1), $current_page) !== false)
		{
			$this->registrationstep = 3;
		}
		else
		{
			$this->registrationstep = 0;
		}


		if ( $this->registrationstep == 0 )
		{
			$registrationPage = $this->pageregistrationstep1;
		}
		elseif ( $this->registrationstep == 1 )
		{
			$registrationPage = $this->pageregistrationstep2;
		}
		elseif ( $this->registrationstep == 2 )
		{
			$registrationPage = $this->pageregistrationstep3;
		}
		elseif ( $this->registrationstep == 3 )
		{
			$registrationPage = $this->pageregistrationstep3;
		}


		if ( $this->registrationstep == 0 )
		{
		$employeeRegisterForm = <<<OJAMBO
			<form id="kpmg-registration-form" class="signup-01" method="post" action="{$registrationPage}" onsubmit="return submitRegsitrationStep(this)">
				<div class="errors">{$registerErrors}
					<p class="small" id="kpmg-registration-ajax-error-area"></p>
				</div>
				<h3 class="sub-heading">Register Today</h3>
				<p class="uppercase"><span class="yellow">*</span>Indicates a required field</p>
				<div class="nameContainer">
					<input type="text" name="register[first_name]" value="{$registerInfoArr['first_name']}" placeholder="First Name" required /><span class="yellow">*</span>
					<input type="text" name="register[last_name]" value="{$registerInfoArr['last_name']}" placeholder="Last name" required /><span class="yellow">*</span>
				</div>
				<div class="emailContainer">
					<input class="email_address" id="kpmg_registration_email_address" type="email" name="register[email_address]" value="{$registerInfoArr['email_address']}" placeholder="Email address" required autocomplete="off" /><span class="yellow">*</span>
					<div class="results" id="kpmg-registration-ajax-results-area"></div>
				</div>
				<div class="passNoteContainer">
					<h4>Password must be a minimum of 8 characters.</h4>
					<p>Must contain at least one number and a lowercase and uppercase character.</p>
				</div>
				<div class="passContainer">
					<input type="password" name="register[password_one]" value="{$registerInfoArr['password_one']}" placeholder="Password" required /><span class="yellow">*</span>
					<input type="password" name="register[password_two]" value="{$registerInfoArr['password_two']}" placeholder="Re-enter Password" required /><span class="yellow">*</span>
				</div>
				<input type="hidden" name="register[step]" value="0" />
				<button type="submit" name="register[button]" value="NEXT" >NEXT</button>
			</form>
OJAMBO;
		}
		elseif ( $this->registrationstep == 1 )
		{
		$employeeRegisterForm = <<<OJAMBO
			<form id="kpmg-registration-form" class="signup-01" method="post" action="{$registrationPage}">
				<div class="errors">{$registerErrors}</div>
				<div class="show attend-info">
					<h3 class="sub-heading">Will You Attend:</h3>
					<p><span class="yellow">*</span>Indicates a required field</p>
					<select name="register[entertainment_only]">
						<option value="">Please Select...</option>
						{$entertainmentOptions}
					</select>
				</div>
				<p>Please note that ID will be required to enter the event and all attendess <b><u>must</u></b> be 19 years or older.</p>
				<div class="buttons">
					<input type="hidden" name="register[step]" value="1" />
					<button type="submit" name="register[button]" value="NEXT" >NEXT</button>
				</div>
			</form>
OJAMBO;
		}
		elseif ( $this->registrationstep == 2 )
		{
		$employeeRegisterForm = <<<OJAMBO
			<form id="kpmg-registration-form" class="signup-01" method="post" action="{$registrationPage}">
				<div class="errors">{$registerErrors}</div>
				<div class="show bring-guest">
					<h3 class="sub-heading">Will You Bring A Guest?</h3>
					<p><span class="yellow">*</span>Indicates a required field</p>
					<select class="has_guest" name="register[has_guest]" value="{$registerInfoArr['has_guest']}">
						<option value="">Please Select...</option>
						{$bringGuestOptions}
					</select>
				</div>
				<div class="{$hideShowGuest} guest-info">
					<h3 class="sub-heading">ENTER THEIR DETAILS BELOW</h3>
					<input type="text" name="register[first_name_guest]" value="{$registerInfoArr['first_name_guest']}" placeholder="First Name" /><span class="yellow">*</span>
					<input type="text" name="register[last_name_guest]" value="{$registerInfoArr['last_name_guest']}" placeholder="Last name" /><span class="yellow">*</span>
				</div>
				<div class="buttons">
					<button type="submit" name="register[button]" value="BACK" >BACK</button>
					<input type="hidden" name="register[step]" value="2" />
					<button type="submit" name="register[button]" value="NEXT" >NEXT</button>
				</div>
			</form>
OJAMBO;
		}
		elseif ( $this->registrationstep == 3 )
		{
		$employeeRegisterForm = <<<OJAMBO
			<form id="kpmg-registration-form" class="signup-01" method="post" action="{$registrationPage}">
				<div class="errors">{$registerErrors}</div>
				<div class="{$hideShowDiet} diet-info" data-dietinfo="{$registerInfoArr['entertainment_only']}">
					<h3 class="sub-heading">Dietary Requirements</h3>
					<select name="register[dietary_requirements]">
						<option value="">Please Select...</option>
						{$dietaryOptions}
					</select>
					<textarea name="register[dietary_requirements_other]" placeholder="If you would like to add any additional info, please do so here.">{$registerInfoArr['dietary_requirements_other']}</textarea>
				</div>
				<div class="show bring-guest">
					<h3 class="sub-heading">Will You Bring A Guest?</h3>
					<select class="has_guest" name="register[has_guest]" value="{$registerInfoArr['has_guest']}">
						<option value="">Please Select...</option>
						{$bringGuestOptions}
					</select>
				</div>
				<div class="{$hideShowGuest} guest-info">
					<h3 class="sub-heading">ENTER THEIR DETAILS BELOW</h3>
					<input type="text" name="register[first_name_guest]" value="{$registerInfoArr['first_name_guest']}" placeholder="First Name" /><span class="yellow">*</span>
					<input type="text" name="register[last_name_guest]" value="{$registerInfoArr['last_name_guest']}" placeholder="Last name" /><span class="yellow">*</span>
				</div>
				<div class="{$hideShowGuestDiet} guest-diet-info">
					<select name="register[dietary_requirements_guest]">
						<option value="">-- DIETARY REQUIREMENTS --</option>
						{$dietaryGuestOptions}
					</select><span class="yellow">*</span>
					<textarea name="register[dietary_requirements_other_guest]" placeholder="If you would like to add any additional info, please do so here.">{$registerInfoArr['dietary_requirements_other_guest']}</textarea>
				</div>
				<input type="hidden" name="register[step]" value="3" />
				<div class="buttons">
					<button type="submit" name="register[button]" value="BACK" >BACK</button>
					<button type="submit" name="register[button]" value="NEXT" >NEXT</button>
				</div>
			</form>
OJAMBO;
		}

		return $employeeRegisterForm;
	}


	// Employee Register Completed
	public function employeeRegisterCompleted()
	{
		$employeeRegisterCompleted = "";
		$emailSentTo = "";
		if ( isset($_SESSION['kpmg_sentemailto']) )
		{
			$emailSentTo = $_SESSION['kpmg_sentemailto'];
		}
		else
		{
			wp_redirect( KPMGWF_Site, 303 );  // Allow Response Cache Only
			exit;
		}

		/*$user_id = get_current_user_id();
		if ( $user_id > 0 )
		{
			// Get Employe Registration Details
			$employeeDetails = kpmg_getEmployeeDetailsByUserID($user_id);
			if ( $data['entertainment_only'] == 1 && strtolower($data['has_guest']) == 'no' )
			{
				// MESSAGE 1: Registration, Just the entertainment, no guest
				$employeeRegisterCompleted = <<<OJAMBO
Thank you. Your registration has now been confirmed with the details below. You will receive a confirmation email to your KPMG address. Please click on the attached icon to save the event in your Outlook calendar. We look forward to seeing you at the event!<br /><br />
OJAMBO;
			}
		}*/

		if ($emailSentTo != "")
		{
			$dataArr = kpmg_getEmployeeListAndDetailsByEmail($emailSentTo);
			if ( strtolower($dataArr['registration_status']) == "waitinglist" )
			{
				$employeeRegisterCompleted = <<<OJAMBO
					<p>Thank you. You are now on the WinterFest waiting list. You will not be able to register a guest or host a group at this stage. If you have any questions, please contact us via the <a class="mail" href="mailto:gtawinterfest@kpmg.ca">WinterFest mailbox</a>.</p>
					<br />
					<p>Thank you, </p>
					<br />
					<p>KPMG WinterFest Crew</p>
OJAMBO;
			}
			else
			{
				$employeeRegisterCompleted = <<<OJAMBO
					<p>Thank you. Your registration has now been confirmed with the details below.</p>
					<p>You will receive a confirmation email to your KPMG address.</p>
					<p>Please click on the attached icon to save the event in your Outlook calendar. We look forward to seeing you at the event!</p>
					<br />
					<a href="{$this->pagemyinfo}">UPDATE MY INFO</a>
					<a href="{$this->pagegroup}">CREATE A GROUP</a>
					<!--<META HTTP-EQUIV="refresh" content="3;URL='{$this->pageregistration}">
					<script type="text/javascript">setTimeout(function(){document.location.href='{$this->pageregistration}'}, 3000);</script>-->
OJAMBO;
			}
		}

		return $employeeRegisterCompleted;
	}

	// Employee Register Closed
	public function employeeRegisterClosed()
	{
		$employeeRegisterClosed = "";

		$employeeRegisterClosed = <<<OJAMBO
			<p>As the overall capacity for this event has been reached or the registration deadline has passed, you are not able to register for WinterFest2016.</p>
			<br />
OJAMBO;

		return $employeeRegisterClosed;
	}

	// Employee Register Form Authorization
	public function employeeRegisterAuthorization( )
	{
		global $wpdb;
		global $user;

		global $KPMG_Email;

		// Get Form data
		$registerInfo = $this->employeeRegisterData();

		// Validation Check Step One
		if ( $this->registrationstep == 0 )
		{
			// Empty Require Fields
			if ( (empty($registerInfo['first_name'])) || (empty($registerInfo['last_name']))
				|| (empty($registerInfo['email_address'])) || (empty($registerInfo['password_one']))
				|| (empty($registerInfo['password_one'])) )
			{
				$this->registrationerrors .= "<p class=\"small\">Please fill in all required fields</p>";
			}
			if ( strlen($registerInfo['first_name']) < 2 )
			{
				$this->registrationerrors .= "<p class=\"small\">The first name is invalid</p>";
			}
			if ( strlen($registerInfo['last_name']) < 2 )
			{
				$this->registrationerrors .= "<p class=\"small\">The last name is invalid</p>";
			}
			if ( !filter_var($registerInfo['email_address'], FILTER_VALIDATE_EMAIL) )
			{
				$this->registrationerrors .= "<p class=\"small\">The email address is invalid</p>";
			}
			elseif ( email_exists($registerInfo['email_address']) )
			{
				// Check to see if the email address exists
				$this->registrationerrors .= "<p class=\"small\">The email address already in use</p>";
			}
			elseif ( !kpmg_emailOnEmployeeList($registerInfo['email_address']) )
			{
				// Check If Email On Employee List
				$this->registrationerrors .= "<p class\"smaill\">The email address is not allowed</p>";
			}
			$employeeListInfo = kpmg_getemailStatusOnEmployeeList($registerInfo['email_address']);
			if ( $employeeListInfo != false )
			{
				if ( in_array($employeeListInfo['employee_status'], array('declined', 'terminated')) )
				{
					// Check If Email On Employee List
					$this->registrationerrors .= "<p class\"smaill\">The email address is not allowed</p>";
				}
			}
			if ( strlen($registerInfo['password_one']) < 8 )
			{
				$this->registrationerrors .= "<p class=\"small\">The password must have at least 8 characters</p>";
			}
			if ( preg_match("/[0-9]/", $registerInfo['password_one']) === 0 )
			{
				$this->registrationerrors .= "<p class=\"small\">The password must have a number</p>";
			}
			if ( preg_match("/[a-z]/", $registerInfo['password_one']) === 0 )
			{
				$this->registrationerrors .= "<p class=\"small\">The password must have a lowercase character</p>";
			}
			if ( preg_match("/[A-Z]/", $registerInfo['password_one']) === 0 )
			{
				$this->registrationerrors .= "<p class=\"small\">The password must have an uppercase character</p>";
			}
			if ( $registerInfo['password_one'] != $registerInfo['password_two'] )
			{
				$this->registrationerrors .= "<p class=\"small\">The passwords do not match</p>";
			}

			// For Waiting List Stay On Step Zero
			if  ($this->registrationerrors != "" )
			{
				$this->registrationstep = 0;
				$_SESSION['registrationerrors'] = $this->registrationerrors;
				wp_redirect($this->pageregistration);
				exit();
			}
		}
		elseif ( $this->registrationstep == 1 )
		{
			// Nothing Now

		}
		elseif ( $this->registrationstep == 2 )
		{
			// Nothing Now

		}
		elseif ( $this->registrationstep == 3 )
		{
			if ( strtolower($registerInfo['has_guest']) == "yes" )
			{
				if ( strlen($registerInfo['first_name_guest']) < 2 )
				{
					$this->registrationerrors .= "<p class=\"small\">The first name is invalid</p>";
				}
				if ( strlen($registerInfo['last_name_guest']) < 2 )
				{
					$this->registrationerrors .= "<p class=\"small\">The last name is invalid</p>";
				}
			}
		}


		if ( $this->registrationerrors == "" )
		{
			// Save Registration Step One
			$_SESSION['kpmg_userdata'] = $registerInfo;

			$registrationDateNow = strtotime("now");
			$registrationLimits = kpmg_getRegistrationCutoff();
			$registrationLimitsDate = strtotime($registrationLimits["registration_end_date"]);
			$registrationStatusCount = kpmg_getRegistrationStatusCount();

			if ( $this->registrationstep == 0 )
			{
				// Check If On Waiting List
				if ( ($registrationStatusCount['registered'] >= $registrationLimits['registration_limit'])
					&& ($registrationStatusCount['waitinglist'] < $registrationLimits['waiting_list_limit']) )
				{

					$registerInfo['employee_status'] = "waitinglist";
									// Save Registration Step Two In Database
					$userdata = kpmg_generateEmployeeData($registerInfo);

					// Save Database Information
					$userID = wp_insert_user($userdata);
					if ( is_wp_error($userID) )
					{
						$this->registrationerrors .= "<p class=\"small\">An error occured while creating new user</p>";
					}
					else
					{
						// Save Registration Data After Addition Of Employee ID
						$registerInfo['user_id'] = $userID;
						$registrationLimits = kpmg_getRegistrationCutoff();
						$registrationStatusCount = kpmg_getRegistrationStatusCount();
						$registerInfo['employee_status'] = "waitinglist";
						$registrationdata = kpmg_generateRegistrationData($registerInfo);
						$registrationdatafieldtypes = kpmg_generateFieldTypes($registrationdata);
						if ( $wpdb->replace($wpdb->kpmg_registration_details, $registrationdata, $registrationdatafieldtypes) === FALSE )
						{
							$this->registrationerrors .= "<p class=\"small\">An error occured while saving registration details</p>";
						}
						else
						{
							// Automatic Login
							//kpmg_autoLogin($_SESSION['kpmg_userdata']['email_address']);

							// Send Email
							if ( $KPMG_Email->sendRegisterWaitingListEmail($_SESSION['kpmg_userdata']) )
							{
								// New Secure Session
								$_SESSION['kpmg_sentemailto'] = $_SESSION['kpmg_userdata']['email_address'];

								// Destroy Session Data
								$_SESSION['kpmg_userdata'] = array();

								// Redirect To Prevent Form Resubmission & Page Reload
								$new_url = add_query_arg( 'thankyou', 1, $this->pageregistrationthankyou ); // thankyou Var

								if (headers_sent())
								{
									echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $new_url . '">';
									echo "<script type='text/javascript'>document.location.href='{$new_url}';</script>";
								}
								else
								{
									wp_redirect( $new_url, 303 );  // Allow Response Cache Only
									exit();
								}
								return "email sent";
							}

						}
					}

					// Go To Step Zero
					$this->registrationstep = 0;
				}
				else
				{
					// Go To Step One
					$this->registrationstep = 1;
				}
			}
			elseif ( $this->registrationstep == 1 )
			{
				// Go To Step Two
				$this->registrationstep = 2;
			}
			elseif ( $this->registrationstep == 2 )
			{
				// Go To Step Two
				$this->registrationstep = 3;
			}
			elseif ( $this->registrationstep == 3 )
			{
				// Save Registration Step Two In Database
				$userdata = kpmg_generateEmployeeData($registerInfo);

				// Save Database Information
				$userID = wp_insert_user($userdata);
				if ( is_wp_error($userID) )
				{
					$this->registrationerrors .= "<p class=\"small\">An error occured while creating new user</p>";
				}
				else
				{
					// Save Registration Data After Addition Of Employee ID
					$registerInfo['user_id'] = $userID;
					$registrationLimits = kpmg_getRegistrationCutoff();
					$registrationStatusCount = kpmg_getRegistrationStatusCount();
					$registerInfo['employee_status'] = "waitinglist";
					if ( $registrationStatusCount['registered'] < $registrationLimits['registration_limit'])
					{
						$registerInfo['employee_status'] = "registered";
					}
					$registrationdata = kpmg_generateRegistrationData($registerInfo);
					$registrationdatafieldtypes = kpmg_generateFieldTypes($registrationdata);
					if ( $wpdb->replace($wpdb->kpmg_registration_details, $registrationdata, $registrationdatafieldtypes) === FALSE )
					{
						$this->registrationerrors .= "<p class=\"small\">An error occured while saving registration details</p>";
					}
					else
					{
						// Automatic Login
						kpmg_autoLogin($_SESSION['kpmg_userdata']['email_address']);

						// Send Email
						if ( $KPMG_Email->sendRegisterEmail($_SESSION['kpmg_userdata']) )
						{
							// New Secure Session
							$_SESSION['kpmg_sentemailto'] = $_SESSION['kpmg_userdata']['email_address'];

							// Destroy Session Data
							$_SESSION['kpmg_userdata'] = array();

							// Redirect To Prevent Form Resubmission & Page Reload
							$new_url = add_query_arg( 'thankyou', 1, $this->pageregistrationthankyou ); // thankyou Var
							if (headers_sent())
							{
								echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $new_url . '">';
								echo "<script type='text/javascript'>document.location.href='{$new_url}';</script>";
							}
							else
							{
								wp_redirect( $new_url, 303 );  // Allow Response Cache Only
								exit();
							}
							return "email sent";
						}

					}
				}

				// Go To Step Three
				$this->registrationstep = 3;

			}

		}

	}

	// Employee Register Form Process
	public function employeeRegisterProcess()
	{
		global $user;

		// Variables
		$registrationDateNow = strtotime("now");
		$registrationLimits = kpmg_getRegistrationCutoff();
		$registrationLimitsDate = strtotime($registrationLimits["registration_end_date"]);
		$registrationStatusCount = kpmg_getRegistrationStatusCount();

		// Employee Form Handler
		if ( isset($_POST['register']['step']) )
		{
			// Set Step
			if ( $_POST['register']['step'] == 0 )
			{
				$this->registrationstep = 0;
			}
			elseif ( $_POST['register']['step'] == 1 )
			{
				$this->registrationstep = 1;
			}
			elseif ( $_POST['register']['step'] == 2 )
			{
				$this->registrationstep = 2;
				if ( strtolower($_POST['register']['button']) ==  "back" )
				{
					$this->registrationstep = 1;
					wp_redirect($this->pageregistrationstep1);
					exit();
					//return $this->employeeRegisterForm();
				}
			}
			elseif ( $_POST['register']['step'] == 3 )
			{
				$this->registrationstep = 3;
				if ( $_POST['register']['button'] ==  "BACK" )
				{
					$this->registrationstep = 2;
					wp_redirect($this->pageregistrationstep2);
					exit();
					//return $this->employeeRegisterForm();
				}
			}

			$this->employeeRegisterAuthorization();
		}
		elseif ( isset($_GET['thankyou']) )
		{
			return $this->employeeRegisterCompleted();
		}
		elseif ( !is_user_logged_in()
				&& ( ($registrationDateNow >= $registrationLimitsDate)
					|| ( $registrationStatusCount['registered'] >= $registrationLimits['registration_limit']
						&& $registrationStatusCount['waitinglist'] >= $registrationLimits['waiting_list_limit'] )) 	)
		{
			return $this->employeeRegisterClosed();
		}

		elseif (is_user_logged_in() )
		{
			// Logged In User Only
			$employeeRole = KPMGWF_EmployeeRole;
			$adminRole = KPMGWF_AdminRole;
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($employeeRole, $roles) || in_array($adminRole, $roles) )
			{
				wp_redirect($this->pagemyinfo);
				exit();
			}
			else
			{
				wp_redirect( KPMGWF_Site, 303 );  // Allow Response Cache Only
				exit;
			}
		}

		// Display Employee Registration Form
		return $this->employeeRegisterForm();


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

	// Employee Cancel Reservation Form
	public function employeeCancelRegistrationForm()
	{
		global $user;

		$employeeRole = KPMGWF_EmployeeRole;
		$adminRole = KPMGWF_AdminRole;

		// Variables
		$cancelErrors = $this->cancelregistrationerrors;
		$cancelThanks = $this->cancelthanks;
		$employeeCancelRegistrationForm = "";

		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$user_email = $current_user->user_email;
			$roles = $current_user->roles;
			if ( in_array($employeeRole, $roles) || in_array($adminRole, $roles) )
			{
				$employeeDetailsArr = kpmg_getEmployeeDetailsByUserID($userID);

				$employeeCancelRegistrationForm = <<<OJAMBO
			<form id="cancel-registration-form" class="signup-01" method="post" action="{$_SERVER['REQUEST_URI']}#cancel-registration-form">
				<div class="errors">
					{$cancelErrors}
					<p class="small" id="cancel-registration-ajax-error-area"></p>
				</div>
				{$cancelThanks}
				<input type="hidden" name="cancelregistration[employee_id]" value="{$employeeDetailsArr['user_id']}" />
				<input type="hidden" name="cancelregistration[step]" value="2" />
				<button type="submit" name="cancelregistration[button]" value="cancelregistration" onclick="return confirm('Are you sure that you want to cancel your registration?')" >cancel registration</button>
			</form>
OJAMBO;

			}
		}

		return $employeeCancelRegistrationForm;
	}

	// Employee Cancel Registration Authorization
	public function employeeCancelRegistrationAuthorization( )
	{
		global $wpdb;
		global $user;

		global $KPMG_Email;

		// Check if Employee Id Is Correct
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$user_email = $current_user->user_email;

			// Place In Cancel Table
			$canceldata = array();
			$canceldata['employee_email_address'] = $user_email;
			$canceldatafieldtypes = kpmg_generateFieldTypes($canceldata);
			if ( $wpdb->replace($wpdb->kpmg_registration_cancellation, $canceldata, $canceldatafieldtypes) === FALSE )
			{
				$this->cancelregistrationerrors .= "<p class=\"small\">An error occured while performing cancellation request</p>";
			}
			else
			{
				// Get User Details
				$employeeDetailsArr = kpmg_getEmployeeDetailsByUserID($userID);

				$reservedGroupSeat = kpmg_reserveGroupSeat($user_email);

				// Check If Group Host
				$reserveGroupHostCheck = ($employeeDetailsArr['is_group_host'] == 1) ? true : false;

				// Check If Table Seated
				$reserveGroupEmployeeCheck = ($employeeDetailsArr['group_id'] > 0) ? true : false;

				// If Table Host
				/*if ( $reserveGroupHostCheck )
				{
					$this->cancelthanks .= <<<OJAMBO
						<p class="small">You have reserved a group for dinner.  Please contact the Winterfest Crew (CA-FM GTA Winterfest) to avoid the whole group reservation being cancelled.</p>
OJAMBO;
				}*/
				if (true)
				{
					// Remove Employee From Reservation Group
					$wpdb->update($wpdb->kpmg_group_seats, array('employee_email_address' => ''), $canceldata, $canceldatafieldtypes);


					// Remove Employee From Registration Table
					$wpdb->delete($wpdb->kpmg_registration_details, $canceldata, $canceldatafieldtypes);

					// Remove User From User Table
					wp_delete_user($userID);

					// Send Employee Email Cancellation Group Removal Confirmation
					if ( $reserveGroupEmployeeCheck )
					{
						// Send Employee Group Removal Email
						$KPMG_Email->sendRegsitrationCancellationGroupRemovalEmail($employeeDetailsArr);
					}
					// Send Employee Email Cancellation Group New Host Confirmations
					if ( $reserveGroupHostCheck )
					{
						// Get Remaining Group Members
						$remainingGroupMembers = kpmg_getEmployeeGroupListByGroup($employeeDetailsArr['group_id']);
						if ( is_array($remainingGroupMembers) )
						{
							// Create New Host
							$updatehostdataconditions = array();
							$updatehostdataconditions['employee_email_address'] = $remainingGroupMembers[0]['employee_email_address'];
							$updatehostdataconditionsfieldtypes = kpmg_generateFieldTypes($updatehostdataconditions);
							$updatehostdata = array();
							$updatehostdata['is_group_host'] = 1;
							$updatehostdatafieldtypes = kpmg_generateFieldTypes($updatehostdata);
							// Change Group Host To Highest Seated Person
							if ( $wpdb->update($wpdb->kpmg_registration_details, $updatehostdata, $updatehostdataconditions, $updatehostdatafieldtypes, $updatehostdataconditionsfieldtypes) === FALSE )
							{
								// Do Not Display Error
							}
							else
							{
								// Notify Employees Of New Host
								$emailData = kpmg_getEmployeeGroupListByGroup($employeeDetailsArr['group_id']);
								$KPMG_Email->sendReserveGroupNewHostEmail($emailData);
							}
						}
					}
					// Send Employee Email Cancellation Confirmation
					$KPMG_Email->sendRegistrationCancellationEmail($employeeDetailsArr);
					$this->cancelthanks .= <<<OJAMBO
						<p class="small">The Winterfest Crew has been notified of your cancellation request.</p>
OJAMBO;
				}
			}
		}
		else
		{
			$this->cancelregistrationerrors .= "<p class=\"small\">You must be login to cancel a registration.</p>";
		}

		// Display Errors & Form
		return $this->employeeCancelRegistrationForm();
	}

	// Employee Cancel Registration Process
	public function employeeCancelRegistrationProcess()
	{
		global $wp_roles;

		$employeeRole = KPMGWF_EmployeeRole;
		$adminRole = KPMGWF_AdminRole;

		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($employeeRole, $roles) || in_array($adminRole, $roles) )
			{
				if ( isset($_POST['cancelregistration']['step']) )
				{
					return $this->employeeCancelRegistrationAuthorization();
				}

				return $this->employeeCancelRegistrationForm();
			}
		}
		else
		{
			return false;
		}

	}


	// Employee Cancel Reservation Menu Item
	public function employeeCancelRegistrationMenuItem()
	{
		global $user;

		$employeeRole = KPMGWF_EmployeeRole;
		$adminRole = KPMGWF_AdminRole;

		// Variables
		$employeeCancelRegistrationMenuItem = "";

		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			//$userID = $current_user->ID;
			//$user_email = $current_user->user_email;
			$roles = $current_user->roles;
			if ( in_array($employeeRole, $roles) || in_array($adminRole, $roles) )
			{
				//$employeeDetailsArr = kpmg_getEmployeeDetailsByUserID($userID);

				$employeeCancelRegistrationMenuItem = <<<OJAMBO
			<a href="{$this->pagemyinfo}#cancel-registration-form">cancel registration</a>
OJAMBO;

			}
		}

		return $employeeCancelRegistrationMenuItem;
	}

	// Employee Cancel Registration Menu Ttem Process
	public function employeeCancelRegistrationMenuItemProcess()
	{
		global $wp_roles;

		$employeeRole = KPMGWF_EmployeeRole;
		$adminRole = KPMGWF_AdminRole;

		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			//$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($employeeRole, $roles) || in_array($adminRole, $roles) )
			{
				return $this->employeeCancelRegistrationMenuItem();
			}
		}
		else
		{
			return false;
		}

	}


	// Employee Update Attend Form
	public function employeeUpdateAttendForm()
	{
		global $user;

		$employeeRole = KPMGWF_EmployeeRole;
		$adminRole = KPMGWF_AdminRole;
		$attendDataArr = kpmg_dietaryRequirementOptionsData();

		// Variables
		$attendErrors = $this->updateattenderrors;
		$attendThanks = $this->updateattendthanks;
		$employeeUpdateDietForm = "";

		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($employeeRole, $roles) || in_array($adminRole, $roles) )
			{

				$employeeDetailsArr = kpmg_getEmployeeDetailsByUserID($userID);
				// Update if Posted
				if ( isset($_POST['updateattend']) )
				{
					if ( isset($_POST['updateattend']['attend_entertainment_only']) )
					{
						$employeeDetailsArr['attend_entertainment_only'] = trim($_POST['updateattend']['attend_entertainment_only']);
					}
				}

				$entertainmentDataArr = kpmg_entertainnmentOptionsData();
				$entertainmentOptions = kpmg_generateKeySelectOptions($entertainmentDataArr, $employeeDetailsArr['attend_entertainment_only']);

				$employeeUpdateDietForm = <<<OJAMBO
			<p class="small edit-info">Editing Information for {$employeeDetailsArr['employee_first_name']} {$employeeDetailsArr['employee_last_name']}</p>
			<form id="update-attendance-form" class="signup-01" method="post" action="{$_SERVER['REQUEST_URI']}#update-attendance-form">
				<div class="errors">
					{$attendErrors}
					<p class="small" id="update-attend-ajax-error-area"></p>
				</div>
				{$attendThanks}
				<input type="hidden" name="updateattend[user_id]" value="{$employeeDetailsArr['user_id']}" />
				<select class="entertainment_only" name="updateattend[attend_entertainment_only]">
					<option value="">Please Select...</option>
					{$entertainmentOptions}
				</select>
				<input type="hidden" name="updateattend[step]" value="2" />
				<button type="submit" name="updateattend[button]" value="UPDATE" >UPDATE</button>
			</form>
OJAMBO;

			}
		}

		return $employeeUpdateDietForm;
	}

	// Employee Update Attend Form Authorization
	public function employeeUpdateAttendAuthorization( )
	{
		global $wpdb;
		global $user;

		// Check if Employee Id Is Correct
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;

			// Get Form data
			$updateAttendInfo = array();
			if ( isset($_POST['updateattend']) )
			{
				if ( isset($_POST['updateattend']['attend_entertainment_only']) )
				{
					$updateAttendInfo['attend_entertainment_only'] = trim($_POST['updateattend']['attend_entertainment_only']);
				}
				else
				{
					$this->updateattenderrors .= "<p class=\"small\">You must be provide correct attendance information.</p>";
				}

				if ( $this->updateattenderrors == "" )
				{
					$updatedataconditions = array();
					$updatedataconditions['user_id'] = $userID;
					$updatedataconditionsfieldtypes = kpmg_generateFieldTypes($updatedataconditions);
					$updatedata = array();
					$updatedata = $updateAttendInfo;
					$updatedatafieldtypes = kpmg_generateFieldTypes($updatedata);

					if ( $wpdb->update($wpdb->kpmg_registration_details, $updatedata, $updatedataconditions, $updatedatafieldtypes, $updatedataconditionsfieldtypes) === FALSE )
					{
						$this->updatedieterrors .= "<p class=\"small\">An error occured while updating attendance requirements</p>";
					}
					else
					{
						$this->updatedietthanks .= "<p class=\"small\">Successfully updated attendance requirements</p>";
					}
				}

			}
			else
			{
				$this->updatedieterrors .= "<p class=\"small\">You must be provide correct attendance information.</p>";
			}
		}
		else
		{
			$this->updatedieterrors .= "<p class=\"small\">You must be login to edit the attendance information.</p>";
		}

		// Display Errors & Form
		return $this->employeeUpdateAttendForm();

	}

	// Employee Update Diet Process
	public function employeeUpdateAttendProcess()
	{
		global $wp_roles;

		$employeeRole = KPMGWF_EmployeeRole;
		$adminRole = KPMGWF_AdminRole;

		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($employeeRole, $roles) || in_array($adminRole, $roles) )
			{
				if ( isset($_POST['updateattend']['step']) )
				{
					return $this->employeeUpdateAttendAuthorization();
				}

				return $this->employeeUpdateAttendForm();
			}
		}
		else
		{
			return false;
		}

	}
		// Employee Update Diet Form
	public function employeeUpdateDietForm()
	{
		global $user;

		$employeeRole = KPMGWF_EmployeeRole;
		$adminRole = KPMGWF_AdminRole;
		$dietaryDataArr = kpmg_dietaryRequirementOptionsData();

		// Variables
		$dietErrors = $this->updatedieterrors;
		$dietThanks = $this->updatedietthanks;
		$employeeUpdateDietForm = "";

		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($employeeRole, $roles) || in_array($adminRole, $roles) )
			{

				$employeeDetailsArr = kpmg_getEmployeeDetailsByUserID($userID);
				// Update if Posted
				if ( isset($_POST['updatediet']) )
				{
					if ( isset($_POST['updatediet']['guest_dietary_requirements']) )
					{
						$employeeDetailsArr['employee_dietary_requirements'] = trim($_POST['updatediet']['employee_dietary_requirements']);
					}
					if ( isset($_POST['updatediet']['employee_dietary_requirements_other']) )
					{
						$employeeDetailsArr['employee_dietary_requirements_other'] = trim($_POST['updatediet']['employee_dietary_requirements_other']);
					}

				}

				$dietaryOptions = kpmg_generateSelectOptions($dietaryDataArr, $employeeDetailsArr['employee_dietary_requirements']);

				$employeeUpdateDietForm = <<<OJAMBO
			<form id="update-diet-form" class="signup-01" method="post" action="{$_SERVER['REQUEST_URI']}#update-diet-form">
				<div class="errors">
					{$dietErrors}
					<p class="small" id="update-diet-ajax-error-area"></p>
				</div>
				{$dietThanks}
				<input type="hidden" name="updatediet[user_id]" value="{$employeeDetailsArr['user_id']}" />
				<select name="updatediet[employee_dietary_requirements]">
									<option value="">Please Select...</option>
									{$dietaryOptions}
								</select>
								<textarea name="updatediet[employee_dietary_requirements_other]" placeholder="If you would like to add any additional info, please do so here.">{$employeeDetailsArr['employee_dietary_requirements_other']}</textarea>
				<input type="hidden" name="updatediet[step]" value="2" />
				<button type="submit" name="updatediet[button]" value="UPDATE" >UPDATE</button>
			</form>
OJAMBO;

			}
		}

		return $employeeUpdateDietForm;
	}

	// Employee Update Diet Form Authorization
	public function employeeUpdateDietAuthorization( )
	{
		global $wpdb;
		global $user;

		// Check if Employee Id Is Correct
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;

			// Get Form data
			$updateDietInfo = array();
			if ( isset($_POST['updatediet']) )
			{
				if ( isset($_POST['updatediet']['employee_dietary_requirements']) )
				{
					$updateDietInfo['employee_dietary_requirements'] = trim($_POST['updatediet']['employee_dietary_requirements']);
				}
				else
				{
					$this->updatedieterrors .= "<p class=\"small\">You must be provide correct dietary information.</p>";
				}
				if ( isset($_POST['updatediet']['employee_dietary_requirements_other']) )
				{
					$updateDietInfo['employee_dietary_requirements_other'] = trim($_POST['updatediet']['employee_dietary_requirements_other']);
				}
				else
				{
					$this->updatedieterrors .= "<p class=\"small\">You must be provide correct dietary information.</p>";
				}

				if ( $this->updatedieterrors == "" )
				{
					$updatedataconditions = array();
					$updatedataconditions['user_id'] = $userID;
					$updatedataconditionsfieldtypes = kpmg_generateFieldTypes($updatedataconditions);
					$updatedata = array();
					$updatedata = $updateDietInfo;
					$updatedatafieldtypes = kpmg_generateFieldTypes($updatedata);

					if ( $wpdb->update($wpdb->kpmg_registration_details, $updatedata, $updatedataconditions, $updatedatafieldtypes, $updatedataconditionsfieldtypes) === FALSE )
					{
						$this->updatedieterrors .= "<p class=\"small\">An error occured while updating dietary requirements</p>";
					}
					else
					{
						$this->updatedietthanks .= "<p class=\"small\">Successfully updated dietary requirements</p>";
					}
				}

			}
			else
			{
				$this->updatedieterrors .= "<p class=\"small\">You must be provide correct dietary information.</p>";
			}
		}
		else
		{
			$this->updatedieterrors .= "<p class=\"small\">You must be login to edit the dietary information.</p>";
		}

		// Display Errors & Form
		return $this->employeeUpdateDietForm();

	}

	// Employee Update Diet Process
	public function employeeUpdateDietProcess()
	{
		global $wp_roles;

		$employeeRole = KPMGWF_EmployeeRole;
		$adminRole = KPMGWF_AdminRole;

		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($employeeRole, $roles) || in_array($adminRole, $roles) )
			{
				if ( isset($_POST['updatediet']['step']) )
				{
					return $this->employeeUpdateDietAuthorization();
				}

				return $this->employeeUpdateDietForm();
			}
		}
		else
		{
			return false;
		}

	}

	// Employee Update Guest Form
	public function employeeUpdateGuestForm()
	{
		global $user;

		$employeeRole = KPMGWF_EmployeeRole;
		$adminRole = KPMGWF_AdminRole;

		$bringGuestDataArr = kpmg_yesNoOptionsData();
		$dietaryDataArr = kpmg_dietaryRequirementOptionsData();

		// Variables
		$guestErrors = $this->updateguesterrors;
		$guestThanks = $this->updateguesthanks;
		$employeeUpdateGuestForm = "";

		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($employeeRole, $roles) || in_array($adminRole, $roles) )
			{
				$employeeDetailsArr = kpmg_getEmployeeDetailsByUserID($userID);
								// Update if Posted
				if ( isset($_POST['updateguest']) )
				{
					if ( isset($_POST['updateguest']['has_guest']) )
					{
						$employeeDetailsArr['has_guest'] = trim($_POST['updateguest']['has_guest']);
					}
					if ( isset($_POST['updateguest']['guest_first_name']) )
					{
						$employeeDetailsArr['guest_first_name'] = trim($_POST['updateguest']['guest_first_name']);
					}
					if ( isset($_POST['updateguest']['guest_last_name']) )
					{
						$employeeDetailsArr['guest_last_name'] = trim($_POST['updateguest']['guest_last_name']);
					}
					if ( isset($_POST['updateguest']['guest_dietary_requirements']) )
					{
						$employeeDetailsArr['guest_dietary_requirements'] = trim($_POST['updateguest']['guest_dietary_requirements']);
					}
					if ( isset($_POST['updateguest']['guest_dietary_requirements_other']) )
					{
						$employeeDetailsArr['guest_dietary_requirements_other'] = trim($_POST['updateguest']['guest_dietary_requirements_other']);
					}

				}
				$bringGuestOptions = kpmg_generateSelectOptions($bringGuestDataArr, $employeeDetailsArr['has_guest']);
		$dietaryGuestOptions = kpmg_generateSelectOptions($dietaryDataArr, $employeeDetailsArr['guest_dietary_requirements']);

				$employeeUpdateGuestForm = <<<OJAMBO
			<form id="update-guest-form" class="signup-01" method="post" action="{$_SERVER['REQUEST_URI']}#update-guest-form">
				<div class="errors">
					{$guestErrors}
					<p class="small" id="update-diet-ajax-error-area"></p>
				</div>
				{$guestThanks}
				<input type="hidden" name="updateguest[user_id]" value="{$employeeDetailsArr['user_id']}" />
				<select name="updateguest[has_guest]" value="{$employeeDetailsArr['has_guest']}">
					<option value="">Please Select...</option>
					{$bringGuestOptions}
				</select>
				<h3 class="sub-heading">Guest Information</h3>
				<input type="text" name="updateguest[guest_first_name]" value="{$employeeDetailsArr['guest_first_name']}" placeholder="First Name" />
				<input type="text" name="updateguest[guest_last_name]" value="{$employeeDetailsArr['guest_last_name']}" placeholder="Last name" />
				<select name="updateguest[guest_dietary_requirements]">
					<option value="">-- DIETARY REQUIREMENTS --</option>
					{$dietaryGuestOptions}
				</select>
				<textarea name="updateguest[guest_dietary_requirements_other]" placeholder="If you would like to add any additional info, please do so here.">{$employeeDetailsArr['guest_dietary_requirements_other']}</textarea>
				<input type="hidden" name="updateguest[step]" value="2" />
				<button type="submit" name="updateguest[button]" value="UPDATE" >UPDATE</button>
			</form>
OJAMBO;

			}
		}

		return $employeeUpdateGuestForm;
	}

	// Employee Update Guest Form Authorization
	public function employeeUpdateGuestAuthorization( )
	{
		global $wpdb;
		global $user;

		// Check if Employee Id Is Correct
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;

			// Get Form data
			$updateDietInfo = array();
			$updateGuestInfo['has_guest'] = "no";
			if ( isset($_POST['updateguest']) )
			{
				if ( isset($_POST['updateguest']['has_guest']) )
				{
					//$updateGuestInfo['has_guest'] = trim($_POST['update_diet']['has_guest']);
					$has_guest = trim($_POST['updateguest']['has_guest']);


					if ( strtolower($has_guest) == "yes" )
					{
						$updateGuestInfo['has_guest'] = "yes";
						if ( isset($_POST['updateguest']['guest_first_name']) )
						{
							$updateGuestInfo['guest_first_name'] = trim($_POST['updateguest']['guest_first_name']);
						}
						else
						{
							$this->updateguesterrors .= "<p class=\"small\">You must be provide correct guest information.</p>";
						}
						if ( isset($_POST['updateguest']['guest_last_name']) )
						{
							$updateGuestInfo['guest_last_name'] = trim($_POST['updateguest']['guest_last_name']);
						}
						else
						{
							$this->updateguesterrors .= "<p class=\"small\">You must be provide correct guest information.</p>";
						}

						// Check Input For Errors
						if ( strlen($updateGuestInfo['guest_first_name']) < 2 )
						{
							$this->updateguesterrors .= "<p class=\"small\">The guest's first name is invalid</p>";
						}
						if ( strlen($updateGuestInfo['guest_last_name']) < 2 )
						{
							$this->updateguesterrors .= "<p class=\"small\">The guest's last name is invalid</p>";
						}
					}
				}

				if ( isset($_POST['updateguest']['guest_dietary_requirements']) )
				{
					$updateGuestInfo['guest_dietary_requirements'] = trim($_POST['updateguest']['guest_dietary_requirements']);
				}
				else
				{
					$this->updateguesterrors .= "<p class=\"small\">You must be provide correct guest dietary information.</p>";
				}
				if ( isset($_POST['updateguest']['guest_dietary_requirements_other']) )
				{
					$updateGuestInfo['guest_dietary_requirements_other'] = trim($_POST['updateguest']['guest_dietary_requirements_other']);
				}
				else
				{
					$this->updateguesterrors .= "<p class=\"small\">You must be provide correct guest dietary information.</p>";
				}


				if ( $this->updateguesterrors == "" )
				{
					// No Guest
					if ( $updateGuestInfo['has_guest'] == "no" )
					{
						$updateGuestInfo['guest_first_name'] = "";
						$updateGuestInfo['guest_last_name'] = "";
						$updateGuestInfo['guest_dietary_requirements'] = "";
						$updateGuestInfo['guest_dietary_requirements_other'] = "";
					}
					// No Guest Diet
					if ($updateGuestInfo['guest_dietary_requirements'] == "" )
					{
						$updateGuestInfo['guest_dietary_requirements_other'] = "";
					}

					$updatedataconditions = array();
					$updatedataconditions['user_id'] = $userID;
					$updatedataconditionsfieldtypes = kpmg_generateFieldTypes($updatedataconditions);
					$updatedata = array();
					$updatedata = $updateGuestInfo;
					$updatedatafieldtypes = kpmg_generateFieldTypes($updatedata);

					if ( $wpdb->update($wpdb->kpmg_registration_details, $updatedata, $updatedataconditions, $updatedatafieldtypes, $updatedataconditionsfieldtypes) === FALSE )
					{
						$this->updateguesterrors .= "<p class=\"small\">An error occured while updating guest requirements</p>";
					}
					else
					{
						// Prevent Repost
						unset($_POST['updateguest']);
						$this->updateguesthanks .= "<p class=\"small\">Successfully updated guest information</p>";
					}
				}

			}
			else
			{
				$this->updateguesterrors .= "<p class=\"small\">You must be provide correct guest information.</p>";
			}
		}
		else
		{
			$this->updateguesterrors .= "<p class=\"small\">You must be login to edit the guest information.</p>";
		}

		// Display Errors & Form
		return $this->employeeUpdateGuestForm();

	}

	// Employee Update Guest Process
	public function employeeUpdateGuestProcess()
	{
		global $wp_roles;

		$employeeRole = KPMGWF_EmployeeRole;
		$adminRole = KPMGWF_AdminRole;

		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			if ( in_array($employeeRole, $roles) || in_array($adminRole, $roles) )
			{
				if ( isset($_POST['updateguest']['step']) )
				{
					return $this->employeeUpdateGuestAuthorization();
				}

				return $this->employeeUpdateGuestForm();
			}
		}
		else
		{
			return false;
		}

	}


}
