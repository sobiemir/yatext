<?php
namespace YaText;

define( 'ZHOOK_BEFORE_BUILD',  0x1 );
define( 'ZHOOK_AFTER_BUILD',   0x2 );
define( 'ZHOOK_BEFORE_OUTPUT', 0x3 );

class TagHook
{
	private static _beforeBuild = [];

	public static AddHook( $func ): void
	{
		self::_beforeBuild[$func] = $func;
	}

	public static RemoveHook( $func ): void
	{
		unset( self::_beforeBuild[$func] );
	}

	public static HookList()
	{
		
	}
}
