<?php

namespace YaText\Tags;

use YaText\Interfaces\TagInterface;
use YaText\Tag;

class UnderlineTag implements TagInterface
{
	private $_tag;
	private $_output;

	private static $_maps = [
		'underline',
		'_',
		'insert',
		'+'
	];

	public function __construct( Tag &$tag )
	{
		$this->_tag = $tag;
	}

	public static function GetMappings(): array
	{
		return UnderlineTag::$_maps;
	}

	public function Build(): void
	{
		$name = strtolower( $this->_tag->Name() );

		if( $name == '_' || $name == 'underline' )
			$this->_output =
				'<u>' .
				$this->_tag->Content() .
				'</u>';
		else if( $name == '+' || $name == 'insert' )
			$this->_output =
				'<ins>' .
				$this->_tag->Content() .
				'</ins>';
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
