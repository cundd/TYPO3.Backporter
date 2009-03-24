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
abstract class AbstractCodeProcessor {

	abstract function processString($inputString);

	/**
	 * Removes the line "declare(ENCODING = 'utf-8');" that appears on top of all FLOW3 classes.
	 *
	 * @param string $inputString
	 * @return string the modified string
	 */
	public function removeUTF8Declaration($inputString) {
		return preg_replace('/^declare\(ENCODING = \'utf-8\'\);\n/m', '', $inputString);
	}
	
	/**
	 * Removes the line "namespace F3/Package/Subpackage..." that appears on top of all FLOW3 classes.
	 *
	 * @param string $inputString
	 * @return string the modified string
	 */
	public function removeNamespaceDeclarations($inputString) {
		return preg_replace('/^namespace\s+.*;\n/m', '', $inputString);
	}
	
	/**
	 * Removes the backslash from global PHP Classes (e.g. \Exception)
	 *
	 * @param string $inputString
	 * @return string the modified string
	 */
	public function removeGlobalNamespaceSeparators($inputString) {
		return preg_replace('/([\( ])\\\\([a-zA-Z]{3,} )/', '$1$2', $inputString);
	}
	
}
?>