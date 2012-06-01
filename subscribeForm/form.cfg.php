<?php exit(0); ?> { 
"settings":
{
	"data_settings" : 
	{
		"save_database" : 
		{
			"database" : "",
			"is_present" : false,
			"password" : "",
			"port" : 3306,
			"server" : "",
			"tablename" : "",
			"username" : ""
		},
		"save_file" : 
		{
			"filename" : "form-results.csv",
			"is_present" : false
		},
		"save_sqlite" : 
		{
			"database" : "subscribeForm.dat",
			"is_present" : false,
			"tablename" : "subscribeForm"
		}
	},
	"email_settings" : 
	{
		"auto_response_message" : 
		{
			"custom" : 
			{
				"body" : "<!DOCTYPE html>\n<html dir=\"ltr\" lang=\"en\">\n<head><title>You got mail!</title></head>\n<body style=\"background-color: #f9f9f9; padding-left: 110px; padding-top: 70px; padding-right: 20px; max-width: 700px; font-family: Helvetica, Arial;\">\n<style type=\"text/css\">\nbody {background-color: #f9f9f9;padding-left: 110px;padding-top: 70px; padding-right: 20px;max-width:700px;font-family: Helvetica, Arial;}\np{font-size: 12px; color: #666666;}\nh2{font-size: 28px !important;color: #666666 ! important;margin: 0px; border-bottom: 1px dotted #00A2FF; padding-bottom:3px;}\ntd {font-size: 12px !important; line-height: 30px;color: #666666 !important; margin: 0px;border-bottom: 1px solid #e9e9e9;}\ntd:first-child {font-size: 13px !important; font-weight:bold; color: #333 !important; vertical-align:text-top; min-width:100px; padding-right:5px;}\na:link {color:#666666; text-decoration:underline;} a:visited {color:#666666; text-decoration:none;} a:hover {color:#00A2FF;}\nb{font-weight: bold;}\n</style>\n<h2 style=\"font-size: 28px !important;color: #666666 ! important;margin: 0px; border-bottom: 1px dotted #00A2FF; padding-bottom:3px;\">Thanks for taking the time to subscribe to News From Side Six. We honor privacy and will only use your email in order to send you our newsletter. </br>Here's a copy of what you submitted:</h2>\n<div>\n[form_results]\n<p>Have Fun!</p>\n<p>Dan Avery</p>\n<p>Side Six, <a href=\"http://sidesix.org\">It's just a click away</a></p>\n</div>\n</body>\n</html>\n",
				"is_present" : true,
				"subject" : "Thank you for your subscription"
			},
			"from" : "info@sidesix.org",
			"is_present" : true,
			"to" : "[email]"
		},
		"notification_message" : 
		{
			"bcc" : "",
			"cc" : "",
			"custom" : 
			{
				"body" : "<!DOCTYPE html>\n<html dir=\"ltr\" lang=\"en\">\n<head><title>You got mail!</title></head>\n<body style=\"background-color: #f9f9f9; padding-left: 110px; padding-top: 70px; padding-right: 20px; max-width: 700px; font-family: Helvetica, Arial;\">\n<style type=\"text/css\">\nbody {background-color: #f9f9f9;padding-left: 110px;padding-top: 70px; padding-right: 20px;max-width:700px;font-family: Helvetica, Arial;}\np{font-size: 12px; color: #666666;}\nh1{font-size: 60px !important;color: #cccccc !important;margin:0px;}\nh2{font-size: 28px !important;color: #666666 ! important;margin: 0px; border-bottom: 1px dotted #00A2FF; padding-bottom:3px;}\ntd {font-size: 12px !important; line-height: 30px;color: #666666 !important; margin: 0px;border-bottom: 1px solid #e9e9e9;}\ntd:first-child {font-size: 13px !important; font-weight:bold; color: #333 !important; vertical-align:text-top; min-width:100px; padding-right:5px;}\na:link {color:#666666; text-decoration:underline;} a:visited {color:#666666; text-decoration:none;} a:hover {color:#00A2FF;}\nb{font-weight: bold;}\n</style>\n<h1 style=\"font-size: 60px !important; color: #cccccc !important; margin: 0px;\">Hey there,</h1>\n<p style=\"font-size: 12px; color: #666666;\">\nSomeone filled out your form, and here's what they said:\n</p>\n<div>\n[form_results]\n<p style=\"font-size: 12px; color: #666666;\">\n<p>Yours,</p>\n<p>Groucho</p>\n</p>\n</div>\n</body>\n</html>\n",
				"is_present" : true,
				"subject" : "Someone Joined The Newsletter"
			},
			"from" : "",
			"is_present" : true,
			"replyto" : "",
			"to" : "danavery@sidesix.org"
		}
	},
	"mailchimp" : 
	{
		"apiKey" : "0ffca439e921707061a95d5e9821465a-us1",
		"lists" : 
		[
			
			{
				"action" : 
				{
					"subscribe" : 
					{
						"condition" : "always"
					},
					"unsubscribe" : 
					{
						"condition" : "never"
					}
				},
				"is_present" : true,
				"listid" : "5542dbf8c2",
				"merge_tags" : 
				[
					
					{
						"fb_name" : "email",
						"field_type" : "email",
						"req" : true,
						"size" : "40",
						"tag" : "EMAIL"
					}
				],
				"name" : "Side Six Newsletter",
				"subscribe" : 
				{
					"double_optin" : true,
					"email_address_field" : "email",
					"email_type_field" : "",
					"replace_interests" : true,
					"send_welcome" : false,
					"update_existing" : false
				},
				"unsubscribe" : 
				{
					"delete_member" : false,
					"email_address_field" : "email",
					"send_goodbye" : true,
					"send_notify" : true
				}
			}
		]
	},
	"redirect_settings" : 
	{
		"confirmpage" : "<!DOCTYPE html>\n<html dir=\"ltr\" lang=\"en\">\n<head>\n<title>Success!</title>\n<meta charset=\"utf-8\">\n<style type=\"text/css\">\nbody {background: #f9f9f9;padding-left: 110px;padding-top: 70px; padding-right: 20px;max-width:700px;font-family: Helvetica, Arial;}\np{font-size: 16px;font-weight: bold;color: #666;}\nh1{font-size: 60px !important;color: #ccc !important;margin:0px;}\nh2{font-size: 28px !important;color: #666 !important;margin: 0px; border-bottom: 1px dotted #00A2FF; padding-bottom:3px;}\nh3{font-size: 16px; color: #a1a1a1; border-top: 1px dotted #00A2FF; padding-top:17px; font-weight: bold;}\nh3 span{color: #ccc;}\ntd {font-size: 12px !important; line-height: 30px;  color: #666 !important; margin: 0px;border-bottom: 1px solid #e9e9e9;}\ntd:first-child {font-size: 13px !important; font-weight:bold; color: #333 !important; vertical-align:text-top; min-width:100px; padding-right:5px;}\na:link {color:#666; text-decoration:none;} a:visited {color:#666; text-decoration:none;} a:hover {color:#00A2FF;}\n</style>\n</head>\n<body>\n<h1>Thanks! </h1>\n<h2>The form is on its way.</h2>\n<p>Here&rsquo;s what was sent:</p>\n<div>[form_results]</div>\n<!-- link back to your Home Page -->\n<h3>Let&rsquo;s go <span> <a href=\"http://www.coffeecup.com\">Back Home</a></span></h3>\n</body>\n</html>\n",
		"gotopage" : "",
		"inline" : "<center>\n<style type=\"text/css\">\n#docContainer table {margin-top: 30px; margin-bottom: 30px;}\n#docContainer td {font-size: 12px !important; line-height: 30px;color: #666666 !important; margin: 0px;border-bottom: 1px solid #e9e9e9;}\n#docContainer td:first-child {font-size: 13px !important; font-weight:bold; color: #333 !important; vertical-align:text-top; min-width:100px; padding-right:5px;}\n</style>\n[form_results]\n<h2>Thank you!</h2><br/>\n<p>Your form was successfully submitted. We received the information shown above.</p>\n</center>",
		"type" : "inline"
	},
	"timezone" : "UTC",
	"uid" : "e5f808072e436484bb87046d9380e778",
	"validation_report" : "in_line"
},
"rules":{"email":{"email":true,"fieldtype":"email","required":true,"messages":"please enter a valid email address","maxlength":"40"}},
"application_version":"Web Form Builder (OSX), build 3880"
}