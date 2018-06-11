<?php 

namespace ITWOC\Traits;

trait ITWOCValidatorTrait {
	// protected $_addCardAction = [
	// 	'CardAcceptor' => ['Id' => ''],
	// 	'Card' => ['StartingNumbers' => ''],
	// 	'Profile' => [
	// 			[
	// 				'FirstName' => '',
	// 				'LastName' => '',
	// 				'Email' => '',
	// 				'CellNumber' => ''
	// 			],
	// 		'ApplyFee' => '',
	// 		]
	// ];
	
	// protected function validateAddCardAction(array $data = [])  {
	// 	return $this->followsFormat($data , $this->_addCardAction);
	// 	// return $this->followsFormat($data , $this->_addCardAction);
	// }

	// protected function followsFormat($data , $format){
	// 	if(array_keys($data) != array_keys($format))
	// 		return false;


	// 		print_r($data);
	// 		echo "<hr>";
	// 		print_r($format);


	// 	foreach ($data as $key => $value) {
	// 		if(is_array($value)){
	// 			$bool = $this->followsFormat($value , $format[$key]);

	// 			if(!$bool)
	// 				return $bool;
	// 		}
	// 	}

	// 	return true;
	// }
}


	// protected function followsFormat($data , $format){
	// 	if(array_keys($data) != array_keys($format))
	// 		return false;


	// 	foreach ($format as $key => $value) {
	// 		if(is_array($value)  ){
	// 			if(is_array($data[$key])){
	// 				$bool = $this->followsFormat($data[$key] , $value);
	// 				if(!$bool)
	// 					return $bool;
	// 			} else {
	// 				return false; 
	// 			}
				
	// 		}
	// 	}

	// 	return true;
	// }










