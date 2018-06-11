<?php 

return [

'wsdl_file' => env('I2C_WSDL_FILE'),
'log_path' => env('I2C_LOG_PATH'),

'acquirer' => [
	'Acquirer' => [
		'EnUserID' => env('I2C_EN_USER_ID'),
		'EnPwd' => env('I2C_EN_PWD'),
	],
]

];