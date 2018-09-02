<?php
namespace YaText;

class YaText
{
	private $_parser;
	private $_directive_mappings;
	private $_normal_mappings;
	private $_domain_mappings;
	private $_empty_mappings;
	private $_attribute_mappings;
	private $_config;

	public function __construct( string $config = null )
	{
		spl_autoload_register( __NAMESPACE__ . '\\YaText::Autoloader' );

		$this->_parser = null;
		$this->_directive_mappings = [];
		$this->_domain_mappings = [];
		$this->_empty_mappings = [];
		$this->_normal_mappings =[];
		$this->_attribute_mappings = [];

		if( $config == null )
			$config = __DIR__ . '/config.json';

		$json = file_get_contents( $config );

		if( $json !== false )
			$this->_config = json_decode( $json );
		else
			throw new \Exception( "Cannot open file '{$config}'" );
	}

	public function Run( string $file ): void
	{
		$this->_parser = new Parser( $file );

		$this->_directive_mappings = $this->PrepareMappings( 'Directives' );
		$this->_normal_mappings = $this->PrepareMappings( 'Tags' );
		$this->_domain_mappings = $this->PrepareMappings( 'Domains' );
		$this->_empty_mappings = $this->PrepareMappings( 'Entities' );
		$this->_attribute_mappings = $this->PrepareMappings( 'Attributes' );

		$this->_parser->Run([
				'directive' => $this->_directive_mappings,
				'normal' => $this->_normal_mappings,
				'domain' => $this->_domain_mappings,
				'entity' => $this->_empty_mappings,
				'attribute' => $this->_attribute_mappings
			],
			$this->_config
		);
	}

	public function PrepareMappings( string $type ): array
	{
		$directives = array_diff( scandir(__DIR__ . "/{$type}"), array('..', '.') );

		$mappings = [];
		foreach( $directives as $value )
		{
			if( !is_file(__DIR__ . "/{$type}/{$value}") )
				continue;

			$directive = str_replace( '.php', '', $value );
			$class     = "\\YaText\\{$type}\\{$directive}";

			if( !class_exists($class) )
				throw new \Exception( "Class '{$class}' not exists!" );

			$maps = $class::GetMappings();

			foreach( $maps as $value )
			{
				$map = strtolower( $value );

				if( isset($mappings[$map]) )
					throw new \Exception(
						"Mapping for name '{$map}' in class '{$class}' already exist in class '{$mappings[$map]}'!"
					);
				else
					$mappings[$map] = $class;
			}
		}
		return $mappings;
	}

	public function GetOuput(): string
	{
		if( $this->_parser == null )
			return '';

		return $this->_parser->GetOutput();
	}

	public function Save(): void
	{
		if( $this->_parser == null )
			return;

		file_put_contents( './test.html', $this->_parser->GetOutput() );
	}

	public static function Autoloader( string $name ): void
	{
		$parts = explode( '\\', $name );
		$count = count( $parts );

		if( $count > 1 && $parts[0] == 'YaText' )
			unset( $parts[0] );

		$path = __DIR__ . '/' . implode( '/', $parts ) . '.php';

		if( !file_exists($path) )
			throw new \Exception( "Object '{$name}' not exist in current context, path '{$path}'!" );

		require_once $path;
	}
}
