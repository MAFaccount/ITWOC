<?php 

return [

'najm_wsdl_file' => env('NAJM_WSDL_FILE'),
'najm_log_path' => env('NAJM_LOG_PATH'),
'wsdl_file' => env('I2C_WSDL_FILE'),
'log_path' => env('I2C_LOG_PATH'),


'acquirer' => [
	'Acquirer' => [
		'EnUserID' => env('I2C_EN_USER_ID'),
		'EnPwd' => env('I2C_EN_PWD'),
	],
],

'najm_payment_info' => [
	'najm_version' => env('NAJM_VERSION'),
	'najm_msg_type' => env('NAJM_MSG_TYPE'),
	'najm_msg_function' => env('NAJM_MSG_FUNCTION'),
	'najm_src_application' => env('NAJM_SRC_APPLICATION'),
	'najm_target_application' => env('NAJM_TARGET_APPLICATION'),
	'najm_bank_id' => env('NAJM_BANK_ID'),
	'najm_channel_name' => env('NAJM_CHANNEL_NAME'),
	'najm_merchant_id' => env('NAJM_MERCHANT_ID'),
	'najm_terminal_id' => env('NAJM_TERMINAL_ID')

],

'allowed_starting_numbers' => env('I2C_ALLOWED_STARTING_NUMBERS')

];