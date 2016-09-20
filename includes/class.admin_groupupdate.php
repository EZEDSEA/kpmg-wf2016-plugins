<?php

/**
 * File: class.admin_groupupdate.php
 * Description of class
 *	Group someone
 *	Update someone's group information
 * 
 * Author: edward <http://ojambo.com>
 * Copyright: 2016  
 * Created : 2016-09-18 10:42:01 AM
 * Last Modified : 2016-09-18T14:42:01Z
 */
class KPMG_Admin_GroupUpdate {
		
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
		$this->formvariable = "admingroupup";
		$this->formaction = "admin_groupup";
		$this->formactionstep1 = "admin_group-up2";
		
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
		$GroupID = isset($InfoArr[0]['group_id']) ? $InfoArr[0]['group_id'] : 0;
		
		if ($this->step == 1 )
		{
		$Form = <<<OJAMBO
			{$Errors}
			<p class="small" id="kpmg-{$formVariable}-ajax-error-area"></p>
			<form id="kpmg-{$formVariable}-get-form" class="signup-01" method="post" action="">
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
			{$Errors}
			<p class="small" id="kpmg-{$formVariable}-ajax-error-area"></p>
			<div id="addtogroupparent">
				<input id="kpmg-{$formVariable}-input" placeholder="Enter a kpmg email address" data-ajax="kpmg_seat_ajax-results-area" autocomplete="off" />
				<div id="kpmg_seat_ajax-results-area"></div>
			</div>
			<button id="kpmg-add-to-group-button" class="add_to_grp_btn">Add to my group</button>
			<p>Please note that photo ID will be required to enter the event and all attendees must be 19 years or older.  The name on the printed ticket will have to match the photo ID.</p>
			<form id="kpmg-{$formVariable}-form" class="signup-01" method="post" action="">
				<input type="hidden" name="kpmg_formaction" value="{$formAction}" />
				<input type="hidden" name="group_id" value="{$GroupID}" />
				<div class="show">
				{$Inputs}
				</div>
				<input type="hidden" name="{$formVariable}[step]" value="{$formStep}" />
				<button type="submit" name="{$formVariable}[button]" value="UPDATE GROUP" >UPDATE GROUP</button>
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
				
		if ( $this->adminrole != NULL && ($_POST['kpmg_formaction'] == $this->formactionstep1) )
		{
			$fieldName = 'email_address';
			$dataArr[$fieldName] = isset($_POST[$fieldName]) ? $_POST[$fieldName] : false;
			if ( !filter_var($dataArr['email_address'], FILTER_VALIDATE_EMAIL) )
			{
				$this->errors .= "<p class=\"small\">The email address is invalid</p>";
			}
			elseif ( !email_exists($dataArr['email_address']) || !kpmg_emailOnEmployeeList($dataArr['email_address']) )
			{
				// Check If Email On Employee List
				$this->errors .= "<p class\"smaill\">The email address is not allowed</p>";
			}		
			$employeeListInfo = kpmg_getemailStatusOnEmployeeList($dataArr['email_address']);
			if ( $employeeListInfo != false )
			{
				if ( in_array($employeeListInfo['employee_status'], array('declined', 'terminated')) )
				{
					// Check If Email On Employee List
					$this->errors .= "<p class\"smaill\">The email address is not allowed</p>";	
				}
				
			}
			
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
			// Validation Check
			$employeeEmailsArr = array();
			$reserve_seat_count = 0;
			$reserve_seat_with_guest_count = 0;
			$min_group_seat_count = 0;
			$min_group_seats = KPMGWF_MinGroupSeats;
		
			$dataArr['group_id'] = isset($_POST['group_id']) ? $_POST['group_id'] : false;
			$dataArr['employees'] = isset($_POST[$formVariable]) ? $_POST[$formVariable] : false;
			$dataArr['host'] = isset($_POST[$formVariable][1]['host_email_address']) ? $_POST[$formVariable][1]['host_email_address'] : false;
			
			// Validate
			if ( $dataArr['employees'] == false || $dataArr['group_id'] == false )
			{
				$this->errors .= "<p class=\"small\">The host member is invalid.</p>";
			}
			else
			{
				// Group ID
				if ( !is_numeric($dataArr['group_id']) )
				{
					$this->errors .= "<p class=\"small\">The group id is invalid.</p>";
				}
				else
				{
					$saveIDArr['group_id'] = (int)$dataArr['group_id'];
				}
				foreach ($dataArr['employees'] as $key => $row)
				{
					if ( isset($dataArr['employees'][$key]['email_address']) )
					{
						// Check if Employee Valid
						$employeeListAndDetails = kpmg_getEmployeeListAndDetailsByEmail($dataArr['employees'][$key]['email_address']);
						if ( trim($dataArr['employees'][$key]['email_address']) != "" )
						{
							if ( !$employeeListAndDetails || in_array($employeeListAndDetails['employee_status'], array('terminated', 'declined')) )
							{
								// Error message 2: trying to add an employee who is not in the system (terminated or new hire)
								$this->errors .= <<<OJAMBO
								<p class="small">
									The person you are trying to add to your group is not in the KPMG employee database. This person has either left the company or is a new hire and has not yet been added to the system. <br /><br />
									If you have any questions, please contact the <a href="mailto:gtawinterfest@kpmg.ca">WinterFest mailbox</a>.<br /><br />

									Thank you, <br />
									KPMG WinterFest Crew
								</p>
OJAMBO;
							}
							else
							{
								// Error message 1: trying to add someone to a group who is already registered as part of another group
								if ( $employeeListAndDetails['group_id'] > 0 && $employeeListAndDetails['group_id'] != $dataArr['group_id'] )
								{
									$this->errors .= <<<OJAMBO
									<p class="small">
										The KPMG employee you are trying to add to your group cannot be added because they have already been included in another group reservation. Please contact them directly for more details.  <br /><br />

										Thank you, <br />
										KPMG WinterFest Crew
									</p>
OJAMBO;
							}

							if  ( strtolower($employeeListAndDetails['has_guest']) == "yes" )
							{
								$reserve_seat_with_guest_count +=2; // Increment By Two
							}

							// Error message 4: shown if someone tries to register a person to their group who has not yet registered
							if ( strtolower($employeeListAndDetails['registration_status']) != 'registered' )
							{
								$this->errors .= <<<OJAMBO
								<p class="small">
									The person you are looking for has not yet registered for the WinterFest. A person cannot be added to a group reservation until they have personally registered themselves for the event. Please contact them directly to register as soon as possible.  <br /><br />

									Thank you, <br />
									KPMG WinterFest Crew
								</p>
OJAMBO;
							}

							if ( $this->errors == "" )
							{
								$min_group_seat_count++; // Increment
							}

							}
						}

						// Count the seats
						$reserve_seat_count++; // Increment	

						// Error message 3: to display if someone tries to add person to their group and they have already reached a maximum of 10 people
						if ( $reserve_seat_count > 10 || $reserve_seat_with_guest_count > 10)
						{
							$this->errors .= <<<OJAMBO
							<p class="small">
								By adding this person (and their guest), you are exceeding the maximum allowable occupancy per table.  There is a maximum number of 10 people per group.  <br /><br />

								Thank you, <br />
								KPMG WinterFest Crew
							</p>
OJAMBO;
						}

						// Save Employee Ids for checking
						$saveArr[$key]['employee_email_address'] = $dataArr['employees'][$key]['email_address'];
						$saveArr[$key]['is_group_host'] = ($key == 0) ? 1 : 0;
						$saveArr[$key]['group_seat'] = $key;					
					}
				}

				if ( $min_group_seat_count < $min_group_seats )
				{
						$this->errors .= <<<OJAMBO
						<p class="small">
							You must have a minimum of 2 people in the group.
						</p>
OJAMBO;
				}	

				// Save Data
				if ( $this->errors == "" )
				{
					// Update Current Group
					$saveiddata = $saveIDArr;
					$saveiddatafieldtypes = kpmg_generateFieldTypes($saveiddata);
					//$savedata = $saveArr;
					$savedata = array(
						'is_group_host' => 0,
						'group_seat' => 0,
						'group_id' => 0
					);
					$savedatafieldtypes = kpmg_generateFieldTypes($savedata);
					if ( $wpdb->update($wpdb->kpmg_registration_details, $savedata, $saveiddata, $savedatafieldtypes, $saveiddatafieldtypes) === FALSE )
					{
						$this->errors .= "<p class=\"small\">An error occured while saving group</p>";
					}
					else
					{
						$update_success = true;
						foreach ($saveArr as $skey => $srow)
						{
							$updateiddata['employee_email_address'] = $srow['employee_email_address'];
							$updateiddatafieldtypes = kpmg_generateFieldTypes($updateiddata);
							$updatedata = array(
								'is_group_host' => $srow['is_group_host'],
								'group_seat' => $srow['group_seat'],
								'group_id' => $saveIDArr['group_id']
							);
							$updatedatafieldtypes = kpmg_generateFieldTypes($updatedata);
							if ( $wpdb->update($wpdb->kpmg_registration_details, $updatedata, $updateiddata, $updatedatafieldtypes, $updateiddatafieldtypes) === FALSE )
							{
								$update_success = false;
							}
						}
						if ( $update_success )
						{
							$this->thanks .= "<p class=\"thanks\">Thank you. Your group has now been saved.</p>";
						}
						else
						{
							$this->errors .= "<p class=\"small\">An error occured while saving group</p>";
						}
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
