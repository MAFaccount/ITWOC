<?php

namespace ITWOC;

use Illuminate\Support\Facades\Config;
use ITWOC\Traits\ITWOCValidatorTrait;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use SoapClient;

class ITWOC {
	use ITWOCValidatorTrait;

    /**
     * $_client will store instance of the SoapClient so we can use the soap services
     * @var SoapClient
     */
	protected $_client;

    /**
     * $_wsdlUrl the url of wsdl file that defines the soap services so we can use them it
     * will be loaded from configuration file
     * @var String
     */
	protected $_wsdlUrl;

    /**
     * $_acquirer will store array of the Acqirer data
     * @var Array
     */
	protected $_acquirer;

    /**
     * $_logPath will store the log file path for this package and will be loaded from the configuration files
     * @var String
     */
	protected $_logPath;


    /**
     * __construct will initialize the package important data and setup some helpers like Logger & SoapClient
     */
	public function __construct(){
		$this->init();
		$this->_logger = new Logger('ITWOC');

		// the default date format is "Y-m-d H:i:s"
		$dateFormat = "Y n j, g:i a";
		// the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
		$output = "%datetime% > %level_name% > %message% %context% %extra%\n";

		$formatter =  new LineFormatter($output,$dateFormat , true);
		$stream = new StreamHandler($this->_logPath, Logger::DEBUG);
		$stream->setFormatter($formatter);
		$this->_logger->pushHandler($stream);
	}

    /**
     * init will load configurations and set up the soap client
     */
	protected function init(){
		//load data from configuration file
		$this->_wsdlUrl = config('itwoc.wsdl_file');
		$this->_acquirer = config('itwoc.acquirer');
		$this->_logPath = config('itwoc.log_path');

		//register the soapclient
		$this->_client = new SoapClient( $this->_wsdlUrl ,array("trace" => 1, "exception" => 0));
	}

    /**
     * getAcquirer will return the Acquirer along with a unique generated ARN
     * @return Array [array of acquirer details]
     */
    protected function getAcquirer(){
        $acquirer = $this->_acquirer;
        $acquirer['Acquirer']['ARN'] = substr(hash('sha512' , microtime() . uniqid() . str_random(100) ) , 0 , 20);
        return $acquirer;
    }

	public function addCard(array $data = []) {
        if($this->validateAddCardAction($data)){
            $acquirer = $this->getAcquirer();
            $data = $acquirer + $data;

            try{
                $this->logInfo(json_encode($data));
                $res = $this->_client->__Call("AddCard" , [$data]);

                $response = [
                    'response' => [
                        'response_code' => $res->ResponseCode,
                        'Response_desc' => $res->ResponseDesc,
                        'reference_id' => $res->ReferenceID
                    ]
                ];


                $this->logInfo(json_encode($response));

                return $acquirer['Acquirer']['ARN'];

            }catch(\SoapException $e){
                $response = [
                    'response' => [
                        'response_code' => $e->getCode(),
                        'Response_desc' => $e->getMessage(),
                    ]
                ];

                $this->logError(json_encode($response));
            }
        }
	}


    public function loadCard(array $data = []){
        if($this->validateLoadCardAction($data)){
            $acquirer = $this->getAcquirer();
            $data = $acquirer + $data;


            try{
                $this->logInfo(json_encode($data));
                $res = $this->_client->__Call("CreditFunds" , [$data]);

                $response = [
                    'response' => [
                        'response_code' => $res->ResponseCode,
                        'Response_desc' => $res->ResponseDesc,
                        // 'reference_id' => $res->ReferenceID,
                    ]
                ];

                $this->logInfo(json_encode($response));

                return $acquirer['Acquirer']['ARN'];

            }catch(\SoapException $e){
                $response = [
                    'response' => [
                        'response_code' => $e->getCode(),
                        'Response_desc' => $e->getMessage(),
                    ]
                ];

                $this->logError(json_encode($response));
            }
        }
    }



    public function checkBalance(array $data = []){
        if($this->validateCheckBalanceAction($data)){
            $acquirer = $this->getAcquirer();
            $data = $acquirer + $data;

            try{
                $this->logInfo(json_encode($data));
                $res = $this->_client->__Call("balanceInquiry" , [$data]);

                $response = [
                    'response' => [
                        'response_code' => $res->ResponseCode,
                        'Response_desc' => $res->ResponseDesc,
                        // 'reference_id' => $res->ReferenceID,
                    ]
                ];

                $this->logInfo(json_encode($response));

                return $acquirer['Acquirer']['ARN'];

            }catch(\SoapException $e){
                $response = [
                    'response' => [
                        'response_code' => $e->getCode(),
                        'Response_desc' => $e->getMessage(),
                    ]
                ];

                $this->logError(json_encode($response));
            }
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