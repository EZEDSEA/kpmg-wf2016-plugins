<?php

/**
 * File: class.generatetables.php
 * Description of class
 * Generate Table Based On Groups
 * Cron Job Runs Daily No More
 *
 * Author: edward <http://ojambo.com>
 * Copyright: 2016  
 * Created : 2016-09-22 11:16:44 PM
 * Last Modified : 2016-09-23T03:16:44Z
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class KPMG_Admin_GenerateTables {
	
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
		$this->formvariable = "admingeneratetables";
		$this->formaction = "admin_generate_tables";
		
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
		// RAn Action Outside Class Definition
		//add_action( 'kpmg_generate_tables_event', array($this,'hook'));
    }

	// Admin Form
	public function adminForm()
	{
		// Variables
		$InfoArr = '';
		$Inputs = '';
		$Errors = $this->errors;
		$Thanks = $this->thanks;
		$formAction = $this->formaction;
		$formVariable = $this->formvariable;
		$formStep = $this->step;

		$Form = <<<OJAMBO
			<form id="kpmg-admin-{$formVariable}-form" class="signup-01" method="post" action="#kpmg-admin-{$formVariable}-form">
				<div class="errors">{$Errors}
					<p class="small" id="kpmg-{$formVariable}-ajax-error-area"></p>
				</div>
				<input type="hidden" name="kpmg_formaction" value="{$formAction}" />
				<div class="show">
				{$Inputs}
				</div>
				<input type="hidden" name="{$formVariable}[step]" value="{$formStep}" />
				<div class="buttonFiller"></div>
				<button type="submit" name="{$formVariable}[button]" value="Generate Tables" >Re/Generate Tables</button>
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
		
		// Variables
		$saveInTable = $wpdb->kpmg_registration_details;
		$tableCounter = 0;

		//ob_start();  // Fixes Header Already Sent Warnings
		if ( $this->adminrole != NULL && ($_POST['kpmg_formaction'] == $this->formaction) )
		{		
			// Broken By Employee Designations
			$employee_designation_result = $wpdb->get_results(
				"SELECT esf.*
					FROM {$wpdb->kpmg_employee_staff} esf"
				, ARRAY_A
			);
			if ( count($employee_designation_result) > 0 )
			{
				foreach ($employee_designation_result as $edkey => $edrow)
				{
					$employee_designation_grouped_results = $wpdb->get_results(
					$wpdb->prepare(
					" SELECT det.user_id, det.employee_email_address, det.group_id, det.has_guest
						, CASE WHEN det.employee_designation IS NULL OR det.employee_designation = '' THEN emp.employee_designation ELSE det.employee_designation END AS employee_designation_
						FROM {$wpdb->kpmg_registration_details}  det
						INNER JOIN {$wpdb->kpmg_employees} emp ON emp.employee_email_address = det.employee_email_address
						WHERE det.group_id > 0 AND det.table_id = 0 
						AND (det.employee_designation = %s OR emp.employee_designation = %s)
						GROUP BY det.group_id ORDER BY det.group_id ASC, det.group_seat ASC
						"
					, $edrow['employee_staff']
					, $edrow['employee_staff']
					)
						, ARRAY_A
					);	

					if ( count($employee_designation_grouped_results) > 0 )
					{
						$in_groups_str = "";
						foreach($employee_designation_grouped_results as $edgkey => $edgrow)
						{
							$in_groups_str .= ($in_groups_str == "") ? $edgrow['group_id'] : ",{$edgrow['group_id']}";
						}
						$result = $wpdb->get_results(
							"SELECT det.user_id, det.employee_email_address, det.group_id, det.has_guest 
								FROM {$wpdb->kpmg_registration_details} det
								WHERE det.group_id > 0 AND det.group_id IN ({$in_groups_str})
								ORDER BY det.employee_designation, det.group_id ASC, det.group_seat ASC"
							, ARRAY_A
						);	

						if ( count($result) > 0 )
						{
							foreach ($result as $key => $row)
							{
								$datakey = $row['group_id'];
								if ( !isset($data[$datakey]) )
								{
									$data[$datakey][$datakey] = 0;
								}


								$data[$datakey][$datakey]++;  // Employee Increment
								if ( strtolower($row['has_guest']) == "yes" )
								{
									$data[$datakey][$datakey]++;  // Guest Increment
								}
							}

							// Remove All Current Tables
							//$wpdb->query("UPDATE {$saveInTable} SET table_id = 0");			

							// Iterate Through Remaining Data & Update Database
							//$keys = array();
							$counter = 0;
							while (count($data) > 0)
							{

								$maxsum = 10;

								$results = $this->bestsum($data,$maxsum);  //function call

								//$tableCounter++;  // Increment As New Table
								// Get The Maximum Table ID ANd Increment
								$nextTableIncrementObj = ($wpdb->get_results("SELECT (MAX(table_id) + 1) AS table_incremented FROM {$wpdb->kpmg_registration_details}", ARRAY_A));
								$tableCounter = isset($nextTableIncrementObj[0]['table_incremented']) ? $nextTableIncrementObj[0]['table_incremented'] : ($tableCounter + 1);
					
								$counter++;  // Increment As New Table
								//$keys[$counter] = $results;

								// Iterate Through Data, Save & Remove Keys
								foreach ($results as $key => $arrkey)
								{
									// Save
									if ( $arrkey > 0 )
									{
										$saveArr['table_id'] = $tableCounter;
										$saveIDArr['group_id'] = $arrkey;
										$saveiddata = $saveIDArr;
										$saveiddatafieldtypes = kpmg_generateFieldTypes($saveiddata);
										$savedata = $saveArr;
										$savedatafieldtypes = kpmg_generateFieldTypes($savedata);	
										$wpdb->update($saveInTable, $savedata, $saveiddata, $savedatafieldtypes, $saveiddatafieldtypes);
									}
									// Remove From Data
									unset($data[$arrkey]);
								}
							}

							if ( $counter == 0 )
							{
								$this->errors .= "<p class=\"small\">An error occured while generating tables for {$edrow['employee_staff']}</p>";
							}
							else
							{
								// Thank You Message
								$this->thanks .= "<p class=\"thanks\">Successfully generated tables for {$edrow['employee_staff']}</p>";

							}
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
	
	public function bestsum($data,$maxsum)
	{
		$res = array_fill(0, $maxsum + 1, '0');
		$res[0] = array();              //base case
		$keys = array();
		foreach($data as $group)
		{
		 $new_res = $res;               //copy res

		  foreach($group as $key => $ele)
		  {

			for($i=0;$i<($maxsum-$ele+1);$i++)
			{   
				if($res[$i] != 0)
				{
					$ele_index = $i+$ele;
					$new_res[$ele_index] = $res[$i];
					//$new_res[$ele_index][] = $ele; /// Return Counts
					$new_res[$ele_index][] = $key;  // Return Key
				}
			}
		  }

		  $res = $new_res;
		}
		
		 for($i=$maxsum;$i>0;$i--)
		  {
			if($res[$i]!=0)
			{
				return $res[$i];
				break;
			}
		  }
		return array();
	} 
}
