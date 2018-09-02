<?php
namespace YaText\Entities;

use YaText\Interfaces\TagInterface;
use YaText\Tag;

class ExtraEntity implements TagInterface
{
	private $_tag;
	private $_output;

	private static $_maps = [
		'hl',
		'===',
		'nl',
		'br',
		'newpage',
		'wbr'
	];

	public function __construct( Tag &$tag )
	{
		$this->_tag = $tag;
		$this->_output = '';
	}

	public static function GetMappings(): array
	{
		return ExtraEntity::$_maps;
	}

	public function Build(): void
	{
		$name = strtolower( $this->_tag->Name() );
		if( $name == 'nl' || $name == 'br' )
			$this->_output = '<br />';
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
