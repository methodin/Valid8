<?php
class Validate {
	private $response = array(); // Generic function response
	private $params = array(); // Incoming parameter array
	private $key = false; // Current key in params being checked
	private $fancy = false; // String representation of the key
	private $errors = array(); // Error buffer
	private $type = false; // Type of currenty $params[$key] element
	private $fatal = false; // We've failed - misereably
	private $key_list = array(); // Buffer to make sure no unexpected parameters are passed
	private $message = false; // Override default error message

	function __construct(&$r, &$p)
	{
		$this->response =& $r;
		$this->params =& $p;
	}

	// General validation error message
	private function error($msg)
	{
		$this->errors[] = $this->message ? $this->message : $msg;
	}

	// Fatal errored - for a particular key is a string when expecting an int
	// No sense in proceeding with additional checked
	private function fatal($msg)
	{
		$this->fatal = true;
		$this->errors[] = $msg;
	}

	// Ignore a particular incoming key
	function ignore($key)
	{
		$this->key_list[] = $key;
		return $this;
	}	

	// Start checking $key of $params array
	function check($key)
	{
		$this->key_list[] = $key;
		$this->key = $key;
		$this->fancy = ucwords(str_replace(array('_', '-', '+'), ' ', strtolower($key)));
		$this->message = false;
		return $this;
	}

	// Set a message to use on the next error
	function message($msg)
	{
		$this->message = $msg;
		return $this;
	}

	function fancy($name)
	{
		$this->fancy = $name;
		return $this;
	}

	// Time for type validation
	function is($type)
	{
		$this->fatal = false;
		$this->type = $type;
		if(isset($this->params[$this->key]))
		{
			switch($type)
			{
				case 'int':
					if(!is_int($this->params[$this->key]))
					{
						$this->fatal("{$this->fancy} is not a valid integer.");
					}
					break;
				case 'numeric':
					if(!is_numeric($this->params[$this->key]))
					{
						$this->fatal("{$this->fancy} is not a valid number.");
					}
					break;
				case 'boolean':
					if(!is_bool($this->params[$this->key]))
					{
						$this->fatal("{$this->fancy} is not a valid boolean.");
					}
					break;
				case 'array':
					if(!is_array($this->params[$this->key]))
					{
						$this->fatal("{$this->fancy} is not a valid array.");
					}
					break;
				case 'date':
					if(!is_string($this->params[$this->key]) || !preg_match('/((19|20)\\d\\d)\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])/', $this->params[$this->key]))
					{
						$this->fatal("{$this->fancy} is not a valid date.");
					}
					break;
				case 'datetime':
					if(!is_string($this->params[$this->key]) || !preg_match('/((19|20)\\d\\d)\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01]) ([0][0-9]|[1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])/', $this->params[$this->key]))
					{
						$this->fatal("{$this->fancy} is not a valid datetime.");
					}
					break;
				case 'string':
					if(!is_string($this->params[$this->key]))
					{
						$this->fatal("{$this->fancy} is not a valid string.");
					}
					break;
				default:
					$this->error("Invalid type {$type}");
					$this->type = false;
					break;
			}
		}
		$this->message = false;
		return $this;
	}

	// See if the value is equal to the parameter
	function equal($value)
	{
		if(!$this->fatal && $this->params[$this->key] != $value)
		{
			$this->error("{$this->key} is not the expected value.");
		}
		$this->message = false;
		return $this;
	}	

	// Check that the current value being checked was passed
	function required($condition=true)
	{
		if(!$this->fatal && !isset($this->params[$this->key]) && $condition)
		{
			$this->error("{$this->fancy} is a required parameter.");
		}
		$this->message = false;
		return $this;
	}

	// Check that the value is not empty (literal empty)
	function not_empty($condition=true)
	{
		if(!$this->fatal && $condition && isset($this->params[$this->key]) && empty($this->params[$this->key]))
		{
			$this->error("{$this->fancy} must not be empty.");
		}
		$this->message = false;
		return $this;
	}

	// Compare the value against a minimum
	function greater_than($value)
	{
		if(!$this->fatal && isset($this->params[$this->key]) && $this->params[$this->key] <= $value)
		{
			$this->error("{$this->fancy} value of [{$this->params[$this->key]}] is not greater than [{$value}].");
		}
		$this->message = false;
		return $this;
	}

	// Compare the value against a maximum
	function less_than($value)
	{
		if(!$this->fatal && isset($this->params[$this->key]) && $this->params[$this->key] >= $value)
		{
			$this->error("{$this->fancy} value of [{$this->params[$this->key]}] is not less than [{$value}].");
		}
		$this->message = false;
		return $this;
	}

	// Maximum length of a string
	function max_length($value)
	{
		if(!$this->fatal && $this->type == 'string' && strlen($this->params[$this->key]) > $value)
		{
			$this->error("{$this->fancy} must be fewer than {$value} characters.");
		}
		$this->message = false;
		return $this;
	}
	
	// Minimum length of a string
	function min_length($value)
	{
		if(!$this->fatal && $this->type == 'string' && strlen($this->params[$this->key]) < $value)
		{
			$this->error("{$this->fancy} must be at least {$value} characters.");
		}
		$this->message = false;
		return $this;
	}

	// Don't accept a particular value
	function is_not($value)
	{
		if(!$this->fatal && $this->params[$this->key] === $value)
		{
			$this->error("{$this->fancy} must not be [{$value}].");
		}
		$this->message = false;
		return $this;
	}

	// If not passed at all set to a default
	function default_to($value)
	{
		if(!$this->fatal && !isset($this->params[$this->key]))
		{
			$this->params[$this->key] = $value;
		}
		$this->message = false;
		return $this;
	}

	// Run a preset or custom regex pattern
	function regex($value)
	{
		if(isset($this->params[$this->key]))
		{
			$pattern = '';
			switch($value)
			{
				case 'url':
					$pattern = '#^http\://[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(/\S*)?$#';
					break;
				case 'email':
					$pattern = "@^[a-z0-9,!#\$%&'\*\+/=\?\^_`\{\|}~-]+(\.[a-z0-9,!#\$%&'\*\+/=\?\^_`\{\|}~-]+)*\@[a-z0-9-]+(\.[a-z0-9-]+)*\.([a-z]{2,})$@";
					break;
			}
			if(empty($pattern))
			{
				$pattern = $value;
			}
			if(!preg_match($pattern, $this->params[$this->key]))
			{
				$this->error("{$this->fancy} does not match expected pattern.");
			}
		}
		$this->message = false;
		return $this;
	}

	// Only allow the value to be in a certain set of values
	// Passed as in(value1, value2, value3)
	function in()
	{
		if(!$this->fatal)
		{
			$value = func_get_args();
			if(is_array($value) && count($value))
			{
				if($this->type != 'array' && array_search($this->params[$this->key], $value) === false)
				{
					$this->error("{$this->fancy} contains an unexpected value.");
				}
				else if($this->type == 'array' && count(array_intersect($this->params[$this->key], $value)) != count($this->params[$this->key]))
				{
					$this->error("{$this->fancy} contains an unexpected value.");
				}
			}
			else
			{
				$this->error("Expected an array with at least one value for the \"in\" comparison against {$this->fancy}.");
			}
		}
		$this->message = false;
		return $this;
	}

	// Perform validation
	function run()
	{
		$diff = array_diff(array_keys($this->params), $this->key_list);
		if(count($diff))
		{
			$keys = implode(', ',$diff);
			$this->error("The following parameters are not valid for this function: {$keys}.");
		}
		if(count($this->errors) > 0)
		{
			$this->response['status'] = 0;
			$this->response['message'] = $this->errors;
			return false;
		}
		return true;
	}
}

function validate(&$response, &$params)
{
	if(!isset($response))
	{
		$response = array();
	}
	// Set response to a default value
	$response = array_merge($response,array(
		'status' => 1,
		'message' => array(),
		'result' => array()
	));
	return new Validate($response, $params);
}