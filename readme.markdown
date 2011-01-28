function my_function($params=array())
{
	if(!valid8($response, $params)
		->check('some_id')->is('int')->required()->greater_than(0)
		->check('other_val')->is('string')->default_to('')
		->check('third_val')->is('array')->in(1,2,3,4,5,6)->default_to(array())
		->run())
	{return $response;}
	// do something
}
my_function(array(
	'some_id' => 5,
	'other_val' => 'my_string',
	'third_val' => array(1,2,3)
));