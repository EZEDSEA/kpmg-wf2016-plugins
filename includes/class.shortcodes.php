<?php

/**
 * File: class.shortcodes.php
 * Description of class
 *
 * Author: edward <http://ojambo.com>
 * Copyright: 2016  
 * Created : 2016-08-27 8:10:41 PM
 * Last Modified : 2016-08-28T00:10:41Z
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class KPMG_ShortCodes {
	
	// Constructor
	public function __construct() 
	{
		$this->activate_shortcodes();
    }
	
	public function activate_shortcodes()
	{
		// Variables
		global $KPMG_Login;
		global $KPMG_Employee;
		global $KPMG_Employee_Group;
		global $KPMG_Employee_GroupDisplay;
		global $KPMG_Admin;
		global $KPMG_Admin_UploadEmployees;
		global $KPMG_Admin_CutoffDate;
		global $KPMG_Admin_ReportEmployees;
		global $KPMG_Admin_ReportWaitingList;
		global $KPMG_Admin_ReportGroup;
		global $KPMG_Admin_ReportDiet;
		global $KPMG_Admin_ReportMaster;
		global $KPMG_Admin_ReportTable;
		global $KPMG_Admin_Register;
		global $KPMG_Admin_RegisterUpdate;
		global $KPMG_Admin_RegisteredGroup;
		global $KPMG_Admin_RegisteredTable;
		global $KPMG_Admin_StatusUpdate;
		global $KPMG_Admin_Group;
		global $KPMG_Admin_GroupUpdate;
		global $KPMG_Admin_Tables;
		global $KPMG_Admin_Statistics;
		global $KPMG_Admin_GenerateTables;
		global $KPMG_Email;
		//$KPMG_Admin;
		$KPMG_Login = new KPMG_Login();
		$KPMG_Employee = new KPMG_Employee();
		$KPMG_Employee_Group = new KPMG_Employee_Group();
		$KPMG_Employee_GroupDisplay = new KPMG_Employee_GroupDisplay();
		$KPMG_Admin = new KPMG_Admin();
		$KPMG_Admin_UploadEmployees = new KPMG_Admin_UploadEmployees();
		$KPMG_Admin_CutoffDate = new KPMG_Admin_CutoffDate();
		$KPMG_Admin_ReportEmployees = new KPMG_Admin_ReportEmployees();
		$KPMG_Admin_ReportWaitingList = new KPMG_Admin_ReportWaitingList();
		$KPMG_Admin_ReportGroup = new KPMG_Admin_ReportGroup();
		$KPMG_Admin_ReportDiet = new KPMG_Admin_ReportDiet();
		$KPMG_Admin_ReportMaster = new KPMG_Admin_ReportMaster();
		$KPMG_Admin_ReportTable = new KPMG_Admin_ReportTable();
		$KPMG_Admin_Register = new KPMG_Admin_Register();
		$KPMG_Admin_RegisterUpdate = new KPMG_Admin_RegisterUpdate();
		$KPMG_Admin_RegisteredGroup = new KPMG_Admin_RegisteredGroup();
		$KPMG_Admin_RegisteredTable = new KPMG_Admin_RegisteredTable();
		$KPMG_Admin_StatusUpdate = new KPMG_Admin_StatusUpdate();
		$KPMG_Admin_Group = new KPMG_Admin_Group();
		$KPMG_Admin_GroupUpdate = new KPMG_Admin_GroupUpdate();
		$KPMG_Admin_Tables = new KPMG_Admin_Tables();
		$KPMG_Admin_Statistics = new KPMG_Admin_Statistics();
		$KPMG_Admin_GenerateTables = new KPMG_Admin_GenerateTables();
		$KPMG_Email = new KPMG_Email();

		ob_start();  // Fixes Header Already Sent Warnings

		
		//Register with hook 'loginForm' for creating front end Form
		add_shortcode( 'kpmgwinterfest_login_form', array($KPMG_Login,'kpmgLoginProcess') );
		//Register with hook 'forgotPasswordForm' for creating front end Form
		add_shortcode( 'kpmgwinterfest_forgot_password_form', array($KPMG_Login,'kpmgForgotPasswordProcess') );
		//Register with hook 'LoginLogout' for creating front end Logout
		add_shortcode( 'kpmgwinterfest_login_logout', array($KPMG_Login,'kpmgLoginLogout') );	
		
		//Register with hook 'registerForm' for creating front end Form
		add_shortcode( 'kpmgwinterfest_employee_register_form', array($KPMG_Employee,'employeeRegisterProcess') );
		
		//Register with hook 'employeeProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_employee_group_form', array($KPMG_Employee_Group,'employeeProcess') );
		
		//Register with hook 'employeeProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_employee_show_reserved_group', array($KPMG_Employee_GroupDisplay,'employeeProcess') );
		
		//Register with hook 'employeeCancelRegistrationProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_employee_cancel_registration', array($KPMG_Employee,'employeeCancelRegistrationProcess') );
		//Register with hook 'employeeCancelRegistrationMenuItemProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_employee_cancel_registration_menuitem', array($KPMG_Employee,'employeeCancelRegistrationMenuItemProcess') );
		
		//Register with hook 'employeeReserveTable' for creating front end Form
		add_shortcode( 'kpmgwinterfest_employee_update_attend', array($KPMG_Employee,'employeeUpdateAttendProcess') );
		
		//Register with hook 'employeeReserveTable' for creating front end Form
		add_shortcode( 'kpmgwinterfest_employee_update_diet', array($KPMG_Employee,'employeeUpdateDietProcess') );
		//Register with hook 'employeeReserveTable' for creating front end Form
		add_shortcode( 'kpmgwinterfest_employee_update_guest', array($KPMG_Employee,'employeeUpdateGuestProcess') );
		
		// Admin
		//Register with hook 'adminUpdateInfoProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_admin_register_cutoff', array($KPMG_Admin_CutoffDate,'adminProcess') );
		//Register with hook 'adminProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_admin_status', array($KPMG_Admin_StatusUpdate,'adminProcess') );
		//Register with hook 'adminProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_admin_register', array($KPMG_Admin_Register,'adminProcess') );
		//Register with hook 'adminProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_admin_registerup', array($KPMG_Admin_RegisterUpdate,'adminProcess') );
		//Register with hook 'adminProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_admin_registeredgroup', array($KPMG_Admin_RegisteredGroup,'adminProcess') );
		//Register with hook 'adminProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_admin_registeredtable', array($KPMG_Admin_RegisteredTable,'adminProcess') );
		//Register with hook 'adminProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_admin_group', array($KPMG_Admin_Group,'adminProcess') );
		//Register with hook 'adminProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_admin_groupup', array($KPMG_Admin_GroupUpdate,'adminProcess') );
		//Register with hook 'adminUnregisteredTables' for creating front end Form
		//add_shortcode( 'kpmgwinterfest_unregistered_tables_count', array($KPMG_Admin_Tables,'adminUnregisteredTables') );
		//Register with hook 'adminRegisteredTables' for creating front end Form
		//add_shortcode( 'kpmgwinterfest_registered_tables_count', array($KPMG_Admin_Tables,'adminRegisteredTables') );
		//Register with hook 'adminUnregisteredEmployees' for creating front end Form
		add_shortcode( 'kpmgwinterfest_unregistered_employees_count', array($KPMG_Admin_Statistics,'adminUnregisteredEmployees') );
		//Register with hook 'adminRegisteredEmployees' for creating front end Form
		add_shortcode( 'kpmgwinterfest_registered_employees_count', array($KPMG_Admin_Statistics,'adminRegisteredEmployees') );
		//Register with hook 'adminRegisteredGuests' for creating front end Form
		add_shortcode( 'kpmgwinterfest_registered_guests_count', array($KPMG_Admin_Statistics,'adminRegisteredGuests') );
		//Register with hook 'adminDeclinedEmployees' for creating front end Form
		add_shortcode( 'kpmgwinterfest_declined_employees_count', array($KPMG_Admin_Statistics,'adminDeclinedEmployees') );
		//Register with hook 'adminDinnerEntertainment' for creating front end Form
		add_shortcode( 'kpmgwinterfest_dinner_entertainment_count', array($KPMG_Admin_Statistics,'adminDinnerEntertainment') );
		//Register with hook 'adminOnlyEntertainment' for creating front end Form
		add_shortcode( 'kpmgwinterfest_only_entertainment_count', array($KPMG_Admin_Statistics,'adminOnlyEntertainment') );
		//Register with hook 'adminOnlyEntertainment' for creating front end Form
		add_shortcode( 'kpmgwinterfest_terminated_employees_count', array($KPMG_Admin_Statistics,'adminTerminatedEmployees') );
		//Register with hook 'adminOnlyEntertainment' for creating front end Form
		add_shortcode( 'kpmgwinterfest_cancelled_employees_count', array($KPMG_Admin_Statistics,'adminCancelledEmployees') );
		//Register with hook 'adminWaitingList' for creating front end Form
		add_shortcode( 'kpmgwinterfest_waiting_list_count', array($KPMG_Admin_Statistics,'adminWaitingList') );
		//Register with hook 'adminRegisteredGroups' for creating front end Form
		add_shortcode( 'kpmgwinterfest_registered_groups_count', array($KPMG_Admin_Statistics,'adminRegisteredGroups') );
		//Register with hook 'adminRegisteredTables' for creating front end Form
		add_shortcode( 'kpmgwinterfest_registered_tables_count', array($KPMG_Admin_Statistics,'adminRegisteredTables') );
		//Register with hook 'adminUpdateInfoProcess' for creating front end Form
		//add_shortcode( 'kpmgwinterfest_admin_update_info', array($KPMG_Admin,'adminUpdateInfoProcess') );
		//Register with hook 'adminChangeATableProcess' for creating front end Form
		//add_shortcode( 'kpmgwinterfest_admin_change_table', array($KPMG_Admin,'adminChangeTableProcess') );
		//Register with hook 'adminProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_admin_report_reservations', array($KPMG_Admin_ReportTable,'adminProcess') );
		//Register with hook 'adminProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_admin_report_dietary', array($KPMG_Admin_ReportDiet,'adminProcess') );
		//Register with hook 'adminProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_admin_report_master', array($KPMG_Admin_ReportMaster,'adminProcess') );
		//Register with hook 'adminProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_admin_report_groups', array($KPMG_Admin_ReportGroup,'adminProcess') );
		//Register with hook 'adminProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_admin_report_waitinglist', array($KPMG_Admin_ReportWaitingList,'adminProcess') );
		//Register with hook 'adminProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_admin_report_employees', array($KPMG_Admin_ReportEmployees,'adminProcess') );
		//Register with hook 'adminProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_admin_upload_employees', array($KPMG_Admin_UploadEmployees,'adminProcess') );
		//Register with hook 'adminProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_admin_generate_tables', array($KPMG_Admin_GenerateTables,'adminProcess') );
	}
}
