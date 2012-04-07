<?php

$email_to = 'danavery@sidesix.org';
$subject = 'Contact Form Submission From Side Six';
//receive variables from contact form
$name = $_POST['name'];
$email = $_POST['email'];
$comments = $_POST['comments'];

$message = "name:       " . $name . "\n";
$message .= "email:       " . $email . "\n\n";
$message .= "comments:       " . $comments . "\n";
if (mail($email_to , $subject , $message)){
	header("Refresh: 0;url=http://sidesix.org/thankyou.html");
} else {
	echo "email failed to send.";
}
?>
