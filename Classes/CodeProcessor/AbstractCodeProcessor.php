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

	/**
	 * Namespace of the processed Class
	 *
	 * @var string
	 */
	protected $classNamespace = '';

	/**
	 * Extension-key of the target Extension (e.g. my_extension)
	 *
	 * @var string
	 */
	protected $extensionKey = '';

	/**
	 * Uppercased Extension-key of the target Extension (e.g. MyExtension)
	 *
	 * @var string
	 */
	protected $upperCasedExtensionKey = '';


	/**
	 * Processes the FLOW3 code by calling the respective helper methods.
	 *
	 * @param string $inputString
	 * @return string the processed code
	 */
	abstract function processString($inputString);

	/**
	 * Setter for the classes namespace
	 *
	 * @param string $classNamespace
	 */
	public function setClassNamespace($classNamespace) {
		$this->classNamespace = $classNamespace;
	}

	/**
	 * Getter for the classes namespace
	 *
	 * @return string $classNamespace
	 */
	public function getClassNamespace() {
		return $this->classNamespace;
	}

	/**
	 * Setter for the target extension key
	 *
	 * @param string $extensionKey
	 */
	public function setExtensionKey($extensionKey) {
		$this->extensionKey = $extensionKey;
		$this->upperCasedExtensionKey = $this->upperCaseExtensionKey($extensionKey);
	}

	/**
	 * Turns my_extension into MyExtension
	 *
	 * @param string $extensionKey
	 * @return string the upper cased extension key
	 */
	protected function upperCaseExtensionKey($extensionKey) {
		$upperCasedExtensionKey = '';
		$extensionKeyParts = explode('_', $extensionKey);
		foreach($extensionKeyParts as $extensionKeyPart) {
			$upperCasedExtensionKey.= ucfirst($extensionKeyPart);
		}
		return $upperCasedExtensionKey;
	}

	/**
	 * Removes the line "declare(ENCODING = 'utf-8');" that appears on top of all FLOW3 classes.
	 *
	 * @param string $inputString
	 * @return string the modified string
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function removeUTF8Declaration($inputString) {
		return preg_replace('/^declare\(ENCODING = \'utf-8\'\);\n/m', '', $inputString);
	}

	/**
	 * Removes the line "namespace F3/Package/Subpackage..." that appears on top of all FLOW3 classes.
	 *
	 * @param string $inputString
	 * @return string the modified string
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function removeNamespaceDeclarations($inputString) {
		return preg_replace('/^namespace\s+.*;\n/m', '', $inputString);
	}

	/**
	 * Removes the backslash from global PHP Classes (e.g. \Exception)
	 *
	 * @param string $inputString
	 * @return string the modified string
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function removeGlobalNamespaceSeparators($inputString) {
		return preg_replace('/([\( ])\\\\([a-zA-Z]{3,} )/', '$1$2', $inputString);
	}

	/**
	 * Turns class MyClass into class Tx_Extension_SupPackage_MyClass
	 *
	 * @param string $inputString
	 * @return string the modified string
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function transformClassName($inputString) {
		$regex = '/((?:abstract )?(?:class|interface)\s)([a-zA-Z]+)/';
		$that = $this;
		$out = preg_replace_callback($regex, function($result) use (&$that) {
			return $result[1] . $that->convertClassName($that->getClassNamespace() . '\\' . $result[2]);
		}, $inputString);

		return $out;
	}

	/**
	 * Transforms all namespaced object names into their un-namespaced equivalents.
	 *
	 * @param string $inputString
	 * @return string the modified string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function transformObjectNames($inputString) {
		$regex = '/
			\\\\?
			(F3(?:\\\\\w+)+)
		/x';
		$that = $this;
		$out = preg_replace_callback($regex, function($result) use (&$that) {
			return $that->convertClassName($result[1]);
		}, $inputString);

		return $out;
	}

	/**
	 * Converts "SomeClass" into "Tx_MyExtension_Subpackage_SomeClass"
	 *
	 * @param string $oldClassName the class name
	 * @return string the converted class name
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function convertClassName($oldClassName) {
		$regex = '/
			F3\\\\
			(?P<PackageKey>[A-Za-z]+)
			(?P<ObjectName>(?:\\\\\w+)+)
		/x';

		preg_match($regex, $oldClassName, $matches);

		$newClassName = 'Tx_';
		$newClassName .= $this->upperCasedExtensionKey;
		$newClassName .= str_replace('\\', '_', $matches['ObjectName']);

		return $newClassName;
	}

	/**
	 * Replaces all occurences of $searchString by $replaceString
	 *
	 * @param string $searchString string to search for
	 * @param string $replaceString replacing string
	 * @param string $inputString
	 * @return string the modified string
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function replaceString($searchString, $replaceString, $inputString) {
		return str_replace($searchString, $replaceString, $inputString);
	}

}
?>