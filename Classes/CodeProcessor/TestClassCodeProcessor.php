<?php
declare(ENCODING = 'utf-8');
namespace F3\Backporter\CodeProcessor;

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
 * Backporter for Test Classes
 *
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class TestClassCodeProcessor extends \F3\Backporter\CodeProcessor\AbstractCodeProcessor {

	/**
	 * Processes the FLOW3 code by calling the respective helper methods.
	 *
	 * @param array $replacePairs an array containing strings to be replaced. Key = search string, value = replacement string.
	 * @return string the processed code
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function processCode(array $replacePairs = array()) {
		$this->replaceStrings($replacePairs);
		$this->suffixClassName('_testcase');
		//$this->addClassHeader('require_once(t3lib_extMgm::extPath(\'extbase\', \'Tests/Base_testcase.php\'));');
		$this->removeEncodingDeclaration();
		$this->removeNamespaceDeclarations();
		$this->removeGlobalNamespaceSeparators();
		$this->transformClassName();
		$this->transformObjectNames();
		return $this->processedClassCode;
	}

}
?>