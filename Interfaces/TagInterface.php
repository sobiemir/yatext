<?php
namespace YaText\Interfaces;

use YaText\Tag;

interface TagInterface
{
	public function __construct( Tag &$tag );
	public static function GetMappings(): array;
	public function Build(): void;
	public function GetOutput(): string;
	public function HasCustomParser(): bool;
}
