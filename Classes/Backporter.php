<?php
declare(ENCODING = 'utf-8');
namespace F3\Backporter;

/*                                                                        *
 * This script belongs to the FLOW3 package "BackPorter".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Backporter main class
 *
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
	 * Specifies whether the target directory should be emptied before files are processed
	 *
	 * @var boolean
	 */
	protected $emptyTargetPath = FALSE;

	/**
	 * Extension-key of the target Extension (e.g. my_extension)
	 *
	 * @var string
	 */
	protected $extensionKey = '';

	/**
	 * Classname of the code processor
	 *
	 * @var string
	 */
	protected $codeProcessorClassName = 'F3\Backporter\CodeProcessor\DefaultClassCodeProcessor';

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
	protected $includeFilePatterns = array();

	/**
	 * An array of regular expressions filenames will be matched against, to determine whether this file should be excluded.
	 *
	 * @var array
	 */
	protected $excludeFilePatterns = array();

	/**
	 * An array of regular expressions to rename target files.
	 *
	 * @var array
	 */
	protected $renameFilenamePatterns = array();

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
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setExtensionKey($extensionKey) {
		$this->extensionKey = $extensionKey;
	}

	/**
	 * Setter for the code processor class name
	 *
	 * @param string $extensionKey
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setCodeProcessorClassName($codeProcessorClassName) {
		$this->codeProcessorClassName = $codeProcessorClassName;
	}

	/**
	 * Setter for the target extension key
	 *
	 * @param array $replacePairs an array containing strings to be replaced. Key = search string, value = replacement string.
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setReplacePairs(array $replacePairs) {
		$this->replacePairs = $replacePairs;
	}

	/**
	 * Setter for filenames to be included to backporting process.
	 *
	 * @param array $includeFilePatterns an array of PCREs which will be used to determine the files to be converted. Filenames are relative to the target path, e.g. "Folder/Subfolder/File.php"
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setIncludeFilePatterns(array $includeFilePatterns) {
		$this->includeFilePatterns = $includeFilePatterns;
	}

	/**
	 * Setter for filenames to be excluded from backporting process (overrules includeFilePattern)
	 *
	 * @param array $excludeFilePatterns an array of PCREs which will be used to determine the files to be exluded from conversion. Filenames are relative to the target path, e.g. "Folder/Subfolder/File.php"
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setExcludeFilePatterns(array $excludeFilePatterns) {
		$this->excludeFilePatterns = $excludeFilePatterns;
	}

	/**
	 * Setter for filename renamings.
	 * Array key will be replaced by array value.
	 * eg: array('/(.*).php/' => '$1_suffix.php')
	 *
	 * @param array $renameFilePatterns an array of PCREs which will be used to rename target filenames.
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setRenameFilenamePatterns(array $renameFilenamePatterns) {
		$this->renameFilenamePatterns = $renameFilenamePatterns;
	}

	/**
	 * Setter for filenames to be excluded from backporting process.
	 *
	 * @param boolean $emptyTargetPath if TRUE, target directory will be emptied before files are processed.
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function emptyTargetPath($emptyTargetPath) {
		$this->emptyTargetPath = $emptyTargetPath;
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
		if ($this->emptyTargetPath) {
			\F3\FLOW3\Utility\Files::emptyDirectoryRecursively($this->targetPath);
		}
		$this->findSourceFilenames();

		$codeProcessor = $this->objectManager->getObject($this->codeProcessorClassName);
		$codeProcessor->setExtensionKey($this->extensionKey);
		foreach($this->sourceFilenames as $sourceFilename) {
			$classCode = \F3\FLOW3\Utility\Files::getFileContents($sourceFilename);
			$relativeFilePath = substr($sourceFilename, strlen($this->sourcePath) + 1);

			if (!$this->shouldFileBeProcessed($relativeFilePath)) {
				continue;
			}
			$targetFilename = \F3\FLOW3\Utility\Files::concatenatePaths(array($this->targetPath, $relativeFilePath));
			$targetFilename = $this->renameTargetFilename($targetFilename);
			\F3\FLOW3\Utility\Files::createDirectoryRecursively(dirname($targetFilename));
			$codeProcessor->setClassCode($classCode);
			file_put_contents($targetFilename, $codeProcessor->processCode($this->replacePairs));
		}
	}

	/**
	 * Return TRUE if current file should be processed.
	 * A file will be included if
	 * 1. relative file path does not match with one of the RegEx given in $this->excludeFilePatterns and
	 * 2. relative file path does match with one of the RegEx given in $this->includeFilePatterns
	 *
	 * @param $relativeFilePath relative file path
	 * @return boolean TRUE if file should be included otherwise FALSE
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function shouldFileBeProcessed($relativeFilePath) {
		foreach ($this->excludeFilePatterns as $excludeFilePattern) {
			if (preg_match($excludeFilePattern, $relativeFilePath) > 0) {
				return FALSE;
			}
		}
		foreach ($this->includeFilePatterns as $includeFilePattern) {
			if (preg_match($includeFilePattern, $relativeFilePath) > 0) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Renames $targetFilename based on $this->renameFilenamePatterns
	 *
	 * @param $targetFilename relative file path
	 * @return string renamed filename
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function renameTargetFilename($targetFilename) {
		foreach ($this->renameFilenamePatterns as $renameFilenamePattern => $renameFilenameReplacement) {
			$targetFilename = preg_replace($renameFilenamePattern, $renameFilenameReplacement, $targetFilename);
		}
		return $targetFilename;
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
		$targetPath = \F3\FLOW3\Utility\Files::getUnixStylePath($targetPath);
		if (!is_dir($targetPath)) {
			\F3\FLOW3\Utility\Files::createDirectoryRecursively($targetPath);
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