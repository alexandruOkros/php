<?php

	class dq { // databaseQuery

		protected function __construct() {
			
			}
		
		private function __clone() {
			
			}

		private function __wakeup() {
			
			}
		
		public static function connect() {

			static $instance = null;
			
			if($instance === null) {
			
				$mysqli = new mysqli("localhost", "aokros", "pass", "aokros");
				if ($mysqli->connect_errno)
					$instance = null;
				else
					$instance = $mysqli;
				
				}

			return $instance;
			
			}

		public static function insert($Params) {

			$mysqli = self::connect();

			$Query = 'INSERT INTO ' . $Params['D'] . ' (';
			for($i = 0; $i < count($Params['C']); $i++) {
				if($i > 0)
					$Query .= ', ';
				$Query .= $Params['C'][$i];
				}

			$Query .= ') VALUE (';
			for($i = 0; $i < count($Params['C']); $i++) {

				// Add separating commas
				if($i > 0)
					$Query .= ', ';

				// Transform ' to \' // Eliminate multiple backslashes
				if(is_string($Params['V'][$i]))
					$Query .= '\'' . pr::removeDuplicateBackslash(str_replace('\'', '\\\'', $Params['V'][$i])) . '\'';
				else {

					// To prevent blank value
					if(!$Params['V'][$i])
						$Params['V'][$i] = 0;

					$Query .= $Params['V'][$i];

					}

				}

			$Query .= ')';

			// Show query for debug
			// echo $Query.'<br>';

			if($mysqli->query($Query))
				return 1;

			return 0;

			}

		public static function select($Params) {

			$mysqli = self::connect();

			$Query = 'SELECT * FROM ' . $Params['D'];

			if(count($Params['C'])) {
				$Query .= ' WHERE ';

				for($i = 0; $i < count($Params['C']); $i++) {

					// Add separating AND
					if($i > 0)
						$Query .= ' AND ';

					// Add Column and Sign to current selection
					$Query .= $Params['C'][$i] . ' ' . $Params['S'][$i] . ' ';

					// Transform ' to \' // Eliminate multiple backslashes
					if(is_string($Params['V'][$i]))
						$Query .= '\'' . pr::removeDuplicateBackslash(str_replace('\'', '\\\'', $Params['V'][$i])) . '\'';
					else {

						// To prevent blank value
						if(!$Params['V'][$i])
							$Params['V'][$i] = 0;

						$Query .= $Params['V'][$i];

						}

					}

				}

			if(isset($Params['O']))
				$Query .= ' '.$Params['O'];

			// Show query for debug
			// echo $Query.'<br>';

			$result = $mysqli->query($Query);

			return $result;

			}
		
		public static function cselect($Params) {
			
				$mysqli = self::connect();
			
				$Query = 'SELECT * FROM ' . $Params['D'];
			
				if(strlen($Params['Q']) > 0)
					$Query .= ' WHERE '.$Params['Q'];

				if(isset($Params['O']))
					$Query .= ' '.$Params['O'];
			
				// Show query for debug
				// echo $Query.'<br>';
			
				$result = $mysqli->query($Query);
			
				return $result;
			
			}

		public static function update($Params) {

			$mysqli = self::connect();

			$Query = 'UPDATE ' . $Params['D'] . ' SET ';
			for($i = 0; $i < count($Params['C']); $i++) {
				if($i > 0)
					$Query .= ', ';
				if(!$Params['V'][$i])
					$Params['V'][$i] = 0;
				$Query .= $Params['C'][$i] . ' = ' . (is_string($Params['V'][$i]) ? '\''.str_replace('\'', '\\\'', $Params['V'][$i]).'\'' : $Params['V'][$i]);
				}

			$Query .= ' WHERE ';
			for($i = 0; $i < count($Params['Cs']); $i++) {
				if($i > 0)
					$Query .= ' AND ';
				if(!$Params['Vs'][$i])
					$Params['Vs'][$i] = 0;
				$Query .= $Params['Cs'][$i] . ' = ' . (is_string($Params['Vs'][$i]) ? '\''.str_replace('\'', '\\\'', $Params['Vs'][$i]).'\'' : $Params['Vs'][$i]);
				}

			// Show query for debug
			// echo $Query.'<br>';
	
			if($mysqli->query($Query))
				return 1;

			return 0;

			}

		public static function delete($Params) {

			$mysqli = self::connect();

			$Query = 'DELETE FROM ' . $Params['D'];

			if(count($Params['C'])) {

				$Query .= ' WHERE ';

				for($i = 0; $i < count($Params['C']); $i++) {
					if($i > 0)
						$Query .= ' AND ';
					if(!$Params['V'][$i])
						$Params['V'][$i] = 0;
					$Query .= $Params['C'][$i] . ' ' . $Params['S'][$i] . ' ' . (is_string($Params['V'][$i]) ? '\''.str_replace('\'', '\\\'', $Params['V'][$i]).'\'' : $Params['V'][$i]);
					}

				// Avoid to delete entire table
				if($mysqli->query($Query))
					return 1;

				}
	
			if($mysqli->query($Query))
				return 1;

			return 0;

			}

		public static function cdelete($Params) {

			$mysqli = self::connect();

			$Query = 'DELETE FROM ' . $Params['D'];
			
			if(strlen($Params['Q']) > 0) {
				
				$Query .= ' WHERE '.$Params['Q'];

				// Show query for debug
				// echo $Query.'<br>';

				// Avoid to delete entire table
				if($mysqli->query($Query))
					return 1;

				}

			return 0;

			}

		public static function getNextIndex($Database) {

			$mysqli = self::connect();

			$query = "SHOW TABLE STATUS LIKE '".$Database."'";
			$result = $mysqli->query($query);
			$row = $result->fetch_object();

			return $row->Auto_increment;

			}

		public static function count($Params) {

			$mysqli = self::connect();

			$Query = 'SELECT COUNT(*) FROM ' . $Params['D'];

			if(count($Params['C'])) {
				$Query .= ' WHERE ';

				for($i = 0; $i < count($Params['C']); $i++) {
					if($i > 0)
						$Query .= ' AND ';
					if(!$Params['V'][$i])
						$Params['V'][$i] = 0;
					$Query .= $Params['C'][$i] . ' ' . $Params['S'][$i] . ' ' . (is_string($Params['V'][$i]) ? '\''.str_replace('\'', '\\\'', $Params['V'][$i]).'\'' : $Params['V'][$i]);
					}

				}

			// echo $Query.'<br>';

			$result = $mysqli->query($Query);
			$count = $result->fetch_array();

			return $count['COUNT(*)'];

			}
		
		public static function escape($String) {

			$mysqli = self::connect();
			
			return $mysqli->real_escape_string($String);
			
			}

		public static function getTableNames() {

			$mysqli = self::connect();

			$Query = "show tables;";
			return $mysqli->query($Query);

			}

		}

?>