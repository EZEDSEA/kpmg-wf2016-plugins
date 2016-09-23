<?php

/**
 * File: class.email.php
 * Description of class
 *
 * Author: edward <http://ojambo.com>
 * Copyright: 2016  
 * Created : 2016-08-27 11:32:29 PM
 * Last Modified : 2016-08-28T03:32:29Z
 */
class KPMG_Email {

	// Constructor
	public function __construct() 
	{

	} 
   
	// Generate Register ICalendar Event
	public function generateRegisterEventICalendar($entertainment_only)
	{
		$startDate = "20161213"; // YYMMDD
		$startTime = ($entertainment_only == 1) ? "210000" : "180000"; // HHMMSS
		$endTime = "240000"; // HHMMSS
		$UID = md5(uniqid(mt_rand(), true))."example.com";
		$DTSTAMP = gmdate("YMD")."T".gmdate("His")."Z"; 
		$DTSTART = $startDate."T".$startTime."Z";
		$DTEND = $startDate."T".$endTime."Z";
		$LOCATION = "Metro Toronto Convention Centre - North Building";
		$SUMMARY = "Winterfest 2016 Registration Confirmation";
		$DESCRIPTION = "WinterFest - Be the Life of the Party!\n";
		$DESCRIPTION .= "Saturday, December 10, 2016\n";
		$DESCRIPTION .= "Metro Toronto Convention Centre - North Building\n";
		$DESCRIPTION .= "255 Front St. West, Toronto, ON, M5V 2W6\n";
		$DESCRIPTION .= ($entertainment_only == 1) ? "Entertainment at 9pm\n" :"Registration and cocktail reception begin at 6pm\nDinner at 7pm\nEntertainment at 9pm\n";
		
		$message = "BEGIN:VCALENDAR\r\n";
		$message .= "VERSION:2.0\r\n";
		$message .= "PRODID:-//Ojambo-mailer//winterfest/NONSCML v1.0//EN\r\n";
		$message .= "METHOD:REQUEST\r\n";
		$message .= "BEGIN:VEVENT\r\n";
		$message .= "UID:{$UID}\r\n";
		$message .= "DTSTAMP:{$DTSTAMP}\r\n";
		$message .= "DTSTART:{$DTSTART}\r\n";
		$message .= "DTEND:{$DTEND}\r\n";
		$message .= "SUMMARY:{$SUMMARY}\r\n";
		$message .= "DESCRIPTION:{$DESCRIPTION}\r\n";
		$message .= "END:VEVENT\r\n";
		$message .= "END:VCALENDAR\r\n";
		
		return $message;
	}

	// Generate Group List Names
	public function generateGroupListNames($data_list)
	{
		$list_group_names = "";
		foreach ($data_list as $key => $row)
		{
			$list_group_names .= "{$row['employee_first_name']} {$row['employee_last_name']} <br />";
			$list_group_names .= isset($row['has_guest']) && strtolower($row['has_guest']) == "yes" ? "{$row['guest_first_name']} {$row['guest_last_name']} <br />" : "";
		}
		
		return $list_group_names;
	}
	
	
	// Send Register Email
	public function sendRegisterEmail($data)
	{
		$to = $data['email_address'];
		$subject = "WinterFest registration confirmation email - this is not your ticket to access the venue";
		$details = "";
		$ical = $this->generateRegisterEventICalendar($data['entertainment_only']);
		
		// Create Details Confirmation
		$details .= "Hello {$data['first_name']},<br /><br />";
		if ( $data['entertainment_only'] == 1 && strtolower($data['has_guest']) == 'no' )
		{
			// Email 1 – Registered/NO Dinner/ NO Guest
			$details .= <<<OJAMBO
			This email is to confirm your registration to attend the <b style="font-weight: bold;">entertainment portion</b> of WinterFest beginning at <b style="font-weight: bold;">9pm</b>.<br /><br />
			<span style="background-color: #FFFF00"><b style="font-weight: bold;">Please note that this is a confirmation only and not your ticket for the event.</b></span><br /><br />
			To access the event: <br />
			On <b style="font-weight: bold;">Wednesday December 7</b>, you will receive your ticket via email. To access the venue, you will need to <b style="font-weight: bold;">print your ticket</b> and bring it with you.  <br /><br /> 
			You will be required to show photo ID to enter the event. The name on the printed ticket must match the photo ID. <br /><br />

			If you do not bring your printed ticket, you will be required to join the queue at the information desk located on site to re-register to attend the event.   <br /><br />
			If you have any questions, please visit our <a target="_blank" href="https://kpmgwinterfest.ca/faq">FAQ<a> page or contact the WinterFest mailbox gtawinterfest@kpmg.ca.<br /><br />
			<b style="font-weight: bold;">Registration Details:</b><br /><br />
			<i style="font-style: italic;">Username and password:</i><br/ >
			{$data['email_address']} and {$data['password_one']}<br />
			<u style="text-decoration: underline;">You have registered to attend:</u><br />
			<b style="font-weight: bold;">Entertainment at 9pm</b><br /><br />
OJAMBO;
		}
		elseif ( $data['entertainment_only'] == 1 && strtolower($data['has_guest']) == 'yes' )
		{
			// Email 2 – Registered/NO Dinner/ YES Guest
			$details .= <<<OJAMBO
			This email is to confirm your registration to attend the <b style="font-weight: bold;">entertainment portion</b> of WinterFest with your guest <b style="font-weight: bold;">{$data['first_name_guest']} {$data['last_name_guest']}</b>.<br /><br />
			<span style="background-color: #FFFF00"><b style="font-weight: bold;">Please note that this is a confirmation only and not your ticket for the event.</b></span><br /><br />
			To access the event: <br />
			On <b style="font-weight: bold;">Wednesday December 7</b>, you will receive your ticket via email. To access the venue, you will need to <b style="font-weight: bold;">print your ticket and bring it with you</b>.  <br /><br /> 
			You and your guest will be required to show photo ID to enter the event. The name on the printed ticket must match the photo ID. <br /><br />

			If you do not bring your printed ticket, you will be required to join the queue at the information desk located on site to re-register to attend the event.  <br /><br /> 
			A reminder that <b style="font-weight: bold;"><u style="text-decoration: underline;">all attendees must be 19 years of age or older</u></b> to attend the event.   <br /><br />
			If you have any questions, please visit our <a target="_blank" href="https://kpmgwinterfest.ca/faq">FAQ<a> page or contact the WinterFest mailbox gtawinterfest@kpmg.ca.<br /><br />
			<b style="font-weight: bold;">Registration Details:</b><br /><br />
			<i style="font-style: italic;">Username and password:</i><br/ >
			{$data['email_address']} and {$data['password_one']}<br />
			<i style="font-style: italic;">Guest Name:</i><br />
			{$data['first_name_guest']} {$data['last_name_guest']}<br />
			<u style="text-decoration: underline;">You have registered to attend:</u><br />
			<b style="font-weight: bold;">Entertainment at 9pm</b><br /><br />
OJAMBO;
		}
		elseif ( $data['entertainment_only'] != 1 && strtolower($data['has_guest']) == 'no' )
		{
			// Email 3 – Registered/YES Dinner/ NO Guest
			$details .= <<<OJAMBO
			This email is to confirm your registration to attend <b style="font-weight: bold;">the dinner and entertainment portions</b> of WinterFest.<br /><br />
			<span style="background-color: #FFFF00"><b style="font-weight: bold;">Please note that this is a confirmation only and not your ticket for the event.</b></span><br /><br />
			To access the event: <br />
			On <b style="font-weight: bold;">Wednesday December 7</b>, you will receive your ticket via email. To access the venue, you will need to <b style="font-weight: bold;">print your ticket and bring it with you</b>.  <br /><br /> 
			You will be required to show photo ID to enter the event. The name on the printed ticket must match the photo ID. <br /><br />

			If you do not bring your printed ticket, you will be required to join the queue at the information desk located on site to re-register to attend the event.  <br /><br /> 
			If you have any questions, please visit our <a target="_blank" href="https://kpmgwinterfest.ca/faq">FAQ<a> page or contact the WinterFest mailbox gtawinterfest@kpmg.ca.<br /><br />
			<b style="font-weight: bold;">Registration Details:</b><br /><br />
			<i style="font-style: italic;">Username and password:</i><br/ >
			{$data['email_address']} and {$data['password_one']}<br />
			<i style="font-style: italic;">Dietary:</i><br />
			{$data['dietary_requirements']} {$data['dietary_requirements_other']}<br />
			<u style="text-decoration: underline;">You have registered to attend:</u><br />
			<b style="font-weight: bold;">Cocktail reception at 6pm / Dinner at 7pm / Entertainment at 9pm</b><br /><br />
OJAMBO;
		}
		elseif ( $data['entertainment_only'] != 1 && strtolower($data['has_guest']) == 'yes' )
		{
			// Email 4 – Registered/YES Dinner/ YES Guest
			$details .= <<<OJAMBO
			This email is to confirm your registration to attend the <b style="font-weight: bold;">dinner and entertainment portions</b> of WinterFest with your guest <b style="font-weight: bold;">{$data['first_name_guest']} {$data['last_name_guest']}</b>.<br /><br />
			<span style="background-color: #FFFF00"><b style="font-weight: bold;">Please note that this is a confirmation only and not your ticket for the event.</b></span><br /><br />
			To access the event: <br />
			On <b style="font-weight: bold;">Wednesday December 7</b>, you will receive your ticket via email indicating the <b style="font-weight: bold;">table number</b> you and your guest have been assigned to.  <br /><br /> 
			To access the venue on the evening, you will need to <b style="font-weight: bold;">print your ticket and bring it with you</b>. You and your guest will be required to show photo ID to enter the event. The name on the printed ticket must match the photo ID. <br /><br />

			If you do not bring your printed ticket, you will be required to join the queue at the information desk located on site to re-register to attend the event.  <br /><br /> 
			<b style="font-weight: bold;"><u style="text-decoration: underline;">A reminder that all attendees must be 19 years of age or older</u></b> to attend the event.   <br /><br />
			If you have any questions, please visit our <a target="_blank" href="https://kpmgwinterfest.ca/faq">FAQ<a> page or contact the WinterFest mailbox gtawinterfest@kpmg.ca.<br /><br />
			<b style="font-weight: bold;">Registration Details:</b><br /><br />
			<i style="font-style: italic;">Username and password:</i><br/ >
			{$data['email_address']} and {$data['password_one']}<br />
			<i style="font-style: italic;">Dietary:</i><br />
			{$data['dietary_requirements']} {$data['dietary_requirements_other']}<br />
			<i style="font-style: italic;">Guest Name:</i><br />
			{$data['first_name_guest']} {$data['last_name_guest']}<br />
			<i style="font-style: italic;">Guest Dietary:</i><br />
			{$data['dietary_requirements_guest']} {$data['guest_dietary_requirements_other']}<br />
			<u style="text-decoration: underline;">You have registered to attend:</u><br />
			<b style="font-weight: bold;">Cocktail reception at 6pm / Dinner at 7pm / Entertainment at 9pm</b><br /><br />
OJAMBO;
		}
		
		$details .= <<<OJAMBO
		WinterFest <i style="font-style: italic;">FastForward!</i><br />
		Saturday, December 10, 2016<br />
		Metro Toronto Convention Centre – North Building<br />
		255 Front St. West, Toronto<br /><br />

		Looking <i style="font-style: italic;">FastForward</i> to seeing you there!<br />
		KPMG WinterFest Crew<br />
OJAMBO;
		
		// Create Mime Boundry
		$mime_boundary = "Winterfest Registration Confirmation-".md5(time());
		 
		// Headers
		$headers = "";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: multipart/alternative; boundary=\"{$mime_boundary}\"\n";
		$headers .= "Content-class: urn:content-classes:calendarmessage\n";

		
		// Email Body
		$message = "";
		$message .= "--{$mime_boundary}\n";
		$message .= "Content-Type: text/html; charset=UTF-8\n";
		$message .= "Content-Transfer-Encoding: 8bit\n\n";
		//$message .= "Content-Transfer-Encoding: quoted-printable\n\n";

		$message .= "<html>\n";
		$message .= "<body>\n";
		$message .= "<p>Thank you.  Your registration has now been confirmed with the details below.  Please click on the attached icon to save the event in your Outlook calendar.  We look forward to seeing you at the event!</p>\n";
		$message .= "<p>{$details}</p>\r\n";
		$message .= "</body>\r\n";
		$message .= "</html>\r\n";
		$message .= "--{$mime_boundary}\r\n";
		
		// Email Body Ical
		$message .= "Content-Type: text/calendar; name=\"meeting.ics\"; method=REQUEST; charset=utf-8\n";
		$message .= "Content-Transfer-Encoding: 8bit\n\n";
		//$message .= "Content-Transfer-Encoding: quoted-printable\n\n";
		$message .= $ical;
		
		if ( wp_mail($to, $subject, $message, $headers) )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	// Send Reserve Group Email
	public function sendReserveGroupEmail($data)
	{
		$to = "";
		$subject = "WinterFest 2016 group booking confirmation";
		$details = "";
		$list_group_names = $this->generateGroupListNames($data);
		
		// Create Details Confirmation
		foreach ($data_list as $key => $row)
		{
			if ($key == 0 )
			{
				// Email 5 – Registered/You booked a group for X people/X names
				// Create Host Details Confirmation
				$details = <<<OJAMBO
				Thank you for registering to attend WinterFest. Your group booking has now been confirmed with the following guests. All KPMG employees in your group will also receive a confirmation email:  <br /><br />


				Please keep this email for your records. If you cancel your registration, the second KPMG employee contact name on this list will automatically become the host of this group. <br /> <br />

				{$list_group_names}<br />

				If you have any questions, please visit our FAQ (provide link) page or contact the WinterFest mailbox gtawinterfest@kpmg.ca. <br /><br />

				Looking <i style="font-style: italic;">FastForward</i> to seeing you there!<br /><br />

				KPMG WinterFest Crew<br />
OJAMBO;
				
			}
			else
			{
				// Email 6 – Registered/You have been booked in a group
				// Create Employee Details Confirmation
				$details = <<<OJAMBO
				Thank you for registering to attend WinterFest.  This email is to confirm that {$row['host_first_name']} {$row['host_last_name']} has reserved seats at their table for you (and your guest).  Should you wish to cancel your seating reservation, please contact {$row['host_first_name']} {$row['host_last_name']} and ask to be removed from the group.  <br /><br />

				{$list_group_names}<br />

				Please keep this email for you records. If you have any questions, please contact the WinterFest mailbox gtawinterfest@kpmg.ca. <br /><br />

				Looking <i style="font-style: italic;">FastForward</i> to seeing you there!<br /><br />

				KPMG WinterFest Crew<br />
OJAMBO;
				
			}

			$to = $row['employee_email_address'];
			
			// Headers
			$headers = "";
			$headers .= "MIME-Version: 1.0\n";
			$headers .= "Content-Type: text/html; charset=UTF-8\n";

			// Email Body
			$message = "";
			$message .= "--{$mime_boundary}\n";
			$message .= "Content-Type: text/html; charset=UTF-8\n";
			$message .= "Content-Transfer-Encoding: 8bit\n\n";
			//$message .= "Content-Transfer-Encoding: quoted-printable\n\n";

			$message .= "<html>\n";
			$message .= "<body>\n";
			$message .= "<p>{$details}</p>\r\n";
			$message .= "</body>\r\n";
			$message .= "</html>\r\n";
			
			// Send Email & Ignore Errors
			wp_mail($to, $subject, $message, $headers);

		}
		/*
		if ( wp_mail($to, $subject, $message, $headers) )
		{
			return true;
		}
		else
		{
			return false;
		}*/
		return true;
	}

	// Send Forgot Password Email
	public function sendForgotPasswordEmail($data)
	{
		$to = $data['email_address'];
		$subject = "Winterfest New Password";
		
		// Headers
		$headers = "";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\n";
		
		// Email Body
		$message = "";
		$message .= "Your new password is: {$data['password_one']} ";
		
		if ( wp_mail($to, $subject, $message, $headers) )
		{
			return true;
		}
		else
		{
			return false;
		}
	}	
	
	// Send Inform Admin Cancellation Request Email
	function sendInformAdminCancellationRequestEmail($data)
	{
		global $wpdb;
		
		$to = $data['employee_email_address'];
		$subject = "Subject line: WinterFest registration CANCELLATION confirmation";
		$message = "";
		
		$message .= <<<OJAMBO
		Hello {$data['employee_first_names']} {$data['employee_last_names']},
		This email is to confirm that your registration to WinterFest has been <b style="font-weight: bold;"<u style="text-decoration: underline;">CANCELLED</u></b>.  If you were the host of a group booking, the second KPMG employee name on that list is now the host. <br /><br />

		In the event that you would like to re-register, please re-visit the website and recreate a login.<br />
		If you have any questions, please visit our FAQ (provide link) page or contact the WinterFest mailbox gtawinterfest@kpmg.ca.<br /><br />

		Thank you, <br />
		KPMG WinterFest Crew <br />			
OJAMBO;
		// Headers
		$headers = "";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\n";
		
		if ( wp_mail($to, $subject, $message, $headers) )
		{
			return true;
		}
		else
		{
			return false;
		}
		
	}	
	
	// Send Inform Admin Cancellation Request Email
	function sendRegistrationCancellationEmail($data)
	{
		global $wpdb;
		
		$to = $data['employee_email_address'];
		$subject = "Subject line: WinterFest registration CANCELLATION confirmation";
		$message = "";
		
		$message .= <<<OJAMBO
		Hello {$data['employee_first_names']} {$data['employee_last_names']},
		This email is to confirm that your registration to WinterFest has been <b style="font-weight: bold;"<u style="text-decoration: underline;">CANCELLED</u></b>.  If you were the host of a group booking, the second KPMG employee name on that list is now the host. <br /><br />

		In the event that you would like to re-register, please re-visit the website and recreate a login.<br />
		If you have any questions, please visit our FAQ (provide link) page or contact the WinterFest mailbox gtawinterfest@kpmg.ca.<br /><br />

		Thank you, <br />
		KPMG WinterFest Crew <br />			
OJAMBO;
		// Headers
		$headers = "";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\n";
		
		if ( wp_mail($to, $subject, $message, $headers) )
		{
			return true;
		}
		else
		{
			return false;
		}
		
	}	
	
	// Send CSV Email
	function sendCSVEmail($csvFile, $email)
	{
		$to = $email;
		$subject = "Winterfest CSV Report";
		
		// Create Mime Boundry
		$mime_boundary = "Winterfest CSV Report-".md5(time());
		
		// Headers
		$headers = "";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: multipart/mixed; boundary=\"{$mime_boundary}\"\r\n";
		
		// Attachement
		$base64EncodedCSVFile = base64_encode($csvFile);
		$attachment = chunk_split($base64EncodedCSVFile);
		
		// Email Body
		$message = "";
		$message .= "--{$mime_boundary}\r\n";
		$message .= "Content-Type: text/html; charset=UTF-8; format=flowed\r\n";
		$message .= "Content-Transfer-Encoding: 8bit\r\n";
		$message .= "\r\n";
		
		$message .= "<html>\r\n";
		$message .= "<body>\r\n";
		$message .= "<p>You can open the attached CSV Report</p>\r\n";
		$message .= "<p></p>\r\n";
		$message .= "</body>\r\n";
		$message .= "</html>\r\n";
		$message .= "--{$mime_boundary}\r\n";
		
		// Email Body CSV
		$message .= "Content-Type: text/csv\r\n";
		$message .= "Content-Transfer-Encoding: base64\r\n";
		$message .= "Content-Disposition: attachment; filename=\"report.csv\"\r\n";
		$message .= "\r\n";
		$message .= "{$attachment}\r\n";
		$message .= "--{$mime_boundary}\r\n";
		
		if ( wp_mail($to, $subject, $message, $headers) )
		{
			return true;
		}
		else
		{
			return false;
		}
		
	}	
}