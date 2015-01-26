<?php

	// Not interested button eliminates song from list decrement index if necessary
	// play recommended song if list is available
	

	class recommendationEngine extends Playlist {
		
		public $Cache, $History;

		public static $Threshold = 0.35;
		
		public function __construct($userId) {
			
			parent::__construct(array('UserId' => $userId, 'PlaylistName' => 'Recommended'));
			$this->Cache = new Playlist(array('UserId' => $userId, 'PlaylistName' => 'RecommendedCache'));
			$this->History = new Playlist(array('UserId' => $userId, 'PlaylistName' => 'RecommendedHistory'));
			
			}
		
		public function recommend($songId) {

			$this->Cache->insertTrack($songId);
			$this->History->insertTrack($songId);
			
			}
		
		public function notRecommended($songId) {
			
			// Check if Song was not already recommended for user
			$response = $this->History->selectTrack($songId);
			if($response['x'] == 1)
				return 0;
			
			// Check if Song is not already in user library
			$result = dq::select(array('D' => 'UserSongDatabase', 'C' => array('UserId', 'SongId'), 'S' => array('=', '='), 'V' => array($this->Row->UserId, $songId)));
			
			if($row = $result->fetch_object())
				return 0;
			
			return 1;
			
			}

		public function notInterested($songId) {

			if($this->size() == 1) {
				self::backgroundStart();
				return array('x' => 1, 'message' => 'Recommendation deleted.');
				}

			// Check current index
			$player = new Player($this->Row->UserId);
			$index = $player->getParam(4);

			$total = 1; $oldItem = $this->feed(($index + 1), $total);
			
			// Delete track from Recommended Playlist
			$response = $this->deleteTrack($songId);

			if($response['x'] == 0)
				return array('x' => 0, 'Message' => errorMessage);

			// Check if Song has done something bad
			$total = 1; $newItem = $this->feed(($index + 1), $total);

			// Update Index if needed
			if($songId != $oldItem[0] && $oldItem[0] != $newItem[0])
				$player->setParam(4, ($index - 1));

			return array('x' => 1, 'message' => 'Recommendation deleted.');

			}
		
		public function flush() {
			
			// Copy Cache in real Playlist
			$this->copy($this->Cache);
			// Empty Cache
			$this->Cache->drop();
			// Reset Index
			$player = new Player($this->Row->UserId);
			$player->setParam(3, 1);
			$player->setParam(4, 0);
			
			}
		
		public function initial() {

			// This function is called only at user creation
			// Create necessary playlists
			parent::insert();
			parent::update('System', 1);
			$this->Cache->insert();
			$this->Cache->update('System', 1);
			$this->History->insert();
			$this->History->update('System', 1);

			// Recommend the first 5 songs from Uktop40
			$total = 5;
			$uk = new Uktop40();
			$item = $uk->feed(1, $total);

			for($i = 0; $i < $total; $i++)
				self::recommend($item[$i]);
			
			self::flush();
	
			}
		
		public function available() {
			
			$player = new Player($this->Row->UserId);
			$available = $player->getParam(3);
			
			if($available) {
				
				if($player->getParam(4) >= $this->size()) {
					self::backgroundStart();
					return 0;
					}
				else
					return 1;
				
				}
			
			return 0;
			
			}
		
		public function getNextRecommendation() {
			
			// Get next index
			$player = new Player($this->Row->UserId);
			$index = $player->getParam(4);
			$player->setParam(4, ($index + 1));
			
			// Fetch songId
			$total = 1;
			$item = $this->feed(($index + 1), $total);
			return $item[0];
			
			}

		public function update($Step) {

			// 5 max topuk
			// 2 expand artist
			// 13 min expand song

			if($Step == 0) {

				// Check top 5 from Uktop40

				$uk = new Uktop40();
				$total = 5;
				$item = $uk->feed(1, $total);

				for($i = 0; $i < $total; $i++)
					if(self::notRecommended($item[$i]))
						self::recommend($item[$i]);

				return ($Step + 1);

				}
			else
			if($Step == 1) {

				// Check top 5 from Trending

				$trend = new Trending();
				$total = 5;
				$item = $trend->feed(1, $total);

				for($i = 0; $i < $total; $i++)
					if(self::notRecommended($item[$i]))		
						self::recommend($item[$i]);


				return ($Step + 1);

				}
			else
			if($Step == 2) {

				// Expand 1-2 times Artist

				$response = self::expandArtist();
				$response |= self::expandArtist();

				if($response == 1 || $response == -1)
					return ($Step + 1);
				else
					return $Step;

				}
			else {

				// The rest expand Song

				$response = self::expandSong();

				// If Library is too small end prematurely
				if($response == -1)
					return 20;

				if($this->Cache->Size() >= 20)
					return 20;
				else
					return $Step;

				}

			}

		private function expandArtist() {

			// Find an Artist
			$artist = new uArtist(array('UserId' => $this->Row->UserId, 'Artist' => 0));
			$row = $artist->selectByExpand();

			if(!$row || $row->Expand > 500) // No Artist to Expand
				return -1;

			$newExpand = $row->Expand + 1;

			// Load Artist
			$artist = new uArtist(array('UserId' => $this->Row->UserId, 'Artist' => $row->ArtistId));
			$library = new Library(array('Userid' => $this->Row->UserId, 'Song' => 0));
			// Search Database
			$result = dq::cselect(array('D' => 'SongDatabase', 'Q' => 'ArtistId = '.$artist->Row->id.' OR FeaturingId = '.$artist->Row->id, 'O' => 'ORDER BY id DESC'));
			
			while($row2 = $result->fetch_object()) {

				// Check if Song was not already recommended for user
				if(!self::notRecommended($row2->id))
					continue;
					
				self::recommend($row2->id);
				$artist->update('Expand', $newExpand);
				return 1;

				}

			// Search YouTube
			$found = Youtube::expandArtist($artist->Row->ArtistName);
			
			if($found) {

				// Search again in Database
				$result = dq::cselect(array('D' => 'SongDatabase', 'Q' => 'ArtistId = '.$artist->Row->id.' OR FeaturingId = '.$artist->Row->id, 'O' => 'ORDER BY id DESC'));
			
				while($row2 = $result->fetch_object()) {

					// Check if Song was not already recommended for user
					if(!self::notRecommended($row2->id))
						continue;
					
					self::recommend($row2->id);
					$artist->update('Expand', $newExpand);
					return 1;

					}

				}

			$artist->update('Expand', $newExpand + 1);

			return 0;

			}
				
		private function expandSong() {

			$song = new Library(array('Song' => 0, 'UserId' => $this->Row->UserId));
			$response = $song->selectByExpand();

			if(!$response || $song->Entry->Expand > 90) // No Song to Expand
				return -1;

			$newExpand = $song->Entry->Expand + 1;

			// Search Related Content
			$rs = new relatedEngine($song->Entry->SongId);
			$total = 20;
			$item = $rs->feed(1, $total);

			for($i = 0; $i < $total; $i++) {

				if($item[$i]->Strength < 0.5)
					continue;

				// Check if Song was not already recommended for user
				if(!self::notRecommended($item[$i]->B))
					continue;
				
				self::recommend($item[$i]->B);
				$song->update('Expand', $newExpand);
				return 1;

				}

			$song->update('Expand', $newExpand + 1);

			return 0;



			}

		public function backgroundStart() {
			
			// Make list unavailable
			$player = new Player($this->Row->UserId);
			$player->setParam(3, 0);
			
			// Start query
			$aq = new aiQuery(array('ItemId' => 0, 'UserId' => $this->Row->UserId, 'TaskId' => 6, 'Priority' => 'high'));
			$aq->insert();
			
			}
		
		public function backgroundWork($Aq) {

			if($Aq->Row->Progress == 0) {

				// Check if user has songs in the database
				$library = new Library(array('Song' => 0, 'UserId' => $this->Row->UserId));

				if($library->size() == 0)
					return array('x' => 3, 'Postpone' => oneDay);

				}
			
			$progress = self::update($Aq->Row->Progress);

			if($progress == 20) {
				// Update playlist
				self::flush();
				// Send notification
				Notification::send(0, 0, $this->Row->UserId, 0, 'Your recommendation platlist was updated.');
				
				return array('x' => 3, 'Postpone' => $Aq->Penalty);
				}
			else
				return array('x' => 2, 'Progress' => $progress);
				
			}
	
		}

?>