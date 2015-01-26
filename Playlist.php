<?php

	class Playlist {

		public $Row, $sizeLimit = 1000;

		public function __construct($Data) {

			if(is_numeric($Data)) {

				$this->Row->id = $Data;
				if($this->Row->id != 0)
					$this->select();
				
				}
			else {

				$this->Row->UserId = $Data['UserId'];
				$this->Row->PlaylistName = $Data['PlaylistName'];
				$this->select();

				}

			if(!$this->Row->UserId)
				$this->Row->UserId = 0;

			}

		public function prepareThumbnail() {
			
			$total = 1;
			$item = self::feed(1, $total);
			
			if($total == 1) { // If Playlist not empty
				$song = new Song($item[0]);
				if($song->Row->ThumbnailId)
					$this->Thumbnail = new Thumbnail($song->Row->ThumbnailId);
				else
					$this->Thumbnail = new blankThumbnail(0);
				}
			else
				$this->Thumbnail = new blankThumbnail(0);

			}

		public function insert() {

			if($this->Row->id)
				return 0;
			
			if(dq::insert(array('D' => 'PlaylistDatabase', 'C' => array('UserId', 'PlaylistName', 'Share'), 'V' => array($this->Row->UserId, $this->Row->PlaylistName, 0))))
				return self::select();
			
			}
		
		public function select() {

			if($this->Row->id)
				$result = dq::select(array('D' => 'PlaylistDatabase', 'C' => array('id'), 'S' => array('='), 'V' => array($this->Row->id)));
			else
				$result = dq::select(array('D' => 'PlaylistDatabase', 'C' => array('UserId', 'PlaylistName'), 'S' => array('=', '='), 'V' => array($this->Row->UserId, $this->Row->PlaylistName)));

			if($row = $result->fetch_object()) {
				$this->Row = $row;
				$this->prepareThumbnail();
				return 1;
				}

			return 0;

			}

		public function create() {

			$response = self::validateName($this->Row->PlaylistName);

			if($response['x'] == 0)
				return $response;

			if($this->Row->id)
				return array('x' => 0, 'message' => 'You already have a playlist with that name.');
			
			if(self::insert()) {

				$cse = new costumSearchEngine(array("UserId" => $this->Row->UserId, "Type" => 1));
				$cse->addString($this->Row->PlaylistName, $this->Row->id);

				return array('x' => 1, 'message' => 'Playlist saved.');
				
				}
			else
				return array('x' => 0, 'message' => errorMessage);

			}

		public function rename($newName) {

			if(!$this->Row->id)
				return array('x' => 0, 'message' => errorMessage);

			$response = self::validateName($newName);

			if($response['x'] == 0)
				return $response;

			if($this->Row->PlaylistName == 'Queue')
				return array('x' => 0, 'message' => 'You cannot rename Queue.');

			$tmp = new Playlist(array('UserId' => $this->Row->UserId, 'PlaylistName' => $newName));
			if($tmp->Row->id)
				return array('x' => 0, 'message' => 'You already have a playlist with that name.');
			
			dq::update(array('D' => 'PlaylistDatabase', 'Cs' => array('id'), 'Vs' => array($this->Row->id), 'C' => array('PlaylistName'), 'V' => array($newName)));
			
			$cse = new costumSearchEngine(array("UserId" => $this->Row->UserId, "Type" => 1));
			$cse->updateItem($this->Row->PlaylistName, $newName, $this->Row->id);

			return array('x' => 1, 'message' => 'Playlist renamed.');

			}

		public static function validateName($Name) {

			$message = '';

			if(strlen($Name) == 0)
				$message = 'Name is empty.';
			else
			if(strlen($Name) > 30)
				$message = 'Playlist name can have maximum 30 characters.';
			else
			if($Name != pr::numberLetter($Name))
				$message = 'Only characters and digits are allowed in name.';
			
			return array('x' => (strlen($message) == 0 ? 1 : 0), 'message' => $message);

			}

		public function update($Column, $Value) {

			if(dq::update(array('D' => 'PlaylistDatabase', 'Cs' => array('id'), 'Vs' => array($this->Row->id), 'C' => array($Column), 'V' => array($Value)))) {
				$this->Row->$Column = $Value;
				return 1;
				}

			return 0;

			}

		public function drop() {

			self::update('Size', 0);
			return dq::delete(array('D' => 'PlaylistTracklist', 'C' => array('PlaylistId'), 'S' => array('='), 'V' => array($this->Row->id)));
			
			}
		
		public function delete() {

			if(!$this->Row->id)
				return array('x' => 0, 'message' => errorMessage);

			if(dq::delete(array('D' => 'PlaylistDatabase', 'C' => array('id'), 'S' => array('='), 'V' => array($this->Row->id)))) {

				$cse = new costumSearchEngine(array("UserId" => $this->Row->UserId, "Type" => 1));
				$cse->deleteString($this->Row->PlaylistName, $this->Row->id);
				
				self::drop();
				return array('x' => 1, 'message' => 'Playlist deleted.');

				}

			return array('x' => 0, 'message' => errorMessage);

			}
		
		public function share() {

			if($this->Row->PlaylistName == 'Queue')
				return array('x' => 0, 'message' => 'You cannot share Queue.');

			$message = ($this->Row->Share == 0 ? $this->Row->PlaylistName.' is now shared with your friends.' : $this->Row->PlaylistName.' is now private.');
			
			if($this->update('Share', (1 - $this->Row->Share)))
				return array('x' => 1, 'message' => $message);
			else
				return array('x' => 0, 'message' => errorMessage);
			
			}

		public function selectTrack($songId) {

			if(!$this->Row->id)
				return array('x' => 0, 'message' => errorMessage);
			
			$result = dq::select(array('D' => 'PlaylistTracklist', 'C' => array('PlaylistId', 'SongId'), 'S' => array('=', '=', '='), 'V' => array($this->Row->id, $songId)));

			if($row = $result->fetch_object())
				return array('x' => 1, 'Row' => $row);

			return array('x' => 0);

			}

		public function insertTrack($songId) {

			if(!$this->Row->id)
				return array('x' => 0, 'message' => errorMessage);

			if($this->size() >= $this->sizeLimit)
				return array('x' => 0, 'message' => 'Playlist maximum size: '.$sizeLimit.' tracks.');

			$response = $this->selectTrack($songId);

			if($response['x'] == 1)
				return array('x' => 0, 'message' => 'Track is already in '.$this->Row->PlaylistName.'.');
			else {
				
				$result = dq::insert(array('D' => 'PlaylistTracklist', 'C' => array('UserId', 'PlaylistId', 'SongId', 'SongOrder', 'ShuffleOrder'), 'V' => array($this->Row->UserId, $this->Row->id, $songId, ($this->Row->PlaylistIndex + 1), ($this->Row->PlaylistIndex + 1))));

				if($result) {

					$result = dq::update(array('D' => 'PlaylistDatabase', 'Cs' => array('id'), 'Vs' => array($this->Row->id), 'C' => array('PlaylistIndex', 'Size'), 'V' => array(($this->Row->PlaylistIndex + 1), ($this->Row->Size + 1))));
					$this->select();
					return array('x' => 1, 'message' => 'Track added to '.$this->Row->PlaylistName.'.');

					}

				return array('x' => 0, 'message' => errorMessage);

				}

			}

		public function updateTrack($Index, $songId) {

			return dq::update(array('D' => 'PlaylistTracklist', 'Cs' => array('PlaylistId', 'SongOrder'), 'Vs' => array($this->Row->id, $Index), 'C' => array('SongId'), 'V' => array($songId)));
		
			}

		public function deleteTrack($songId) {

			$response = $this->selectTrack($songId);

			if($response['x']) {
				
				$result = dq::delete(array('D' => 'PlaylistTracklist', 'C' => array('id'), 'S' => array('='), 'V' => array($response['Row']->id)));

				if($result) {
					$result = dq::update(array('D' => 'PlaylistDatabase', 'Cs' => array('id'), 'Vs' => array($this->Row->id), 'C' => array('Size'), 'V' => array(($this->Row->Size - 1))));
					$this->select();
					return array('x' => 1, 'message' => 'Track deleted from '.$this->Row->PlaylistName.'.');
					}

				}

			return array('x' => 0, 'message' => errorMessage);

			}

		public function feed($Start, & $Limit) {

			$result = dq::select(array('D' => 'PlaylistTracklist', 'C' => array('PlaylistId'), 'S' => array('='), 'V' => array($this->Row->id), 'O' => 'ORDER BY SongOrder ASC LIMIT '.($Start - 1).', '.$Limit));

			for($i = 0; $row = $result->fetch_object(); $i++)
				$item[$i] = $row->SongId;

			$Limit = $i;
			return $item;

			}
		
		public function feedShuffle($Start, & $Limit) {
			
			$result = dq::select(array('D' => 'PlaylistTracklist', 'C' => array('PlaylistId'), 'S' => array('='), 'V' => array($this->Row->id), 'O' => 'ORDER BY ShuffleOrder ASC LIMIT '.($Start - 1).', '.$Limit));
		
			for($i = 0; $row = $result->fetch_object(); $i++)
				$item[$i] = $row->SongId;
		
				$Limit = $i;
				return $item;
			
			}
	
		public function size() {

			return $this->Row->Size;

			}

		public function shuffle() {

			$Size = $this->size();
			
			$result = dq::select(array('D' => 'PlaylistTracklist', 'C' => array('UserId', 'PlaylistId'), 'S' => array('=', '='), 'V' => array($this->Row->UserId, $this->Row->id), 'O' => 'ORDER BY id ASC'));

			for($i = 1; $i <= $Size; $i++)
				$Used[$i] = false;

			for($i = 1; $i <= $Size; $i++) {

				$k = mt_rand(1, $Size - $i + 1);

				for($j = 1, $count = 0; $count < $k; $j++)
					if(!$Used[$j])
						$count++;

				$k = $j - 1; $Used[$k] = true;
				$row = $result->fetch_object();

				dq::update(array('D' => 'PlaylistTracklist', 'Cs' => array('id'), 'Vs' => array($row->id), 'C' => array('ShuffleOrder'), 'V' => array($k)));

				}

			return 1;

			}
		
		public function copy(Playlist $Playlist) {
					
				// Maximum 200
				// We suppose that they have same number of tracks, same song order
				// We just update the songId from songOrder 1 to $total
				
				// First empty destination Playlist
				self::drop();
					
				$total = 200;
				$item = $Playlist->feed(1, $total);
					
				for($i = 0; $i < $total; $i++)
					$this->insertTrack($item[$i]);
						
			}

		}

?>