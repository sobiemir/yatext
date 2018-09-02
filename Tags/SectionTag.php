<?php

namespace YaText\Tags;

use YaText\Interfaces\TagInterface;
use YaText\Tag;

class SectionTag implements TagInterface
{
	private $_tag;
	private $_output;

	private static $_maps = [
		'section',
		'heading',
		'subsection',
		'subheading',
		'subsubsection',
		'subsubheading'
	];

	public function __construct( Tag &$tag )
	{
		$this->_tag = $tag;
		$this->_output = '';
	}

	public static function GetMappings(): array
	{
		return SectionTag::$_maps;
	}

	public function Build(): void
	{
		$name = strtolower( $this->_tag->Name() );

		if( $name == 'section' || $name == 'heading' )
			$this->_output =
				'<h1>' .
				$this->_tag->Content() .
				'</h1>';
		else if( $name == 'subsection' || $name == 'subheading' )
			$this->_output =
				'<h2>' .
				$this->_tag->Content() .
				'</h2>';
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
