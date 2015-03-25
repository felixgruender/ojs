<?php

/**
 * @file pages/publicfiles/PublicFilesHandler.inc.php
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicFilesHandler
 * @ingroup pages_publicfiles
 *
 * @brief Handle requests for public files functions.
 */

import('classes.handler.Handler');
import('lib.pkp.classes.file.FileManager');

class PublicFilesHandler extends Handler {
	
	/* @var fileManager FileManager object (see lib/pkp/classes/file/FileManager.inc.php) */
	var $fileManager;
	
	//Constructor
	function PublicFilesHandler() {
		$this->fileManager = new FileManager();
	}
	
	/**
	 * Delete a file in a user's public files directory
	 * @param $args array
	 * @param $request PKPRequest
	 * @return bool
	 */
	function delete($args, $request) {
	
		// it's necessary to be logged in to delete a file
		$user = $request->getUser();
		$currentUser = $user->getUserName();
		if(!$currentUser) {
			return false;
		}
		
		$directoryOwner = $args[0];
		$fileName = $args[1];
		
		// logged in user can only delete files in his own directory
		if (trim($currentUser) != trim($directoryOwner)) {
			return false;
		}
		
		$publicDir = $this->_getRealPublicFilesDir($directoryOwner);
		$filePath = $publicDir . $fileName;
		
		if($this->fileManager->deleteFile($filePath)) {
			if (isset($_SERVER['HTTP_REFERER'])) {
				$request->redirectUrl($_SERVER['HTTP_REFERER']);
			}
			return true;
		}
	}


	/**
	 * Download a file from a user's public files directory
	 * @param $args array
	 * @return bool
	 */
	function download($args) {
		
		$user = $args[0];
		$fileName = $args[1];
		
		$publicDir = $this->_getRealPublicFilesDir($user);
		$filePath = $publicDir . $fileName;
		
		// display file in the browser
		$this->fileManager->downloadFile($filePath, String::mime_content_type($filePath), true);
	}
	
	
	/**
	 * Get the path of a user's public files directory
	 * @param $user string
	 * @return string
	 */
	function _getRealPublicFilesDir($user) {
		$publicDir = Config::getVar('files', 'files_dir') . '/publicuploads/' . $user . '/';
		return $publicDir;
	}
}
?>
