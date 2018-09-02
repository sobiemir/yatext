<?php

namespace YaText\Tags;

use YaText\Interfaces\TagInterface;
use YaText\Tag;

class ParagraphTag implements TagInterface
{
	private $_tag;
	private $_output;

	private static $_maps = [
		'paragraph',
		'description',
		'summary',
		'synopsis',
		'brief'
	];

	public function __construct( Tag &$tag )
	{
		$this->_tag = $tag;
		$this->_output = '';
	}

	public static function GetMappings(): array
	{
		return ParagraphTag::$_maps;
	}

	public function Build(): void
	{
		$lines = explode( "\n", $this->_tag->Content() );
		$output = '';
		$newpar = false;

		foreach( $lines as $line )
		{

			$line = trim( $line );
			if( $line == '' )
				$newpar = true;
			else if( $newpar )
				$output .= "</p><p>" . $line;
			else
				$output .= $line;
		}

		$this->_output = '<p>';
		$this->_output .= $this->_tag->Content();
		$this->_output .= '</p>';
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
