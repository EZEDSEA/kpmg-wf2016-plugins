<?php

/**
 * File: class.admin_uploademployees.php
 * Description of class:
 *	Upload Employees Into Database
 *	Update regsitration information
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
			{$Errors}
			<p class="small" id="kpmg-{$formVariable}-ajax-error-area"></p>
			<form id="kpmg-reserve-a-group-form" class="signup-01" method="post" action="" enctype="multipart/form-data">
				<input type="hidden" name="kpmg_formaction" value="{$formAction}" />
				<div class="show">
					<input type="file" name="uploadEmployees" id="uploadEmployees">
				</div>
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
							$csvArrayHeader[] = $dataH;
						}
						fclose($fileHandleH);

						$newDailyTableSQL = "CREATE TABLE IF NOT EXISTS {$db_daily_table} (";
						$copyFieldNamesINS = "";
						$copyFieldNamesUPD = "";
						$headerCounter = 0;
						$columnNamesUnique = array();
						foreach ( $csvArrayHeader[0] as $fieldName ) 
						{
							// Replace Spaces & Hypens With Underscores
							$fieldName = str_replace(' ', '_', $fieldName);
							$fieldName = str_replace('-', '_', $fieldName);
							$columnNamesUnique[$fieldName] = isset($columnNamesUnique[$fieldName]) ? ($columnNamesUnique[$fieldName] + 1) : 1; 
							$fieldName .=  ($columnNamesUnique[$fieldName] > 1 ) ? "_{$columnNamesUnique[$fieldName]}" : "";
							$fieldType = (strtolower($fieldName) == 'sent') ? "DATETIME NOT NULL" : "TEXT NOT NULL";
							$newDailyTableSQL .= ($headerCounter == 0) ? "{$fieldName} VARCHAR(255) PRIMARY KEY" : ", {$fieldName} {$fieldType}"; // With PK
							$copyFieldNamesINS .= (($headerCounter == 0) ? "{$fieldName}" : ", {$fieldName} "); // With PK
							$copyFieldNamesUPD .= ($headerCounter == 0) ? "" : (($headerCounter == 1) ? "dt.{$fieldName} = ot.{$fieldName}" : ", dt.{$fieldName} = ot.{$fieldName} "); // Without PK
							//$copyFieldNamesArr[] = "$fieldName}"; // Without PKID 
							$headerCounter++; // Increment
						}
						$newDailyTableSQL .= ")";
						// Create Daily Table
						$wpdb->query($newDailyTableSQL);

						// Compare Tables And Add New Columns To Statis PK Table
						$newTableFieldsResult = kpmg_getDatabaseTableColumns($db_table);
						$newDailyTableFieldsResult = kpmg_getDatabaseTableColumns($db_daily_table);
						$newTableFieldsArr = array();
						foreach($newTableFieldsResult as $row)
						{
							$newTableFieldsArr[] = $row['Field'];
						}
						foreach($newDailyTableFieldsResult as $row)
						{
							if( !in_array($row['Field'], $newTableFieldsArr) )
							{
								$newTableAddFieldSQL = "ALTER TABLE {$db_table} ADD {$row['Field']} TEXT NOT NULL";
								$wpdb->query($newTableAddFieldSQL); // Add New Column To The End
							}
						}

						// Load Daily Data
						if (($handle_d = fopen($local_file_d, "r")) !== FALSE) {
							$upload_row = 0;
							while (($data_d = fgetcsv($handle_d, 1000, ",")) !== FALSE) {

								$upload_row++; // Increment
								$ins_data = array(); // Rest
								$ins_count = 0; // Reset
								foreach ( $csvArrayHeader[0] as $ins_field ) 
								{
									$ins_data[$ins_field] = $data_d[$ins_count];
									$ins_count++;  // Increment
								}
								// Insert
								$employeeuploaddata = $ins_data;
								$employeeuploadfieldtypes = kpmg_generateFieldTypes($employeeuploaddata);
								if ( $wpdb->replace($wpdb->kpmg_employees_upload, $employeeuploaddata, $employeeuploadfieldtypes) === FALSE )
								{
									$this->errors .= "<p class=\"small\">An error occured while saving employee upload row {$upload_row}</p>";
								}
							}
						}

						// Update Persistent Table
						$updatePKSQL = "UPDATE {$db_table} AS ot INNER JOIN {$db_daily_table} dt ON dt.employee_email_address = ot.employee_email_address SET {$copyFieldNamesUPD} ";
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
									$this->errors .= "<p class=\"small\">An error occured while saving employee upload row </p>";
								}
							}
						}


						// TODO
						// Update employee status
						// If Cancelled, remove from groups & update host if applicable
					}	

					// Delete Files
					unlink($local_file);
					unlink($local_file_g);
					unlink($local_file_h);
					unlink($local_file_d);
					unlink($local_file_d1);
					//unlink($local_file_i);
					
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
