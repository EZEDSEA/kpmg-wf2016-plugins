<?php

/**
 * File: class.menuitems.php
 * Description of class
 * DIsplay Appropriate Menu Items Based On Role & Login
 * 
 * Author: edward <http://ojambo.com>
 * Copyright: 2016  
 * Created : 2016-09-28 8:18:40 PM
 * Last Modified : 2016-09-29T00:18:40Z
 */
class KPMG_MenuItems {
	// Variables
	private $salt;
	private $role = NULL;
	private $isEmployee = false;

	// Constructor
	public function __construct() 
	{
		$this->salt = KPMGWF_Salt;
		
		global $user;

		$employeeRole = KPMGWF_EmployeeRole;
		$adminRole = KPMGWF_AdminRole;

		// Logged In User Only
		if (is_user_logged_in() )
		{
			$current_user = wp_get_current_user();
			$userID = $current_user->ID;
			$roles = $current_user->roles;
			$user_email = $current_user->user_email;
			$this->updateemail = $user_email;
			if ( in_array($employeeRole, $roles) || in_array($adminRole, $roles) )
			{
				$this->role = in_array($adminRole, $roles) ? $adminRole : $employeeRole;
				$this->isEmployee = true;
			}
		}	
		
		// Filter Menu Items
		//add_filter( 'plugin_action_links_' . KPMGWF_FILE, array( __CLASS__, 'plugin_action_links' ) );
		add_filter( 'wp_nav_menu_objects', array($this, 'displayMenu') );
	}	
	
	// Display Menu
	public function displayMenu($menu)
	{
		// Login Pages Only
		$loginPagesArr = array (
			'my-info',
			'my info',
			'group'
		);
		
		// Not Logged Pages Only
		$notLoginPagesArr = array (
			'login',
			'sign up/log in',
		);
		
		
		if (  is_page() )
		{
			foreach ($menu as $key => $item )
			{
				// Hide My Information Page Based On Login Information
				if ( in_array(strtolower($item->title), $loginPagesArr) && $this->role == NULL )
				{
					unset($menu[$key]);
				}
				elseif ( in_array(strtolower($item->title), $notLoginPagesArr) && $this->role != NULL )
				{
					// Hide My Information Page Based On Login Information
					unset($menu[$key]);
				}

			}
		
		}
		return $menu;
	}
	
}
