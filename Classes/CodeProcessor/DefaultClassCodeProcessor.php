<?php
declare(ENCODING = 'utf-8');
namespace F3\Backporter\CodeProcessor;

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
 * @subpackage CodeProcessor
 * @version $Id$
 */

/**
 * Collection of backport utility methods
 *
 * @package Backporter
 * @subpackage CodeProcessor
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class DefaultClassCodeProcessor extends \F3\Backporter\CodeProcessor\AbstractCodeProcessor {

	/**
	 * Processes the FLOW3 code by calling the respective helper methods.
	 *
	 * @param string $inputString
	 * @param array $replacePairs an array containing strings to be replaced. Key = search string, value = replacement string.
	 * @return string the processed code
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function processCode(array $replacePairs = array()) {
		$this->removeUTF8Declaration();
		$this->removeNamespaceDeclarations();
		$this->removeGlobalNamespaceSeparators();
		$this->transformClassName();
		$this->transformObjectNames();
		$this->replaceStrings($replacePairs);
		return $this->processedClassCode;
	}

}
?>