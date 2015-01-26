<?php

	class Thumbnail {

		public $Row;

		public function __construct($Data) {

			if(is_numeric($Data)) {

				$this->Row->id = $Data;
				$this->select();

				}

			}

		private function prepareThumbnail() {

			$this->Row->Src = "Data/images/" . $this->Row->id . ".jpg";

			}

		public function select() {

			$result = dq::select(array('D' => 'ThumbnailDatabase', 'C' => array('id'), 'S' => array('='), 'V' => array($this->Row->id)));
	
			if($row = $result->fetch_object()) {

				$this->Row = $row;
				$this->prepareThumbnail();
				return 1;

				}

			return 0;
			}

		public function saveImage($Url) {

			$Size = 120;
			$PathBig = "../Data/images/" . $this->Row->id . "big.jpg";
			$Path = "../Data/images/" . $this->Row->id . ".jpg";

			// Save Thumbnail in original size
			if(!@file_put_contents($PathBig, @file_get_contents($Url)))
				return 0;

			// Save Thumbnail in small size
			$Image = new simpleImage();
			$Image->load($PathBig);
			$Image->resizeToHeight($Size);
			$Image->save($Path);

			// Get Significant color
			$Image = new colorImage($Path);
			$this->saveColor($Image->getSignificantColor());

			@unlink($PathBig);

			return 1;

			}

		public function saveProfilePicture($File) {

			$Size = 120;
			$PathBig = "Data/images/" . $this->Row->id . "big.jpg";
			$Path = "Data/images/" . $this->Row->id . ".jpg";

			if(!@move_uploaded_file($File["tmp_name"], $PathBig))
				return 0;

			// Save Thumbnail in small size
			$Image = new simpleImage();
			$Image->load($PathBig);
			$Image->resizeToHeight($Size);
			$Image->save($Path);

			// Get Significant color
			$Image = new colorImage($Path);
			$this->saveColor($Image->getSignificantColor());

			@unlink($PathBig);

			return 1;

			}

		public function saveColor($significantColor) {

			if($this->select())
				$result = dq::update(array('D' => 'ThumbnailDatabase', 'Cs' => array('id'), 'Vs' => array($this->Row->id), 'C' => array('SignificantColor'), 'V' => array($significantColor)));
			else
				$result = dq::insert(array('D' => 'ThumbnailDatabase', 'C' => array('SignificantColor'), 'V' => array($significantColor)));

			return $result;

			}

		public function resizeThumbnails($Start, $Limit) {

			$newSize = 120; // Max 105

			$result = dq::select(array('D' => 'ThumbnailDatabase', 'C' => array(), 'S' => array(), 'V' => array(), 'O' => 'ORDER BY id ASC LIMIT '.($Start - 1).', '.$Limit));

			while($row = $result->fetch_object()) {

				$Path = "../Data/images/" . $row->id . ".jpg";

				$Image = new simpleImage();
				$Image->load($Path);
				$Image->resizeToHeight($newSize);
				$Image->save($Path);

				}

			}

		public function delete() {

			if(!$this->Row->id)
				return 0;

			$result = dq::delete(array('D' => 'ThumbnailDatabase', 'C' => array('id'), 'S' => array('='), 'V' => array($this->Row->id)));

			if($result) {

				$Path = "../Data/images/" . $this->Row->id . ".jpg";
				@unlink($Path);
				$Path = "Data/images/" . $this->Row->id . ".jpg";
				@unlink($Path);

				}

			return $result;

			}

		public function clear() {

			$total = 0;
			$result = dq::select(array('D' => 'ThumbnailDatabase', 'C' => array()));

			while($row = $result->fetch_object()) {

				$result2 = dq::select(array('D' => 'AlbumDatabase', 'S' => array('='), 'C' => array('ThumbnailId'), 'V' => array($row->id)));
				if($row2 = $result2->fetch_object())
					continue;
				$result2 = dq::select(array('D' => 'ArtistDatabase', 'S' => array('='), 'C' => array('ThumbnailId'), 'V' => array($row->id)));
				if($row2 = $result2->fetch_object())
					continue;
				$result2 = dq::select(array('D' => 'SongDatabase', 'S' => array('='), 'C' => array('ThumbnailId'), 'V' => array($row->id)));
				if($row2 = $result2->fetch_object())
					continue;

				$T = new Thumbnail($row->id);
				$T->delete();
				$total++;

				}

			return $total;

			}

		}

?>