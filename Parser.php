<?php
namespace YaText;

class Parser
{
	private $_file;
	private $_index;
	private $_tag;
	private $_tags;
	private $_directive_mappings;
	private $_normal_mappings;
	private $_domain_mappings;
	private $_empty_mappings;
	private $_config;

	public function __construct( string $file )
	{
		if( file_exists($file) )
			$this->_file = $file;
		else
			throw new \Exception( "File in path '{$file}' doesn't exist!" );

		$this->_tag = null;
		$this->_config = null;
		$this->_tags = [];
		$this->_index = 0;
		$this->_directive_mappings = [];
		$this->_normal_mappings = [];
		$this->_domain_mappings = [];
	}

	public function GetOutput(): string
	{
		$content = '';
		foreach( $this->_tags as &$tag )
		{
			$this->_DetectClass( $tag );
			$tag->Class()->Build();
			$content .= $tag->Class()->GetOutput();
		}
		return $content;
	}

	public function Run( array $maps, object $config ): void
	{
		$this->_directive_mappings = $maps['directive'];
		$this->_normal_mappings = $maps['normal'];
		$this->_domain_mappings = $maps['domain'];
		$this->_empty_mappings = $maps['entity'];
		$this->_config = $config;

		// wczytaj plik i odczytaj pierwsze znaczniki
		if( $this->_file != null )
			$this->_ParseFile( $this->_file );

		foreach( $this->_tags as &$tag )
		{
			if( !$tag->Class()->HasCustomParser() )
			{
				$content = $this->_ParseTag( $tag );
				$tag->SetContent( $content );
			}
		}
	}

	private function _ParseFile( string $path ): void
	{
		$handle = fopen( $path, 'r' );

		// pliku nie można otworzyć...
		if( $handle == null )
			throw new \Exception( "Cannot open file at path: '{$path}'" );

		$line = '';
		$start = 0;
		$current = null;

		// przetwarzaj każdą linię z pliku
		while( ($line = fgets($handle)) !== false )
		{
			// policz ilość białych znaków (spacja i tabulator)
			$length = strlen( $line );
			$indent = $this->_WhitespaceCount( $line, $length );
	
			$this->_index = $indent;

			if( $indent == -1 )
				continue;

			// pomiń wszystkie komentarze w tekście
			if( $this->_index + 2 < $length && $this->_CheckComment($line, $indent, $length) )
				continue;

			if( $indent > 0 || ($indent == 0 && trim($line) == '') )
			{
				if( !$current )
				{
					// nie wyświetlaj błędu braku znacznika w przypadku pustej liniii bez wcięcia
					if( $indent == 0 )
					{
						if( $current != null )
							$current->AddContent("\n");
						continue;
					}
					else
						throw new \Exception( "Unable to find tag name, that belongs to this indent" );
				}

				// pobierz wielkość wcięcia względem pierwszej linii treści znacznika
				if( $start ==  -1 )
					$start = $indent;

				// dodaj linię, usuwając wcięcie
				if( trim($line) == '' )
					$current->AddContent( "\n" );
				else
					$current->AddContent( substr($line, $start) );
				continue;
			}

			$block = false;

			// sprawdź czy istnieje dyrektywa lub znacznik blokowy
			if(
				$this->_BlockDirective($line, $indent, $length) ||
				$this->_BlockTag($line, $indent, $length)
			) {
				// jeżeli tak, dodaj pozostałą część linii do zawartości znacznika
				$this->_tag->AddContent( trim(substr($line, $this->_index)) );
				$start = -1;

				$this->_DetectClass( $this->_tag );
				$this->_tags[] = $this->_tag;
				$current = &$this->_tags[count($this->_tags) - 1];

				$block = true;
				continue;
			}
			else
				throw new \Exception( "No tag detected at zero index level!" );
		}
	}

	private function _DetectClass( Tag &$tag ): void
	{
		$name = strtolower( $tag->Name() );

		$mappings = [];
		switch( $tag->Type() )
		{
			case ZTAG_DIRECTIVE: $mappings = $this->_directive_mappings; break;
			case ZTAG_NORMAL:    $mappings = $this->_normal_mappings;    break;
			case ZTAG_DOMAIN:    $mappings = $this->_domain_mappings;    break;
			case ZTAG_EMPTY:     $mappings = $this->_empty_mappings;     break;
		}

		if( !isset($mappings[$name]) )
			throw new \Exception(
				'Class ' . ucfirst($name) . $tag->StringType() . ' for name ' . $name . ' does not exist!'
			);

		$tag->SetClass( new $mappings[$name]($tag) );
	}

	private function _ParseTag( Tag &$tag ): string
	{
		$content = $tag->Content();
		$lines = preg_split( "/\r\n|\n|\r/", $content );
		$current = null;
		$content = '';
		$start = -1;
		$tags = [];

		foreach( $lines as $line )
		{
			$length = strlen( $line );
			$indent = $this->_WhitespaceCount( $line, $length );

			$this->_index = $indent;
			$helper = $indent;

			if( $indent == -1 )
			{
				if( $current != null )
					$current->AddContent( "\n" );
				else
					$content .= "\n";
				continue;
			}

			// zawartość należy do aktualnego znacznika
			if( $indent == 0 )
			{
				// przetwarzaj zawartość ostatniego wykrytego bloku
				if( $current != null && trim($line) != '' )
				{
					$current->SetParent( $tag );

					$current->SetContent( $this->_ParseTag($current) );
					$current->Class()->Build();

					$content .= $current->Class()->GetOutput();
					$current = null;
				}

				// sprawdź czy istnieje dyrektywa lub znacznik blokowy
				if(
					$this->_BlockDirective($line, $indent, $length) ||
					$this->_BlockTag($line, $indent, $length)
				) {
					// jeżeli tak, dodaj pozostałą część linii do zawartości znacznika
					$this->_tag->AddContent( substr($line, $this->_index) );
					$start = -1;

					$this->_DetectClass( $this->_tag );
					$current = $this->_tag;

					$block = true;
					continue;
				}
				else
					$content .= $this->_ParseLine( $line ) . "\n";
			}
			// zawartość należy do innego znacznika
			else
			{
				// jeżeli nie wykryto znacznika, dodaj do zawartości
				if( $current == null )
				{
					$content .= $line . "\n";
					continue;
				}

				// pobierz wielkość wcięcia względem pierwszej linii treści znacznika
				if( $start ==  -1 )
					$start = $indent;

				// dodaj linię, usuwając wcięcie
				$current->AddContent( substr($line, $start) );
			}
			$block = false;
		}

		// przetwarzaj zawartość ostatniego wykrytego bloku
		if( $current != null )
		{
			$current->SetParent( $tag );

			$current->SetContent( $this->_ParseTag($current) );
			$current->Class()->Build();

			$content .= $current->Class()->GetOutput();
			$current = null;
		}

		return trim( $content );
	}

	private function _ParseLine( string $line ): string
	{
		$tnkey = "a-zA-Z0-9!-\\/;-@[-`|~";
		$tnval = "^:}";

		$drx = '\\:([' . $tnkey . ']+)\\:\\s*((?:\\:.|[' . $tnval . '])*)';
		$nrx = '([' . $tnkey . ']+)\\:\\s*((?:\\:.|[' . $tnval . '])+)';
		$erx = '\\:([' . $tnkey . ']+)';
		$arx = "\\:\\[(?:\\:.|[^:\\]])*\\]";

		$regex = '/\\{(?:' . $erx . '|' . $nrx . '|' . $drx . ')\\}(?:' . $arx . '){0,}/';

		// $regex = "/\{(?:\:([^:}\s]+)|([^\:]+)\:\s*((?:\:.|[^:}])+)|\:([^:}\s]+)\:\s*((?:\:.|[^:}])*))\}(?:\:\[(?:\:.|[^:\]])*\]){0,}/";
		$matches = [];

		// szukaj w linii elementów
		$success = preg_match_all(
			$regex,
			$line,
			$matches,
			PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL
		);

		if( !$success || $matches[0] == null || count($matches[0]) == 0 )
			return $line . " ";

		$mappings = [];

		// przechodź po wszystkich dostępnych dopasowaniach
		for( $x = 0, $y = count($matches[0]); $x < $y; ++$x )
		{
			$match = $matches[0][$x];
			$val = '';
			$key = '';
			$tag = null;

			// znacznik pusty
			if( $matches[1] != null && isset($matches[1][$x]) && $matches[1][$x][1] != -1 )
			{
				$key = $matches[1][$x][0];
				$mappings = &$this->_empty_mappings;
				$tag = new Tag( $key, ZTAG_EMPTY );
			}
			// znacznik liniowy
			else if( $matches[2] != null && isset($matches[2][$x]) && $matches[2][$x][1] != -1 )
			{
				$key = $matches[2][$x][0];
				$val = $matches[3][$x][0];
				$mappings = &$this->_normal_mappings;
				$tag = new Tag( $key, ZTAG_NORMAL );
			}
			// dyrektywa liniowa
			else if( $matches[4] != null && isset($matches[4][$x]) && $matches[4][$x][1] != -1 )
			{
				$key = $matches[4][$x][0];
				$val = $matches[5][$x][0];
				$mappings = &$this->_directive_mappings;
				$tag = new Tag( $key, ZTAG_DOMAIN );
			}

			if( $tag == null )
				continue;

			$tag->AddContent( $val );
			$this->_DetectClass( $tag );
			$tag->Class()->Build();

			$content = $tag->Class()->GetOutput();
			$this->_DetectClass( $tag );

			if( $content == '' )
				print_r($match);
			$line = str_replace( $match, $content, $line );
		}
		return $line . " ";
	}

	private function _BlockTag( string &$line, int $indent, int $length ): bool
	{
		// znacznik blokowy rozpoczyna sie innym znakiem niż dwukropek
		if( $line[$indent] == ':' )
			return false;

		$this->_index = $indent;

		$name = $this->_GetBlockTagName( $line, $length );
		if( $name == null )
			return false;

		$this->_tag = new Tag( $name, ZTAG_NORMAL );

		// po pobraniu nazwy znacznika, pobieraj jego atrybuty
		if( $this->_index < $length )
		{
			$this->_index++;
			while( $this->_GetAttribute($line, $length, false) )
			{
				$this->_index++;
				if( $this->_index < $length )
				{
					// kolejny atrybut musi być podany bezpośrednio po aktualnym
					if( $line[$this->_index] == '[' )
						continue;
					// znak dwukropka kończy definicję atrybutów
					if( $line[$this->_index] != ':' )
						throw new \Exception( "Error parsing tag attributes!" );
					else
						$this->_index++;
				}
				break;
			}
		}
		return true;
	}

	private function _BlockDirective( string &$line, int $indent, int $length ): bool
	{
		// dyrektywa zaczyna się i kończy znakiem dwukropka
		if( $line[$indent] != ':' || $line[$indent + 1] == ':' )
			return false;

		$this->_index = $indent + 1;

		// pobierz nazwę tagu dla bloku
		$name = $this->_GetBlockTagName( $line, $length );
		if( $name == null )
			return false;

		// gdy nazwa zostanie pobrana, utwórz obiekt znacznika
		$this->_tag = new Tag( $name, ZTAG_DIRECTIVE );

		// a następnie pobieraj atrybuty
		if( $this->_index < $length )
		{
			$this->_index++;
			while( $this->_GetAttribute($line, $length, false) )
			{
				$this->_index++;
				if( $this->_index < $length )
				{
					// kolejny atrybut musi być podany bezpośrednio po aktualnym
					if( $line[$this->_index] == '[' )
						continue;
					// znak dwukropka kończy definicję atrybutów
					if( $line[$this->_index] != ':' )
						throw new \Exception( "Error parsing tag attributes!" );
					else
						$this->_index++;
				}
				break;
			}
		}

		return true;
	}

	private function _GetAttribute( string &$line, int $length, bool $hasname = false ): bool
	{
		$name    = '';
		$content = '';

		if( $this->_index >= $length )
			return false;

		// atrybut rozpoczyna się znakiem nawiasu kwadratowego
		if( $line[$this->_index] == '[' )
		{
			$this->_index++;

			// pierwszym elementem atrybutu jest jego nazwa, która nie istnieje dla dyrektywy
			// dyrektywy mają argumenty, jednak ich definicja jest taka sama jak atrybutu
			if( $hasname )
			{
				// nazwy mogą być zapisywane tylko znakami alfanumerycznymi
				while( $this->_index < $length && ctype_alnum($line[$this->_index]) )
					$name .= $line[$this->_index++];

				if( $this->_index != ' ' )
					throw new \Exception( "Invalid attribute name" );
				$this->_index++;
			}
			else
				// dla dyrektyw, nazwa atrybutu to ilość elementów w tablicy + 1
				$name = (string)(count($this->_tag->Attributes()) + 1);

			$last = '';
			$hasend = false;

			// pobierz wartość atrybutu
			// generalnie gdy atrybut posiada nazwę, nie musi mieć zdefiniowanej wartości
			while( $this->_index < $length )
			{
				$chr = $line[$this->_index];

				// znak ucieczki przed zakończeniem atrybutu, znak ] musi być poprzedzony dwukropkiem
				if( ($chr == ']' && $last == ':') || $chr != ']' )
					$content .= $chr;
				else
				{
					$hasend = true;
					break;
				}
				$last = $chr;
				$this->_index++;
			}

			// gdy atrybut został zakończony poprawnie, dodaj go do listy
			if( $hasend )
			{
				$this->_tag->AddAttribute( $name, $content );
				return true;
			}
		}

		return false;
	}

	private function _GetBlockTagName( string &$line, int $length ): ?string
	{
		$tag = '';
		$ends  = false;

		// nazwa tagu blokowego nie może rozpoczynać się od dwukrpka,
		// jest to zarezerowany znak dla zakończenia tagu i rozpoczęcia dyrektywy
		if( $line[$this->_index] == ':' )
			return false;

		// pobieraj poszczególne znaki z których składa się nazwa tagu
		while( $this->_index < $length )
		{
			$chr = $line[$this->_index];

			// nazwa tagu nie może mieć białych znaków
			if( $chr == ' ' || $chr == "\t" )
				break;
			// a kończy się na podaniu dwukropka
			else if( $chr == ':' )
			{
				$ends = true;
				break;
			}
			else
				$tag .= $chr;
			$this->_index++;
		}

		if( $this->_index >= $length )
			return $ends
				? $tag
				: null;

		// gdy na końcu tagu wykryte zostaną dwa dwukropki, nie przetwarzaj go
		if( $line[$this->_index] == ':' && $this->_index + 1 < $length && $line[$this->_index + 1] == ':' )
		{
			$line = substr_replace( $line, ':', $this->_index, 2 );
			return false;
		}

		return $ends
			? $tag
			: null;
	}

	// private function _LineDirective( string &$line, int $indent, int $length ): bool
	// {
	// 	print_r($line);
	// }

	private function _CheckComment( string &$line, int $indent, int $length ): bool
	{
		$ip2 = $indent + 2 == $length
			? ''
			: $line[$indent + 2];

		return
			$line[$indent + 0] == ':' &&
			$line[$indent + 1] == ':' &&
			(
				$ip2 == "\r" ||
				$ip2 == "\n" ||
				$ip2 == ' '  ||
				$ip2 == "\t"
			);
	}

	private function _WhitespaceCount( string &$line, int $length ): int
	{
		$index = 0;
		while( $index < $length )
			switch( $line[$index] )
			{
				case ' ':
				case "\t":
					++$index;
				break;
				default:
					return $index;
			}
		return -1;
	}
}

class WhenDirective
{
	public function __construct()
	{

	}

	public function Parse( $params ): string
	{

	}

	public function ImportanceLevel(): int
	{

	}
}

class IncludeDirective
{

}

class LinkDirective
{

}

class FootnoteDirective
{

}

class CounterDirective
{

}

class MakepageDirective
{

}
