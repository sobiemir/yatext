<?php
namespace YaText;

class Attribute
{
	private $_name;
	private $_content;
	private $_class;

	public function __construct( string $name, string $value )
	{
		$this->_name  = $name;
		$this->_value = $value;
	}

	public function Name()
	{
		return $this->_name;
	}

	public function Content()
	{
		return $this->_content;
	}
}
