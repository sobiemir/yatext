<?php
namespace YaText\Entities;

use YaText\Interfaces\TagInterface;
use YaText\Tag;

class SimpleEntity implements TagInterface
{
	private $_tag;
	private $_output;

	private static $_maps = [
		'ellipsize',
		'...',
		'hyphen',
		'-',
		'ndash',
		'--',
		'mdash',
		'---',
		'minus',
		'nbsp'
	];

	public function __construct( Tag &$tag )
	{
		$this->_tag = $tag;
		$this->_output = '';
	}

	public static function GetMappings(): array
	{
		return SimpleEntity::$_maps;
	}

	public function Build(): void
	{
		$name = strtolower( $this->_tag->Name() );
		if( $name == "--" )
			$this->_output = '&ndash;';
	}

	public function GetOutput(): string
	{
		return $this->_output;
	}

	public function HasCustomParser(): bool
	{
		return false;
	}
}
