<?php

/**
 * File: class.admin_uploademployees.php
 * Description of class:
 *	Upload Employees Into Database
 *	Update registration information
 *	Remove from group if applicable & make second employee host or delete group.
 * 
 * Author: edward <http://ojambo.com>
 * Copyright: 2016  
 * Created : 2016-09-17 4:07:25 PM
 * Last Modified : 2016-09-17T20:07:25Z
 */
						// TODO
						// Update employee status
						// If Cancelled, remove from groups & update host if applicable
class KPMG_Admin_UploadEmployees {
	
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
		$this->formvariable = "adminuploademployees";
		$this->formaction = "admin_upload_employees";
		
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
			<form id="kpmg-admin-{$formVariable}-form" class="signup-01" method="post" action="#kpmg-admin-{$formVariable}-form" enctype="multipart/form-data">
				<div class="errors">{$Errors}
					<p class="small" id="kpmg-{$formVariable}-ajax-error-area"></p>
				</div>
				<input type="hidden" name="kpmg_formaction" value="{$formAction}" />
				<div class="show">
					<label for="uploadEmployees" class="upload">Choose File</label>
					<input type="file" name="uploadEmployees" id="uploadEmployees">
				</div>
				<div id="file-selected">no file selected</div>
				<input type="hidden" name="{$formVariable}[step]" value="{$formStep}" />
				<button type="submit" name="{$formVariable}[button]" value="Upload" >Upload</button>
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
		$db_daily_table = $wpdb->kpmg_employees_upload;
		$db_table = $wpdb->kpmg_employees;
		$uploadedPath = KPMFWF_UploadsFolder;
		$field_separator = ','; 
		$optional_separator = '\"';
		$line_separator = '\n';
		$line_separator_ori = '\r\n';
		
		if ( $this->adminrole != NULL && ($_POST['kpmg_formaction'] == $this->formaction) )
		{
			
			// Handle Uploaded CSV File If Applicable
			if ( isset($_FILES['uploadEmployees']) )
			{
				if ( ($_FILES['uploadEmployees']["tmp_name"] != '') )
				{
					$local_file = $_FILES['uploadEmployees']["tmp_name"];
					//$local_file = $uploadedPath."/data.csv";
					$local_file_g = $local_file."g";
					$local_file_h = $local_file."h";
					$local_file_d = $local_file."d";
					$local_file_d1 = $local_file."d1";
					//$local_file_i = $local_file."i";

					// Drop Daily Upload Table
					$sql_drop_table = "DROP TABLE IF EXISTS {$db_daily_table};";
					$wpdb->query($sql_drop_table);

					// Convert To UTF-8 (Unix)
					$file_encoding = exec('file -bi "'.$local_file.'"');
					if ( strpos(strtolower($file_encoding), "utf-16") !== false )
					{	 
						exec('iconv -f UTF-16 -t UTF-8 "'.$local_file.'" > "'.$local_file_g.'"');
					}
					else
					{
						exec('cp "'.$local_file.'" "'.$local_file_g.'"');
					}
					
					// Fix Mac Bug
					exec('sed -e "s/\r/\n/g" ' .$local_file_g. '> "'.$local_file_h.'"');
					exec('mv "' .$local_file_h. '" "'.$local_file_g.'"');

					// Get Header Row
					exec('head -1 "'.$local_file_g.'" > "'.$local_file_h.'"');

					// Remove First Line (Header)
					exec('tail -n +2 "'.$local_file_g.'" > "'.$local_file_d1.'"');

					// Remove Empty Lines At End
					exec('sed "/^\x0D\?$/d" "'.$local_file_d1.'" > "'.$local_file_d.'"');

					// Get Header Row For Table
					$csvArrayHeader = array();

					if (($fileHandleH = fopen($local_file_h, "r")) !== FALSE) 
					{

						while (($dataH = fgetcsv($fileHandleH, 0, ",")) !== FALSE) 
						{
							if ( array(null) !== $dataH )
							{
								$csvArrayHeader = $dataH;
							}
						}
						fclose($fileHandleH);

						$newDailyTableSQL = "CREATE TABLE IF NOT EXISTS {$db_daily_table} (";
						$copyFieldNamesINS = "";
						$copyFieldNamesUPD = "";
						$headerCounter = 0;
						$columnNamesUnique = array();

						foreach ( $csvArrayHeader as $fieldName ) 
						{
							// Replace Spaces & Hypens With Underscores
							$fieldName = str_replace(' ', '_', $fieldName);
							$fieldName = str_replace('-', '_', $fieldName);
							$columnNamesUnique[$fieldName] = isset($columnNamesUnique[$fieldName]) ? ($columnNamesUnique[$fieldName] + 1) : 1; 
							$fieldName .=  ($columnNamesUnique[$fieldName] > 1 ) ? "_{$columnNamesUnique[$fieldName]}" : "";
							$fieldType = (strtolower($fieldName) == 'sent') ? "DATETIME NOT NULL" : "TEXT NOT NULL";
							$newDailyTableSQL .= ($headerCounter == 0) ? "{$fieldName} VARCHAR(255) PRIMARY KEY" : ", {$fieldName} {$fieldType}"; // With PK
							$copyFieldNamesINS .= (($headerCounter == 0) ? "{$fieldName}" : ", {$fieldName} "); // With PK
							$copyFieldNamesUPD .= ($headerCounter == 0) ? "" : (($headerCounter == 1) ? "ot.{$fieldName} = dt.{$fieldName}" : ", ot.{$fieldName} = dt.{$fieldName} "); // Without PK
							//$copyFieldNamesArr[] = "$fieldName}"; // Without PKID 
							$headerCounter++; // Increment
						}
						$newDailyTableSQL .= ");";
						// Create Daily Table
						$wpdb->query($newDailyTableSQL);

						// Compare Tables And Add New Columns To Statis PK Table
						$newTableFieldsResult = kpmg_getDatabaseTableColumns($db_table);
						$newDailyTableFieldsResult = kpmg_getDatabaseTableColumns($db_daily_table);
						$newTableFieldsArr = array();
						if ( is_array($newTableFieldsResult) ) 
						{ 
							foreach($newTableFieldsResult as $row)
							{
								$newTableFieldsArr[] = $row['Field'];
							}
						}
						if ( is_array($newDailyTableFieldsResult) ) 
						{ 
							foreach($newDailyTableFieldsResult as $key => $row)
							{
								if( !in_array($row['Field'], $newTableFieldsArr) )
								{
									$newTableAddFieldSQL = "ALTER TABLE {$db_table} ADD {$row['Field']} TEXT NOT NULL";
									$wpdb->query($newTableAddFieldSQL); // Add New Column To The End
								}
							}
						}

						$employeeStatusArr = kpmg_getAllEmployeeStatus();
						$employeeDesignationArr = kpmg_getAllEmployeeDesignation();
						$makeAdminArr = array('yes', 'no');
						// Load Daily Data
						if (($handle_d = fopen($local_file_d, "r")) !== FALSE) {
							$upload_row = 0;
							while (($data_d = fgetcsv($handle_d, 1000, ",")) !== FALSE) {

								$upload_row++; // Increment
								$ins_data = array(); // Rest
								$ins_count = 0; // Reset
								foreach ( $csvArrayHeader as $ins_field ) 
								{
									$ins_data[$ins_field] = $data_d[$ins_count];
									// Check For Errors
									if ( $ins_field == "employee_status" && !in_array($data_d[$ins_count], $employeeStatusArr) )
									{
										$this->errors .= "<p class=\"small\">An error occured with the employee status while saving employee upload row {$upload_row}</p>";
										break;
									}
									if ( $ins_field == "make_admin" && !in_array($data_d[$ins_count], $makeAdminArr) )
									{
										$this->errors .= "<p class=\"small\">An error occured with the make admin while saving employee upload row {$upload_row}</p>";
										break;
									} 
									if ( $ins_field == "employee_designation" && !in_array($data_d[$ins_count], $employeeDesignationArr) )
									{
										$this->errors .= "<p class=\"small\">An error occured with the employee designation while saving employee upload row {$upload_row}</p>";
										break;
									} 
									if ( $ins_field == "group_id" && !is_numeric($data_d[$ins_count]) && $data_d[$ins_count] != '' )
									{
										$this->errors .= "<p class=\"small\">An error occured with the group_id while saving employee upload row {$upload_row}</p>";
										break;
									} 
									if ( $ins_field == "table_id" && !is_numeric($data_d[$ins_count]) && $data_d[$ins_count] != '' )
									{
										$this->errors .= "<p class=\"small\">An error occured with the table_id while saving employee upload row {$upload_row}</p>";
										break;
									} 
									$ins_count++;  // Increment
								}
								if ( $this->errors != "" )
								{
									break;
								}
								// Insert
								$employeeuploaddata = $ins_data;
								$employeeuploadfieldtypes = kpmg_generateFieldTypes($employeeuploaddata);
								if ( $wpdb->replace($wpdb->kpmg_employees_upload, $employeeuploaddata, $employeeuploadfieldtypes) === FALSE )
								{
									$this->errors .= "<p class=\"small\">An error occured while saving employee upload row {$upload_row}</p>";
									break;
								}
							}
						}
						
						if ( $this->errors == "" )
						{
							// Truncate Table
							$wpdb->query("TRUNCATE TABLE {$wpdb->kpmg_employees_update}"); // Update Employee Details Groups
							// Copy Registration Details
							$wpdb->query("INSERT INTO  {$wpdb->kpmg_employees_update} SELECT * FROM {$wpdb->kpmg_registration_details} WHERE {$wpdb->kpmg_employees_update}.employee_email_address NOT IN (SELECT employee_email_address FROM {$wpdb->kpmg_registration_details}"); // Update Employee Details Groups
							// Update Employee Update Groups
							$wpdb->query("UPDATE {$wpdb->kpmg_employees_update} AS eeu 
									INNER JOIN {$wpdb->kpmg_employees_upload} det ON eup.employee_email_address = eeu.employee_email_address 
									SET eeu.group_id = eup.group_id
									WHERE eeu.group_id > 0"); // Update Employee Details Groups
							// Update Employee Update Tables
							$wpdb->query("UPDATE {$wpdb->kpmg_employees_update} AS eeu 
									INNER JOIN {$wpdb->kpmg_employees_upload} det ON eup.employee_email_address = eeu.employee_email_address 
									SET eeu.table_id = eup.table_id
									WHERE eeu.table_id > 0"); // Update Employee Details Tables
							
							// Check For Group Errors
							$groupCheckSQL = "SELECT det.group_id, COUNT(CASE WHEN LOWER(det.has_guest) = 'yes' THEN 2 ELSE 1 END) AS table_count 
								FROM wp_kpmg_registration_details det 
								WHERE det.table_id > 0
								GROUP BY det.table_id HAVING table_count > 10";
							$groupCheckResult = $wpdb->get_results(
								$groupCheckSQL
								, ARRAY_A
							);
							if( count($groupCheckResult) > 0 ) 
							{
								$this->errors .= "<p class=\"small\">An error occured {$groupCheckResult[0]['group_id']} has over 10 people while saving employee upload row</p>";
							}
							// Check For Table Errors
							$tableCheckSQL = "SELECT det.table_id, COUNT(CASE WHEN LOWER(det.has_guest) = 'yes' THEN 2 ELSE 1 END) AS table_count 
								FROM wp_kpmg_registration_details det 
								WHERE det.table_id > 0
								GROUP BY det.table_id HAVING table_count > 10";
							$tableCheckResult = $wpdb->get_results(
								$tableCheckSQL
								, ARRAY_A
							);
							if( count($tableCheckResult) > 0 ) 
							{
								$this->errors .= "<p class=\"small\">An error occured {$groupCheckResult[0]['table_id']} has over 10 people while saving employee upload row</p>";
							}
							
							if ($this->errors == '')
							{
								// Update Persistent Table
								$updatePKSQL = "UPDATE {$db_table} AS ot INNER JOIN {$db_daily_table} dt ON dt.employee_email_address = ot.employee_email_address SET {$copyFieldNamesUPD}; ";
								$wpdb->query($updatePKSQL); // Update Persistent Data

								// Insert New Records Into Persistent Table
								$insertExportPKSQL = " SELECT {$copyFieldNamesINS} FROM {$db_daily_table} dt ";
								$insertExportPKSQL .= " WHERE NOT EXISTS (SELECT {$copyFieldNamesINS} FROM {$db_table} ot WHERE dt.employee_email_address = ot.employee_email_address)";
								$insertExportResult =  $wpdb->get_results($insertExportPKSQL, ARRAY_A);

								if ( count($insertExportResult) > 0 )
								{
									foreach ($insertExportResult as $iekey => $ierow )
									{
										// Insert
										$employeeuploaddata = $ierow;
										$employeeuploadfieldtypes = kpmg_generateFieldTypes($employeeuploaddata);
										if ( $wpdb->replace($wpdb->kpmg_employees, $employeeuploaddata, $employeeuploadfieldtypes) === FALSE )
										{
											$this->errors .= "<p class=\"small\">An error occured while saving employee upload row {$iekey}</p>";
											break;
										}
									}
								}
							}
							
							
							if ( $this->errors == "" )
							{
								// Update groups
								$updategroups_sql = "UPDATE {$wpdb->kpmg_registration_details} AS det 
									INNER JOIN {$wpdb->kpmg_employees} emp ON emp.employee_email_address = det.employee_email_address 
									SET det.group_id = emp.group_id
									WHERE emp.group_id > 0;
									";
								$wpdb->query($updategroups_sql); // Update Employee Details Groups
								//
								// Update tables
								$updategroups_sql = "UPDATE {$wpdb->kpmg_registration_details} AS det 
									INNER JOIN {$wpdb->kpmg_employees} emp ON emp.employee_email_address = det.employee_email_address 
									SET det.table_id = emp.table_id
									WHERE emp.table_id > 0;
									";
								$wpdb->query($updategroups_sql); // Update Employee Details Tables

								
								
								// Update employee status
								$updateterminated_sql = "UPDATE {$wpdb->kpmg_registration_details} AS det 
									INNER JOIN {$wpdb->kpmg_employees} emp ON emp.employee_email_address = det.employee_email_address 
									SET det.employee_status = LOWER(emp.employee_status)
									WHERE LOWER(emp.employee_status) = 'terminated';
									";
								$wpdb->query($updateterminated_sql); // Update Employee Details Status

								// Get Terminated Employee Details
								$terminatedEmployeesDetails = kpmg_getEmployeesTerminatedDetails();
								if ( is_array($terminatedEmployeesDetails) )
								{
									foreach ($terminatedEmployeesDetails as $tkey => $trow)
									{
										$terminated_userID = $trow['user_id'];
										$terminated_userEmail = $trow['employee_email_address'];

										// Notify Host 
										if ($trow['group_id'] > 0 )
										{
											$employeeGroupList = kpmg_getEmployeeGroupListByEmail($trow['employee_email_address']);
											$emailData = array();
											$emailData['subject'] = KPMGWF_ToHostTerminated_Subject;
											$emailData['email_address'] = $employeeGroupList[0]['host_email_address'];
											$KPMG_Email->sendNotifyHostEmail($emailData);
										}
										// Termination Infor
										$terminationdata = array();
										$terminationdata['employee_email_address'] = $terminated_userEmail;
										$terminationdatafieldtypes = kpmg_generateFieldTypes($terminationdata);
										// Remove Employee From Reservation Group
										$wpdb->update($wpdb->kpmg_group_seats, array('employee_email_address' => ''), $terminationdata, $terminationdatafieldtypes);
										// Remove Employee From Registration Table
										if ( $wpdb->delete($wpdb->kpmg_registration_details, $terminationdata, $terminationdatafieldtypes) === FALSE )
										{
											$this->errors .= "<p class=\"small\">An error occured while removing terminated employee upload row {$terminationdata['employee_email_address']}</p>";
											break;
										}
										else
										{
											// Remove User From User Table
											wp_delete_user($terminated_userID);								
										}
									}
								}

								// Get Waiting List Employee Details
								$waitinglistEmployeesDetails = kpmg_getEmployeesWaitingListDetails();
								if ( is_array($waitinglistEmployeesDetails) )
								{
									foreach ($waitinglistEmployeesDetails as $wkey => $wrow)
									{


										// Notify Host 
										if ($wrow['registration_status'] == 'waitinglist' && $wrow['employee_status'] == 'registered' )
										{
											// Update Registration Status
											$updatestatusdataconditions = array();
											$updatestatusdataconditions['employee_email_address'] = $wrow['employee_email_address'];
											$updatestatusdataconditionsfieldtypes = kpmg_generateFieldTypes($updatestatusdataconditions);
											$updatestatusdata = array();
											$updatestatusdata['employee_status'] = "registered";
											$updatestatusdatafieldtypes = kpmg_generateFieldTypes($updatestatusdata);
											// Change Employee Status To Registered
											if ( $wpdb->update($wpdb->kpmg_registration_details, $updatestatusdata, $updatestatusdataconditions, $updatestatusdatafieldtypes, $updatestatusdataconditionsfieldtypes) === FALSE )
											{
												$this->errors .= "<p class=\"small\">An error occured while changing waiting list employee upload row {$updatestatusdataconditions['employee_email_address']}</p>";
												break;
											}
											else
											{
												// Notify Employee
												$emailData = array();
												$emailData = kpmg_generateRegistrationEmailData($wrow);
												$KPMG_Email->sendWaitingListRegisterEmail($emailData);
											}
										}

									}
								}	
							}
						}
					}	

					// Delete Files
					$FilesArr = array();
					$FileArr[] = $local_file;
					$FileArr[] = $local_file_g;
					$FileArr[] = $local_file_h;
					$FileArr[] = $local_file_d;
					$FileArr[] = $local_file_d1;
					foreach ( $FileArr as $fkey => $ffile )
					{
						if ( file_exists($ffile) )
						{
							unlink($ffile);
						}
					}
					/*unlink($local_file);
					unlink($local_file_g);
					unlink($local_file_h);
					unlink($local_file_d);
					unlink($local_file_d1);
					//unlink($local_file_i);*/
					
					if ( $this->errors == "" )
					{
						// Thank You Message
						$this->thanks .= "<p class=\"thanks\">Successfully saved employee upload</p>";
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
	
}
