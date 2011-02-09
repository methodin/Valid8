<?php
include_once dirname(__FILE__).'/../Valid8.php';

class Valid8Test extends PHPUnit_Framework_TestCase
{
	public function testIs()
	{
		$valid = array(
			'int' => 66,
			'numeric' => 4.56,
			'boolean' => true,
			'array' => array(1,2,3,4),
			'date' => '2010-10-01',
			'string' => 'I am a string',
		);
		$this->assertEquals(true, valid8($response,$valid)
			->check('int')->is('int')
			->check('numeric')->is('numeric')
			->check('boolean')->is('boolean')
			->check('array')->is('array')
			->check('date')->is('date')
			->check('string')->is('string')
			->run()
		);

		$invalid = array(
			'test' => 'test',
		);
		$this->assertEquals(false, valid8($response,$invalid)
			->check('test')->is('test')
			->run()
		);
		$this->assertEquals(array(
			"Invalid type test",
		), $response['message']);
	}

	public function testCheck()
	{
		$valid = array(
			'int' => 66,
			'numeric' => 4.56,
			'boolean' => true,
			'array' => array(1,2,3,4),
			'date' => '2010-10-01',
			'datetime' => '2010-10-01 23:59:59',
			'string' => 'I am a string',
		);
		$this->assertEquals(true, valid8($response,$valid)
			->check('int')->is('int')
			->check('numeric')->is('numeric')
			->check('boolean')->is('boolean')
			->check('array')->is('array')
			->check('date')->is('date')
			->check('datetime')->is('datetime')
			->check('string')->is('string')
			->run()
		);

		$invalid = array(
			'int' => 'test',
			'numeric' => 'test',
			'boolean' => 'test',
			'array' => 'test',
			'date' => '2010/10/01',
			'datetime' => '2010-10-01 24:59:59',
			'string' => 5,
			'int2' => null
		);
		$this->assertEquals(false, valid8($response,$invalid)
			->check('int')->is('int')
			->check('int2')->is('int')->required()
			->check('numeric')->is('numeric')
			->check('boolean')->is('boolean')
			->check('array')->is('array')
			->check('date')->is('date')
			->check('datetime')->is('datetime')
			->check('string')->is('string')
			->run()
		);
		$this->assertEquals(array(
			"int is not a valid integer.",
			"int2 is a required parameter.",
			"numeric is not a valid number.",
			"boolean is not a valid boolean.",
			"array is not a valid array.",
			"date is not a valid date.",
			"datetime is not a valid datetime.",
			"string is not a valid string.",
		), $response['message']);
	}

	public function testRequired()
	{
		$valid = array(
			'int' => 66,
			'numeric' => 2.3
		);
		$this->assertEquals(true, valid8($response,$valid)
			->check('int')->is('int')
			->check('numeric')->is('numeric')->required()
			->run()
		);

		$invalid = array(
			'int' => 66,
		);
		$this->assertEquals(false, valid8($response,$invalid)
			->check('int')->is('int')
			->check('numeric')->is('numeric')->required()
			->run()
		);
		$this->assertEquals(array(
			"numeric is a required parameter.",
		), $response['message']);
	}

	public function testNotEmpty()
	{
		$valid = array(
			'int' => 66,
			'numeric' => 2.3
		);
		$this->assertEquals(true, valid8($response,$valid)
			->check('int')->is('int')->not_empty()
			->check('numeric')->is('numeric')->not_empty()
			->run()
		);

		$invalid = array(
			'int' => 0,
			'numeric' => 0,
			'string' => '',
			'array' => array(),
		);
		$this->assertEquals(false, valid8($response,$invalid)
			->check('int')->is('int')->not_empty()
			->check('numeric')->is('numeric')->not_empty()
			->check('string')->is('string')->not_empty()
			->check('array')->is('array')->not_empty()
			->run()
		);
		$this->assertEquals(array(
			"int in parameter list cannot be empty.",
			"numeric in parameter list cannot be empty.",
			"string in parameter list cannot be empty.",
			"array in parameter list cannot be empty.",
		), $response['message']);
	}

	public function testGreaterThan()
	{
		$valid = array(
			'int' => 66,
			'numeric' => 2.3
		);
		$this->assertEquals(true, valid8($response,$valid)
			->check('int')->is('int')->greater_than(65)
			->check('numeric')->is('numeric')->greater_than(1)
			->run()
		);

		$invalid = array(
			'int' => 66,
			'numeric' => 2.3
		);
		$this->assertEquals(false, valid8($response,$invalid)
			->check('int')->is('int')->greater_than(67)
			->check('numeric')->is('numeric')->greater_than(2.4)
			->run()
		);
		$this->assertEquals(array(
			"int value of [66] is not greater than [67].",
			"numeric value of [2.3] is not greater than [2.4].",
		), $response['message']);
	}

	public function testLessThan()
	{
		$valid = array(
			'int' => 66,
			'numeric' => 2.3
		);
		$this->assertEquals(true, valid8($response,$valid)
			->check('int')->is('int')->less_than(67)
			->check('numeric')->is('numeric')->less_than(2.4)
			->run()
		);

		$invalid = array(
			'int' => 66,
			'numeric' => 2.3
		);
		$this->assertEquals(false, valid8($response,$invalid)
			->check('int')->is('int')->less_than(65)
			->check('numeric')->is('numeric')->less_than(2.2)
			->run()
		);
		$this->assertEquals(array(
			"int value of [66] is not less than [65].",
			"numeric value of [2.3] is not less than [2.2].",
		), $response['message']);
	}

	public function testDefaultTo()
	{
		$valid = array(
			'int' => 66,
		);
		$this->assertEquals(true, valid8($response,$valid)
			->check('int')->is('int')->less_than(67)
			->check('numeric')->is('numeric')->default_to(2.4)
			->run()
		);
		$this->assertEquals($valid['numeric'], 2.4);
	}

	public function testIn()
	{
		$valid = array(
			'int' => 66,
			'numeric' => 2.3,
			'string' => 'test',
			'array' => array('test')
		);
		$this->assertEquals(true, valid8($response,$valid)
			->check('int')->is('int')->in(66,67)
			->check('numeric')->is('numeric')->in(2.3,2.4)
			->check('string')->is('string')->in('test','test2')
			->check('array')->is('array')->in('test','test2')
			->run()
		);

		$valid = array(
			'int' => 65,
			'numeric' => 2.1,
			'string' => 'testy',
			'array' => array('testy')
		);
		$this->assertEquals(false, valid8($response,$valid)
			->check('int')->is('int')->in(66,67)
			->check('numeric')->is('numeric')->in(2.3,2.4)
			->check('string')->is('string')->in('test','test2')
			->check('array')->is('array')->in('test','test2')
			->run()
		);
		$this->assertEquals(array(
			"int contains an unexpected value.",
			"numeric contains an unexpected value.",
			"string contains an unexpected value.",
			"array contains an unexpected value.",
		), $response['message']);
	}

	public function testRegex()
	{
		$valid = array(
			'url' => 'http://www.blah.com',
			'random' => '12345'
		);
		$this->assertEquals(true, valid8($response,$valid)
			->check('url')->is('string')->regex('url')
			->check('random')->is('string')->regex('/[1-5]{5}/')
			->run()
		);

		$valid = array(
			'url' => 'http://www.blah',
			'random' => '12345',
			'email' => 'fffff@'
		);
		$this->assertEquals(false, valid8($response,$valid)
			->check('url')->is('string')->regex('url')
			->check('random')->is('string')->regex('/[1-5]{6}/')
			->check('email')->is('string')->regex('email')
			->run()
		);

		$this->assertEquals(array(
			"url does not match exepected pattern.",
			"random does not match exepected pattern.",
			"email does not match exepected pattern.",
		), $response['message']);
	}
}