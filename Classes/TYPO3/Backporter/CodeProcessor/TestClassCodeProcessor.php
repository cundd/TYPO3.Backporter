<?php
namespace TYPO3\Backporter\CodeProcessor;

/*                                                                        *
 * This script belongs to the Flow package "Backporter".                 *
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
 * Backporter for Test Classes
 *
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class TestClassCodeProcessor extends \TYPO3\Backporter\CodeProcessor\AbstractCodeProcessor {

	/**
	 * Processes the Flow code by calling the respective helper methods.
	 *
	 * @param array $replacePairs an array containing strings to be replaced. Key = search string, value = replacement string.
	 * @param array $fileSpecificReplacePairs an array containing strings to be replaced. Key = search string, value = replacement string.
	 * @param array $unusedReplacePairs an array which should be initialized to the same value as $replacePairs. After calling processCode(), it contains only the $replacePairs which were not used during the replacement.
	 * @param array $unusedFileSpecificReplacePairs an array which should be initialized to the same value as $fileSpecificReplacePairs. After calling processCode(), it contains only the $fileSpecificReplacePairs which were not used during the replacement.
	 * @return string the processed code
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	function processCode(array $replacePairs, array $fileSpecificReplacePairs, array &$unusedReplacePairs, array&$unusedFileSpecificReplacePairs) {
		$this->replaceStrings($replacePairs, $unusedReplacePairs);
		$this->replaceStrings($fileSpecificReplacePairs, $unusedFileSpecificReplacePairs);
		$this->removeNamespaceDeclarations();
		$this->removeGlobalNamespaceSeparators();
		$this->removeUseStatements();
		$this->transformClassName();
		$this->transformObjectNames();
		return $this->processedClassCode;
	}

}
?>