<?php

	class tagVector { // Vector of tags for songs
		
		public $songId, $A;
		public static $detailId = 1;
		
		public function __construct($songId) {
			
			$this->songId = $songId;
			self::select();
			
			}
		
		public function select() {
			
			$result = dq::select(array('D' => 'SongTags', 'C' => array('SongId'), 'S' => array('='), 'V' => array($this->songId), 'O' => 'ORDER BY TagId ASC'));
			
			$this->A = array();
			while($row = $result->fetch_object())		
				array_push($this->A, $row);
			
			}
		
		public static function exists($songId) {
			
			// Check if the song's tag vector was built
			$detail = new Detail(array('Type' => 0, 'ItemId' => $songId));
			$response = $detail->get(self::$detailId);
			
			return ($response == 1);
			
			}
		
		public function magnitude() {
			
			// The Length of the vector
			// Add for artist and album field
			$result = 200;
				
			foreach($this->A as $row)
				$result += ($row->Value * $row->Value);
				
			return sqrt($result);
				
			}
		
		public function dot($Vector) {
			
			// Formula for inner product for vector
			$result = 0;

			// Add more fields like artist and album
			$songA = new Song($this->songId);
			$songB = new Song($Vector->songId);

			if($songA->Row->ArtistId == $songB->Row->ArtistId)
				$result += 100;

			if($songA->Row->AlbumId == $songB->Row->AlbumId && $songA->Row->AlbumId > 0)
				$result += 100;

			$i = 0;
			$size = count($Vector->A);
			
			foreach($this->A as $row) {
				
				for(; $i < $size && $Vector->A[$i]->TagId < $row->TagId; $i++);

				if($row->TagId == $Vector->A[$i]->TagId)
					$result += ($row->Value * $Vector->A[$i]->Value);
				
				}
			
			return $result;
			
			}
		
		public function similarity($Vector) {
			
			// Cosine formula
			return $this->dot($Vector) / ($this->magnitude() * $Vector->magnitude());
			
			}
		
		public function build() {
			
			// Build the Tag Vector for a song
			$song = new Song($this->songId);
				
			// Wikipedia search
			if(!($code = Wikipedia::searchSong($song)))
				$response = 0;
			else {

				// Start to parse
				$start = 0;
				$end = 0;

				// Skip table
				if($start = strpos($code, 'infobox vevent', 0))
					$start = strpos($code, '</table>', $start);

				$end = min(strpos($code, 'id="See_also"', $start), strpos($code, 'id="References"', $start));

				// Get relevant text and proccess it
				$text = strip_tags(substr($code, $start, ($end - $start) + 1));
				$text = pr::translateLatinCharacters($text);
				$text = pr::translateSpecialCharacters($text);
				$text = pr::serialize($text);
				$text = pr::numberLetterSpace($text);
				$list = pr::splitInWords($text);

				// Search for tags
				$cse = new costumSearchEngine(array('UserId' => 0, 'Type' => 0));

				$tags = array();
				$size = count($list);

				for($i = 0; $i < $size; $i = $k + 1) {

					$word = '';
					$last = 0;
					$k = $i;

					for($j = $i; $j < $size; $j++) {

						$word .= costumSearchEngine::hash($list[$j]);
						$one = 1; $result = $cse->search($word, 1, $one);

						if($result) {
							$last = $result[0];
							$k = $j;
							}
						else
						if(!$cse->findWord($word))
							break;

						}

					if($last != 0)
						$tags[$last] = min($tags[$last] + 0.5, 10);
					/*else // Print words not used as tags
						for($h = $i; $h <= $k; $h++)
							echo $list[$h].endLine; // */

					}

				// Propagate Weight from children to parents
				foreach($tags as $tagId => $value) {
					
					$tag = new Tag($tagId);
					if($father = $tag->root()) {
						$tags[$father->Row->id] = min(10, $tags[$father->Row->id] + $father->Row->Weight * $value);
						unset($tags[$tagId]);
						}

					}

				/*foreach($tags as $tagId => $value) { // Print tags found
					$tag = new Tag($tagId);
					echo $tag->printInline().' - '.$value.'<br>';
					} // */

				// Delete all previous tags
				$result = dq::delete(array('D' => 'SongTags', 'C' => array('SongId'), 'S' => array('='), 'V' => array($this->songId)));

				// Save all tags
				$song = new Song($this->songId);
				foreach($tags as $tagId => $value)
					if($value)
						$song->tag($tagId, $value);

				$response = 1;
				
				}
			
			$detail = new Detail(array('Type' => 0, 'ItemId' => $this->songId));
			$detail->set(self::$detailId, $response);
			
			return $response;
			
			}
	
	}
	
?>