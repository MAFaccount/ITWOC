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
     * $_client will store instance of the SoapClient so we can use the soap services
     * @var SoapClient
     */
    protected $_najm_client;

    /**
     * $_wsdlUrl the url of wsdl file that defines the soap services so we can use them it
     * will be loaded from configuration file
     * @var String
     */
    protected $_wsdlUrl;

    /**
     * $_wsdlUrl the url of wsdl file that defines the soap services so we can use them it
     * will be loaded from configuration file
     * @var String
     */
    protected $_najm_wsdlUrl;

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
     * $_logPath will store the log file path for this package and will be loaded from the configuration files
     * @var String
     */
    protected $_najm_logPath;

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
        $this->initLog();
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

        $this->_client = new SoapClient( $this->_wsdlUrl ,array("trace" => 1, "exception" => 0));
    }

    /**
     * init will load configurations and set up the soap client
     */
    protected function najmInit(){
        //load data from configuration file
        $this->_najm_wsdlUrl = config('itwoc.najm_wsdl_file');
        $this->_najm_logPath = config('itwoc.najm_log_path');

        //register the soapclient
        $this->_najm_client = new SoapClient( $this->_najm_wsdlUrl ,array("trace" => 1, "exception" => 0));
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
    public function checkBalance(array $data = [] , $cvv = '') : array {
        if($this->validateCheckBalanceAction($data) && !empty($cvv)){
            $acquirer = $this->getAcquirer();
            $data = $acquirer + $data;


            //decide if it is a virtual card or not
            $data['Card']['CardBin'] = substr($data['Card']['Number'], 0 , 7);

            $firstEightDigits = substr($data['Card']['Number'] , 0 , 8);

            $virtualNumbers = config('itwoc.virtual_allowed_starting_numbers');

            if($firstEightDigits == $virtualNumbers)
                $data['Card']['AAC'] = $cvv;
            else
                $data['Card']['PIN'] = $cvv;

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


            try{

                $this->initLog($this->_najm_logPath,'NAJM');
                $dataLogObject = new ArrayObject($data);
                $dataLogObject['CardNo'] = "************".substr($data['CardNo'], -4);
                $dataLogObject['ExpiryDate'] = '****';
                $dataLog = $dataLogObject->getArrayCopy();
                $this->logInfo(json_encode($dataLog));

                $input = array(
                        'header' => array(
                            'version' => config('itwoc.najm_version'),
                            'msg_id' => $data['TxnReferenceId'],
                            'msg_type' => config('itwoc.najm_msg_type'),
                            'msg_function' => config('itwoc.najm_msg_function'),
                            'src_application' => config('itwoc.najm_src_application'),
                            'target_application' => config('itwoc.najm_target_application'),
                            'timestamp' => $time,
                            'tracking_id' => $data['TxnReferenceId'],
                            'bank_id' => config('itwoc.najm_bank_id'),
                        ),
                        'body' => array(
                            'card_no' => $data['CardNo'],
                            'expiry_date' => $data['ExpiryDate'],
                            'channel_name' => config('itwoc.najm_channel_name'),
                            'txn_reference_id' => $data['TxnReferenceId'],
                            'transaction_amount' => $data['Amount'],
                            'merchant_id' => config('itwoc.najm_merchant_id'),
                            'terminal_id' => config('itwoc.najm_terminal_id')
                        )
                    );

                $res = $this->_najm_client->__Call("CARD_DEBIT" , [$input]);
                $reportRespCode = $res->exception_details->error_code;
                $reportRespMessage = $res->exception_details->error_description;
                $referenceNumber = $res->exception_details->transaction_ref_id;
                $statusResponse = $res->exception_details->status;
                //check for success
                if(strtolower($statusResponse) == 's' && strtolower($reportRespMessage) == 'success' && $reportRespCode == 000){
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
        $response = [
            'code' => 422,
            'message' => 'Validation error check your array structure and starting number may not be allowed'
        ];
        
        return $response;
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
    public function initLog($logPath = '',$logChanel = 'ITWOC'){
        if($logPath != ''){
            $this->_logPath = $logPath;
        }
        $this->_logger = new Logger($logChanel);
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
