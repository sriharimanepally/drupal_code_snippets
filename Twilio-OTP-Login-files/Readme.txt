Make sure twillio SDK installed, refer above compressor command to install.
Make sure you are updating $account_sid, $auth_token and $from_no in send() function in file /src/Services/Otp.php
Clear all the caches after installation.
Go to  Configurations->account settings -> Manage form display, move  mobile number field to enabled section.
/admin/config/people/accounts/form-display

Update below details in Path - /dn_login/src/Services/Otp.php

public function send($otp, $to) {
	$account_sid = 'xxxxxxxxxxxxxxxxxxxxxxxxxx';
	$auth_token = 'xxxxxxxxxxxxxxxxxxxxxxxxxx';
	$from_no = '+13xxxxxxx';
