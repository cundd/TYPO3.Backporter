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
 * Collection of backport utility methods
 *
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
abstract class AbstractCodeProcessor {

	const PATTERN_NAMESPACE_DECLARATION = '/^namespace\s+(?P<namespace>.*);\n/m';
	const PATTERN_ENCODING_DECLARATION = '/^declare\(ENCODING = \'(?P<encoding>[^\']+)\'\);\n/m';
	const PATTERN_SCOPE_ANNOTATION = '/^\s+\*\s@scope\s+(?P<scope>[a-zA-Z]+).*\n/m';
	const PATTERN_METHOD_SIGNATURES = '/(?<=^\s)(?P<modifiers>(?P<abstract>abstract )?(?P<visibilityModifier>public|private|protected)\s+function\s+)(?P<methodName>[^ (]+)/m';
	const PATTERN_GLOBAL_OBJECT_NAMES = '/(?<=[( ])(?P<namespaceSeparator>\\\\)(?P<objectName>[a-zA-Z0-9_]{3,})(?=[ ():\n])/m';
	const PATTERN_OBJECT_NAMES = '/\\\\?(?P<objectName>F3(?:\\\\\w+)+)/x';
	const PATTERN_CLASS_SIGNATURE = '/(?<=^)(?P<modifiers>(?P<abstract>abstract )?(?P<type>class|interface)\s)(?P<className>[a-zA-Z0-9_]+)(?<parents>(\sextends\s(?<extends>[a-zA-Z0-9_\\\\]+))?(\simplements\s(?<implements>[a-zA-Z0-9_, \\\\]+))?)(?=\s*{$)/m';
	const PATTERN_CLASS_NAME = '/F3\\\\(?P<packageKey>[A-Za-z0-9]+)(?P<objectName>(?:\\\\\w+)+)/x';

	const SCOPE_PROTOTYPE = 'prototype';
	const SCOPE_SINGLETON = 'singleton';
	const SCOPE_SESSION = 'session';

	/**
	 * Unmodified FLOW3 class code.
	 *
	 * @var string
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
	 * Scope of the class (One of the SCOPE_* constants)
	 *
	 * @var string
	 */
	protected $scope = NULL;

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
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setClassCode($classCode) {
		$this->processedClassCode = $this->originalClassCode = $classCode;
		$this->classNamespace = NULL;
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
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function setClassNamespace($classNamespace) {
		$this->classNamespace = $classNamespace;
	}

	/**
	 * Getter for the classes namespace
	 *
	 * @return string $classNamespace
	 * @author Bastian Waidelich <bastian@typo3.org>
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
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function findClassNamespace() {
		$matches = array();
		preg_match(self::PATTERN_NAMESPACE_DECLARATION, $this->originalClassCode, $matches);
		return $matches['namespace'];
	}

	/**
	 * Getter for the classes scope
	 *
	 * @return string $scope (one of SCOPE_* constants)
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function getClassScope() {
		$matches = array();
		preg_match(self::PATTERN_SCOPE_ANNOTATION, $this->originalClassCode, $matches);
		if (!isset($matches['scope'])) {
			return self::SCOPE_PROTOTYPE;
		}
		switch ($matches['scope']) {
			case self::SCOPE_PROTOTYPE:
				return self::SCOPE_PROTOTYPE;
			case self::SCOPE_SESSION:
				return self::SCOPE_SESSION;
			case self::SCOPE_SINGLETON:
				return self::SCOPE_SINGLETON;
			default:
				throw new \F3\Backporter\Exception\InvalidScopeException('Invalid scope "' . $matches['scope'] . '"');
		}
	}

	/**
	 * Removes @scope annotation and adds t3lib_Singleton to the list of implemented interfaces
	 * If the class is of scope singleton
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function processScopeAnnotation() {
		$scope = $this->getClassScope();
		$this->removeScopeAnnotation();
		if ($scope !== self::SCOPE_SINGLETON) {
			return;
		}
		$this->processedClassCode = preg_replace_callback(self::PATTERN_CLASS_SIGNATURE, function($matches) {
			$classSignature = $matches['modifiers'] . $matches['className'];
			if (!empty($matches['extends'])) {
				$classSignature .= ' extends ' . $matches['extends'];
			}
			if (trim($matches['modifiers']) === 'interface') {
				return $classSignature;
			}
			if (!empty($matches['implements'])) {
				$classSignature .= ' implements ' . trim($matches['implements']) . ', t3lib_Singleton ';
			} else {
				$classSignature .= ' implements t3lib_Singleton';
			}
			return $classSignature;
		}, $this->processedClassCode);
	}

	/**
	 * Removes @scope prototype|singleton|session annotations
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function removeScopeAnnotation() {
		$this->processedClassCode = preg_replace(self::PATTERN_SCOPE_ANNOTATION, '', $this->processedClassCode);
	}

	/**
	 * Setter for the target extension key
	 *
	 * @param string $extensionKey
	 * @author Bastian Waidelich <bastian@typo3.org>
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
	 * @author Bastian Waidelich <bastian@typo3.org>
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
	public function removeEncodingDeclaration() {
		$this->processedClassCode = preg_replace(self::PATTERN_ENCODING_DECLARATION, '', $this->processedClassCode);
	}

	/**
	 * Removes the line "namespace F3/Package/Subpackage..." that appears on top of all FLOW3 classes.
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function removeNamespaceDeclarations() {
		$this->processedClassCode = preg_replace(self::PATTERN_NAMESPACE_DECLARATION, '', $this->processedClassCode);
	}

	/**
	 * Removes the backslash from global PHP Classes (e.g. \Exception)
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function removeGlobalNamespaceSeparators() {
		$this->processedClassCode = preg_replace_callback(self::PATTERN_GLOBAL_OBJECT_NAMES, function($matches) {
			return $matches['objectName'];
		}, $this->processedClassCode);
	}

	/**
	 * Turns class MyClass into class Tx_Extension_SupPackage_MyClass
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function transformClassName() {
		$that = $this;
		$this->processedClassCode = preg_replace_callback(self::PATTERN_CLASS_SIGNATURE, function($matches) use (&$that) {
			return $matches['modifiers'] . $that->convertClassName($that->getClassNamespace() . '\\' . $matches['className']) . $matches['parents'];
		}, $this->processedClassCode);
	}

	/**
	 * Transforms all namespaced object names into their un-namespaced equivalents.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function transformObjectNames() {
		$that = $this;
		$this->processedClassCode = preg_replace_callback(self::PATTERN_OBJECT_NAMES, function($matches) use (&$that) {
			return $that->convertClassName($matches['objectName']);
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
		preg_match(self::PATTERN_CLASS_NAME, $oldClassName, $matches);

		$newClassName = 'Tx_';
		$newClassName .= $this->upperCasedExtensionKey;
		$newClassName .= str_replace('\\', '_', $matches['objectName']);

		return $newClassName;
	}

	/**
	 * Replaces all occurences of search strings in $replacePairs by their replace strings.
	 *
	 * @param array $replacePairs an array containing strings to be replaced. Key = search string, value = replacement string.
	 * @return void
	 * @see replaceString()
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function replaceStrings(array $replacePairs = array()) {
		foreach($replacePairs as $searchString => $replaceString) {
			$this->replaceString($searchString, $replaceString);
		}
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

	/**
	 * Prefixes all methods with a given prefix
	 *
	 * @param string $prefix string to be prepended to method names
	 * @param array $excludeModifiers methods with the specified modifiers (abstract private/public/protected) will be excluded
	 * @param array $excludeMethodNames methods to be excluded
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function prefixMethodNames($prefix, array $excludeModifiers = array(), array $excludeMethodNames = array()) {
		$replacementsToBeDone = array();

		$this->processedClassCode = preg_replace_callback(self::PATTERN_METHOD_SIGNATURES, function($matches) use ($prefix, $excludeModifiers, $excludeMethodNames, &$replacementsToBeDone) {
			if (in_array($matches['modifiers'], $excludeModifiers) || in_array($matches['methodName'], $excludeMethodNames)) {
				return $matches['modifiers'] . $matches['methodName'];
			}
			// prepend all local calls as well ($this->yourMethodCall)
			$replacementsToBeDone['$this->' . $matches['methodName'] . '('] = '$this->' . $prefix . $matches['methodName'] . '(';
			return $matches['modifiers'] . $prefix . $matches['methodName'];
		}, $this->processedClassCode);
		$this->replaceStrings($replacementsToBeDone);
	}

	/**
	 * Suffixes all methods with a given prefix
	 *
	 * @param string $suffix string to be appended to method names
	 * @param array $excludeModifiers methods with the specified modifiers (abstract private/public/protected) will be excluded
	 * @param array $excludeMethodNames methods to be excluded
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function suffixMethodNames($suffix, array $excludeModifiers = array(), array $excludeMethodNames = array()) {
		$this->processedClassCode = preg_replace_callback(self::PATTERN_METHOD_SIGNATURES, function($matches) use ($suffix, $excludeModifiers, $excludeMethodNames) {
			if (in_array($matches['modifiers'], $excludeModifiers) || in_array($matches['methodName'], $excludeMethodNames)) {
				return $matches['modifiers'] . $matches['methodName'];
			}
			return $matches['modifiers'] . $matches['methodName'] . $suffix;
		}, $this->processedClassCode);
	}

	/**
	 * Prefixes class name with a given prefix
	 *
	 * @param string $prefix string to be prepended to class name
	 * @param array $excludeMethods methods to be excluded (method names including modifiers)
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function prefixClassName($prefix) {
		$this->processedClassCode = preg_replace_callback(self::PATTERN_CLASS_SIGNATURE, function($matches) use ($prefix) {
			return $matches['modifiers'] . $prefix . $matches['className'] . $matches['parents'];
		}, $this->processedClassCode);
	}

	/**
	 * Suffixes class name with a given prefix
	 *
	 * @param string $suffix string to be appended to class name
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function suffixClassName($suffix) {
		$this->processedClassCode = preg_replace_callback(self::PATTERN_CLASS_SIGNATURE, function($matches) use ($suffix) {
			return $matches['modifiers'] . $matches['className'] . $suffix . $matches['parents'];
		}, $this->processedClassCode);
	}

	/**
	 * Inserts the given string above the class declaration
	 *
	 * @param string $classHeader line to be inserted above class declaration, e.g. an include/require statement
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function addClassHeader($classHeader) {
		$this->processedClassCode = preg_replace_callback(self::PATTERN_CLASS_SIGNATURE, function($matches) use ($classHeader) {
			return $classHeader . chr(10) . $matches['modifiers'] . $matches['className'] . $matches['parents'];
		}, $this->processedClassCode);
	}

	/**
	 * Add package and subpackage annotations after @version annotation in file-level docblock.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function addPackageAndSubpackageAnnotations() {
		$that = $this;
		$this->processedClassCode = preg_replace_callback('/ @version(.*)/', function($matches) use ($that) {
			$classNamespaceArray = explode('\\', $that->getClassNamespace());
			array_shift($classNamespaceArray);
			$package = array_shift($classNamespaceArray);
			$subpackage = implode('\\', $classNamespaceArray);
			return $matches[0]
			. chr(10) . ' * @package ' . $package
			. chr(10) . ' * @subpackage ' . $subpackage;
		}, $this->processedClassCode);
	}
}
?>