<?php
declare(ENCODING = 'utf-8');
namespace F3\Backporter;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * @package Backporter
 * @version $Id$
 */

/**
 * Backporter main class
 *
 * @package Backporter
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Backporter {

	/**
	 * Absolute path of the source file directory
	 *
	 * @var string
	 */
	protected $sourcePath;

	/**
	 * Absolute path of the target file directory
	 *
	 * @var string
	 */
	protected $targetPath;

	/**
	 * Extension-key of the target Extension (e.g. my_extension)
	 *
	 * @var string
	 */
	protected $extensionKey = '';

	/**
	 * An array containing strings to be replaced. Key = search string, value = replacement string.
	 *
	 * @var array
	 */
	protected $replacePairs = array();

	/**
	 * @var \F3\FLOW3\Object\ManagerInterface A reference to the Object Manager
	 */
	protected $objectManager;

	/**
	 * Source path and filenames
	 *
	 * @var array
	 */
	protected $sourceFilenames = array();

	/**
	 * An array of regular expressions filenames will be matched against, to determine whether this file should be processed.
	 *
	 * @var array
	 */
	protected $includeFilePattern = array();

	/**
	 * Injects the object manager
	 *
	 * @param \F3\FLOW3\Object\ManagerInterface $objectManager A reference to the object manager
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function injectObjectManager(\F3\FLOW3\Object\ManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Setter for the target extension key
	 *
	 * @param string $extensionKey
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setExtensionKey($extensionKey) {
		$this->extensionKey = $extensionKey;
	}

	/**
	 * Setter for the target extension key
	 *
	 * @param array $replacePairs an array containing strings to be replaced. Key = search string, value = replacement string.
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setReplacePairs(array $replacePairs) {
		$this->replacePairs = $replacePairs;
	}

	/**
	 * Setter for filenames to be excluded from backporting process.
	 *
	 * @param array $includedFilePattern an array of PCREs which will be used to determine the files to be converted. Filenames are relative to the target path, e.g. "Folder/Subfolder/File.php"
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setIncludeFilePattern(array $includeFilePattern) {
		$this->includeFilePattern = $includeFilePattern;
	}

	/**
	 * Loads all files in $sourcePath, transforms and stores them in $targetPath
	 *
	 * @param string $sourcePath Absolute path of the source file directory
	 * @param string $targetPath Absolute path of the target file directory
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function processFiles($sourcePath, $targetPath) {
		$this->setSourcePath($sourcePath);
		$this->setTargetPath($targetPath);
		$this->findSourceFilenames();

		$codeProcessor = $this->objectManager->getObject('F3\Backporter\CodeProcessor\DefaultClassCodeProcessor');
		$codeProcessor->setExtensionKey($this->extensionKey);
		foreach($this->sourceFilenames as $sourceFilename) {
			$classCode = \F3\FLOW3\Utility\Files::getFileContents($sourceFilename);
			$relativeFilePath = substr($sourceFilename, strlen($this->sourcePath) + 1);

			if (!$this->shouldFileBeProcessed($relativeFilePath)) {
				continue;
			}
			$targetFilename = \F3\FLOW3\Utility\Files::concatenatePaths(array($this->targetPath, $relativeFilePath));
			\F3\FLOW3\Utility\Files::createDirectoryRecursively(dirname($targetFilename));
			$codeProcessor->setClassCode($classCode);
			file_put_contents($targetFilename, $codeProcessor->processCode($this->replacePairs));
		}
	}

	/**
	 * Return TRUE if current file should be processed, thus, if file matches with one of the RegEx given in $this->includeFilePattern
	 */
	protected function shouldFileBeProcessed($relativeFilePath) {
		foreach ($this->includeFilePattern as $singleFilePattern) {
			if (preg_match($singleFilePattern, $relativeFilePath)) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Setter for source path
	 * Checks if the source path is a readable directory.
	 * Throws an exception if that's not the case.
	 *
	 * @param string $sourcePath Absolute path of the source file directory
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setSourcePath($sourcePath) {
		$sourcePath =  \F3\FLOW3\Utility\Files::getUnixStylePath($sourcePath);
		if (!is_dir($sourcePath)) {
			throw new \F3\Backporter\Exception\InvalidPathException('sourcePath "' . $sourcePath . '" is no directory');
		}
		if (!is_readable($sourcePath)) {
			throw new \F3\Backporter\Exception\InvalidPathException('sourcePath "' . $sourcePath . '" is not readable');
		}
		$this->sourcePath = rtrim($sourcePath, '/');
	}

	/**
	 * Setter for target path
	 * Checks if the target path is a writeable directory.
	 * Throws an exception if that's not the case.
	 *
	 * @param string $targetPath Absolute path of the target file directory
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setTargetPath($targetPath) {
		$targetPath =  \F3\FLOW3\Utility\Files::getUnixStylePath($targetPath);
		if (!is_dir($targetPath)) {
			throw new \F3\Backporter\Exception\InvalidPathException('targetPath "' . $targetPath . '" is no directory');
		}
		if (!is_writable($targetPath)) {
			throw new \F3\Backporter\Exception\InvalidPathException('targetPath "' . $targetPath . '" is not writable');
		}
		$this->targetPath = rtrim($targetPath, '/');
	}

	/**
	 * Retrieves absolute filenames from the source path
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function findSourceFilenames() {
		$this->sourceFilenames = \F3\FLOW3\Utility\Files::readDirectoryRecursively($this->sourcePath);
	}
}
?>