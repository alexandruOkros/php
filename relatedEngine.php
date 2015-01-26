<?php

	class relatedEngine {
		
		public $songId;

		public static $Threshold = 0.2;
		
		public function __construct($songId) {
			
			$this->songId = $songId;
			
			}
		
		private function insert($songB, $Strength) {
			
			// Check for space
			$total = 20;
			$item = self::feed(1, $total);
				
			if($total >= 20) {
				
				if($item[$total - 1]->Strength < $Strength)
					self::delete($item[$total - 1]->id);
				else
					return 0;
				
				}

			if(self::select($this->songId, $songB))
				return self::update($this->songId, $songB, $Strength);
			else
				return dq::insert(array('D' => 'Related', 'C' => array('A', 'B', 'Strength'), 'V' => array($this->songId, $songB, $Strength)));
			
			}

		public static function select($A, $B) {

			$result = dq::select(array('D' => 'Related', 'C' => array('A', 'B'), 'S' => array('=', '='), 'V' => array($A, $B)));
			$row = $result->fetch_object();
			return $row;

			}

		private static function update($A, $B, $Strength) {

			return dq::update(array('D' => 'Related', 'Cs' => array('A', 'B'), 'Vs' => array($A, $B), 'C' => array('Strength'), 'V' => array($Strength)));

			}
		
		public static function delete($Id) {
			
			return dq::delete(array('D' => 'Related', 'C' => array('id'), 'S' => array('='), 'V' => array($Id)));
			
			}
		
		public function add($songB) { // make private
			
			$vectorA = new tagVector($this->songId);
			$vectorB = new tagVector($songB);
			
			$Strength = $vectorA->similarity($vectorB);

			// Add to this song list
			self::insert($songB, $Strength);

			// Add to songB list
			$rs = new relatedEngine($songB);
			$rs->insert($this->songId, $Strength);
			
			return 0;
			
			}
		
		public function feed($Start, & $Limit) {
			
			// Return related songs (raw data)
			return Feed::get(array('D' => 'Related', 'Q' => 'A = '.$this->songId,'O' => 'ORDER BY Strength DESC'), $Start, $Limit);
			
			}

		public function feedC($Start, & $Limit, & $Title) {

			$tmp = $Limit;
			$item = self::feed($Start, $Limit);

			if($Limit == 0 || $item[0]->Strength < self::$Threshold) {
				
				// Fetch items
				$song = new Song($this->songId);
				$Limit = $tmp;
				$item = Feed::get(array('D' => 'SongDatabase', 'Q' => '(ArtistId = '.$song->Row->ArtistId.' OR FeaturingId = '.$song->Row->ArtistId.') AND id <> '.$this->songId, 'O' => 'ORDER BY Views DESC'), $Start, $Limit);

				// Load Items
				for($i = 0; $i < $Limit; $i++) 
					$result[$i] = new Song($item[$i]->id);

				// Title
				$Title = 'More from '.$song->Artist->Row->ArtistName;

				}
			else {

				// Load Items
				for($i = 0; $i < $Limit; $i++)
					if($item[$i]->Strength > self::$Threshold)
						$result[$i] = new Song($item[$i]->B);
					else
						break;

				// Title
				$Title = 'Related Tracks';

				$Limit = $i;

				}

			return $result;

			}

		public static function refresh() {

			//$result = dq::delete(array('D' => 'AiQuery', 'C' => array('TaskId'), 'S' => array('='), 'V' => array(5)));
	
			$result = dq::select(array('D' => 'SongDatabase', 'C' => array(), 'S' => array(''), 'V' => array(), 'O' => 'ORDER BY id ASC'));
			
			while ($row = $result->fetch_object()) {
				$aq = new aiQuery(array('ItemId' => $row->id, 'UserId' => 0, 'TaskId' => 5, 'Priority' => 'low'));
				$aq->insert();
				$aq->update('Progress', 1);
				}

			}

		public function backgroundWork($Aq) {
			
			if($Aq->Row->Progress == 0) {
				
				//Build tagVector for this song
				$vector = new tagVector($this->songId);
				if($vector->build())
					return array('x' => 2, 'Progress' => 1);
				else
					return array('x' => 0);
				
				}
			else {
				
				// Compare current song with all older songs
				$result = dq::select(array('D' => 'SongDatabase', 'C' => array('id', 'id'), 'S' => array('>=', '<'), 'V' => array($Aq->Row->Progress, $this->songId), 'O' => 'ORDER BY id ASC'));
				
				$total = 10;
				$song = new Song($this->songId);
			
				for($i = 0; $i < $total && $row = $result->fetch_object(); $i++) {
					
					if(!$song->equals(new Song($row->id)))
					if(tagVector::exists($row->id))
						self::add($row->id);
					
					}

				if($i == $total)
					return array('x' => 2, 'Progress' => ($row->id + 1));	
				else
					return array('x' => 1);
				
				}
			
			}
			
	}
	
?>