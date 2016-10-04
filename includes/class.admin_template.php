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
class KPMG_Admin_Group {
		
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

		$Form = <<<OJAMBO
			<div class="errors">
				{$Errors}
				<p class="small" id="kpmg-{$formVariable}-ajax-error-area"></p>
			</div>
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
		$saveInTable = $wpdb->kpmg_registration_cutoff;
		$saveArr = array();
		$saveIDArr = array();
		$dataArr = array();
		$arrTypes = array (
			'registration_limit' => 'number',
			'waiting_list_limit' => 'number',
			'table_limit' => 'number',
			'table_seat_limit' => 'number',
			'registration_end_date' => 'date',
			'registration_cuttoff_id' => 'hidden',
		);
		$update_date_format = 'Y-m-d H:i:s';
		$updateID = "registration_cuttoff_id";
		
		if ( $this->adminrole != NULL && ($_POST['kpmg_formaction'] == $this->formaction) )
		{
			$saveTableFieldsResult = kpmg_getDatabaseTableColumns($saveInTable);
			$saveTableFieldsArr = array();
			$saveID = NULL;
			
			foreach($saveTableFieldsResult as $row)
			{
				$fieldName = $row['Field'];
				$dataArr[$fieldName] = isset($_POST[$fieldName]) ? $_POST[$fieldName] : false;
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
					else
					{
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
		
			// Save Data
			if ( $this->errors == "" )
			{
				$saveiddata = $saveIDArr;
				$saveiddatafieldtypes = kpmg_generateFieldTypes($saveiddata);
				$savedata = $saveArr;
				$savedatafieldtypes = kpmg_generateFieldTypes($savedata);
				if ( $wpdb->update($saveInTable, $savedata, $saveiddata, $savedatafieldtypes, $saveiddatafieldtypes) === FALSE )
				{
					$this->errors .= "<p class=\"small\">An error occured while saving registration cutoff</p>";
				}
				else
				{
					// Thank You Message
					$this->thanks .= "<p class=\"thanks\">Successfully saved registration cutoff</p>";
									
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
