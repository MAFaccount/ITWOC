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
     * $_allowedStartingNumbers will store array of allowed starting numbers for cards these will be loaded from configuration files
     * @var String
     */
    protected $_allowedStartingNumbers = [];


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
        $this->_allowedStartingNumbers = explode(',' , config('itwoc.allowed_starting_numbers'));

        //register the soapclient
        $this->_client = new SoapClient( $this->_wsdlUrl ,array("trace" => 1, "exception" => 0));
    }

    /**
     * getAcquirer will return the Acquirer along with a unique generated ARN
     * @return Array [array of acquirer details]
     */
    protected function getAcquirer() : array {
        $acquirer = $this->_acquirer;
        $acquirer['Acquirer']['ARN'] = substr(hash('sha512' , microtime() . uniqid() . str_random(100) ) , 0 , 20);
        return $acquirer;
    }

    /**
     * generateCard this function is to generate a new card
     * @param Array $data [card details]
     * @return [Array]       will return response
     */
    public function generateCard(array $data = []) : array {
        if($this->validateAddCardAction($data)){
            if(in_array($data['Card']['StartingNumbers'], $this->_allowedStartingNumbers)){
                $acquirer = $this->getAcquirer();
                $data = $acquirer + $data;

                try{
                    $this->logInfo(json_encode($data));
                    $res = $this->_client->__Call("AddCard" , [$data]);

                    //check for success
                    if($res->ResponseCode == 'I2C00'){
                        $response = [
                            'code' => 200,
                            'data' => $res,
                            'message' => ''
                        ];
                    }else{
                        $response = [
                            'code' => 422,
                            'data' => $res,
                            'message' => 'Validation Error'
                        ];
                    }

                    $this->logInfo(json_encode($response));
                    $response['ARN'] = $acquirer['Acquirer']['ARN'];

                    return $response;

                }catch(\SoapException $e){
                    $response = [
                        'code' => $e->getCode() ,
                        'message' => $e->getMessage(),
                    ];

                    $this->logError(json_encode($response));
                    return $response;
                }
            }
        }

        $response = [
            'code' => 422,
            'message' => 'Validation error check your array structure and starting number may not be allowed'
        ];

        return $response;
    }


    /**
     * deposit this function will load money in the card
     * @param  Array  $data [card details]
     * @return [array]      will return response
     */
    public function deposit(array $data = []) : array {
        if($this->validateLoadCardAction($data)){
            $acquirer = $this->getAcquirer();
            $data = $acquirer + $data;


            try{
                $this->logInfo(json_encode($data));
                $res = $this->_client->__Call("CreditFunds" , [$data]);

                //check for success
                if($res->ResponseCode == 'I2C00'){
                    $response = [
                        'code' => 200,
                        'data' => $res,
                        'message' => ''
                    ];
                }else{
                    $response = [
                        'code' => 422,
                        'data' => $res,
                        'message' => 'Validation Error'
                    ];
                }

                $this->logInfo(json_encode($response));

                $response['ARN'] = $acquirer['Acquirer']['ARN'];
                return $response;
            }catch(\SoapException $e){
                $response = [
                    'code' => $e->getCode() ,
                    'message' => $e->getMessage(),
                ];

                $this->logError(json_encode($response));
                return $response;
            }
        }
    }


    /**
     * checkBalance This will check the balance in the card
     * @param  Array  $data [card details]
     * @return [Array]       will return response
     */
    public function checkBalance(array $data = []) : array {
        if($this->validateCheckBalanceAction($data)){
            $acquirer = $this->getAcquirer();
            $data = $acquirer + $data;

            try{
                $this->logInfo(json_encode($data));
                $res = $this->_client->__Call("balanceInquiry" , [$data]);


                //check for success
                if($res->ResponseCode == 'I2C00'){
                    $response = [
                        'code' => 200,
                        'data' => $res,
                        'message' => ''
                    ];
                }else{
                    $response = [
                        'code' => 422,
                        'data' => $res,
                        'message' => 'Validation Error'
                    ];
                }

                $this->logInfo(json_encode($response));

                $response['ARN'] = $acquirer['Acquirer']['ARN'];
                return $response;

            }catch(\SoapException $e){
                $response = [
                    'code' => $e->getCode() ,
                    'message' => $e->getMessage(),
                ];

                $this->logError(json_encode($response));
                return $response;
            }
        }
    }

    /**
     * withdraw this function will load money in the card
     * @param  Array  $data [card details]
     * @return [array]      will return response
     */
    public function withdraw(array $data = []) : array {
        if($this->validateDebitCardAction($data)){
            $acquirer = $this->getAcquirer();
            $data = $acquirer + $data;


            try{
                $this->logInfo(json_encode($data));
                $res = $this->_client->__Call("debitFunds" , [$data]);

                //check for success
                if($res->ResponseCode == 'I2C00'){
                    $response = [
                        'code' => 200,
                        'data' => $res,
                        'message' => ''
                    ];
                }else{
                    $response = [
                        'code' => 422,
                        'data' => $res,
                        'message' => 'Validation Error'
                    ];
                }

                $this->logInfo(json_encode($response));

                $response['ARN'] = $acquirer['Acquirer']['ARN'];
                return $response;
            }catch(\SoapException $e){
                $response = [
                    'code' => $e->getCode() ,
                    'message' => $e->getMessage(),
                ];

                $this->logError(json_encode($response));
                return $response;
            }
        }
    }

    public function activateCard(array $data = []) : array{
        if($this->validateActivateCardAction($data)){
            $acquirer = $this->getAcquirer();
            $data = $acquirer + $data;

            try{
                $this->logInfo(json_encode($data));
                $res = $this->_client->__Call("activateCard" , [$data]);


                //check for success
                if($res->ResponseCode == 'I2C00'){
                    $response = [
                        'code' => 200,
                        'data' => $res,
                        'message' => ''
                    ];
                }else{
                    $response = [
                        'code' => 422,
                        'data' => $res,
                        'message' => 'Validation Error'
                    ];
                }

                $this->logInfo(json_encode($response));

                $response['ARN'] = $acquirer['Acquirer']['ARN'];
                return $response;

            }catch(\SoapException $e){
                $response = [
                    'code' => $e->getCode() ,
                    'message' => $e->getMessage(),
                ];

                $this->logError(json_encode($response));
                return $response;
            }
        }
    }

    //logging functions

    /**
     * @param  string           $info           Info message to log in the log files
     */
    public function logInfo($info = ''){
        $this->_logger->info($info);
    }

    /**
     * @param  string           $notice         Notice message to log in the log files
     */
    public function logNotice($notice = ''){
        $this->_logger->notice($notice);
    }

    /**
     * @param  string           $error          Error message to log in the log files
     */
    public function logError($error = ''){
        $this->_logger->error($error);
    }

    /**
     * @param  string           $warning            Warning message to log in log files
     */
    public function logWarning($warning = ''){
        $this->_logger->warning($warning);
    }
}