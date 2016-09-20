<?php

/**
 * File: class.login.php
 * Description of class.login.php
 *
 * Author: edward <http://ojambo.com>
 * Copyright: 2016  
 * Created : 2016-08-27 6:46:02 PM
 * Last Modified : 2016-08-27T22:46:02Z
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class KPMG_Login {

	// Variables
	private $salt;
	private $table_db_version;
	public $employee_role;
	public $admin_role;
	private $winterfesturl;
	private $pagemyinfo;
	private $pageadministration;
	private $pageforgotpassword;
	private $pagelogin;
	private $pageregister;
	private $pagelogoutredirect;
	private $errorslogin;
	private $errorsforgotpassword;
	
	// Constructor
	public function __construct() 
	{	
		$this->salt = KPMGWF_Salt;
		$this->table_db_version = "1.0";
		$this->employee_role = KPMGWF_EmployeeRole;
		$this->admin_role = KPMGWF_AdminRole;
		$this->winterfesturl = KPMGWF_Site;
		$this->pagemyinfo = KPMGWF_Info;
		$this->pageadministration = KPMGWF_Admin;
		$this->pageforgotpassword =  KPMGWF_ForgotPassword;
		$this->pagelogin =  KPMGWF_Login;
		$this->pageregister =  KPMGWF_Register;
		$this->pagelogoutredirect = KPMGWF_LogoutRedirect;
		$this->errorslogin = "";
		$this->errorsforgotpassword = "";
	}	

	// Generate KPMG Login Password
	/**
   * Generate Random String
   * @param Int Length of string(50)
   * @param Bool Upper Case(True,False)
   * @param Bool Numbers(True,False)
   * @param Bool Special Chars(True,False)
   * @return String  Random String
	* 
	* Borrowed From StackOverFlow Emilio Gort Sept 26 '13 at 1:55
   */
	public function generateKpmgLoginPassword($length, $uc, $n, $sc)
	{
	   $rstr='';
      $source = 'abcdefghijklmnopqrstuvwxyz';
      if ($uc)
          $source .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
      if ($n)
          $source .= '1234567890';
      if ($sc)
          $source .= '|@#~$%()=^*+[]{}-_';
      if ($length > 0) {
          $rstr = "";
          $length1= $length-1;
          $input=array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z') ;
          $rand = array_rand($input, 1);
          $source = str_split($source, 1);
          for ($i = 1; $i <= $length1; $i++) {
              $num = mt_rand(1, count($source));
              $rstr .= $source[$num - 1];
              $rstr = "{$rand}{$rstr}";
          }
      }
      return $rstr;
	}
	
	// Custom KPMG Forgot Password Form For Admins & Employees 
   public function kpmgForgotPasswordForm() 
	{
		$formErrors = $this->errorsforgotpassword;
		$employeeForgotPasswordForm = <<<OJAMBO
			<form method="post" action="{$_SERVER['REQUEST_URI']}">
				<fieldset>
					<p>Please enter your email address in order to reset your password</p>
					<label for="login-name"></label>
					<input name="login_name" type="text" value="" placeholder="Email Address" required />

					<input  name="action" type="hidden" value="forgotpassword" />
					<button type="submit" name="kpmg_forgot_password_submit" value="Get New Password" >Get New Password</button>
				</fieldset>
			</form>
			<div class="error">
			{$formErrors}
			</div>
OJAMBO;
		 return $employeeForgotPasswordForm;
    }
	 
	// KPMG Forgot Password Completed
	public function kpmgForgotPasswordCompleted()
	{		
		$kpmgForgotPasswordCompleted = <<<OJAMBO
		<h3>YOUR PASSWORD HAS BEEN RESET</h3>
			<p>Check your email address for your new password</p>
OJAMBO;
		
		return $kpmgForgotPasswordCompleted;
	}	
	// KPMG Forgot Password Form Authorization
	public function kpmgForgotPasswordAuthorization( $emailaddress, $action ) 
	{
		global $wpdb;
		
		global $KPMG_Email;
		
		$this->errorsforgotpassword = "";
		
		$email_address = strtolower(trim($emailaddress));
		// Validate
		if ( empty($email_address) )
		{
			$this->errorsforgotpassword .= "<p class=\"small\">Please fill in all required fields.</p>"; 
		}
		if ( !filter_var($email_address, FILTER_VALIDATE_EMAIL) )
		{
			$this->errorsforgotpassword .= "<p class=\"small\">The email address is invalid.</p>";
		}
		elseif ( !email_exists($email_address) )
		{
			// Check to see if the email address does not exists
				$this->errorsforgotpassword .= "<p class=\"small\">There is no user registered with that email address.</p>";
		}
		
		if ( $this->errorsforgotpassword == "" )
		{
			$new_password = $this->generateKpmgLoginPassword(8, TRUE, TRUE, FALSE);
			$new_password_hash = MD5($new_password);
			$user = get_user_by('email', $email_address);
			// Update User Information
			$updateuserdata = array('user_pass' => $new_password_hash);
			$updateuserdatafieldtypes = kpmg_generateFieldTypes($updateuserdata);
			$updateuserdatacondition = array('ID' => $user->ID);
			$updateuserdataconditionfieldtypes = kpmg_generateFieldTypes($updateuserdatacondition);
			
			$update_user = $wpdb->update(
				$wpdb->users,
				$updateuserdata,
				$updateuserdatacondition,
				$updateuserdatafieldtypes,
				$updateuserdataconditionfieldtypes
			);
			// If User Information Updated
			if ( $update_user )
			{
				$updatedUserData = array(
					'email_address' => $email_address,
					'password_one' => $new_password
				);
				// Send Reset Password Email
				if ( $KPMG_Email->sendForgotPasswordEmail($updatedUserData) )
				{
					// Redirect To Prevent Form Resubmission & Page Reload 
					$new_url = add_query_arg( 'forgotpassword', 1, get_permalink() );  // forgotpassword Var
					wp_redirect( $new_url, 303 );  // Allow Response Cache Only
					return "email sent";
				}
			}
		}
		
	}
	
	// KPMG Forgot Password Form Process
	public function kpmgForgotPasswordProcess() 
	{
		// Forgot Password  Form Handler
		if ( isset($_POST['kpmg_forgot_password_submit']) )
		{
			$this->kpmgForgotPasswordAuthorization($_POST['login_name'], $_POST['action']);
		}
		elseif ( isset($_GET['forgotpassword']) )
		{
			return $this->kpmgForgotPasswordCompleted();
		}
		
		// Display Forgot Password  Form
		return $this->kpmgForgotPasswordForm();
	}	 
	 	
	// KPMG Login Form For Admins & Employees 
   public function kpmgLoginForm() 
	{
		$formErrors = $this->errorslogin;
		$forgotPasswordPage = $this->pageforgotpassword;
		
		$employeeLoginForm = <<<OJAMBO
			<form id="login" method="post" action="{$_SERVER['REQUEST_URI']}">
				<label for="login-name"></label>
				<input name="login_name" type="text" value="" placeholder="Username" required />
							
				<label for="login-pass"></label>
				<input  name="login_password" type="password" value="" placeholder="Password" required />
				
				<p><a href="{$forgotPasswordPage}">Forgot your password?</a></p>
							
				<button type="submit" name="kpmg_login_submit" value="Sign In" >Sign In</button>
			</form>
			{$formErrors}
			
OJAMBO;
		 return $employeeLoginForm;
    }
	 
	// KPMG Login Form Authorization
	public function kpmgLoginAuthorization( $username, $password ) 
	{
		global $user;
		
		$employeeRole = $this->employee_role;
		$adminRole = $this->admin_role;
		$employeePage = $this->pagemyinfo;
		$adminPage = $this->pageadministration;
		$redirectPage = "";
		
		
		if ( !isset($_SESSION['kpmg_login_attempts']) )
		{
			$_SESSION['kpmg_login_attempts'] = 1;
		}
		else
		{
			$_SESSION['kpmg_login_attempts']++; // Increment
		}
		
		if ( $_SESSION['kpmg_login_attempts'] < 7 )
		{			
			
			$creds = array();
			$creds['user_login'] = $username;
			$creds['user_password'] =  $password;
			$creds['remember'] = true;
			$user = wp_signon( $creds, false );
			if ( is_wp_error($user) ) 
			{
				//echo $user->get_error_message();
				$this->errorslogin = "<p class=\"small\">The username and password you entered are incorrect.</p>";
			}
			if ( !is_wp_error($user) ) 
			{
				// Get User Role
				$roles = $user->roles;
				if ( in_array($employeeRole, $roles) )
				{
					$redirectPage = $employeePage;
				}
				if ( in_array($adminRole, $roles) )
				{
					$redirectPage = $adminPage;
				}
				// Redirect To User's Page
				wp_redirect( $redirectPage, 303 );  // Allow Response Cache Only
			}
		}
		else
		{
			$this->errorslogin = "<p class=\"small\">You have exceeded the maximum allowed login attempts.</p>";
		}
		
	}
	
	// KPMG Login Form Process
	public function kpmgLoginProcess() 
	{
		// Login Form Handler
		if ( isset($_POST['kpmg_login_submit']) )
		{
			$this->kpmgLoginAuthorization($_POST['login_name'], $_POST['login_password']);
		}
		
		// Display Login Form
		return $this->kpmgLoginForm();
	}
	
	// KPMG Login Logout
	public function kpmgLoginLogout() 
	{
		global $user;
		
		$loginPage = $this->pagelogin;
		$registerPage = $this->pageregister;
		$logoutRedirectPage = $this->pagelogoutredirect;
		$logoutURL = wp_logout_url( $logoutRedirectPage ); // Logout Redirect;
		$loginLogout = "";
		
		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userFirstName = $current_user->first_name;
			$loginLogout .= "<span>Welcome {$userFirstName}</span>";
			$loginLogout .= "<a href=\"{$logoutURL}\">Logout</a>";
		}
		else
		{

			$loginLogout .= "<a href=\"{$loginPage}\">Login</a>";
			$loginLogout .= "or";
			$loginLogout .= "<a href=\"{$registerPage}\">Register</a>";
		}
		
		// Display Login Logout
		return $loginLogout;
		
	}
}
