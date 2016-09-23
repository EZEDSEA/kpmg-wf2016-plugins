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
		global $KPMG_Admin_ReportGroup;
		global $KPMG_Admin_ReportDiet;
		global $KPMG_Admin_ReportMaster;
		global $KPMG_Admin_ReportTable;
		global $KPMG_Admin_Register;
		global $KPMG_Admin_RegisterUpdate;
		global $KPMG_Admin_Group;
		global $KPMG_Admin_GroupUpdate;
		global $KPMG_Admin_Tables;
		global $KPMG_Email;
		//$KPMG_Admin;
		$KPMG_Login = new KPMG_Login();
		$KPMG_Employee = new KPMG_Employee();
		$KPMG_Employee_Group = new KPMG_Employee_Group();
		$KPMG_Employee_GroupDisplay = new KPMG_Employee_GroupDisplay();
		$KPMG_Admin = new KPMG_Admin();
		$KPMG_Admin_UploadEmployees = new KPMG_Admin_UploadEmployees();
		$KPMG_Admin_CutoffDate = new KPMG_Admin_CutoffDate();
		$KPMG_Admin_ReportGroup = new KPMG_Admin_ReportGroup();
		$KPMG_Admin_ReportDiet = new KPMG_Admin_ReportDiet();
		$KPMG_Admin_ReportMaster = new KPMG_Admin_ReportMaster();
		$KPMG_Admin_ReportTable = new KPMG_Admin_ReportTable();
		$KPMG_Admin_Register = new KPMG_Admin_Register();
		$KPMG_Admin_RegisterUpdate = new KPMG_Admin_RegisterUpdate();
		$KPMG_Admin_Group = new KPMG_Admin_Group();
		$KPMG_Admin_GroupUpdate = new KPMG_Admin_GroupUpdate();
		$KPMG_Admin_Tables = new KPMG_Admin_Tables();
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
		
		//Register with hook 'employeeReserveTable' for creating front end Form
		add_shortcode( 'kpmgwinterfest_employee_cancel_registration', array($KPMG_Employee,'employeeCancelRegistrationProcess') );
		
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
		add_shortcode( 'kpmgwinterfest_admin_register', array($KPMG_Admin_Register,'adminProcess') );
		//Register with hook 'adminProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_admin_registerup', array($KPMG_Admin_RegisterUpdate,'adminProcess') );
		//Register with hook 'adminProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_admin_group', array($KPMG_Admin_Group,'adminProcess') );
		//Register with hook 'adminProcess' for creating front end Form
		add_shortcode( 'kpmgwinterfest_admin_groupup', array($KPMG_Admin_GroupUpdate,'adminProcess') );
		//Register with hook 'adminUnregisteredTables' for creating front end Form
		add_shortcode( 'kpmgwinterfest_unregistered_tables_count', array($KPMG_Admin_Tables,'adminUnregisteredTables') );
		//Register with hook 'adminRegisteredTables' for creating front end Form
		add_shortcode( 'kpmgwinterfest_registered_tables_count', array($KPMG_Admin_Tables,'adminRegisteredTables') );
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
		add_shortcode( 'kpmgwinterfest_admin_upload_employees', array($KPMG_Admin_UploadEmployees,'adminProcess') );
	}
}
