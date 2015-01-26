<?php

	class costumSearchEngine {

		// Type 1 userPlaylistNames

		public $Row;

		public function __construct($Data) {

			$this->Row->UserId = $Data['UserId'];
			$this->Row->Type = $Data['Type'];
			$this->select();

			}

		private function create() {
			
			return dq::insert(array('D' => 'CostumDictionary', 'C' => array('UserId', 'Type'), 'V' => array($this->Row->UserId, $this->Row->Type)));

			}

		public function select() {

			$result = dq::select(array('D' => 'CostumDictionary', 'C' => array('UserId', 'Type'), 'S' => array('=', '='), 'V' => array($this->Row->UserId, $this->Row->Type)));

			if($row = $result->fetch_object()) {
				$this->Row = $row;
				return 1;
				}
			else
			if($this->create())
				return $this->select();

			return 0;

			}

		public function delete() {

			$result = dq::delete(array('D' => 'CDictionaryEdge', 'C' => array('Did'), 'S' => array('='), 'V' => array($this->Row->id)));
			$result &= dq::delete(array('D' => 'CDictionaryList', 'C' => array('Did'), 'S' => array('='), 'V' => array($this->Row->id)));
			$result &= dq::delete(array('D' => 'CostumDictionary', 'C' => array('id'), 'S' => array('='), 'V' => array($this->Row->id)));

			return $result;

			}
		
		public static function hash($String) {
			
			$String = pr::serialize($String);
			$String = pr::lower($String);
			$String = pr::numberLetter($String);
			
			return $String;
			
			}
		
		private static function splitInWords($String) {

			$String = pr::translateLatinCharacters(strtolower($String));

			return pr::splitInWords($String);

			}

		private function addWord($Word, $itemId, $Match) {
			
			$Nod = 0;

			for($i = 0; $i < strlen($Word); $i++) {

				$result = dq::select(array('D' => 'CDictionaryEdge', 'C' => array('Nod', 'KeyC', 'Did'), 'S' => array('=', '=', '='), 'V' => array($Nod, ord($Word[$i]), $this->Row->id)));
				if(!($row = $result->fetch_object())) {

					dq::insert(array('D' => 'CDictionaryEdge', 'C' => array('Nod', 'KeyC', 'Did'), 'V' => array($Nod, ord($Word[$i]), $this->Row->id)));
					$result = dq::select(array('D' => 'CDictionaryEdge', 'C' => array('Nod', 'KeyC', 'Did'), 'S' => array('=', '=', '='), 'V' => array($Nod, ord($Word[$i]), $this->Row->id)));
					$row = $result->fetch_object();

					}

				$Nod = $row->Vecin;
				// Add To Solution
				if(!$Match || ($i == strlen($Word) - 1)) {
				$result = dq::select(array('D' => 'CDictionaryList', 'C' => array('Nod', 'ItemId', 'Did'), 'S' => array('=', '=', '='), 'V' => array($Nod, $itemId, $this->Row->id)));
				if(!($row = $result->fetch_object()))
					dq::insert(array('D' => 'CDictionaryList', 'C' => array('Nod', 'ItemId', 'Did'), 'V' => array($Nod, $itemId, $this->Row->id)));
					}
				
				}
			
			}

		public function addString($String, $itemId) {

			$List = $this->splitInWords($String);

			for($i = 0; $i < count($List); $i++)
				$this->addWord($List[$i], $itemId, 0);

			}

		public function addStringMatch($String, $itemId) {

			$List = $this->splitInWords($String);

			for($i = 0; $i < count($List); $i++)
				$this->addWord($List[$i], $itemId, 1);

			}

		private function deleteWord($Word) {

			$Nod = 0;

			for($i = 0; $i < strlen($Word); $i++) {

				$result = dq::select(array('D' => 'CDictionaryEdge', 'C' => array('Nod', 'KeyC', 'Did'), 'S' => array('=', '=', '='), 'V' => array($Nod, ord($Word[$i]), $this->Row->id)));
				$row[$i] = $result->fetch_object();

				$Nod = $row[$i]->Vecin;

				if(!$Nod)
					return 0;

				}

			for($i = strlen($Word) - 1; $i >= 0; $i--) {

				if(!$row[$i])
					return 0;

				$Nod = $row[$i]->Vecin;

				$result = dq::select(array('D' => 'CDictionaryList', 'C' => array('Nod', 'Did'), 'S' => array('=', '='), 'V' => array($Nod, $this->Row->id)));
				if(!($row2 = $result->fetch_object()))
					dq::delete(array('D' => 'CDictionaryEdge', 'C' => array('Vecin'), 'S' => array('='), 'V' => array($Nod)));
				else
					return 1;

				}

			return 1;

			}

		public function deleteString($String, $itemId) {

			// Delete from Solution List
			
			$result = dq::delete(array('D' => 'CDictionaryList', 'C' => array('ItemId', 'Did'), 'S' => array('=', '='), 'V' => array($itemId, $this->Row->id)));

			// Delete nodes form Tree

			$List = $this->splitInWords($String);

			for($i = 0; $i < count($List); $i++)
				$this->deleteWord($List[$i]);

			}

		public function updateItem($oldString, $newString, $itemId) {

			$this->deleteString($oldString, $itemId);
			$this->addString($newString, $itemId);

			}

		public function updateItemMatch($oldString, $newString, $itemId) {

			$this->deleteString($oldString, $itemId);
			$this->addStringMatch($newString, $itemId);

			}

		public function findWord($Word) {
			
			$Nod = 0;

			for($i = 0; $i < strlen($Word); $i++) {

				$result = dq::select(array('D' => 'CDictionaryEdge', 'C' => array('Nod', 'KeyC', 'Did'), 'S' => array('=', '=', '='), 'V' => array($Nod, ord($Word[$i]), $this->Row->id)));
				if(!($row = $result->fetch_object()))
					return 0;

				$Nod = $row->Vecin;
				
				}

			return $Nod;

			}

		private function findString($String) {

			$List = $this->splitInWords($String);

			for($i = 0; $i < count($List); $i++) {
				$Node[$i] = $this->findWord($List[$i]);
				if(!$Node[$i])
					return 0;
				}

			return $Node;

			}

		public function search($Query, $Start, & $Limit) {

			$Node = $this->findString($Query);
			if(!$Node) {
				$Limit = 0;
				return 0;
				}
			else
				$Total = count($Node);

			for($k = 0; $k < $Total; $k++) {
				$result[$k] = dq::select(array('D' => 'CDictionaryList', 'C' => array('Nod', 'Did'), 'S' => array('=', '='), 'V' => array($Node[$k], $this->Row->id), 'O' => 'ORDER BY id ASC'));
				$row[$k] = $result[$k]->fetch_object();
				}

			$More = true;
			for($i = 0; $i < $Limit && $More; $Start--) {

				$Min = 0; $Max = 1;
				for($Solution = 0; $Min != $Max && $More;) {

					$Min = $Max = $row[0]->ItemId;

					for($k = 1; $k < $Total; $k++) {
						if($row[$k]->ItemId < $Min)
							$Min = $row[$k]->ItemId;
						else
						if($row[$k]->ItemId > $Max)
							$Max = $row[$k]->ItemId;
						}

					if($Min == $Max)
						$Solution = $row[0]->ItemId;

					for($k = 0; $k < $Total; $k++)
						if($row[$k]->ItemId == $Min)
							if(!($row[$k] = $result[$k]->fetch_object())) {
								$More = false;
								break;
								}

					}

				if($Start > 1)
					continue;

				if($Solution)
					$Item[$i++] = $Solution;

				}

			$Limit = $i;
			return $Item;

			}

		}

?>