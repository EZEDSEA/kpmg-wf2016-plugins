<?php

/**
 * File: class.generatetables.php
 * Description of class
 * Generate Table Based On Groups
 * Cron Job Runs Daily
 *
 * Author: edward <http://ojambo.com>
 * Copyright: 2016  
 * Created : 2016-09-22 11:16:44 PM
 * Last Modified : 2016-09-23T03:16:44Z
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class KPMG_GenerateTables {
	
	// Constructor
	public function __construct() 
	{
			
		// RAn Action Outside Class Definition
		add_action( 'kpmg_generate_tables_event', array($this,'hook'));
    }
	
	public function hook()
	{
		global $wpdb;
		
		// Variables
		$saveInTable = $wpdb->kpmg_registration_details;

		//ob_start();  // Fixes Header Already Sent Warnings
		
		$data = array();
		$result = $wpdb->get_results(
			"SELECT det.user_id, det.employee_email_address, det.group_id, det.has_guest 
				FROM wp_kpmg_registration_details det
				WHERE det.group_id > 0
				ORDER BY det.group_id ASC, det.group_seat ASC"
			, ARRAY_A
		);	
		
		if ( count($result) > 0 )
		{
			foreach ($result as $key => $row)
			{
				$datakey = $row['group_id'];
				if ( !isset($data[$datakey]) )
				{
					$data[$datakey] = 0;
				}
				else
				{
					$data[$datakey]++;  // Employee Increment
					if ( strtolower($row['has_guest']) == "yes" )
					{
						$data[$datakey]++;  // Guest Increment
					}
				}
			}
			
			// Remove All Current Tables
			$wpdb->query("UPDATE {$saveInTable} SET table_id = 0");			
			
			// Iterate Through Remaining Data & Update Database
			//$keys = array();
			$counter = 0;
			while (count($data) > 0)
			{

				$maxsum = 10;

				$results = $this->bestsum($data,$maxsum);  //function call

				$counter++;  // Increment As New Table
				//$keys[$counter] = $results;

				// Iterate Through Data, Save & Remove Keys
				foreach ($results as $key => $arrkey)
				{
					// Save
					if ( $key > 0 )
					{
						$saveArr['table_id'] = $counter;
						$saveIDArr['group_id'] = $key;
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
