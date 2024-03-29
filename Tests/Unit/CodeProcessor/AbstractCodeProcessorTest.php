<?php
namespace TYPO3\Backporter\CodeProcessor;

/*                                                                        *
 * This script belongs to the Flow package "BackPorter".                 *
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

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * Testcase for AbstractCodeProcessor
 *
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class AbstractCodeProcessorTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\Backporter\CodeProcessor\AbstractCodeProcessor
	 */
	protected $codeProcessor;

	public function setUp() {
		$this->codeProcessor = $this->getMock($this->buildAccessibleProxy('TYPO3\Backporter\CodeProcessor\AbstractCodeProcessor'), array('processCode'), array(), '', FALSE);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function extensionKeysAreProperlyTransformed() {
		$extensionKey = 'my_extension_key';
		$expectedResult = 'MyExtensionKey';
		$this->assertEquals($expectedResult, $this->codeProcessor->_call('upperCaseExtensionKey', $extensionKey));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function namespaceCanBeExtractedFromClassCode() {
		$classCode = '<?php
namespace TYPO3\Flow\Cache\Frontend;

foobar';
		$expectedResult = 'TYPO3\Flow\Cache\Frontend';
		$this->codeProcessor->setClassCode($classCode);
		$this->assertEquals($expectedResult, $this->codeProcessor->getClassNamespace());
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function namespaceDeclarationsCanBeRemoved() {
		$classCode = '<?php
namespace TYPO3\Flow\Cache\Frontend;

foobar';
		$expectedResult = '<?php

foobar';
		$this->codeProcessor->setClassCode($classCode);
		$this->codeProcessor->removeNamespaceDeclarations();
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function globalNamespaceSeparatorsCanBeRemoved() {
		$classCode = 'class FooBar extends \ArrayObject {
public function someMethod(\ArrayObject $arguments, \TYPO3\Flow\Subpackage\FooInterface $someFlowObject) {
	try {
		$someOtherFlowObject = new \TYPO3\Flow\Subpackage\Bar();
	} catch (\Exception $exception) {
	}
}';
		$expectedResult = 'class FooBar extends ArrayObject {
public function someMethod(ArrayObject $arguments, \TYPO3\Flow\Subpackage\FooInterface $someFlowObject) {
	try {
		$someOtherFlowObject = new \TYPO3\Flow\Subpackage\Bar();
	} catch (Exception $exception) {
	}
}';
		$this->codeProcessor->setClassCode($classCode);
		$this->codeProcessor->removeGlobalNamespaceSeparators();
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function classScopePrototypeCanBeDetermined() {
		$classCode = '
/**
 * Some comments
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @Flow\Scope("prototype")
 */
 class SomeClassname {';
		$expectedResult = \TYPO3\Backporter\CodeProcessor\AbstractCodeProcessor::SCOPE_PROTOTYPE;
		$this->codeProcessor->setClassCode($classCode);
		$this->assertEquals($expectedResult, $this->codeProcessor->getClassScope());
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function classScopeSingletonCanBeDetermined() {
		$classCode = '
/**
 * Some comments
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @Flow\Scope("singleton")
 */
 class SomeClassname {';
		$expectedResult = \TYPO3\Backporter\CodeProcessor\AbstractCodeProcessor::SCOPE_SINGLETON;
		$this->codeProcessor->setClassCode($classCode);
		$this->assertEquals($expectedResult, $this->codeProcessor->getClassScope());
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function classScopeSessionCanBeDetermined() {
		$classCode = '
/**
 * @Flow\Scope("session")
 * @someOtherAnnotation
 */
 class SomeClassname {';
		$expectedResult = \TYPO3\Backporter\CodeProcessor\AbstractCodeProcessor::SCOPE_SESSION;
		$this->codeProcessor->setClassCode($classCode);
		$this->assertEquals($expectedResult, $this->codeProcessor->getClassScope());
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function classScopeDefaultsToPrototype() {
		$classCode = '
/**
 * Some comments
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
 class SomeClassname {';
		$expectedResult = \TYPO3\Backporter\CodeProcessor\AbstractCodeProcessor::SCOPE_PROTOTYPE;
		$this->codeProcessor->setClassCode($classCode);
		$this->assertEquals($expectedResult, $this->codeProcessor->getClassScope());
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Backporter\Exception\InvalidScopeException
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function invalidScopeAnnotationThrowsException() {
		$classCode = '
/**
 * Some comments
 *
 * @Flow\Scope("someNonExistingScope")
 */
 class SomeClassname {';
		$this->codeProcessor->setClassCode($classCode);
		$this->codeProcessor->getClassScope();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function scopeAnnotationCanBeRemoved() {
		$classCode = '
/**
 * some comment before
 * @Flow\Scope("prototype")
 * some comment after
 */
 class SomeClassname {';
		$expectedResult = '
/**
 * some comment before
 * some comment after
 */
 class SomeClassname {';
		$this->codeProcessor->setClassCode($classCode);
		$this->codeProcessor->removeScopeAnnotation();
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}

	/**
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function classSignatures() {
		return array(
			array('
class SomeClassname {
', '
class SomeClassname {
'),array('
class SomeClassname extends Some\Other\Class {
', '
class SomeClassname extends Some\Other\Class {
'),array('
/**
 * Template parser building up an object syntax tree
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class TemplateParser {
','
/**
 * Template parser building up an object syntax tree
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class TemplateParser {
'),array('
/**
 * @Flow\Scope("singleton")
 */
class SomeClassname implements Some\Interface {
', '
/**
 */
class SomeClassname implements Some\Interface, t3lib_Singleton {
'),array(
'
/**
 * @Flow\Scope("prototype")
 */
class SomeClassname implements Some\Interface {
', '
/**
 */
class SomeClassname implements Some\Interface {
'),array(
'
/**
 * @Flow\Scope("session")
 */
class SomeClassname extends Some\Other\Class implements Some\Interface {
', '
/**
 */
class SomeClassname extends Some\Other\Class implements Some\Interface {
'),array(
'
interface SomeInterface {
', '
interface SomeInterface {
')
,array(
'
interface SomeInterface extends SomeOtherInterface {
', '
interface SomeInterface extends SomeOtherInterface {
')
		);
	}

	/**
	 * @test
	 * @dataProvider classSignatures
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function processScopeAnnotationCorrectlyRemovesAnnotationAndAddsT3libSingletonInterfaceToSingletonClasses($classCode, $expectedResult) {
		$this->codeProcessor->setClassCode($classCode);
		$this->codeProcessor->processScopeAnnotation();
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function classNameCanBeTransformed() {
		$classCode = '// some class name within comments
abstract class Some123ClassName implements \TYPO3\Package\Subpackage\SomeInterface {
';
		$expectedResult = '// some class name within comments
abstract class Tx_ExtensionKey_Subpackage_Some123ClassName implements \TYPO3\Package\Subpackage\SomeInterface {
';
		$this->codeProcessor->setClassCode($classCode);
		$this->codeProcessor->setExtensionKey('extension_key');
		$this->codeProcessor->setClassNamespace('TYPO3\Package\Subpackage');
		$this->codeProcessor->transformClassName();
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function interfaceNameCanBeTransformed() {
		$classCode = '
interface Some123Interface extends \TYPO3\Package\Subpackage\SomeInterface {
';
		$expectedResult = '
interface Tx_ExtensionKey_Subpackage_Some123Interface extends \TYPO3\Package\Subpackage\SomeInterface {
';
		$this->codeProcessor->setClassCode($classCode);
		$this->codeProcessor->setExtensionKey('extension_key');
		$this->codeProcessor->setClassNamespace('TYPO3\Package\Subpackage');
		$this->codeProcessor->transformClassName();
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function objectNamesAreConverted() {
		$classCode = 'class FooBar extends \TYPO3\Fluid\TestingBla {
	public function someMethod($arguments, \TYPO3\Fluid\Subpackage\FooInterface $someFlowObject) {
		try {
			$someOtherFlowObject = new \TYPO3\Fluid\Subpackage\Bar();
			$someOtherFlowObject = objectFactory->create(\'TYPO3\Flow\Subpackage\Bar\');
		} catch (\Exception $exception) {
		}
	}';
		$expectedResult = 'class FooBar extends Tx_Fluid_TestingBla {
	public function someMethod($arguments, Tx_Fluid_Subpackage_FooInterface $someFlowObject) {
		try {
			$someOtherFlowObject = new Tx_Fluid_Subpackage_Bar();
			$someOtherFlowObject = objectFactory->create(\'Tx_Fluid_Subpackage_Bar\');
		} catch (\Exception $exception) {
		}
	}';
		$this->codeProcessor->setClassCode($classCode);
		$this->codeProcessor->setExtensionKey('fluid');
		$this->codeProcessor->setClassNamespace('TYPO3\Package\Subpackage');
		$this->codeProcessor->transformObjectNames();
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function stringCanBeReplaced() {
		$classCode = 'Foo bar foo Foo';
		$expectedResult = 'Bar bar foo Bar';
		$this->codeProcessor->setClassCode($classCode);
		$unusedReplacePairs = array();
		$this->codeProcessor->replaceString('Foo', 'Bar', $unusedReplacePairs);
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function multipleStringsCanBeReplaced() {
		$classCode = 'Foo bar foo Foo';
		$expectedResult = 'Bar foo foo Bar';
		$this->codeProcessor->setClassCode($classCode);
		$unusedReplacePairs = array();
		$this->codeProcessor->replaceStrings(array('Foo' => 'Bar', 'bar' => 'foo'), $unusedReplacePairs);
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function methodNamesCanBePrefixed() {
		$classCode = 'class FooBar extends \TYPO3\Fluid\TestingBla {
	public function someMethod($arguments, \TYPO3\Fluid\Subpackage\FooInterface $someFlowObject) {
		foo();
	}';
		$expectedResult = 'class FooBar extends \TYPO3\Fluid\TestingBla {
	public function somePrefix_someMethod($arguments, \TYPO3\Fluid\Subpackage\FooInterface $someFlowObject) {
		foo();
	}';
		$this->codeProcessor->setClassCode($classCode);
		$this->codeProcessor->prefixMethodNames('somePrefix_');
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function methodsCanBeExcludedFromBeingPrefixed() {
		$classCode = '	public function toBeExcluded() {}
	protected function someProtectedMethod() {}
	public function publicMethod() {}';
		$expectedResult = '	public function toBeExcluded() {}
	protected function someProtectedMethod() {}
	public function somePrefix_publicMethod() {}';
		$this->codeProcessor->setClassCode($classCode);
		$this->codeProcessor->prefixMethodNames('somePrefix_', array('protected function '), array('toBeExcluded'));
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function methodNamesCanBeSuffixed() {
		$classCode = 'class FooBar extends \TYPO3\Fluid\TestingBla {
	public function someMethod($arguments, \TYPO3\Fluid\Subpackage\FooInterface $someFlowObject) {
		foo();
	}';
		$expectedResult = 'class FooBar extends \TYPO3\Fluid\TestingBla {
	public function someMethod_someSuffix($arguments, \TYPO3\Fluid\Subpackage\FooInterface $someFlowObject) {
		foo();
	}';
		$this->codeProcessor->setClassCode($classCode);
		$this->codeProcessor->suffixMethodNames('_someSuffix');
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function methodsCanBeExcludedFromBeingSuffixed() {
		$classCode = '	public function toBeExcluded() {}
	protected function someProtectedMethod() {}
	public function publicMethod() {}';
		$expectedResult = '	public function toBeExcluded() {}
	protected function someProtectedMethod() {}
	public function publicMethod_someSuffix() {}';
		$this->codeProcessor->setClassCode($classCode);
		$this->codeProcessor->suffixMethodNames('_someSuffix', array('protected function '), array('toBeExcluded'));
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function classNameCanBePrefixed() {
		$classCode = 'abstract class FooBar extends \TYPO3\Fluid\TestingBla {
	public function someMethod($arguments, \TYPO3\Fluid\Subpackage\FooInterface $someFlowObject) {
		foo();
	}';
		$expectedResult = 'abstract class SomePrefix_FooBar extends \TYPO3\Fluid\TestingBla {
	public function someMethod($arguments, \TYPO3\Fluid\Subpackage\FooInterface $someFlowObject) {
		foo();
	}';
		$this->codeProcessor->setClassCode($classCode);
		$this->codeProcessor->prefixClassName('SomePrefix_');
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function classNameCanBeSuffixed() {
		$classCode = 'abstract class FooBar extends \TYPO3\Fluid\TestingBla {
	public function someMethod($arguments, \TYPO3\Fluid\Subpackage\FooInterface $someFlowObject) {
		foo();
	}';
		$expectedResult = 'abstract class FooBar_SomeSuffix extends \TYPO3\Fluid\TestingBla {
	public function someMethod($arguments, \TYPO3\Fluid\Subpackage\FooInterface $someFlowObject) {
		foo();
	}';
		$this->codeProcessor->setClassCode($classCode);
		$this->codeProcessor->suffixClassName('_SomeSuffix');
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function classHeaderCanBeAdded() {
		$classCode = '<?php
/*
 * Some header comment
 */

abstract class FooBar extends \TYPO3\Fluid\TestingBla {';
		$expectedResult = '<?php
/*
 * Some header comment
 */

// added header line
abstract class FooBar extends \TYPO3\Fluid\TestingBla {';
		$this->codeProcessor->setClassCode($classCode);
		$this->codeProcessor->addClassHeader('// added header line');
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}
}


?>
