<?php

namespace ITWOC\Traits;

trait ITWOCValidatorTrait {
    /**
     * This trait is a helper trait for ITWOC Class and the aim for it is to sanitize data
     * and check if the called service passed array of it in the same structure that it should
     * each service should have a protected array that defines it's structure for example
     * add cardSerivce should have array of structure here called _addCardAction then to check
     * if the cardService passed array matches the structure of the _addCardAction array we send them
     * both to the protected function followsFormat that will recursivly traverse both arrays and check
     * for differences in there structure based on there keys not values
     */


    /**
     * $_addCardAction shows the correct structure for AddCard Service
     * @var Array
     */
    protected $_addCardAction = [
        'CardAcceptor' => ['Id' => ''],
        'Card' => ['StartingNumbers' => ''],
        'Profile' => [
                [
                    'FirstName' => '',
                    'LastName' => '',
                    'Email' => '',
                    'CellNumber' => ''
                ],
            'ApplyFee' => '',
            ]
    ];

    /**
     * $_loadCardAction show the correct structure for LoadCard Service
     * @var Array
     */
    protected $_loadCardAction = [
        'CardAcceptor' => ['Id' => '' ],
        'Card' => ['ReferenceID' => ''],
        'FundingCard' => ['Number' => ''],
        'ApplyFee' => '',
        'Amount' => 0
    ];

    /**
     * $_checkBalanceAction show the correct structure for CheckBalance Service
     * @var Array
     */
    protected $_checkBalanceAction = [
        'CardAcceptor' => ['Id' => '' ],
        'Card' => [ 'ReferenceID' => ''],
        'ApplyFee' => '',
    ];

    /**
     * $_debitCardAction show the correct structure for DebitFunds Service
     * @var Array
     */
    protected $_debitCardAction = [
        'CardAcceptor' => ['Id' => '' ],
        'Card' => [ 'ReferenceID' => ''],
        'ApplyFee' => '',
        'Amount' => ''
    ];

    /**
     * $_activateCardAction show the correct structure for activateCard Service
     * @var Array
     */
    protected $_activateCardAction = [
        'CardAcceptor' => ['LocalDateTime' => '' ],
        'Card' => [ 'ReferenceID' => ''],
    ];


    /**
     * validateAddCardAction accepts the array to be validated fot AddCard Service
     * it will pass the array it recieved along with it's correct structure that
     * it should implement to the followsFormat function
     * @param  Array  $data [array for AddCard Service]
     * @return Bool       [whether it follows the format or not]
     */
    protected function validateAddCardAction(array $data = []) : bool {
        return $this->followsFormat($data , $this->_addCardAction);
    }

    /**
     * validateLoadCardAction accepts the array to be validated fot LoadCard Service
     * it will pass the array it recieved along with it's correct structure that
     * it should implement to the followsFormat function
     * @param  Array  $data [array for LoadCard Service]
     * @return Bool       [whether it follows the format or not]
     */
    protected function validateLoadCardAction(array $data = []) : bool {
        return $this->followsFormat($data , $this->_loadCardAction);
    }


    /**
     * validateCheckBalanceAction accepts the array to be validated fot CheckBalance Service
     * it will pass the array it recieved along with it's correct structure that
     * it should implement to the followsFormat function
     * @param  Array  $data [array for CheckBalance Service]
     * @return Bool       [whether it follows the format or not]
     */
    protected function validateCheckBalanceAction(array $data = []) : bool {
        return $this->followsFormat($data , $this->_checkBalanceAction);
    }



    /**
     * validateDebitCardAction accepts the array to be validated fot DebitFunds Service
     * it will pass the array it recieved along with it's correct structure that
     * it should implement to the followsFormat function
     * @param  Array  $data [array for DebitFunds Service]
     * @return Bool       [whether it follows the format or not]
     */
    protected function validateDebitCardAction(array $data = []) : bool {
        return $this->followsFormat($data , $this->_debitCardAction);
    }


    /**
     * validateDebitCardAction accepts the array to be validated fot DebitFunds Service
     * it will pass the array it recieved along with it's correct structure that
     * it should implement to the followsFormat function
     * @param  Array  $data [array for DebitFunds Service]
     * @return Bool       [whether it follows the format or not]
     */
    protected function validateActivateCardAction(array $data = []) : bool {
        return $this->followsFormat($data , $this->_activateCardAction);
    }


    /**
     * followsFormat checks if the passed array follows it's predefined structure
     * @param  Array $data   [array of data for a particular service]
     * @param  [Array] $format [array of predefined data structure for a particular service  ]
     * @return [Bool]         [whether the match or not based on there keys matching recursively]
     */
    protected function followsFormat($data , $format) : bool {
        if(array_keys($data) != array_keys($format))
            return false;

            // print_r($data);
            // echo "<hr>";
            // print_r($format);

        foreach ($data as $key => $value) {
            if(is_array($value)){
                $bool = $this->followsFormat($value , $format[$key]);

                if(!$bool)
                    return $bool;
            }
        }

        return true;
    }
}