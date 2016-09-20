<?php

/**
 * File: class.admin_reportgroup.php
 * Description of class
 *	Send report to specified email address
 *	Download report
 * 
 * Author: edward <http://ojambo.com>
 * Copyright: 2016  
 * Created : 2016-09-18 12:26:11 AM
 * Last Modified : 2016-09-18T04:26:11Z
 */
class KPMG_Admin_ReportGroup {
	
	// Variables
	private $salt;
	private $step;
	private $formaction;
	private $formvariable;
	private $errors;
	private $thanks;
	private $adminrole = NULL;
	private $isAdmin = false;
	private $downloadprefix;
	
	// Constructor
	public function __construct() 
	{
		$this->salt = KPMGWF_Salt;
		$this->step = 0;
		$this->errors = "";
		$this->thanks = "";
		$this->formvariable = "adminreportgroup";
		$this->formaction = "admin_report_group";
		$this->downloadprefix = "admin_report_group";
		
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

		$Form = <<<OJAMBO
			{$Errors}
			<p class="small" id="kpmg-{$formVariable}-ajax-error-area"></p>
			<form id="kpmg-admin-{$formVariable}-form" class="signup-01" method="post" action="">
				<input type="hidden" name="kpmg_formaction" value="{$formAction}" />
				<input type="email" class="email_address" name="email_address" value="" placeholder="Email" required />
				<input type="hidden" name="{$formVariable}[step]" value="{$formStep}" />
				<button type="submit" name="{$formVariable}[button]" value="SEND" >SEND</button>
				<a class="button button-download" href="?kpmg_download={$formVariable}">CSV</a>	
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
			elseif ( isset($_GET['kpmg_download']) )
			{
				return $this->adminDownload();
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
		$reportInTable = $wpdb->kpmg_registration_details;
		$saveArr = array();
		$saveIDArr = array();
		$dataArr = array();
		$timestamp = date("ymd_his");
		$filename = $this->downloadprefix.'_'.$timestamp;
		
		if ( $this->adminrole != NULL && ($_POST['kpmg_formaction'] == $this->formaction) )
		{
			// Validate
			$fieldName = "email_address";
			$humanLabel = kpmg_generateHumanLabel($fieldName);
			$dataArr[$fieldName] = isset($_POST[$fieldName]) ? $_POST[$fieldName] : false;
			if ( !filter_var($dataArr[$fieldName], FILTER_VALIDATE_EMAIL) )
			{
				$this->errors .= "<p class=\"small\">The {$humanLabel} is invalid</p>";
			}
			elseif ( !kpmg_emailOnEmployeeList($dataArr[$fieldName]) )
			{
				// Check If Email On Employee List
				$this->errors .= "<p class\"smaill\">The {$humanLabel} is not allowed</p>";
			}
			$employeeListInfo = kpmg_getemailStatusOnEmployeeList($dataArr[$fieldName]);
			if ( $employeeListInfo != false )
			{
				if ( in_array($employeeListInfo['employee_status'], array('declined', 'terminated')) )
				{
					// Check If Email On Employee List
					$this->errors .= "<p class\"smaill\">The {$humanLabel} is not allowed</p>";	
				}
			}
		
			// Save Data
			if ( $this->errors == "" )
			{
				$saveArr = $dataArr;

				$sql_report = "SELECT * FROM {$reportInTable} WHERE group_id > 0 ORDER BY group_id ASC, group_seat ASC";
				$result_report = $wpdb->get_results($sql_report, ARRAY_A);
				
				if ( count($result_report) > 0 )
				{
					// Data
					$data = $this->adminReportData($result_report);
					$csvFile = kpmg_generateCSVString($data);
					$sendReport = $KPMG_Email->sendCSVEmail($csvFile, $saveArr['email_address']);
					if ( $sendReport )
					{
						// Thank You Message
						$this->thanks .= "<p class=\"thanks\">The Report was sent to the provided email</p>";
					}
				}
				else
				{
					$this->errors .= "<p class=\"small\">No Groups Exist</p>";
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
	
	// Admin Download
	function adminDownload()
	{
		global $wpdb;
		
		// Variables
		$reportInTable = $wpdb->kpmg_registration_details;
		$formVariable = $this->formvariable;
		$timestamp = date("ymd_his");
		$filename = $this->downloadprefix.'_'.$timestamp;
		
		if ( $this->adminrole != NULL && ($_GET['kpmg_download'] == $this->formvariable) )
		{
			
			$sql_report = "SELECT * FROM {$reportInTable} WHERE group_id > 0 ORDER BY group_id ASC, group_seat ASC";
			$result_report = $wpdb->get_results($sql_report, ARRAY_A);


			if ( count($result_report) > 0 )
			{
				// Data
				$data = $this->adminReportData($result_report);
				
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
		else
		{
			return false;
		}
	}
	
	function adminReportData($data)
	{
		$arr = array();
		foreach($data as $key => $row)
		{
			$arr[$key]['employee_email_address'] = $data[$key]['employee_email_address'];
			$arr[$key]['employee_first_name'] = $data[$key]['employee_first_name'];
			$arr[$key]['employee_last_name'] = $data[$key]['employee_last_name'];
			$arr[$key]['employee_status'] = $data[$key]['employee_status'];
			$arr[$key]['guest_first_name'] = $data[$key]['guest_first_name'];
			$arr[$key]['guest_last_name'] = $data[$key]['guest_last_name'];
			
			if ( $row['is_group_host'] > 0 )
			{
				$arr[$key]['is_group_host'] = "Yes";
			}
			else
			{
				$arr[$key]['is_group_host'] = "No";
			}
		}
		
		return $arr;
	}
	
}
