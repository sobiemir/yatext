<?php
namespace YaText;

define( 'ZTAG_NORMAL',    0x1 );
define( 'ZTAG_DIRECTIVE', 0x2 );
define( 'ZTAG_DOMAIN',    0x3 );
define( 'ZTAG_EMPTY',     0x4 );

class Tag
{
	private $_name;
	private $_type;
	private $_attributes;
	private $_content;
	private $_class;
	private $_parent;

	public function __construct( string $name, int $type = ZTAG_NORMAL )
	{
		$this->_name = $name;
		$this->_type = $type;
		$this->_attributes = [];
		$this->_content = "";
		$this->_class = null;
		$this->_parent = null;
	}

	public function AddContent( string $data )
	{
		$this->_content .= $data;
	}

	public function SetContent( string $data )
	{
		$this->_content = $data;
	}

	public function AddAttribute( string $name, string $value ): void
	{
		$this->_attributes[] = new Attribute( $name, $value );
	}

	public function Attributes(): array
	{
		return $this->_attributes;
	}

	public function Content(): string
	{
		return $this->_content;
	}

	public function Name(): string
	{
		return $this->_name;
	}

	public function SetClass( object $class ): void
	{
		$this->_class = $class;
	}

	public function Class(): object
	{
		return $this->_class;
	}

	public function Type(): int
	{
		return $this->_type;
	}

	public function StringType(): string
	{
		switch( $this->_type )
		{
			case ZTAG_NORMAL:
				return 'Tag';
			case ZTAG_DOMAIN:
				return 'Domain';
			case ZTAG_DIRECTIVE:
				return 'Directive';
			case ZTAG_EMPTY:
				return 'Entity';
		}
		return '';
	}

	public function SetParent( Tag &$parent ): void
	{
		$this->_parent = $parent;
	}

	public function Parent(): Tag
	{
		return $this->_parent;
	}
}
