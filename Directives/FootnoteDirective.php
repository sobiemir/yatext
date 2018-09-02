<?php

namespace YaText\Directives;

use YaText\Interfaces\TagInterface;
use YaText\Tag;

class FootnoteDirective implements TagInterface
{
	private $_tag;
	private $_output;

	private static $_maps = [ 'footnote' ];

	public function __construct( Tag &$tag )
	{
		$this->_tag = $tag;
		$this->_output = '';
	}

	public static function GetMappings(): array
	{
		return FootnoteDirective::$_maps;
	}

	public function Build(): void
	{
		
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
