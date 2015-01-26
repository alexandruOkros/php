<?php

	class pr {

		public static function lower($String) {

			// Return lower-case string
			return strtolower($String);

			}

		public static function serialize($String) {

			// Transform special characters to words
			$TranslateTable = array('&' => 'and');
			return str_replace(array_keys($TranslateTable), array_values($TranslateTable), $String);

			}

		public static function numberLetter($String) {

			// Keep only numbers and letter from String
			return preg_replace("/[^a-zA-Z0-9]+/", '', $String);

			}

		public static function numberLetterSpace($String) {

			// Same as the above one but transforms special characters into spaces
			return preg_replace('/[^a-zA-Z0-9]+/', ' ', $String);

			}

		public static function splitInWords($String) {

			$String = trim($String);
			return preg_split('/\s+/', $String);

			}

		public static function inBetween($Code, $A, $B, & $Index) {

			$Index = strpos($Code, $A, $Index);
			if(!$Index) return notFound;
			$FinalIndex = strpos($Code, $B, $Index + strlen($A));
			if(!$FinalIndex) return notFound;
			
			return substr($Code, $Index + strlen($A), $FinalIndex - $Index - strlen($A));

			}

		public static function badWordFilter($String) {

			$List = self::splitInWords($String);

			$A = '';

			for($i = 0; $i < count($List); $i++) {
				
				$result = dq::select(array('D' => 'BadWord', 'C' => array('Word'), 'S' => array('='), 'V' => array($List[$i])));
				if($result->fetch_object())
					$A .= '***';
				else
					$A .= $List[$i];

				if($i + 1 < count($List))
					$A .= ' ';

				}

			return $A;

			}

		public static function translateLatinCharacters($String) {

			$TranslateTable = array('á' => 'a', 'Á' => 'A', 'à' => 'a', 'À' => 'A', 'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A', 'å' => 'a', 'Å' => 'A', 'ã' => 'a', 'Ã' => 'A', 'ą' => 'a', 'Ą' => 'A', 'ā' => 'a', 'Ā' => 'A', 'ä' => 'ae', 'Ä' => 'AE', 'æ' => 'ae', 'Æ' => 'AE', 'ḃ' => 'b', 'Ḃ' => 'B', 'ć' => 'c', 'Ć' => 'C', 'ĉ' => 'c', 'Ĉ' => 'C', 'č' => 'c', 'Č' => 'C', 'ċ' => 'c', 'Ċ' => 'C', 'ç' => 'c', 'Ç' => 'C', 'ď' => 'd', 'Ď' => 'D', 'ḋ' => 'd', 'Ḋ' => 'D', 'đ' => 'd', 'Đ' => 'D', 'ð' => 'dh', 'Ð' => 'Dh', 'é' => 'e', 'É' => 'E', 'è' => 'e', 'È' => 'E', 'ĕ' => 'e', 'Ĕ' => 'E', 'ê' => 'e', 'Ê' => 'E', 'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ė' => 'e', 'Ė' => 'E', 'ę' => 'e', 'Ę' => 'E', 'ē' => 'e', 'Ē' => 'E', 'ḟ' => 'f', 'Ḟ' => 'F', 'ƒ' => 'f', 'Ƒ' => 'F', 'ğ' => 'g', 'Ğ' => 'G', 'ĝ' => 'g', 'Ĝ' => 'G', 'ġ' => 'g', 'Ġ' => 'G', 'ģ' => 'g', 'Ģ' => 'G', 'ĥ' => 'h', 'Ĥ' => 'H', 'ħ' => 'h', 'Ħ' => 'H', 'í' => 'i', 'Í' => 'I', 'ì' => 'i', 'Ì' => 'I', 'î' => 'i', 'Î' => 'I', 'ï' => 'i', 'Ï' => 'I', 'ĩ' => 'i', 'Ĩ' => 'I', 'į' => 'i', 'Į' => 'I', 'ī' => 'i', 'Ī' => 'I', 'ĵ' => 'j', 'Ĵ' => 'J', 'ķ' => 'k', 'Ķ' => 'K', 'ĺ' => 'l', 'Ĺ' => 'L', 'ľ' => 'l', 'Ľ' => 'L', 'ļ' => 'l', 'Ļ' => 'L', 'ł' => 'l', 'Ł' => 'L', 'ṁ' => 'm', 'Ṁ' => 'M', 'ń' => 'n', 'Ń' => 'N', 'ň' => 'n', 'Ň' => 'N', 'ñ' => 'n', 'Ñ' => 'N', 'ņ' => 'n', 'Ņ' => 'N', 'ó' => 'o', 'Ó' => 'O', 'ò' => 'o', 'Ò' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ő' => 'o', 'Ő' => 'O', 'õ' => 'o', 'Õ' => 'O', 'ø' => 'oe', 'Ø' => 'OE', 'ō' => 'o', 'Ō' => 'O', 'ơ' => 'o', 'Ơ' => 'O', 'ö' => 'oe', 'Ö' => 'OE', 'ṗ' => 'p', 'Ṗ' => 'P', 'ŕ' => 'r', 'Ŕ' => 'R', 'ř' => 'r', 'Ř' => 'R', 'ŗ' => 'r', 'Ŗ' => 'R', 'ś' => 's', 'Ś' => 'S', 'ŝ' => 's', 'Ŝ' => 'S', 'š' => 's', 'Š' => 'S', 'ṡ' => 's', 'Ṡ' => 'S', 'ş' => 's', 'Ş' => 'S', 'ș' => 's', 'Ș' => 'S', 'ß' => 'SS', 'ť' => 't', 'Ť' => 'T', 'ṫ' => 't', 'Ṫ' => 'T', 'ţ' => 't', 'Ţ' => 'T', 'ț' => 't', 'Ț' => 'T', 'ŧ' => 't', 'Ŧ' => 'T', 'ú' => 'u', 'Ú' => 'U', 'ù' => 'u', 'Ù' => 'U', 'ŭ' => 'u', 'Ŭ' => 'U', 'û' => 'u', 'Û' => 'U', 'ů' => 'u', 'Ů' => 'U', 'ű' => 'u', 'Ű' => 'U', 'ũ' => 'u', 'Ũ' => 'U', 'ų' => 'u', 'Ų' => 'U', 'ū' => 'u', 'Ū' => 'U', 'ư' => 'u', 'Ư' => 'U', 'ü' => 'ue', 'Ü' => 'UE', 'ẃ' => 'w', 'Ẃ' => 'W', 'ẁ' => 'w', 'Ẁ' => 'W', 'ŵ' => 'w', 'Ŵ' => 'W', 'ẅ' => 'w', 'Ẅ' => 'W', 'ý' => 'y', 'Ý' => 'Y', 'ỳ' => 'y', 'Ỳ' => 'Y', 'ŷ' => 'y', 'Ŷ' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y', 'ź' => 'z', 'Ź' => 'Z', 'ž' => 'z', 'Ž' => 'Z', 'ż' => 'z', 'Ż' => 'Z', 'þ' => 'th', 'Þ' => 'Th', 'µ' => 'u', 'а' => 'a', 'А' => 'a', 'б' => 'b', 'Б' => 'b', 'в' => 'v', 'В' => 'v', 'г' => 'g', 'Г' => 'g', 'д' => 'd', 'Д' => 'd', 'е' => 'e', 'Е' => 'e', 'ё' => 'e', 'Ё' => 'e', 'ж' => 'zh', 'Ж' => 'zh', 'з' => 'z', 'З' => 'z', 'и' => 'i', 'И' => 'i', 'й' => 'j', 'Й' => 'j', 'к' => 'k', 'К' => 'k', 'л' => 'l', 'Л' => 'l', 'м' => 'm', 'М' => 'm', 'н' => 'n', 'Н' => 'n', 'о' => 'o', 'О' => 'o', 'п' => 'p', 'П' => 'p', 'р' => 'r', 'Р' => 'r', 'с' => 's', 'С' => 's', 'т' => 't', 'Т' => 't', 'у' => 'u', 'У' => 'u', 'ф' => 'f', 'Ф' => 'f', 'х' => 'h', 'Х' => 'h', 'ц' => 'c', 'Ц' => 'c', 'ч' => 'ch', 'Ч' => 'ch', 'ш' => 'sh', 'Ш' => 'sh', 'щ' => 'sch', 'Щ' => 'sch', 'ъ' => '', 'Ъ' => '', 'ы' => 'y', 'Ы' => 'y', 'ь' => '', 'Ь' => '', 'э' => 'e', 'Э' => 'e', 'ю' => 'ju', 'Ю' => 'ju', 'я' => 'ja', 'Я' => 'ja');
			$String = str_replace(array_keys($TranslateTable), array_values($TranslateTable), $String);

		    return $String;

			}

		public static function translateSpecialCharacters($String) {

			$HtmlTag = array('&#39;', '&apos;', '&#38;', '&amp;', '&#34;', '&quot;', '&#60;', '&lt;', '&#62;', '&gt;', '--'); // Also extra stuff
			$SpecialCharacter = array('\'', '\'', '&', '&', '"', '"', '<', '<', '>', '>', '-');

			$String = str_replace($HtmlTag, $SpecialCharacter, $String);

			return $String;

			}

		public static function equal($A, $B) {

			if(!$A || !$B)
				return 0;

			if(strlen($A) < strlen($B)) {
				$tmp = $A; $A = $B; $B = $tmp;
				}

			if(stripos($A, $B) !== FALSE)
				return 1;

			return 0;

			}

		public static function normaliseSpaces($String) {

			if($String == '')
				return $String;

			$String = preg_replace("/\s+/", " ", $String);
			$String = trim($String);

			return $String;

			}

		public static function normalise($String) {

			if($String == '')
				return $String;

			$String = self::normaliseSpaces($String);

			// Upper first word
			$String = ucwords($String);

			// And quotes
			$String = trim($String, '\'');

			// End line problem
			$Index = strpos($String, '- ');
			if($Index)
				$String = substr($String, 0, $Index);

			return $String;

			}

		public static function removeDuplicateBackslash($string) {

			return preg_replace('/\\\\+/', "\\", $string);

			}

		public static function getArtistName($String) {

			$CharactersInArtistName = '0123456789.$-!* \'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$CharactersNotInArtistName = array(',', '"', '|', '\\', '/', '?', '_');
			$ArtistName = '';

			$String = self::normalise($String);

			for($i = 0; $i < strlen($String); $i++)
				if(strpos($CharactersInArtistName, $String[$i]) || $String[$i] == '0')
					break;
				
			for(; $i < strlen($String); $i++)
				if(strpos($CharactersInArtistName, $String[$i]) || $String[$i] == '0')
					$ArtistName = $ArtistName . $String[$i];
				else
					break;

			$ArtistName = self::normalise($ArtistName);

			if(str_word_count($ArtistName, 0) > 5)
				$ArtistName = '';

			return $ArtistName;

			}

		public static function validSongName($String) {

			$CharactersInSongName = '0123456789-.,?!* \'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			
			for($i = 0; $i < strlen($String); $i++)
				if(!strpos($CharactersInSongName, $String[$i]) && $String[$i] != '0')
					return 0;

			return 1;

			}

		public static function getSongName($String) {

			$CharactersInSongName = '0123456789-.,?!* \'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$SongName = '';

			$String = self::normalise($String);

			for($i = 0; $i < strlen($String); $i++)
				if(strpos($CharactersInSongName, $String[$i]) || $String[$i] == '0')
					break;
				
			for(; $i < strlen($String); $i++)
				if(strpos($CharactersInSongName, $String[$i]) || $String[$i] == '0')
					$SongName = $SongName . $String[$i];
				else
					break;

			$SongName = self::normalise($SongName);

			return $SongName;

			}

		public static function disectTitle($Title) {

			$Null = array('', $Title, '');
			$ArtistName = '';
			$SongName = '';
			$FeaturingName = '';

			$FeaturingExpressions = array(' feat ', ' feat. ', ' (feat ', ' (feat. ', ' ft ', ' ft. ', '(ft ', '(ft. ', ' featuring ', ' featuring. ', '(featuring ', '(featuring. ', ' vs ', ' vs. ', '(vs ', '(vs. ', ' x ');
			$WordsNotNeeded = array('original lyrics', 'original lyric', 'lyrics', 'lyric', 'Official Music Video', 'music video', 'official video', 'with lyrics', 'radio edit', 'official audio', 'hd');

			$Title = $Title . ' ';
			// Solve special characters
			$Title = self::translateLatinCharacters($Title);
			$Title = self::translateSpecialCharacters($Title);

			// Erase words not needed
			$Title = str_ireplace($WordsNotNeeded, '', $Title);

			// Normalise
			$Title = str_ireplace($FeaturingExpressions, ' featuring ', $Title);

			// Solve Featuring Problem
			$FeaturingIndex = strpos($Title, ' featuring ', 3);
			if(!$FeaturingIndex) {
				$Title = str_ireplace(array('& ', '&. '), ' featuring ', $Title);
				$FeaturingIndex = strpos($Title, ' featuring ', 3);
				}

			// Solve Line Problem
			$LineIndex = strpos($Title, '- ', 3);
			if(!$LineIndex) {
				$LineIndex = strpos($Title, '-', 3);
				if(!$LineIndex) {
					$LineIndex = strpos($Title, ': ', 3);
					if(!$LineIndex)
						return $Null;
					}
				}

			
			// Find Artist/Song/Featuring Index
			$Stop = $Stop2 = $Stop3 = strlen($Title);
			if($FeaturingIndex) {

				$Stop = min($LineIndex, $FeaturingIndex);
				$FeaturingIndex += 11;
				
				$FeaturingIndex2 = @strpos($Title, ' featuring ', ($FeaturingIndex));
				if($FeaturingIndex2)
					$Stop2 = $FeaturingIndex2;

				if($LineIndex > $FeaturingIndex)
					$Stop2 = min($Stop2, $LineIndex);

				}
			else
				$Stop = $LineIndex;

			$LineIndex++;
			$SongIndex = strpos($Title, ' featuring ', $LineIndex);
			if($SongIndex)
				$Stop3 = min($Stop3, $SongIndex);


			// Find ArtistName
			$ArtistName = self::getArtistName(substr($Title, 0, $Stop));

			// Find FeaturingName
			if($FeaturingIndex)
				$FeaturingName = self::getArtistName(substr($Title, $FeaturingIndex, ($Stop2 - $FeaturingIndex)));

			// Find Song
			$SongName = self::getSongName(substr($Title, $LineIndex, ($Stop3 - $LineIndex)));


			if($ArtistName == '' || $SongName == '')
				return $Null;

			return array($ArtistName, $SongName, $FeaturingName);

			}
		
		public static function disectTitleOld($Title) {

			$Null = array('', $Title, '');
			$ArtistName = '';
			$SongName = '';
			$FeaturingName = '';

			$FeaturingExpressions = array(' feat ', ' feat. ', ' (feat ', ' (feat. ', ' ft ', ' ft. ', '(ft ', '(ft. ', ' featuring ', ' featuring. ', '(featuring ', '(featuring. ', ' vs ', ' vs. ', '(vs ', '(vs. ', ' x '); // case insensitive
			$WordsNotNeeded = array('original lyrics', 'original lyric', 'lyrics', 'lyric', 'Official Music Video', 'music video', 'official video', 'with lyrics', 'radio edit', 'official audio');

			$Title = $Title . ' ';
			// Solve special characters
			$Title = self::translateLatinCharacters($Title);
			$Title = self::translateSpecialCharacters($Title);

			// Erase words not needed
			$Title = str_ireplace($WordsNotNeeded, '', $Title);

			// Normalise
			$Title = str_ireplace($FeaturingExpressions, ' featuring ', $Title);

			// Solve Featuring Problem
			$FeaturingIndex = strpos($Title, ' featuring ', 3);
			if(!$FeaturingIndex) {
				$Title = str_ireplace(array('& ', '&. '), ' featuring ', $Title);
				$FeaturingIndex = strpos($Title, ' featuring ', 3);
				}

			// Solve Line Problem
			$LineIndex = strpos($Title, '- ', 3);
			if(!$LineIndex) {
				$LineIndex = strpos($Title, '-', 3);
				if(!$LineIndex) {
					$LineIndex = strpos($Title, ': ', 3);
					if(!$LineIndex)
						return $Null;
					}
				}

			// Find Artist/Song/Featuring Index
			$Stop = $Stop2 = $Stop3 = strlen($Title);
			if($FeaturingIndex) {

				$Stop = min($LineIndex, $FeaturingIndex);
				$FeaturingIndex += 11;
				
				$FeaturingIndex2 = @strpos($Title, ' featuring ', ($FeaturingIndex + 3));
				if($FeaturingIndex2)
					$Stop2 = $FeaturingIndex2;

				if($LineIndex > $FeaturingIndex)
					$Stop2 = min($Stop2, $LineIndex);

				}
			else
				$Stop = $LineIndex;

			$LineIndex++;
			$SongIndex = strpos($Title, ' featuring ', $LineIndex);
			if($SongIndex)
				$Stop3 = min($Stop3, $SongIndex);


			// Find ArtistName
			$ArtistName = self::getArtistName(substr($Title, 0, $Stop));

			// Find FeaturingName
			if($FeaturingIndex)
				$FeaturingName = self::getArtistName(substr($Title, $FeaturingIndex, ($Stop2 - $FeaturingIndex)));

			// Find Song
			$SongName = self::getSongName(substr($Title, $LineIndex, ($Stop3 - $LineIndex)));


			if($ArtistName == '' || $SongName == '')
				return $Null;

			return array($ArtistName, $SongName, $FeaturingName);

			}

		public static function disectTitleOld2($Title) {

			error_reporting(E_ERROR | E_PARSE);

			$Exceptions = array('lyric','lyrics','&quot','!','\"','\'','&','#',';','0','1','2','3','4','5','6','7','8','9');
			$Exceptions2 = array('&amp;',',');
			$Exceptions3 = array(':');

		    $Title = str_ireplace($Exceptions2,' ft. ',$Title);
		    $Title = str_ireplace($Exceptions3,' - ',$Title);
		    $Title = str_ireplace($Exceptions,'',$Title);
		    
			$Second = array(1=>'feat ',2=>'featuring',3=>' ft ',4=>'ft.',5=>'feat.');
			$pos = strpos($Title,'- ',3);
			for($i=1;$i<=5;$i++) {
				$e = stripos($Title,$Second[$i]);
				if($e)
			     $pos = min($pos,$e);
				}
			$Artist = trim(substr($Title,0,$pos));

			$pos = strpos($Title,'- ',3)+1;
			for($i=1,$posf=300;$i<=5;$i++) {
				$e = stripos($Title,$Second[$i],$pos);
				if($e)
			     $posf = min($posf,$e);
				}
			if(!$posf) $posf=strlen($Title);
			$Song = trim(substr($Title,$pos,$posf-$pos));

			for($posf=0;(@$Song{$posf}==' '||@ctype_alpha($Song{$posf}));$posf++);
			$Song = trim(substr($Song,0,$posf));

		     for($i=1,$pos=300;$i<=5;$i++) {
				$e = stripos($Title,$Second[$i]);
				if($e)
			     $pos = min($pos,$e);
				}
			if($pos!=300) {
				for($pos=strpos($Title,' ',$pos),$posf=$pos;(($Title[$posf]=='-'&&$Title[$posf+1]!=' ')||$Title[$posf]=='.'||$Title[$posf]==' '||ctype_alpha($Title[$posf]));$posf++);
			 	$Featuring = trim(substr($Title,$pos,$posf-$pos));
				}

			return array($Artist,$Song,$Featuring);

			}

		}


?>