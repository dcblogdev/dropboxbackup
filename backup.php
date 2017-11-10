<?php

class Backup
{
	private $dbxClient;
	private $projectFolder;

	/**
	 * __construct pass token and project to the client method
	 * @param string $token  authorization token for Dropbox API
	 * @param string $project       name of project and version
	 * @param string $projectFolder name of the folder to upload into
	 */
	public function __construct($token, $projectFolder)
	{
		$this->dbxClient = new Spatie\Dropbox\Client($token);
		$this->projectFolder = $projectFolder;
	}

	/**
	 * upload set the file or directory to upload
	 * @param  [type] $dirtocopy [description]
	 * @return [type]            [description]
	 */
	public function upload($dirtocopy)
	{
		if (!file_exists($dirtocopy)) {

			exit("File $dirtocopy does not exist");

		} else {

			//if dealing with a file upload it
			if (is_file($dirtocopy)) {
				$this->uploadFile($dirtocopy);

			} else { //otherwise collect all files and folders

				$iter = new \RecursiveIteratorIterator(
				    new \RecursiveDirectoryIterator($dirtocopy, \RecursiveDirectoryIterator::SKIP_DOTS),
				    \RecursiveIteratorIterator::SELF_FIRST,
				    \RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
				);

				//loop through all entries
				foreach ($iter as $file) {

					$words = explode('/',$file);
					$stop = end($words);

					//if file is not in the ignore list pass to uploadFile method
					if (!in_array($stop, $this->ignoreList())) {
						$this->uploadFile($file);
					}

				}
			}
		}
	}

	/**
	 * uploadFile upload file to dropbox using the Dropbox API
	 * @param  string $file path to file
	 */
	public function uploadFile($file, $mode = 'add')
	{
		$path = "/".$this->projectFolder."/$file";
		$contents = file_get_contents($file);
	    $this->dbxClient->upload($path, $contents, $mode);
	}

	/**
	 * ignoreList array of filenames or directories to ignore
	 * @return array
	 */
	public function ignoreList()
	{
		return array(
			'.DS_Store',
			'cgi-bin'
		);
	}
}
