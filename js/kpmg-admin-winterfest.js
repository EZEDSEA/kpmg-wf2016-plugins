/* 
 * Plugin Name: KPMG Winterfest 2016
 * Plugin URI: http://ojambo.com
 * Description: KPMG Winterfest 2016 Wordpress plugin
 * Author: Edward Ojambo
 * Author URI: http://ojambo.com
 * Version: 1.0.0
 * License: GPL2
 */

// Navigation
jQuery(function($) {
	// Admin Navigation
	var pathFull = window.location.href; // because the 'href' property of the DOM element is the absolute path
	/*var hash  = pathFull.substr(pathFull.indexOf("#") + 1); 
	if (pathFull === hash ) { hash = ""; }
	window.location.replace("#", ""); // Remove Fragement as much as possible*/
	var path = (function(s){var i=s.indexOf('#');
		return i==-1 ? s : s.substr(0,i);})(pathFull); // Everything before potential hash
	jQuery('ul.nav-tabs li a').each(function() {
		var target = jQuery(this).attr('data-target');
		if (this.href === path) {
			// Make Active
			jQuery(this).removeClass('inactive');
			jQuery(this).addClass('active');
			jQuery('#'+target).removeClass('hide');
			jQuery('#'+target).addClass('show');
		}
		else
		{
			// Make Inactive
			jQuery(this).removeClass('active');
			jQuery(this).addClass('inactive');
			jQuery('#'+target).removeClass('show');
			jQuery('#'+target).addClass('hide');
		}
	});
	
	/*// Smooth Scroll
	// Make sure this.hash has a value before overriding default behavior
	if (hash !== "") {
		// Prevent default anchor click behavior
		//event.preventDefault();

		// Store hash
		//var hash = this.hash;
		hash = "#"+hash;

		// Make Sure Element Exists
		if ( jQuery(hash).length )
		{
			// Using jQuery's animate() method to add smooth page scroll
			// The optional number (800) specifies the number of milliseconds it takes to scroll to the specified area
			jQuery('html, body').animate({
				scrollTop: jQuery(hash).offset().top
			}, 800, function(){

			// Add hash (#) to URL when done scrolling (default click behavior)
			//window.location.hash = hash;
			});
		}
	} // End if*/
	
	// Draggable Group Items
	var draggedGroupItem = null;
	jQuery('.draggable_group_item').each(function(index){
        jQuery(this).on("dragstart", handleDragStart);
        jQuery(this).on("drop", handleDrop);
        jQuery(this).on("dragover", handleDragOver);
    });
});
	function handleDragStart(e){
        draggedItem=this;
        e.originalEvent.dataTransfer.effectAllowed = 'move';
        //e.dataTransfer.dropEffect = 'move'; //MH - do we need both of these?
        e.originalEvent.dataTransfer.setData('text/html', this.innerHTML);
    }

    function handleDragOver(e) {
          if (e.preventDefault) {
            e.preventDefault(); // Necessary. Allows us to drop.
          }
          e.originalEvent.dataTransfer.dropEffect = 'move';  // See the section on the DataTransfer object.
          return false;
    }

    function handleDrop(e){
        if (e.stopPropagation) {
            e.stopPropagation(); // Stops some browsers from redirecting.
        }

        if (draggedItem != this) { //MH - swap if we're not dragging the item onto itself
			// Hide/Show Remove 
			jQuery(draggedItem).addClass('is_host');
			jQuery(draggedItem).find('.remove_field').addClass('hide');
			jQuery(this).removeClass('is_host');
			jQuery(this).find('.remove_field').removeClass('hide');
			
			// Swap names
			var oldHostName = jQuery(this).find('.host_email_address').attr('name');
			var oldEmailName = jQuery(this).find('.email_address').attr('name');
			var newHostName = jQuery(draggedItem).find('.host_email_address').attr('name');
			var newEmailName = jQuery(draggedItem).find('.email_address').attr('name');
			jQuery(this).find('.host_email_address').attr('name', newHostName);
			jQuery(this).find('.email_address').attr('name', newEmailName);
			jQuery(draggedItem).find('.host_email_address').attr('name', oldHostName);
			jQuery(draggedItem).find('.email_address').attr('name', oldEmailName);
			jQuery(draggedItem).find('.email_address').attr('name', oldEmailName);
			
			// Swap Positions
            var copy=jQuery(this).clone(true,true);
            jQuery(this).replaceWith(jQuery(draggedItem).clone(true,true));
            jQuery(draggedItem).replaceWith(jQuery(copy).clone(true,true));
			
			
			// Set new Host
			var newHost = jQuery(draggedItem).find('.email_address').val();
			jQuery('.admingroupform .host_email_address').val(newHost);
			
			
        } 
        return false;
    }	


// Automatic Register Email Ajax
jQuery('form#kpmg-adminregister-form input.email_address').keyup(function()
{
	var formErrorArea = jQuery("#kpmg-adminregister-ajax-error-area");
	var formResultsArea = jQuery("#kpmg-adminregister-ajax-results-area");
	var form = jQuery('form#kpmg-adminregister-form');
	var emailAddressInput = jQuery('input#kpmg_adminregister_email_address');
	//var emailValidate = /\S+@\S+/; // Only hav forward slashes
	var emailValidate = /\S{2,}/; // Only have forward slashes
	var emailAddressValue = jQuery(this).val();
	var theAction = 'kpmgEmployeeCheckAJAX';

	// Validate Email Before Ajax Check
	if ( emailValidate.test(emailAddressValue) )
	{
		// Ajax Submit
		jQuery.ajax(
		{
			url: ajaxregisteremployeecheck.ajaxurl,
			type: 'POST',
			data: {
				'action': theAction,
				'register_employee_check': '1',
				'email_address': emailAddressValue
			},
			success: function(msg){

				var objData = jQuery.parseJSON(msg);
				
				// Error 
				formErrorArea.html('');

				// Results 
				formResultsArea.html('');

				// Check if error
				if (objData.hasOwnProperty('error') )
				{
					formErrorArea.html(objData.error);
				}
				else
				{
					// Create Results Drop-down
					jQuery(objData).each(function(key, value) {
						formResultsArea.append('<div class="item">' +value.employee_email_address+ '</div>');
					});
					// Results Item Click
					jQuery('.item', formResultsArea).click(function() {
						var text = jQuery(this).html();
						// Critical Data Update
						emailAddressInput.val(text);
					});								
				}
			}
		});

	}
	
});

// Automatic Register Email Results Ajax
jQuery("form#kpmg-adminregister-form input.email_address").blur(function(){
	jQuery("#kpmg-adminregister-ajax-results-area").fadeOut(500);
})
.focus(function() {		
	jQuery("#kpmg-adminregister-ajax-results-area").show();
});

// Register Bring Guest
jQuery('form#kpmg-adminregister-form select.entertainment_only').on('change', function() {
	if (jQuery(this).val() == '1' || jQuery(this).val() == '')
	{
		// Hide Diet Information & Clear
		jQuery('form#kpmg-adminregister-form .diet-info').removeClass('show');
		jQuery('form#kpmg-adminregister-form .diet-info').addClass('hide');
		jQuery('form#kpmg-adminregister-form .diet-info').removeAttr('selected');
		// Hide Guest Diet Information & Clear
		jQuery('form#kpmg-adminregister-form .guest-diet-info').removeClass('show');
		jQuery('form#kpmg-adminregister-form .guest-diet-info').addClass('hide');
		jQuery('form#kpmg-adminregister-form .guest-diet-info-select').removeAttr('selected');
	}
	else
	{
		// Assume Dinner And Entertainment Show Diet Information
		jQuery('form#kpmg-adminregister-form .diet-info').removeClass('hide');
		jQuery('form#kpmg-adminregister-form .diet-info').addClass('show');
		// Show Guest Dietary Information If Applicable
		if ( jQuery('form#kpmg-adminregister-form select.has_guest').val() == 'yes' )
		{
			jQuery('form#kpmg-adminregister-form .guest-diet-info').removeClass('hide');
			jQuery('form#kpmg-adminregister-form .guest-diet-info').addClass('show');
		}
	}
});

// Register Entertainment Only
jQuery('form#kpmg-adminregister-form select.has_guest').on('change', function() {
	if (jQuery(this).val() == 'Yes')
	{
		// Show Guest Information
		jQuery('form#kpmg-adminregister-form .guest-info').removeClass('hide');
		jQuery('form#kpmg-adminregister-form .guest-info').addClass('show');
		
		// Show Guest Dietary Information If Applicable
		if ( jQuery('form#kpmg-adminregister-form select.entertainment_only').val() != '1' )
		{
			jQuery('form#kpmg-adminregister-form .guest-diet-info').removeClass('hide');
			jQuery('form#kpmg-adminregister-form .guest-diet-info').addClass('show');
		}
	}
	else
	{
		// Hide Guest Information
		jQuery('form#kpmg-adminregister-form .guest-info').removeClass('show');
		jQuery('form#kpmg-adminregister-form .guest-info').addClass('hide');
		
		// Hide Guest Diet Information & Clear
		jQuery('form#kpmg-adminregister-form .guest-diet-info').removeClass('show');
		jQuery('form#kpmg-adminregister-form .guest-diet-info').addClass('hide');
		jQuery('form#kpmg-adminregister-form .guest-diet-info-select').removeAttr('selected');
	}
});

// Automatic Update Status  Email Ajax
jQuery('form#kpmg-adminstatusup-get-form input.email_address').keyup(function()
{
	var formErrorArea = jQuery("#kpmg-adminstatusup-ajax-error-area");
	var formResultsArea = jQuery("#kpmg-adminstatusup-ajax-results-area");
	var form = jQuery('form#kpmg-adminstatusup-get-form');
	var emailAddressInput = jQuery('input#kpmg_adminstatusup_email_address');
	//var emailValidate = /\S+@\S+/; // Only hav forward slashes
	var emailValidate = /\S{2,}/; // Only have forward slashes
	var emailAddressValue = jQuery(this).val();
	var theAction = 'kpmgEmployeeCheckAJAX';

	// Validate Email Before Ajax Check
	if ( emailValidate.test(emailAddressValue) )
	{
		// Ajax Submit
		jQuery.ajax(
		{
			url: ajaxregisteremployeecheck.ajaxurl,
			type: 'POST',
			data: {
				'action': theAction,
				'register_employee_check': '1',
				'email_address': emailAddressValue
			},
			success: function(msg){

				var objData = jQuery.parseJSON(msg);
				
				// Error 
				formErrorArea.html('');

				// Results 
				formResultsArea.html('');

				// Check if error
				if (objData.hasOwnProperty('error') )
				{
					formErrorArea.html(objData.error);
				}
				else
				{
					// Create Results Drop-down
					jQuery(objData).each(function(key, value) {
						formResultsArea.append('<div class="item">' +value.employee_email_address+ '</div>');
					});
					// Results Item Click
					jQuery('.item', formResultsArea).click(function() {
						var text = jQuery(this).html();
						// Critical Data Update
						emailAddressInput.val(text);
					});								
				}
			}
		});

	}
	
});

// Automatic Update Status  Email Results Ajax
jQuery("form#kpmg-adminstatusup-get-form input.email_address").blur(function(){
	jQuery("#kpmg-adminstatusup-ajax-results-area").fadeOut(500);
})
.focus(function() {		
	jQuery("#kpmg-adminstatusup-ajax-results-area").show();
});

// Automatic Register Email Ajax
jQuery('form#kpmg-adminregisterup-get-form input.email_address').keyup(function()
{
	var formErrorArea = jQuery("#kpmg-adminregisterup-ajax-error-area");
	var formResultsArea = jQuery("#kpmg-adminregisterup-ajax-results-area");
	var form = jQuery('form#kpmg-adminregisterup-get-form');
	var emailAddressInput = jQuery('input#kpmg_adminregisterup_email_address');
	//var emailValidate = /\S+@\S+/; // Only hav forward slashes
	var emailValidate = /\S{2,}/; // Only have forward slashes
	var emailAddressValue = jQuery(this).val();
	var theAction = 'kpmgEmployeeRegCheckAJAX';

	// Validate Email Before Ajax Check
	if ( emailValidate.test(emailAddressValue) )
	{
		// Ajax Submit
		jQuery.ajax(
		{
			url: ajaxregisteremployeecheck.ajaxurl,
			type: 'POST',
			data: {
				'action': theAction,
				'register_employee_check': '1',
				'email_address': emailAddressValue
			},
			success: function(msg){

				var objData = jQuery.parseJSON(msg);
				
				// Error 
				formErrorArea.html('');

				// Results 
				formResultsArea.html('');

				// Check if error
				if (objData.hasOwnProperty('error') )
				{
					formErrorArea.html(objData.error);
				}
				else
				{
					// Create Results Drop-down
					jQuery(objData).each(function(key, value) {
						formResultsArea.append('<div class="item">' +value.employee_email_address+ '</div>');
					});
					// Results Item Click
					jQuery('.item', formResultsArea).click(function() {
						var text = jQuery(this).html();
						// Critical Data Update
						emailAddressInput.val(text);
					});								
				}
			}
		});

	}
	
});

// Automatic Register Email Results Ajax
jQuery("form#kpmg-adminregisterup-get-form input.email_address").blur(function(){
	jQuery("#kpmg-adminregisterup-ajax-results-area").fadeOut(500);
})
.focus(function() {		
	jQuery("#kpmg-adminregisterup-ajax-results-area").show();
});

// Register Bring Guest
jQuery('form#kpmg-adminregisterup-form select.entertainment_only').on('change', function() {
	if (jQuery(this).val() == '1' || jQuery(this).val() == '')
	{
		// Hide Diet Information & Clear
		jQuery('form#kpmg-adminregisterup-form .diet-info').removeClass('show');
		jQuery('form#kpmg-adminregisterup-form .diet-info').addClass('hide');
		jQuery('form#kpmg-adminregisterup-form .diet-info').removeAttr('selected');
		// Hide Guest Diet Information & Clear
		jQuery('form#kpmg-adminregisterup-form .guest-diet-info').removeClass('show');
		jQuery('form#kpmg-adminregisterup-form .guest-diet-info').addClass('hide');
		jQuery('form#kpmg-adminregisterup-form .guest-diet-info-select').removeAttr('selected');
	}
	else
	{
		// Assume Dinner And Entertainment Show Diet Information
		jQuery('form#kpmg-adminregisterup-form .diet-info').removeClass('hide');
		jQuery('form#kpmg-adminregisterup-form .diet-info').addClass('show');
		// Show Guest Dietary Information If Applicable
		if ( jQuery('form#kpmg-adminregisterup-form select.has_guest').val() == 'yes' )
		{
			jQuery('form#kpmg-adminregisterup-form .guest-diet-info').removeClass('hide');
			jQuery('form#kpmg-adminregisterup-form .guest-diet-info').addClass('show');
		}
	}
});

// Register Entertainment Only
jQuery('form#kpmg-adminregisterup-form select.has_guest').on('change', function() {
	if (jQuery(this).val() == 'Yes')
	{
		// Show Guest Information
		jQuery('form#kpmg-adminregisterup-form .guest-info').removeClass('hide');
		jQuery('form#kpmg-adminregisterup-form .guest-info').addClass('show');
		
		// Show Guest Dietary Information If Applicable
		if ( jQuery('form#kpmg-adminregisterup-form select.entertainment_only').val() != '1' )
		{
			jQuery('form#kpmg-adminregisterup-form .guest-diet-info').removeClass('hide');
			jQuery('form#kpmg-adminregisterup-form .guest-diet-info').addClass('show');
		}
	}
	else
	{
		// Hide Guest Information
		jQuery('form#kpmg-adminregisterup-form .guest-info').removeClass('show');
		jQuery('form#kpmg-adminregisterup-form .guest-info').addClass('hide');
		
		// Hide Guest Diet Information & Clear
		jQuery('form#kpmg-adminregisterup-form .guest-diet-info').removeClass('show');
		jQuery('form#kpmg-adminregisterup-form .guest-diet-info').addClass('hide');
		jQuery('form#kpmg-adminregisterup-form .guest-diet-info-select').removeAttr('selected');
	}
});



// Automatic Group Email Get Ajax
jQuery('form#kpmg-admingroup-get-form input.email_address').keyup(function()
{
	var formErrorArea = jQuery("#kpmg-admingroup-ajax-error-area");
	var formResultsArea = jQuery("#kpmg-admingroup-ajax-results-area");
	var form = jQuery('form#kpmg-admingroup-get-form');
	var emailAddressInput = jQuery('input#kpmg_admingroup_email_address');
	//var emailValidate = /\S+@\S+/; // Only hav forward slashes
	var emailValidate = /\S{2,}/; // Only have forward slashes
	var emailAddressValue = jQuery(this).val();
	var theAction = 'kpmgEmployeeRegCheckAJAX';

	// Validate Email Before Ajax Check
	if ( emailValidate.test(emailAddressValue) )
	{
		// Ajax Submit
		jQuery.ajax(
		{
			url: ajaxregisteremployeecheck.ajaxurl,
			type: 'POST',
			data: {
				'action': theAction,
				'register_employee_check': '1',
				'email_address': emailAddressValue
			},
			success: function(msg){

				var objData = jQuery.parseJSON(msg);
				
				// Error 
				formErrorArea.html('');

				// Results 
				formResultsArea.html('');

				// Check if error
				if (objData.hasOwnProperty('error') )
				{
					formErrorArea.html(objData.error);
				}
				else
				{
					// Create Results Drop-down
					jQuery(objData).each(function(key, value) {
						formResultsArea.append('<div class="item">' +value.employee_email_address+ '</div>');
					});
					// Results Item Click
					jQuery('.item', formResultsArea).click(function() {
						var text = jQuery(this).html();
						// Critical Data Update
						emailAddressInput.val(text);
					});								
				}
			}
		});

	}
	
});

// Automatic Group Email Results Get Ajax
jQuery("form#kpmg-admingroup-get-form input.email_address").blur(function(){
	jQuery("#kpmg-admingroup-ajax-results-area").fadeOut(500);
})
.focus(function() {		
	jQuery("#kpmg-admingroup-ajax-results-area").show();
});

// Automatic Group Email Ajax
jQuery('input#kpmg-admingroup-input').keyup(function()
{

	var formErrorArea = jQuery("#kpmg-admingroup-ajax-error-area");
	var ajax_results_area = jQuery(this).attr("data-ajax");
	var seat_number = jQuery(this).attr("data-seatnum");
	var next_seat_number = parseInt(seat_number) + 1;
	var formResultsArea = jQuery("#"+ajax_results_area);
	var form = jQuery('form#kpmg-admingroup-form');
	//var emailAddressInput = jQuery('input#kpmg-reserve-a-group_email_address');
	var emailAddressInput = jQuery(this);
	//var emailValidate = /\S+@\S+/; // Only hav forward slashes
	var emailValidate = /\S{2,}/; // Only have forward slashes
	var emailAddressValue = jQuery(this).val();
	var theAction = 'kpmgEmployeeForGroupCheckAJAX';

	// Validate Email Before Ajax Check
	if ( emailValidate.test(emailAddressValue) )
	{
		// Ajax Submit
		jQuery.ajax(
		{
			url: ajaxregisteremployeecheck.ajaxurl,
			type: 'POST',
			data: {
				'action': theAction,
				'register_employee_check': '1',
				'email_address': emailAddressValue
			},
			success: function(msg){

				var objData = jQuery.parseJSON(msg);
				
				// Error 
				formErrorArea.html('');

				// Results 
				formResultsArea.html('');

				// Check if error
				if (objData.hasOwnProperty('error') )
				{
					formErrorArea.html(objData.error);
				}
				else
				{
					// Create Results Drop-down
					jQuery(objData).each(function(key, value) {
						var guestName = value.guest_first_name+' '+value.guest_last_name;
						var employeeName = value.employee_first_name+' '+value.employee_last_name;
						if ( value.has_guest == "yes")
						{
							formResultsArea.append('<div class="item item-with-guest item'+key+'" data-email="'+value.employee_email_address+'" data-employeename="'+employeeName+'"  data-guestname="'+guestName+'" data-has-guest="'+value.has_guest+'">' +employeeName+' '+value.employee_email_address+ '</div>');
						}
						else
						{
							formResultsArea.append('<div class="item item'+key+'"  data-email="'+value.employee_email_address+'" data-employeename="'+employeeName+'"  data-guestname="'+guestName+'" data-has-guest="'+value.has_guest+'">' +employeeName+' '+value.employee_email_address+ '</div>');
						}
						
					});
					// Results Item Click
					jQuery('.item', formResultsArea).click(function() {
						var has_guest = jQuery(this).attr("data-has-guest");
						var text = jQuery(this).html();
						var emp_name = jQuery(this).attr("data-employeename");
						var emp_guest_name = jQuery(this).attr("data-guestname");
						var email = jQuery(this).attr("data-email");
						
						// Critical Data Update
						emailAddressInput.removeClass("guest");
						emailAddressInput.attr("data-email", email);
						emailAddressInput.attr("data-employeename", emp_name);
						emailAddressInput.attr("data-guestname", emp_guest_name);
						emailAddressInput.attr("data-hasguest", has_guest);
						emailAddressInput.val(text);
						/*jQuery("#group_seat_"+seat_number+"_is_guest").val("0");
						if ( has_guest == "yes" )
						{
							// Make Sure Next Cell Exists
							if ( jQuery("#group_seat_"+next_seat_number+"_display_name").length )
							{
								jQuery("#group_seat_"+next_seat_number+"_display_name").removeClass("guest");
								jQuery("#group_seat_"+next_seat_number+"_display_name").addClass("guest");
								jQuery("#group_seat_"+next_seat_number+"_display_name").val(text);
								jQuery("#group_seat_"+next_seat_number+"_is_guest").val("1");
							}
						}*/
					});								
				}
			}
		});

	}
	
});

// Automatic Group Email Results Ajax
jQuery("input#kpmg-admingroup-input").blur(function(){
	var ajax_results_area = jQuery(this).attr("data-ajax");
	jQuery("#"+ajax_results_area).fadeOut(500);
})
.focus(function() {		
	var ajax_results_area = jQuery(this).attr("data-ajax");
	jQuery("#"+ajax_results_area).show();
});


// Automatic Group Update Email Get Ajax
jQuery('form#kpmg-admingroupup-get-form input.email_address').keyup(function()
{
	var formErrorArea = jQuery("#kpmg-admingroupup-ajax-error-area");
	var formResultsArea = jQuery("#kpmg-admingroupup-ajax-results-area");
	var form = jQuery('form#kpmg-admingroupup-get-form');
	var emailAddressInput = jQuery('input#kpmg_admingroupup_email_address');
	//var emailValidate = /\S+@\S+/; // Only hav forward slashes
	var emailValidate = /\S{2,}/; // Only have forward slashes
	var emailAddressValue = jQuery(this).val();
	var theAction = 'kpmgEmployeeRegCheckAJAX';

	// Validate Email Before Ajax Check
	if ( emailValidate.test(emailAddressValue) )
	{
		// Ajax Submit
		jQuery.ajax(
		{
			url: ajaxregisteremployeecheck.ajaxurl,
			type: 'POST',
			data: {
				'action': theAction,
				'register_employee_check': '1',
				'email_address': emailAddressValue
			},
			success: function(msg){

				var objData = jQuery.parseJSON(msg);
				
				// Error 
				formErrorArea.html('');

				// Results 
				formResultsArea.html('');

				// Check if error
				if (objData.hasOwnProperty('error') )
				{
					formErrorArea.html(objData.error);
				}
				else
				{
					// Create Results Drop-down
					jQuery(objData).each(function(key, value) {
						formResultsArea.append('<div class="item">' +value.employee_email_address+ '</div>');
					});
					// Results Item Click
					jQuery('.item', formResultsArea).click(function() {
						var text = jQuery(this).html();
						// Critical Data Update
						emailAddressInput.val(text);
					});								
				}
			}
		});

	}
	
});

// Automatic Group Update Email Get Results Ajax
jQuery("form#kpmg-admingroupup-get-form input.email_address").blur(function(){
	jQuery("#kpmg-admingroupup-ajax-results-area").fadeOut(500);
})
.focus(function() {		
	jQuery("#kpmg-admingroupup-ajax-results-area").show();
});

// Automatic Group Update Email Ajax
jQuery('input#kpmg-admingroupup-input').keyup(function()
{

	var formErrorArea = jQuery("#kpmg-admingroupup-ajax-error-area");
	var ajax_results_area = jQuery(this).attr("data-ajax");
	var seat_number = jQuery(this).attr("data-seatnum");
	var next_seat_number = parseInt(seat_number) + 1;
	var formResultsArea = jQuery("#"+ajax_results_area);
	var form = jQuery('form#kpmg-admingroupup-form');
	//var emailAddressInput = jQuery('input#kpmg-reserve-a-group_email_address');
	var emailAddressInput = jQuery(this);
	//var emailValidate = /\S+@\S+/; // Only hav forward slashes
	var emailValidate = /\S{2,}/; // Only have forward slashes
	var emailAddressValue = jQuery(this).val();
	var theAction = 'kpmgEmployeeForGroupCheckAJAX';

	// Validate Email Before Ajax Check
	if ( emailValidate.test(emailAddressValue) )
	{
		// Ajax Submit
		jQuery.ajax(
		{
			url: ajaxregisteremployeecheck.ajaxurl,
			type: 'POST',
			data: {
				'action': theAction,
				'register_employee_check': '1',
				'email_address': emailAddressValue
			},
			success: function(msg){

				var objData = jQuery.parseJSON(msg);
				
				// Error 
				formErrorArea.html('');

				// Results 
				formResultsArea.html('');

				// Check if error
				if (objData.hasOwnProperty('error') )
				{
					formErrorArea.html(objData.error);
				}
				else
				{
					// Create Results Drop-down
					jQuery(objData).each(function(key, value) {
						var guestName = value.guest_first_name+' '+value.guest_last_name;
						var employeeName = value.employee_first_name+' '+value.employee_last_name;
						if ( value.has_guest == "yes")
						{
							formResultsArea.append('<div class="item item-with-guest item'+key+'" data-email="'+value.employee_email_address+'" data-employeename="'+employeeName+'"  data-guestname="'+guestName+'" data-has-guest="'+value.has_guest+'">' +employeeName+' '+value.employee_email_address+ '</div>');
						}
						else
						{
							formResultsArea.append('<div class="item item'+key+'"  data-email="'+value.employee_email_address+'" data-employeename="'+employeeName+'"  data-guestname="'+guestName+'" data-has-guest="'+value.has_guest+'">' +employeeName+' '+value.employee_email_address+ '</div>');
						}
						
					});
					// Results Item Click
					jQuery('.item', formResultsArea).click(function() {
						var has_guest = jQuery(this).attr("data-has-guest");
						var text = jQuery(this).html();
						var emp_name = jQuery(this).attr("data-employeename");
						var emp_guest_name = jQuery(this).attr("data-guestname");
						var email = jQuery(this).attr("data-email");
						
						// Critical Data Update
						emailAddressInput.removeClass("guest");
						emailAddressInput.attr("data-email", email);
						emailAddressInput.attr("data-employeename", emp_name);
						emailAddressInput.attr("data-guestname", emp_guest_name);
						emailAddressInput.attr("data-hasguest", has_guest);
						emailAddressInput.val(text);
						/*jQuery("#group_seat_"+seat_number+"_is_guest").val("0");
						if ( has_guest == "yes" )
						{
							// Make Sure Next Cell Exists
							if ( jQuery("#group_seat_"+next_seat_number+"_display_name").length )
							{
								jQuery("#group_seat_"+next_seat_number+"_display_name").removeClass("guest");
								jQuery("#group_seat_"+next_seat_number+"_display_name").addClass("guest");
								jQuery("#group_seat_"+next_seat_number+"_display_name").val(text);
								jQuery("#group_seat_"+next_seat_number+"_is_guest").val("1");
							}
						}*/
					});								
				}
			}
		});

	}
	
});

// Automatic Group Update Email Results Ajax
jQuery("input#kpmg-admingroupup-input").blur(function(){
	var ajax_results_area = jQuery(this).attr("data-ajax");
	jQuery("#"+ajax_results_area).fadeOut(500);
})
.focus(function() {		
	var ajax_results_area = jQuery(this).attr("data-ajax");
	jQuery("#"+ajax_results_area).show();
});

// Automatic Group Email Preview Ajax
jQuery('input#kpmg-admingrouprs-input').keyup(function()
{
	var formErrorArea = jQuery("#kpmg-admingrouprs-ajax-error-area");
	var ajax_results_area = jQuery(this).attr("data-ajax");
	var seat_number = jQuery(this).attr("data-seatnum");
	var next_seat_number = parseInt(seat_number) + 1;
	var formResultsArea = jQuery("#"+ajax_results_area);
	var form = jQuery('form#kpmg-admingrouprs-form');
	//var emailAddressInput = jQuery('input#kpmg-reserve-a-group_email_address');
	var emailAddressInput = jQuery(this);
	//var emailValidate = /\S+@\S+/; // Only hav forward slashes
	var emailValidate = /\S{2,}/; // Only have forward slashes
	var emailAddressValue = jQuery(this).val();
	var theAction = 'kpmgEmployeeForGroupCheckAJAX';

	// Validate Email Before Ajax Check
	if ( emailValidate.test(emailAddressValue) )
	{
		// Ajax Submit
		jQuery.ajax(
		{
			url: ajaxregisteremployeecheck.ajaxurl,
			type: 'POST',
			data: {
				'action': theAction,
				'register_employee_check': '1',
				'email_address': emailAddressValue
			},
			success: function(msg){

				var objData = jQuery.parseJSON(msg);
				
				// Error 
				formErrorArea.html('');

				// Results 
				formResultsArea.html('');

				// Check if error
				if (objData.hasOwnProperty('error') )
				{
					formErrorArea.html(objData.error);
				}
				else
				{
					// Create Results Drop-down
					jQuery(objData).each(function(key, value) {
						var guestName = value.guest_first_name+' '+value.guest_last_name;
						var employeeName = value.employee_first_name+' '+value.employee_last_name;
						if ( value.has_guest == "yes")
						{
							formResultsArea.append('<div class="item item-with-guest item'+key+'" data-email="'+value.employee_email_address+'" data-employeename="'+employeeName+'"  data-guestname="'+guestName+'" data-has-guest="'+value.has_guest+'">' +employeeName+' '+value.employee_email_address+ '</div>');
						}
						else
						{
							formResultsArea.append('<div class="item item'+key+'"  data-email="'+value.employee_email_address+'" data-employeename="'+employeeName+'"  data-guestname="'+guestName+'" data-has-guest="'+value.has_guest+'">' +employeeName+' '+value.employee_email_address+ '</div>');
						}
						
					});
					// Results Item Click
					jQuery('.item', formResultsArea).click(function() {
						var has_guest = jQuery(this).attr("data-has-guest");
						var text = jQuery(this).html();
						var emp_name = jQuery(this).attr("data-employeename");
						var emp_guest_name = jQuery(this).attr("data-guestname");
						var email = jQuery(this).attr("data-email");
						
						// Critical Data Update
						emailAddressInput.removeClass("guest");
						emailAddressInput.attr("data-email", email);
						emailAddressInput.attr("data-employeename", emp_name);
						emailAddressInput.attr("data-guestname", emp_guest_name);
						emailAddressInput.attr("data-hasguest", has_guest);
						emailAddressInput.val(text);
						/*jQuery("#group_seat_"+seat_number+"_is_guest").val("0");
						if ( has_guest == "yes" )
						{
							// Make Sure Next Cell Exists
							if ( jQuery("#group_seat_"+next_seat_number+"_display_name").length )
							{
								jQuery("#group_seat_"+next_seat_number+"_display_name").removeClass("guest");
								jQuery("#group_seat_"+next_seat_number+"_display_name").addClass("guest");
								jQuery("#group_seat_"+next_seat_number+"_display_name").val(text);
								jQuery("#group_seat_"+next_seat_number+"_is_guest").val("1");
							}
						}*/
					});								
				}
			}
		});

	}
	
});

// Automatic Group Email Results Preview Ajax
jQuery("input#kpmg-admingrouprs-input").blur(function(){
	var ajax_results_area = jQuery(this).attr("data-ajax");
	jQuery("#"+ajax_results_area).fadeOut(500);
})
.focus(function() {		
	var ajax_results_area = jQuery(this).attr("data-ajax");
	jQuery("#"+ajax_results_area).show();
});


kpmg_groupfields(10, 'admingroup', 'admingroup', 'input#kpmg-admingroup-input', '.adminreserveagroupparent_add_area', '.add_to_grp-admingroup_btn');
kpmg_groupfields(10, 'admingroupup', 'admingroupup', 'input#kpmg-admingroupup-input', '.adminreserveagroupparent_add_area', '.add_to_grp-admingroupup_btn');
kpmg_groupfields(10, 'admingrouprs', 'admingrouprs', 'input#kpmg-admingrouprs-input', '.adminreserveagroupparent_add_area', '.add_to_grp-admingrouprs_btn');
	

// Create Input Fields
function kpmg_groupfields(max, formid, variable, source, wrap, butt) {
	var max_fields = max; //maximum input boxes allowed
	var wrapper = jQuery(wrap); //Fields wrapper
	var add_button = jQuery(butt); //Add button class
	var remaining = jQuery('#remaining-group-seats');
	var sremaining = remaining.attr('data-seatsremaining');
	var snext = remaining.attr('data-seatlast');
	var add_input = jQuery(source);
	var host_email = jQuery('#reserveagroupparent1 input.email_address').val();
	var the_formid = 'kpmg-'+formid+'-form';
	var the_groupid = 'kpmg-'+formid+'-group-id';
	var the_errorarea = 'kpmg-'+formid+'-ajax-error-area';
	var formErrorArea = jQuery("#"+the_errorarea);
	var theAction = 'kpmgEmployeeRegForGroupCheckAJAX';	
	
	var x = 1; // Initial Text Box Count
	x = (max_fields - sremaining);
	jQuery(add_button).click(function (e) { //on add input button click
		e.preventDefault();
		if (x < max_fields) 
		{ 
			//max input box allowed
			// Clear Ajax Error
			document.getElementById(the_errorarea).innerHTML = "";
			// Get Current Group ID
			var group_id = jQuery("#"+the_groupid).val();
			// Recheck Email Address
			var emailPre = add_input.val();
			var emailRegExtract = /\S+[a-z0-9]@[a-z0-9\.]+/img
			var emailExtract = emailPre.match(emailRegExtract);
			var emailExtracted = '';
			try {
				if(typeof emailExtract[0] == 'undefined') {
				  // does not exist
				}
				else {
					// does exist
					emailExtracted = emailExtract[0];
				}
			} 
			catch (error){ /* ignore */ }
			var emailAddressValue = emailExtracted.replace(/^\s+|\s+$/g, '');
			var emailAdd = false;
			// Regular Expression For Email
			var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
			if (emailAddressValue != '')
			{
				if ( emailAddressValue.match(emailReg) )
				{
					// Can Add
					emailAdd = true;
					// Now See If Already Added To Group
					var frm = document.getElementById(the_formid);
					for (var f=0; f < frm.elements.length; f++)
					{
						if (frm.elements[f].value == emailAddressValue)
						{
							emailAdd = false;
							document.getElementById(the_errorarea).innerHTML = "The KPMG employee you are trying to add to to your group is already in your group";
							break;
						}
					}
					if (emailAdd)
					{
						// Can Not Add
						emailAdd = false;
						// Get The Information Again
						// Ajax Submit
						jQuery.ajax(
						{
							url: ajaxregisteremployeecheck.ajaxurl,
							type: 'POST',
							data: {
								'action': theAction,
								'register_employee_check': '1',
								'email_address': emailAddressValue
							},
							success: function(msg){

								var objData = jQuery.parseJSON(msg);

								// Error 
								formErrorArea.html('');

								// Results 
								// Check if error
								if (objData.hasOwnProperty('error') )
								{
									emailAdd = false;
									formErrorArea.html(objData.error);
								}
								else
								{
									// Create Results Drop-down
									jQuery(objData).each(function(key, value) {
										var guestName = value.guest_first_name+' '+value.guest_last_name;
										var employeeName = value.employee_first_name+' '+value.employee_last_name;
										var emailAddress = value.employee_email_address;
										var hasGuest = value.has_guest.toLowerCase();
										var employeeStatus = value.employee_status; 
										var groupID = value.group_id;
										
										if (employeeStatus == 'waitinglist')
										{
											// Error
											emailAdd = false;
											formErrorArea.html('The KPMG employee you are trying to add to your group cannot be added because they are on the waitling list');
										}
										else if (groupID > 0 && groupID != group_id)
										{
											// Error
											emailAdd = false;
											formErrorArea.html('<p class="small">The KPMG employee you are trying to add to your group cannot be added because they have already been included in another group reservation. Please contact them directly for more details.  <br /><br />Thank you, <br />KPMG WinterFest Crew</p>');
										}
										else if (hasGuest == "yes" && (sremaining == 1) )
										{
											// Error
											emailAdd = false;
											formErrorArea.html('<p class="small">By adding this person (and their guest), you are exceeding the maximum allowable occupancy per table.  There is a maximum number of 10 people per group.  <br /><br />Thank you, <br />KPMG WinterFest Crew</p>');
										}
										else
										{
											emailAdd = true;
											// Update Attributes
											add_input.removeClass("guest");
											add_input.attr("data-email", emailAddress);
											add_input.attr("data-employeename", employeeName);
											add_input.attr("data-guestname", guestName);
											add_input.attr("data-hasguest", hasGuest);
											
											// Check If Input Has Proper Data
											if ( add_input.attr('data-employeename') && emailAdd )
											{
												var email = add_input.attr("data-email");
												var emp_name = add_input.attr("data-employeename");
												var emp_guest_name = add_input.attr("data-guestname");
												var has_guest = add_input.attr("data-hasguest").toLowerCase();
												var new_next = parseInt(snext);
												new_next++; // Increment


												x++; //text box increment
												sremaining--; // Decrement
												var sstring = '<div class="is_employee '+removeTarget+' draggable_group_item" draggable="true">';
												sstring += '<span class="display-name">'+emp_name+'</span>'; 
												sstring += '<span class="display-email">'+email+'</span>';
												// Check If Guest Spot Available
												if (has_guest == "yes" && (sremaining > 1) )
												{
													new_next++; // Increment
													sstring += '<span class="guest-name">'+emp_guest_name+'</span>'; 
													sstring += '<span class="guest-email">Guest</span>';

													x++; //text box increment
													sremaining--; // Decrement
												}
												var removeTarget = 'added_field'+new_next;
												sstring += '<input type="hidden" name="'+variable+'['+new_next+'][host_email_address]" value="'+host_email+'" />';
												sstring += '<input type="hidden" name="'+variable+'['+new_next+'][is_guest]" value="0" />';
												sstring += '<input type="hidden" name="'+variable+'['+new_next+'][email_address]" value="'+email+'" readonly />';
												sstring += '<a href="#" data-hasguest="'+has_guest+'" data-remove="'+removeTarget+'" class="remove_field" title="Remove">&times;</a>';
												sstring += '</div>';
												var cstring = 'jQuery(wrapper).append(\''+sstring+'\');' //add input box

												eval(cstring);

												// Draggable
												jQuery('.draggable_group_item').each(function(index){
													jQuery(this).on("dragstart", handleDragStart);
													jQuery(this).on("drop", handleDrop);
													jQuery(this).on("dragover", handleDragOver);
												});

												// Update Next Count
												remaining.attr("data-seatsremaining", sremaining);
												remaining.attr("data-seatlast", new_next);
												remaining.text(sremaining);
											}											
										}

									});								
								}
							}
						});
					}
				}
				else
				{
					document.getElementById(the_errorarea).innerHTML = "The email address is invalid";
				}
			}
			

		}
	});

	jQuery("#"+the_formid).on("click", ".remove_field", function (e) { //user click on remove text
		e.preventDefault();
		//var remaining = jQuery('#remaining-group-seats');
		var class_target = jQuery(this).attr("data-remove");
		var hasguest_target = jQuery(this).attr("data-hasguest").toLowerCase();
		jQuery(this).parent('div').remove(); // Remove
		x--;
		// Update Next Count
		sremaining++; // Increment
		
		//remaining.attr("data-seatlast", "");
		// Check If Guest Exists
		if ( hasguest_target == "yes" )
		{
			x--; // Decrement
			sremaining++; // Increment
		}

		remaining.attr("data-seatsremaining", sremaining);
		remaining.text(sremaining);
	});
}

// Remove Added Group Fields
jQuery("#kpmg-reserve-a-group-form").on("click", ".remove_group_fields", function (e) { 
	////user click on remove text
	e.preventDefault();
	var remaining = jQuery('#remaining-group-seats');
	var sremaining = remaining.attr('data-seatsremaining');
	var snext = remaining.attr('data-seatlast');
	var class_target = jQuery(this).attr("data-remove");
	jQuery(this).parent('div').remove(); // Remove
	//x--;
	// Update Next Count
	sremaining++; // Increment

	//remaining.attr("data-seatlast", "");
	// Check If Guest Exists
	if ( jQuery('div.'+class_target).length )
	{
		jQuery('div.'+class_target).remove(); // Remove
		//x--; // Decrement
		sremaining++; // Increment
	}
	remaining.attr("data-seatsremaining", sremaining);
	remaining.text(sremaining);
});

// Automatic Group Email Ajax
jQuery('input#kpmg-create-group-input').keyup(function()
{
	var formErrorArea = jQuery("#kpmg-reserve-a-group-ajax-error-area");
	var ajax_results_area = jQuery(this).attr("data-ajax");
	var seat_number = jQuery(this).attr("data-seatnum");
	var next_seat_number = parseInt(seat_number) + 1;
	var formResultsArea = jQuery("#"+ajax_results_area);
	var form = jQuery('form#kpmg-reserve-a-group-form');
	//var emailAddressInput = jQuery('input#kpmg-reserve-a-group_email_address');
	var emailAddressInput = jQuery(this);
	//var emailValidate = /\S+@\S+/; // Only hav forward slashes
	var emailValidate = /\S{2,}/; // Only have forward slashes
	var emailAddressValue = jQuery(this).val();
	var theAction = 'kpmgEmployeeForGroupCheckAJAX';

	// Validate Email Before Ajax Check
	if ( emailValidate.test(emailAddressValue) )
	{
		// Ajax Submit
		jQuery.ajax(
		{
			url: ajaxregisteremployeecheck.ajaxurl,
			type: 'POST',
			data: {
				'action': theAction,
				'register_employee_check': '1',
				'email_address': emailAddressValue
			},
			success: function(msg){

				var objData = jQuery.parseJSON(msg);
				
				// Error 
				formErrorArea.html('');

				// Results 
				formResultsArea.html('');

				// Check if error
				if (objData.hasOwnProperty('error') )
				{
					formErrorArea.html(objData.error);
				}
				else
				{
					// Create Results Drop-down
					jQuery(objData).each(function(key, value) {
						var guestName = value.guest_first_name+' '+value.guest_last_name;
						var employeeName = value.employee_first_name+' '+value.employee_last_name;
						if ( value.has_guest == "yes")
						{
							formResultsArea.append('<div class="item item-with-guest item'+key+'" data-email="'+value.employee_email_address+'" data-employeename="'+employeeName+'"  data-guestname="'+guestName+'" data-has-guest="'+value.has_guest+'">' +employeeName+' '+value.employee_email_address+ '</div>');
						}
						else
						{
							formResultsArea.append('<div class="item item'+key+'"  data-email="'+value.employee_email_address+'" data-employeename="'+employeeName+'"  data-guestname="'+guestName+'" data-has-guest="'+value.has_guest+'">' +employeeName+' '+value.employee_email_address+ '</div>');
						}
						
					});
					// Results Item Click
					jQuery('.item', formResultsArea).click(function() {
						var has_guest = jQuery(this).attr("data-has-guest");
						var text = jQuery(this).html();
						var emp_name = jQuery(this).attr("data-employeename");
						var emp_guest_name = jQuery(this).attr("data-guestname");
						var email = jQuery(this).attr("data-email");
						
						// Critical Data Update
						emailAddressInput.removeClass("guest");
						emailAddressInput.attr("data-email", email);
						emailAddressInput.attr("data-employeename", emp_name);
						emailAddressInput.attr("data-guestname", emp_guest_name);
						emailAddressInput.attr("data-hasguest", has_guest);
						emailAddressInput.val(text);
						/*jQuery("#group_seat_"+seat_number+"_is_guest").val("0");
						if ( has_guest == "yes" )
						{
							// Make Sure Next Cell Exists
							if ( jQuery("#group_seat_"+next_seat_number+"_display_name").length )
							{
								jQuery("#group_seat_"+next_seat_number+"_display_name").removeClass("guest");
								jQuery("#group_seat_"+next_seat_number+"_display_name").addClass("guest");
								jQuery("#group_seat_"+next_seat_number+"_display_name").val(text);
								jQuery("#group_seat_"+next_seat_number+"_is_guest").val("1");
							}
						}*/
					});								
				}
			}
		});

	}
	
});

// Automatic Group Email Results Ajax
jQuery("input#kpmg-create-group-input").blur(function(){
	var ajax_results_area = jQuery(this).attr("data-ajax");
	jQuery("#"+ajax_results_area).fadeOut(500);
})
.focus(function() {		
	var ajax_results_area = jQuery(this).attr("data-ajax");
	jQuery("#"+ajax_results_area).show();
});

/*// Automatic Group Email Ajax
jQuery('form#kpmg-reserve-a-group-form input.email_address').keyup(function()
{
	var formErrorArea = jQuery("#kpmg-reserve-a-group-ajax-error-area");
	var ajax_results_area = jQuery(this).attr("data-ajax");
	var seat_number = jQuery(this).attr("data-seatnum");
	var next_seat_number = parseInt(seat_number) + 1;
	var formResultsArea = jQuery("#"+ajax_results_area);
	var form = jQuery('form#kpmg-reserve-a-group-form');
	//var emailAddressInput = jQuery('input#kpmg-reserve-a-group_email_address');
	var emailAddressInput = jQuery(this);
	//var emailValidate = /\S+@\S+/; // Only hav forward slashes
	var emailValidate = /\S{2,}/; // Only have forward slashes
	var emailAddressValue = jQuery(this).val();
	var theAction = 'kpmgEmployeeForGroupCheckAJAX';

	// Validate Email Before Ajax Check
	if ( emailValidate.test(emailAddressValue) )
	{
		// Ajax Submit
		jQuery.ajax(
		{
			url: ajaxregisteremployeecheck.ajaxurl,
			type: 'POST',
			data: {
				'action': theAction,
				'register_employee_check': '1',
				'email_address': emailAddressValue
			},
			success: function(msg){

				var objData = jQuery.parseJSON(msg);
				
				// Error 
				formErrorArea.html('');

				// Results 
				formResultsArea.html('');

				// Check if error
				if (objData.hasOwnProperty('error') )
				{
					formErrorArea.html(objData.error);
				}
				else
				{
					// Create Results Drop-down
					jQuery(objData).each(function(key, value) {
						if ( value.has_guest == "yes")
						{
							formResultsArea.append('<div class="item item-with-guest" data-has-guest="'+value.has_guest+'">' +value.employee_email_address+ '</div>');
						}
						else
						{
							formResultsArea.append('<div class="item" data-has-guest="'+value.has_guest+'">' +value.employee_email_address+ '</div>');
						}
						
					});
					// Results Item Click
					jQuery('.item', formResultsArea).click(function() {
						var has_guest = jQuery(this).attr("data-has-guest");
						var text = jQuery(this).html();
						// Critical Data Update
						emailAddressInput.removeClass("guest");
						emailAddressInput.val(text);
						jQuery("#group_seat_"+seat_number+"_is_guest").val("0");
						if ( has_guest == "yes" )
						{
							// Make Sure Next Cell Exists
							if ( jQuery("#group_seat_"+next_seat_number+"_display_name").length )
							{
								jQuery("#group_seat_"+next_seat_number+"_display_name").removeClass("guest");
								jQuery("#group_seat_"+next_seat_number+"_display_name").addClass("guest");
								jQuery("#group_seat_"+next_seat_number+"_display_name").val(text);
								jQuery("#group_seat_"+next_seat_number+"_is_guest").val("1");
							}
						}
					});								
				}
			}
		});

	}
	
});

// Automatic Group Email Results Ajax
jQuery("form#kpmg-reserve-a-group-form input.email_address").blur(function(){
	var ajax_results_area = jQuery(this).attr("data-ajax");
	jQuery("#"+ajax_results_area).fadeOut(500);
})
.focus(function() {		
	var ajax_results_area = jQuery(this).attr("data-ajax");
	jQuery("#"+ajax_results_area).show();
});*/