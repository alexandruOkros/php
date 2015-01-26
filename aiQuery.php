<?php

	class aiQuery {

		public $Row, $Penalty;

		public function __construct($Data) {

			$this->Penalty = (10 * oneDay);

			if(is_numeric($Data)) {
				$this->Row->id = $Data;
				}
			else {
				$this->Row = json_decode(json_encode($Data), FALSE);
				self::setPriority();
				}

			}

		private function setPriority() {

			if(!is_numeric($this->Row->Priority))
				switch ($this->Row->Priority) {
					case 'low':
						$this->Row->Priority = 1;
						break;
					case 'medium':
						$this->Row->Priority = 2;
						break;
					case 'high':
						$this->Row->Priority = 3;
						break;
					case 'ultra':
						$this->Row->Priority = 4;
						break;
					case 'admin':
						$this->Row->Priority = 5;
						break;
					}

			}

		public function insert() {

			if($this->Row->id)
				return self::start();

			if(!self::select()) {
				
				$result = dq::insert(array('D' => 'AiQuery', 'C' => array('ItemId', 'UserId', 'TaskId', 'Progress', 'Priority', 'LastTry', 'Ban'), 'V' => array($this->Row->ItemId, $this->Row->UserId,  $this->Row->TaskId, 0, $this->Row->Priority, 0, 0)));
				if($result)
					return self::select();

				}
			else
				return self::start();

			return 0;

			}

		public function select() {

			if($this->Row->id)
				$result = dq::select(array('D' => 'AiQuery', 'C' => array('id'), 'S' => array('='), 'V' => array($this->Row->id)));
			else
				$result = dq::select(array('D' => 'AiQuery', 'C' => array('ItemId', 'UserId', 'TaskId'), 'S' => array('=', '=', '='), 'V' => array($this->Row->ItemId, $this->Row->UserId, $this->Row->TaskId)));

			if($row = $result->fetch_object()) {
				$this->Row = $row;
				return 1;
				}

			return 0;

			}

		public function update($Column, $Value) {

			if(dq::update(array('D' => 'AiQuery', 'Cs' => array('id'), 'Vs' => array($this->Row->id), 'C' => array($Column), 'V' => array($Value)))) {
				$this->Row->$Column = $Value;
				return 1;
				}

			return 0;

			}

		public function delete() {

			return dq::delete(array('D' => 'AiQuery', 'C' => array('id'), 'S' => array('='), 'V' => array($this->Row->id)));

			}

		public function getQueryUser($userId) {

			$time = now() - $this->Penalty;
			$result = dq::cselect(array('D' => 'AiQuery', 'Q' => '(UserId = 0 OR UserId = '.$userId.') AND (TaskId = 4 OR TaskId = 6 OR TaskId = 7 OR TaskId = 8 OR TaskId = 9) AND Ban = 0 AND LastTry < '.$time, 'O' => 'ORDER BY Priority DESC, id ASC'));

			if($row = $result->fetch_object()) {
				$this->Row = $row;
				return 1;
				}

			return 0;

			}

		public function getQuery() {

			$time = now() - $this->Penalty;
			$result = dq::select(array('D' => 'AiQuery', 'C' => array('TaskId', 'Ban', 'LastTry'), 'S' => array('<>', '=', '<'), 'V' => array(0, 0, $time), 'O' => 'ORDER BY Priority DESC, id ASC'));

			if($row = $result->fetch_object()) {
				$this->Row = $row;
				return 1;
				}

			// DeBan
			/*
			$result = dq::select(array('D' => 'AiQuery', 'C' => array('LastTry'), 'S' => array('<'), 'V' => array($time), 'O' => 'ORDER BY Priority DESC, id ASC'));

			if($row = $result->fetch_object()) {
				$this->Row = $row;
				$this->update('Ban', 0);
				return 1;
				}
			*/

			return 0;

			}

		public function getQueryZero() {

			$result = dq::select(array('D' => 'AiQuery', 'C' => array('TaskId', 'Ban'), 'S' => array('=', '='), 'V' => array(0, 0), 'O' => 'ORDER BY Priority DESC, id ASC'));

			if($row = $result->fetch_object()) {
				$this->Row = $row;
				return 1;
				}

			return 0;

			}

		public function solve() {

			switch($this->Row->TaskId) {

				case 0: // Thumbnail for Song
					$response = aiResearcher::findThumbnailSong($this);
					break;

				case 1: // Thumbnail for Artist
					$response = aiResearcher::findThumbnailArtist($this);
					break;

				case 2: // Thumbnail for Album
					$response = aiResearcher::findThumbnailAlbum($this);
					break;

				case 3: // Find Album
					$response = aiResearcher::findAlbum($this);
					break;

				case 4: // Update Uktop40
					$uk = new Uktop40();
					$response = $uk->backgroundWork($this);
					break;

				case 5: // relatedEngine Work
					$rs = new relatedEngine($this->Row->ItemId);
					$response = $rs->backgroundWork($this);
					break;

				case 6: // recomandationEngine Work
					$re = new recommendationEngine($this->Row->UserId);
					$response = $re->backgroundWork($this);
					break;

				case 7: // Trending Work
					$trend = new Trending();
					$response = $trend->backgroundWork($this);
					break;

				case 8: // Popularity computation
					$player = new Player($this->Row->UserId);
					$response = $player->computeStep($this);
					break;
					
				case 9: // Statistics
					$player = new Player(0);
					$response = $player->statistics($this);
					break;

				case 10: // Expand Database
					$response = aiResearcher::expandDatabase($this);
					break;

				}

			switch($response['x']) {
				
				case 0: // Not solved
					self::update('Ban', 1);
					self::postpone($this->Penalty);
					break;
					
				case 1: // Solved
					self::delete();
					break;
					
				case 2: // Update Progress
					self::update('Progress', $response['Progress']);
					break;
					
				case 3: // Persistent Task
					self::update('Progress', 0);
					self::postpone($response['Postpone']);
					break;

				}
				
			}
		
		public function postpone($Time) {
		
			return self::update('LastTry', (now() - $this->Penalty + $Time));
		
			}
		
		public function start() {
			
			return self::postpone(0);
			
			}

		public static function startAll() {

			startZero();
			startOne();
			startTwo();
			startThree();
			startFour();
			startFive();
			startSix();
			startSeven();
			startEight();
			startNine();
			startTen();

			}

		public static function startZero() {

			$result = dq::select(array('D' => 'SongDatabase', 'C' => array('ThumbnailId'), 'S' => array('<='), 'V' => array(0)));

			while($row = $result->fetch_object()) {
				$aq = new aiQuery(array('ItemId' => $row->id, 'UserId' => 0, 'TaskId' => 0, 'Priority' => 'low'));
				$aq->insert();
				}

			}

		public static function startOne() {

			$result = dq::select(array('D' => 'ArtistDatabase', 'C' => array('ThumbnailId'), 'S' => array('<='), 'V' => array(0)));

			while($row = $result->fetch_object()) {
				$aq = new aiQuery(array('ItemId' => $row->id, 'UserId' => 0, 'TaskId' => 1, 'Priority' => 'low'));
				$aq->insert();
				}

			}

		public static function startTwo() {

			$result = dq::select(array('D' => 'AlbumDatabase', 'C' => array('ThumbnailId'), 'S' => array('<='), 'V' => array(0)));

			while($row = $result->fetch_object()) {
				$aq = new aiQuery(array('ItemId' => $row->id, 'UserId' => 0, 'TaskId' => 2, 'Priority' => 'low'));
				$aq->insert();
				}

			}

		public static function startThree() {

			$result = dq::select(array('D' => 'SongDatabase', 'C' => array('AlbumId'), 'S' => array('<='), 'V' => array(0)));

			while($row = $result->fetch_object()) {
				$aq = new aiQuery(array('ItemId' => $row->id, 'UserId' => 0, 'TaskId' => 3, 'Priority' => 'low'));
				$aq->insert();
				}

			}

		public static function startFour() {

			Uktop40::backgroundStart();

			}

		public static function startFive() {

			$result = dq::select(array('D' => 'SongDatabase', 'C' => array(), 'S' => array(), 'V' => array()));

			while($row = $result->fetch_object()) {
				
				$num = dq::count(array('D' => 'Related', 'C' => array('A'), 'S' => array('='), 'V' => array($row->id)));

				if($num == 0) {
					$aq = new aiQuery(array('ItemId' => $row->id, 'UserId' => 0, 'TaskId' => 5, 'Priority' => 'low'));
					$aq->insert();
					}

				}

			}

		public static function startSix() {

			$result = dq::select(array('D' => 'Account', 'C' => array(), 'S' => array(), 'V' => array()));

			while($row = $result->fetch_object()) {
				$aq = new aiQuery(array('ItemId' => 0, 'UserId' => $row->id, 'TaskId' => 6, 'Priority' => 'high'));
				$aq->insert();
				}

			}

		public static function startSeven() {

			Trending::backgroundStart();

			}

		public static function startEight() {

			$result = dq::select(array('D' => 'Account', 'C' => array(), 'S' => array(), 'V' => array()));

			while($row = $result->fetch_object()) {
				$aq = new aiQuery(array('ItemId' => 0, 'UserId' => $row->id, 'TaskId' => 8, 'Priority' => 'ultra'));
				$aq->insert();
				}

			}

		public static function startNine() {

			$aq = new aiQuery(array('ItemId' => 0, 'UserId' => 0, 'TaskId' => 9, 'Priority' => 'medium'));
			$aq->insert();

			}

		public static function startTen() {

			$aq = new aiQuery(array('ItemId' => 0, 'UserId' => 0, 'TaskId' => 10, 'Priority' => 'low'));
			$aq->insert();

			}


		// Deprecated

		/*
		
		private function findWorkOld() {

			require_once("../php/connect.php");

			$Total = 4;
			$Database = array('SongDatabase', 'ArtistDatabase', 'AlbumDatabase', 'SongDatabase');
			$Column = array('ThumbnailId', 'ThumbnailId', 'ThumbnailId', 'AlbumId');

			for($i = 0; $i < $Total; $i++) {

				$Query = "SELECT * FROM " . $Database[$i] . " WHERE " . $Column[$i] . " = 0";
				$Result = mysql_query($Query);

				while($Row = $result->fetch_object()) {

					$aiQuery = new aiQuery(array('ConnectionType' => $i, 'ItemId' => $Row['id']));
					$aiQuery->InsertInDatabase();

					// Update Status

					$Query = "UPDATE " . $Database[$i] . " SET " . $Column[$i] . " = -1 WHERE id = " . $Row['id'];
					$Result2 = mysql_query($Query);

					}
				
				}

			return $this->SelectInDatabase();

			}

		*/

		}

?>