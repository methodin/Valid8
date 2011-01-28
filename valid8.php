<?php
class Valid8 {
	private $response = array();
	private $params = array();
	private $key = false;
	private $errors = array();
	private $type = false;
	private $fatal = false;
	private $key_list = array();

	function __construct(&$r, &$p)
	{
		$this->response =& $r;
		$this->params =& $p;
	}

	private function error($msg)
	{
		$this->errors[] = $msg;
	}

	private function fatal($msg)
	{
		$this->fatal = true;
		$this->errors[] = $msg;
	}

	function check($key)
	{
		$this->key_list[] = $key;
		$this->key = $key;
		return $this;
	}

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
						$this->fatal("{$this->key} is not a valid integer");
					}
					break;
				case 'numeric':
					if(!is_numeric($this->params[$this->key]))
					{
						$this->fatal("{$this->key} is not a valid number");
					}
					break;
				case 'boolean':
					if(!is_bool($this->params[$this->key]))
					{
						$this->fatal("{$this->key} is not a valid boolean");
					}
					break;
				case 'array':
					if(!is_array($this->params[$this->key]))
					{
						$this->fatal("{$this->key} is not a valid array");
					}
					break;
				case 'date':
					if(!is_string($this->params[$this->key]) || !preg_match('/((19|20)\\d\\d)\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])/', $this->params[$this->key]))
					{
						$this->fatal("{$this->key} is not a valid date");
					}
					break;
				case 'datetime':
					if(!is_string($this->params[$this->key]) || !preg_match('/((19|20)\\d\\d)\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01]) ([0][0-9]|[1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])/', $this->params[$this->key]))
					{
						$this->fatal("{$this->key} is not a valid datetime");
					}
					break;
				case 'string':
					if(!is_string($this->params[$this->key]))
					{
						$this->fatal("{$this->key} is not a valid string");
					}
					break;
				default:
					$this->error("Invalid type {$type}");
					$this->type = false;
					break;
			}
		}
		return $this;
	}

	function required()
	{
		if(!$this->fatal && !isset($this->params[$this->key]))
		{
			$this->error("{$this->key} is a required parameter");
		}
		return $this;
	}

	function not_empty()
	{
		if(!$this->fatal && isset($this->params[$this->key]) && empty($this->params[$this->key]))
		{
			$this->error("{$this->key} in parameter list cannot be empty");
		}
		return $this;
	}

	function greater_than($value)
	{
		if(!$this->fatal && isset($this->params[$this->key]) && $this->params[$this->key] <= $value)
		{
			$this->error("{$this->key} value of [{$this->params[$this->key]}] is not greater than [{$value}]");
		}
		return $this;
	}

	function less_than($value)
	{
		if(!$this->fatal && isset($this->params[$this->key]) && $this->params[$this->key] >= $value)
		{
			$this->error("{$this->key} value of [{$this->params[$this->key]}] is not less than [{$value}]");
		}
		return $this;
	}

	function default_to($value)
	{
		if(!$this->fatal && !isset($this->params[$this->key]))
		{
			$this->params[$this->key] = $value;
		}
		return $this;
	}

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
			}
			if(empty($pattern))
			{
				$pattern = $value;
			}
			if(!preg_match($pattern, $this->params[$this->key]))
			{
				$this->error("{$this->key} does not match exepected pattern");
			}
		}
		return $this;
	}

	function in()
	{
		if(!$this->fatal)
		{
			$value = func_get_args();
			if(is_array($value) && count($value))
			{
				if($this->type != 'array' && array_search($this->params[$this->key], $value) === false)
				{
					$this->error("{$this->key} contains an unexpected value");
				}
				else if($this->type == 'array' && count(array_intersect($this->params[$this->key], $value)) != count($this->params[$this->key]))
				{
					$this->error("{$this->key} contains an unexpected value");
				}
			}
			else
			{
				$this->error("Expected an array with at least one value for the \"in\" comparison against {$this->key}");
			}
		}
		return $this;
	}

	function run()
	{
		$diff = array_diff(array_keys($this->params), $this->key_list);
		if(count($diff))
		{
			$keys = implode(', ',$diff);
			$this->error("The following parameters are not valid for this function: {$keys}");
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

function valid8(&$response, &$params)
{
	$response = array(
		'status' => 1,
		'message' => array(),
		'result' => array()
	);
	return new Valid8($response, $params);
}