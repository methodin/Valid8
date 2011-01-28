Valid8 for PHP
======================================================================

# Introduction

Valid8 is a singleton validation class that will reduce the amount of bugs in your code that a result of unexpected values in functions or APIs. Currently it's designed to work with associative arrays ($_GET, $_POST, custom arrays etc...). A mostly complete example is below.
	
	function my_function($params=array())
	{
		if(!valid8($response, $params)
			->check('some_id')->is('int')->required()->greater_than(0)
			->check('other_val')->is('string')->default_to('')
			->check('third_val')->is('array')->in(1,2,3,4,5,6)->default_to(array())
			->check('some_id2')->is('int')->less_than(5)
			->check('date_stamp')->is('date')->not_empty()
			->check('number')->is('numeric')->greater_than(1.5)
			->run())
		{return $response;}

		// do something with the sweet knowledge your parameters are safe
	}
	my_function(array(
		'some_id' => 5,
		'other_val' => 'my_string',
		'third_val' => array(1,2,3)
	));