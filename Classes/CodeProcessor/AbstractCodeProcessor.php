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
	 * Unmodified FLOW3 class code.
	 *
	 * @var string
	 */
	protected $originalClassCode = '';

	/**
	 * The processed FLOW3 class code.
	 *
	 * @var string
	 */
	protected $processedClassCode = '';

	/**
	 * Namespace of the processed Class
	 *
	 * @var string
	 */
	protected $classNamespace = NULL;

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
	 * Setter for the FLOW3 class code.
	 * 
	 * @param string $classCode the FLOW3 class code to be processed.
	 * @return string the processed code
	 */
	public function setClassCode($classCode) {
		$this->processedClassCode = $this->originalClassCode = $classCode;
	}

	/**
	 * Processes the FLOW3 code by calling the respective helper methods.
	 *
	 * @param array $replacePairs an array containing strings to be replaced. Key = search string, value = replacement string.
	 * @return string the processed code
	 */
	abstract function processCode(array $replacePairs = array());

	/**
	 * Setter for the classes namespace
	 *
	 * @param string $classNamespace
	 * @return void
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
		if ($this->classNamespace === NULL) {
			$this->classNamespace = $this->findClassNamespace();
		}
		return $this->classNamespace;
	}

	/**
	 * Extracts the classes namespace
	 *
	 * @return string the extracted Class namespace
	 */
	protected function findClassNamespace() {
		$matches = array();
		preg_match('/^namespace\s+(.*);/m', $this->originalClassCode, $matches);
		return $matches[1];
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
	 * @return string the modified string
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function removeUTF8Declaration() {
		$this->processedClassCode = preg_replace('/^declare\(ENCODING = \'utf-8\'\);\n/m', '', $this->processedClassCode);
	}

	/**
	 * Removes the line "namespace F3/Package/Subpackage..." that appears on top of all FLOW3 classes.
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function removeNamespaceDeclarations() {
		$this->processedClassCode = preg_replace('/^namespace\s+.*;\n/m', '', $this->processedClassCode);
	}

	/**
	 * Removes the backslash from global PHP Classes (e.g. \Exception)
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function removeGlobalNamespaceSeparators() {
		$this->processedClassCode = preg_replace('/([\( ])\\\\([a-zA-Z]{3,} )/', '$1$2', $this->processedClassCode);
	}

	/**
	 * Turns class MyClass into class Tx_Extension_SupPackage_MyClass
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function transformClassName() {
		$regex = '/((?:abstract )?(?:class|interface)\s)([a-zA-Z]+)/';
		$that = $this;
		$this->processedClassCode = preg_replace_callback($regex, function($result) use (&$that) {
			return $result[1] . $that->convertClassName($that->getClassNamespace() . '\\' . $result[2]);
		}, $this->processedClassCode);
	}

	/**
	 * Transforms all namespaced object names into their un-namespaced equivalents.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function transformObjectNames() {
		$regex = '/
			\\\\?
			(F3(?:\\\\\w+)+)
		/x';
		$that = $this;
		$this->processedClassCode = preg_replace_callback($regex, function($result) use (&$that) {
			return $that->convertClassName($result[1]);
		}, $this->processedClassCode);
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
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function replaceString($searchString, $replaceString) {
		$this->processedClassCode = str_replace($searchString, $replaceString, $this->processedClassCode);
	}
}
?>