<?php

	class Wikipedia {
		
		public static $detailId;

		private static function getPageId($Title) {

			$Title = str_replace(' ', '+', $Title);
			$link = 'http://en.wikipedia.org/w/api.php?action=query&format=json&prop=info&titles='.$Title;

			if(!($result = @file_get_contents($link,'r')))
				return 0;

			$result = json_decode($result, true);
			$keys = array_keys($result['query']['pages']);

			return $keys[0];

			}

		private static function getPageContent($Page) {

			if(is_numeric($Page))
				$title = 'pageid='.$Page;
			else
				$title = 'page='.str_replace(' ', '+', $Page);

			$link = 'http://en.wikipedia.org/w/api.php?action=parse&format=json&prop=text&'.$title;

			if(!($result = @file_get_contents($link,'r')))
				return 0;

			$result = json_decode($result, true);
			return $result['parse']['text']['*'];

			}

		public static function search($Query, $Filter) {

			$link = 'https://en.wikipedia.org/w/api.php?format=json&action=query&list=search&srsearch='.str_replace(' ', '+', $Query);

			if(!($result = @file_get_contents($link,'r')))
				return 0;

			$result = json_decode($result, true);

			for($i = 0; isset($result['query']['search'][$i]); $i++) {
				// echo $result['query']['search'][$i]['title'].' '.$Filter.'<br>';
				if(stripos($result['query']['search'][$i]['title'], $Filter) !== FALSE)
					return $result['query']['search'][$i]['title'];
				}

			return 0;

			}

		public static function searchItem($Type, $Id, $Query, $Filter) {

			$d = new Detail(array('Type' => $Type, 'ItemId' => $Id));

			if(($value = $d->get(self::$detailId)) == notFound) {

				if(!($title = self::search($Query, $Filter)))
					return 0;

				if(!($pageid = self::getPageId($title)))
					return 0;

				$d->set(self::$detailId, $pageid);

				return self::getPageContent($pageid);
			
				}
			else
				return self::getPageContent($value);

			}

		public static function searchSong($Song) {

			return self::searchItem(0, $Song->Row->id, $Song->printInline(), $Song->Row->SongName);

			}

		public static function searchArtist($Artist) {

			return self::searchItem(1, $Artist->Row->id, $Artist->printInline(), $Artist->printInline());

			}

		public static function searchAlbum($Album) {

			return self::searchItem(2, $Album->Row->id, $Album->printInline(), $Album->Row->AlbumName);

			}

		/*

		private static function wikipediaDescriptionParserOld($Query) {

			$Link = $this->searchOld($Query);

			if(!($code = @file_get_contents($Link,'r')))
				return 0;

			while($pos2-$pos<50)
				{$pos=strpos($code,"</table>",$pos2);
				$pos2=strpos($code,"table",$pos+3);
				}
			$max_words=63;$ok2=0;
			while($code[$pos]!='<'||$code[$pos+1]!='t')
				{if($code[$pos]==' ')
					{$words++;
					$temp=$temp.$code[$pos];
					}
				else
				if($code[$pos]=='<')
					while($code[$pos]!='>')
						$pos++;
				else
				if($code[$pos]=='[')
					while($code[$pos]!=']')
						$pos++;
				else
				if($code[$pos]=='('&&$code[$pos+1]=='p'&&(($code[$pos+2]=='r'&&$code[$pos+3]=='o')||
				($code[$pos+2]=='l'&&$code[$pos+3]=='a')))
					$pos=strpos($code,')',$pos);
				else
					if(ctype_alnum($code[$pos])||strstr("–/.,?![]()-=+*|;:@#$%&~{}",$code[$pos])
						||$code[$pos]=='"'||$code[$pos]=='\'')
							$temp=$temp.$code[$pos];
				if($words>$max_words)
					$ok2=1;
				if($ok2&&$code[$pos]=='.')
					{$n=strlen($temp)-1;
					for($i=$n;$temp[$i]!=' ';$i--);
					if($n-$i>=5)
						break;
					}
				$pos++;
				}
			//extension for too little text
			if($words<40)
				{$pos=strpos($code,"</table>",$pos);
				$ok3=0;
				while($code[$pos]!='<'||$code[$pos+1]!='t')
					{if($code[$pos]=='.'&&$ok3==0)
						$ok3=1;
					else
					if($code[$pos]==' '&&$ok3)
						{$words++;
						$temp=$temp.$code[$pos];
						}
					else
					if($code[$pos]=='<')
						while($code[$pos]!='>')
							$pos++;
					else
					if($code[$pos]=='[')
						while($code[$pos]!=']')
							$pos++;
					else
					if($code[$pos]=='('&&$code[$pos+1]=='p'&&(($code[$pos+2]=='r'&&$code[$pos+3]=='o')||
						($code[$pos+2]=='l'&&$code[$pos+3]=='a')))
							$pos=strpos($code,')',$pos);
					else
						if(ctype_alnum($code[$pos])||strstr("–/.,?![]()-=+*|;:@#$%&~{}",$code[$pos])
							||$code[$pos]=='"'||$code[$pos]=='\'')
								if($ok3)
									$temp=$temp.$code[$pos];
					if($words>$max_words)
						$ok2=1;
					if($ok2&&$code[$pos]=='.')
						{$n=strlen($temp)-1;
						for($i=$n;$temp[$i]!=' ';$i--);
						if($n-$i>=5)
							break;
						}
					$pos++;
					}
				}

			return $temp;

			}

		private static function searchOld($Query) {

			$Query = str_replace(' ', '+', $Query);

			$link = "http://en.wikipedia.org/w/index.php?search=" . $Query;

			if(!($code = @file_get_contents($link,'r')))
				return 0;

			$index = 0;
			$index = strpos($code, 'mw-search-results', $index);

			if(!$index)
				return 0;//$link;

			for($i = 1; $i < 10; $i++) {

				// Go to a new Search Result
				$index = strpos($code, 'mw-search-result-heading', $index);
				$endUrl = pr::inBetween($code, 'href="', '"', $index);

				break;

				}

			return "http://en.wikipedia.org" . $EndUrl;

			}

		*/
			
		}

?>