<?php

namespace YaText\Tags;

use YaText\Interfaces\TagInterface;
use YaText\Tag;

class CodeTag implements TagInterface
{
	private $_tag;
	private $_output;

	private static $_maps = [
		'verbatim',
		'listing',
		'example',
		'code',
		'@',
		'input',
		'>>>',
		'output',
		'<<<'
	];

	public function __construct( Tag &$tag )
	{
		$this->_tag = $tag;
		$this->_output = '';
	}

	public static function GetMappings(): array
	{
		return CodeTag::$_maps;
	}

	public function Build(): void
	{
		$name = strtolower( $this->_tag->Name() );

		if( $name == 'code' || $name == '@' )
			$this->_output =
				'<code>' .
				$this->_tag->Content() .
				'</code>';
		else if( $name == 'verbatim' || $name == 'listing' || $name == 'example' )
			$this->_output =
				'<pre>' .
				$this->_tag->Content() .
				'</pre>';
		else if( $name == 'output' )
			$this->_output =
				'<samp>' .
				str_replace("\n", '<br />', $this->_tag->Content()) .
				'</samp>';
	}

	public function GetOutput(): string
	{
		return $this->_output;
	}

	public function HasCustomParser(): bool
	{
		return true;
	}
}
