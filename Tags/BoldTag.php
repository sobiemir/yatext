<?php

namespace YaText\Tags;

use YaText\Interfaces\TagInterface;
use YaText\Tag;

class BoldTag implements TagInterface
{
	private $_tag;
	private $_output;

	private static $_maps = [
		'bold',
		'b',
		'*',
		'strong',
		'**'
	];

	public function __construct( Tag &$tag )
	{
		$this->_tag = $tag;
		$this->_output = '';
	}

	public static function GetMappings(): array
	{
		return BoldTag::$_maps;
	}

	public function Build(): void
	{
		$this->_output =
			'<b>' .
			$this->_tag->Content() .
			'</b>';
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
