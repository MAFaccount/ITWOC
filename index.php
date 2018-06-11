<?php 

require 'vendor/autoload.php';


use ITWOC\ITWOC;


$e = new ITWOC;

$data = [
'CardAcceptor' => ['Id' => '7219001'],
'Card' => ['StartingNumbers' => '1233'],
'Profile' => [
		 [
			'FirstName' => 'Mohammad',
			'LastName' => 'Istanbouly',
			'Email' => 'mohistanboli@gmail.com',
			'CellNumber' => [
				'test' => ''
			]
		],
	'ApplyFee' => '',
	]
];



$e->addCard($data);