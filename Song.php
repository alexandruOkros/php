<?php

	
	class Song {

		public $Row;

		public function __construct($Data) {

			if(is_numeric($Data)) {

				$this->Row->id = $Data;
				if($Data != 0)
					self::select();

				}
			else {

				$this->Row->id = 0;
				$this->Row->Link = $Data['Link'];

				if(!self::select()) {

					$this->Row->SongName = $Data['Song'];
					$this->Artist = new Artist($Data['Artist']);
					$this->Featuring = new Artist($Data['Featuring']);

					}

				}

			}
		
		public function printNormal() {
			
			$string = $this->Artist->printVar().(self::isFeaturing() ? ' featuring '.$this->Featuring->printVar() : '').' - '.$this->Row->SongName;
			return $string;
			
			}

		public function printVar() {

			$string = $this->Artist->printVar().(self::isFeaturing() ? ' featuring '.$this->Featuring->printVar() : '').' - '.$this->Row->SongName.' ('.$this->Row->Link.')';
			return $string;

			}

		public function printInline() {

			$string = $this->Artist->printVar().(self::isFeaturing() ? ' '.$this->Featuring->printVar() : '').' '.$this->Row->SongName;
			return $string;

			}

		public function isFeaturing() {

			return (strlen($this->Featuring->Row->ArtistName) != 0);

			}

		public function valid() {

			return (strlen($this->Row->SongName) > 0 && strlen($this->Row->SongName) <= 50 && strlen($this->Artist->Row->ArtistName) > 0 && strlen($this->Row->Link) > 0);	

			}

		public function equals($Song) {

			return (($this->Row->SongName == $Song->Row->SongName) && ($this->Artist->Row->ArtistName == $Song->Artist->Row->ArtistName));

			}

		public function insert() {

			if($this->Row->id)
				return 1;

			if(!$this->valid())
				return 0;

			// Add Artist to Database
			if(!$this->Artist->insert())
				return 0;

			// Add Featuring to Database
			if(self::isFeaturing())
				if(!$this->Featuring->insert())
					return 0;

			// Add Song to Database
			$result = dq::insert(array('D' => 'SongDatabase', 'C' => array('ArtistId', 'FeaturingId', 'SongName', 'Link'), 'V' => array($this->Artist->Row->id, $this->Featuring->Row->id, $this->Row->SongName, $this->Row->Link)));

			if($result) {

				self::select();

				// Add AiQuery
				$aq = new aiQuery(array('ItemId' => $this->Row->id, 'UserId' => 0, 'TaskId' => 0, 'Priority' => 'low'));
				$aq->insert();
				$aq = new aiQuery(array('ItemId' => $this->Row->id, 'UserId' => 0, 'TaskId' => 3, 'Priority' => 'low'));
				$aq->insert();
				$aq = new aiQuery(array('ItemId' => $this->Row->id, 'UserId' => 0, 'TaskId' => 5, 'Priority' => 'low'));
				$aq->insert();

				// Also insert in Dictionary
				$se = new searchEngine();
				$se->addSong($this->Row->id);

				return 1;

				}

			return 0;

			}

		public function select() {

			if($this->Row->id)
				$result = dq::select(array('D' => 'SongDatabase', 'C' => array('id'), 'S' => array('='), 'V' => array($this->Row->id)));
			else
			if($this->Row->Link)
				$result = dq::select(array('D' => 'SongDatabase', 'C' => array('Link'), 'S' => array('='), 'V' => array($this->Row->Link)));
			else
				return 0;

			if($row = $result->fetch_object()) {

				$this->Row = $row;

				$this->Artist = new Artist($row->ArtistId);
				$this->Featuring = new Artist($row->FeaturingId);

				if($this->Row->ThumbnailId > 0)
					$this->Thumbnail = new Thumbnail($this->Row->ThumbnailId);
				else {
					$this->Thumbnail = new blankThumbnail(0);
					// $this->Thumbnail->Row->Src = "i1.ytimg.com/vi/".$this->Row->Link."/mqdefault.jpg";
					}

				return 1;

				}

			$this->Thumbnail = new blankThumbnail(1);

			return 0;

			}

		public function selectByName() {

			if($this->Row->id)
				return 1;

			if(!$this->Artist->select())
				return 0;

			if(strlen($this->Featuring->Row->ArtistName) > 0 && !$this->Featuring->select())
				return 0;

			$result = dq::select(array('D' => 'SongDatabase', 'C' => array('ArtistId', 'FeaturingId', 'SongName'), 'S' => array('=', '=', '='), 'V' => array($this->Artist->Row->id, $this->Featuring->Row->id, $this->Row->SongName)));

			if($row = $result->fetch_object()) {

				$this->Row = $row;
				return $this->select();

				}

			return 0;

			}

		public function update($Column, $Value) {

			if(dq::update(array('D' => 'SongDatabase', 'Cs' => array('id'), 'Vs' => array($this->Row->id), 'C' => array($Column), 'V' => array($Value)))) {
				$this->Row->$Column = $Value;
				return 1;
				}

			return 0;

			}

		public function delete() {

			if(!$this->Row->id)
				return 0;

			// From userDatabase
			$result = dq::delete(array('D' => 'UserSongDatabase', 'C' => array('SongId'), 'S' => array('='), 'V' => array($this->Row->id)));

			// From Playlists
			$result = dq::delete(array('D' => 'PlaylistTracklist', 'C' => array('SongId'), 'S' => array('='), 'V' => array($this->Row->id)));

			// From Track History
			$result = dq::delete(array('D' => 'UserSongHistory', 'C' => array('SongId'), 'S' => array('='), 'V' => array($this->Row->id)));

			// From aiquery
			$result = dq::cdelete(array('D' => 'AiQuery', 'Q' => 'ItemId = '.$this->Row->id.' AND (TaskId = 0 OR TaskId = 3 OR TaskId = 5)'));

			// From Dictionary
			$se = new searchEngine();
			$se->deleteSong($this->Row->id);

			// From Database
			$result = dq::delete(array('D' => 'SongDatabase', 'C' => array('id'), 'S' => array('='), 'V' => array($this->Row->id)));

			// From Thumbnail Database
			$this->Thumbnail->delete();

			return 1;

			}	
		
		public function tag($TagId, $Value) {
			
			return  dq::insert(array('D' => 'SongTags', 'C' => array('SongId', 'TagId', 'Value'), 'V' => array($this->Row->id, $TagId, $Value)));
			
			}
		
		public function live() {
					
			if(@file_get_contents("http://i1.ytimg.com/vi/". $this->Row->Link . "/default.jpg"))
				return 1;
			else
				return 0;
					
			}

		public function rename($newName) {

			// Delete oldName from live search
			searchEngine::deleteSong($this->Row->id);

			// Update name
			self::update('SongName', $newName);

			// Add newName to live search
			searchEngine::addSong($this->Row->id);
				
			}

		public function getExtra() {

			// Get Album name
			if(isset($this->Row->AlbumId) && $this->Row->AlbumId > 0) {
				$Album = new Album($this->Row->AlbumId);
				$AlbumName = $Album->Row->AlbumName;
				}
			else
				$AlbumName = '';

			$Year = 0;

			$result = dq::select(array('D' => 'SongTags', 'C' => array('SongId'), 'S' => array('='), 'V' => array($this->Row->id), 'O' => 'ORDER BY Value DESC'));
			
			for($i = 0; ($row = $result->fetch_object()); ) {

				$tag = new Tag($row->TagId);

				if(is_numeric($tag->Row->Name)) {
					if($Year == 0)
						$Year = $tag->Row->Name;
					}
				else
				if($row->Value >= 0.3 && $i < 5 && ($tag->Row->Type < 22 || $tag->Row->Type > 24))
					$Item[$i++] = $tag->Row->Name;

				}

			$Total = $i;

			// If year not found
			if(!$Year)
				$Year = '';

			return array('AlbumName' => $AlbumName, 'Year' => $Year/*, 'Tags' => array('Total' => $Total, 'Item' => $Item)*/);

			}

		}

?>