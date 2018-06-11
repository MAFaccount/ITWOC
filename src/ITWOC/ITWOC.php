<?php

namespace ITWOC;

use Illuminate\Support\Facades\Config;
use ITWOC\Traits\ITWOCValidatorTrait;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use SoapClient;

class ITWOC {
	// use ITWOCValidatorTrait;

	protected $_client;

	protected $_wsdlUrl;

	protected $_acquirer;

	protected $_logPath;

	public function __construct(){
		$this->init();
		$this->_logger = new Logger('ITWOC');

		// the default date format is "Y-m-d H:i:s"
		$dateFormat = "Y n j, g:i a";
		// the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
		$output = "%datetime% > %level_name% > %message% %context% %extra%\n";

		$formatter =  new LineFormatter($output,$dateFormat);
		$stream = new StreamHandler($this->_logPath, Logger::DEBUG);
		$stream->setFormatter($formatter);
		$this->_logger->pushHandler($stream);
	}

	protected function init(){
		//load data from configuration file
		$this->_wsdlUrl = config('itwoc.wsdl_file');
		$this->_acquirer = config('itwoc.acquirer');
		$this->_logPath = config('itwoc.log_path');

		//register the soapclient
		$this->_client = new SoapClient( $this->_wsdlUrl ,array("trace" => 1, "exception" => 0));
	}

	public function addCard(array $data = []) : string{
        $this->_acquirer['Acquirer']['ARN'] = substr(hash('sha512' , microtime() . uniqid() . str_random(100) ) , 0 , 20);
        $data = $this->_acquirer + $data;

		try{
            $this->logInfo(json_encode($data));
            $res = $this->_client->__Call("AddCard" , [$data]);


            $response = [
                'response' => [
                    'response_code' => $res->ResponseCode,
                    'Response_desc' => $res->ResponseDesc,
                    'response_id' => $res->ReferenceID
                ]
            ];

            $this->logInfo(json_encode($response));

            return $this->_acquirer['Acquirer']['ARN'];

        }catch(\SoapException $e){
            $response = [
                'response' => [
                    'response_code' => $e->getCode(),
                    'Response_desc' => $e->getMessage(),
                ]
            ];

            $this->logError(json_encode($response));

            return 'error';
        }
	}



	//logging functions

	/**
	 * @param  string 			$info 			Info message to log in the log files
	 */
	public function logInfo($info = ''){
		$this->_logger->info($info);
	}

	/**
	 * @param  string 			$notice 		Notice message to log in the log files
	 */
	public function logNotice($notice = ''){
		$this->_logger->notice($notice);
	}

	/**
	 * @param  string 			$error 			Error message to log in the log files
	 */
	public function logError($error = ''){
		$this->_logger->error($error);
	}

	/**
	 * @param  string 			$warning 			Warning message to log in log files
	 */
	public function logWarning($warning = ''){
		$this->_logger->warning($warning);
	}
}
