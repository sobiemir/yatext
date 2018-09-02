<?php

namespace YaText\Tags;

use YaText\Interfaces\TagInterface;
use YaText\Tag;

class ReferenceTag implements TagInterface
{
	private $_tag;
	private $_output;

	private static $_maps = [
		'reference',
		'ref',
		'#'
	];

	public function __construct( Tag &$tag )
	{
		$this->_tag = $tag;
		$this->_output = '';
	}

	public static function GetMappings(): array
	{
		return ReferenceTag::$_maps;
	}

	public function Build(): void
	{
		$this->_output =
			'<a href="' . $this->_tag->Content() . '">' .
			$this->_tag->Content() .
			'</a> ';
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
