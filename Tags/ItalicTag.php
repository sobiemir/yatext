<?php

namespace YaText\Tags;

use YaText\Interfaces\TagInterface;
use YaText\Tag;

class ItalicTag implements TagInterface
{
	private $_tag;
	private $_output;

	private static $_maps = [
		'italic',
		'i',
		'/',
		'emphasis',
		'em',
		'//'
	];

	public function __construct( Tag &$tag )
	{
		$this->_tag = $tag;
		$this->_output = '';
	}

	public static function GetMappings(): array
	{
		return ItalicTag::$_maps;
	}

	public function Build(): void
	{
		$this->_output =
			'<i>' .
			$this->_tag->Content() .
			'</i>';
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
