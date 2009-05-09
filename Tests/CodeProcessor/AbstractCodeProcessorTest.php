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
 * @subpackage Tests
 * @version $Id$
 */
/**
 * Testcase for AbstractCodeProcessor
 *
 * @package Backporter
 * @subpackage Tests
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

class AbstractCodeProcessorTest extends \F3\Testing\BaseTestCase {

	protected $codeProcessor;

	public function setUp() {
		$this->codeProcessor = $this->getMock($this->buildAccessibleProxy('F3\Backporter\CodeProcessor\AbstractCodeProcessor'), array('processCode'), array(), '', FALSE);
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
declare(ENCODING = \'utf-8\');
namespace F3\FLOW3\Cache\Frontend;

foobar';
		$expectedResult = 'F3\FLOW3\Cache\Frontend';
		$this->codeProcessor->setClassCode($classCode);
		$this->assertEquals($expectedResult, $this->codeProcessor->getClassNamespace());
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function EncodingDeclarationCanBeRemoved() {
		$classCode = '<?php
declare(ENCODING = \'utf-8\');
namespace F3\FLOW3\Cache\Frontend;

foobar';
		$expectedResult = '<?php
namespace F3\FLOW3\Cache\Frontend;

foobar';
		$this->codeProcessor->setClassCode($classCode);
		$this->codeProcessor->removeEncodingDeclaration($classCode);
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function namespaceDeclarationsCanBeRemoved() {
		$classCode = '<?php
declare(ENCODING = \'utf-8\');
namespace F3\FLOW3\Cache\Frontend;

foobar';
		$expectedResult = '<?php
declare(ENCODING = \'utf-8\');

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
public function someMethod(\ArrayObject $arguments, \F3\FLOW3\Subpackage\FooInterface $someFlow3Object) {
	try {
		$someOtherFlow3Object = new \F3\FLOW3\Subpackage\Bar();
	} catch (\Exception $exception) {
	}
}';
		$expectedResult = 'class FooBar extends ArrayObject {
public function someMethod(ArrayObject $arguments, \F3\FLOW3\Subpackage\FooInterface $someFlow3Object) {
	try {
		$someOtherFlow3Object = new \F3\FLOW3\Subpackage\Bar();
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
	public function classNameCanBeTransformed() {
		$classCode = '// some class name within comments
abstract class Some123ClassName implements \F3\Package\Subpackage\SomeInterface';
		$expectedResult = '// some class name within comments
abstract class Tx_ExtensionKey_Subpackage_Some123ClassName implements \F3\Package\Subpackage\SomeInterface';
		$this->codeProcessor->setClassCode($classCode);
		$this->codeProcessor->setExtensionKey('extension_key');
		$this->codeProcessor->setClassNamespace('F3\Package\Subpackage');
		$this->codeProcessor->transformClassName();
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function interfaceNameCanBeTransformed() {
		$classCode = 'interface Some123Interface extends \F3\Package\Subpackage\SomeInterface';
		$expectedResult = 'interface Tx_ExtensionKey_Subpackage_Some123Interface extends \F3\Package\Subpackage\SomeInterface';
		$this->codeProcessor->setClassCode($classCode);
		$this->codeProcessor->setExtensionKey('extension_key');
		$this->codeProcessor->setClassNamespace('F3\Package\Subpackage');
		$this->codeProcessor->transformClassName();
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function objectNamesAreConverted() {
		$classCode = 'class FooBar extends \F3\Fluid\TestingBla {
	public function someMethod($arguments, \F3\Fluid\Subpackage\FooInterface $someFlow3Object) {
		try {
			$someOtherFlow3Object = new \F3\Fluid\Subpackage\Bar();
			$someOtherFlow3Object = objectFactory->create(\'F3\FLOW3\Subpackage\Bar\');
		} catch (\Exception $exception) {
		}
	}';
		$expectedResult = 'class FooBar extends Tx_Fluid_TestingBla {
	public function someMethod($arguments, Tx_Fluid_Subpackage_FooInterface $someFlow3Object) {
		try {
			$someOtherFlow3Object = new Tx_Fluid_Subpackage_Bar();
			$someOtherFlow3Object = objectFactory->create(\'Tx_Fluid_Subpackage_Bar\');
		} catch (\Exception $exception) {
		}
	}';
		$this->codeProcessor->setClassCode($classCode);
		$this->codeProcessor->setExtensionKey('fluid');
		$this->codeProcessor->setClassNamespace('F3\Package\Subpackage');
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
		$this->codeProcessor->replaceString('Foo', 'Bar');
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
		$this->codeProcessor->replaceStrings(array('Foo' => 'Bar', 'bar' => 'foo'));
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function methodNamesCanBePrefixed() {
		$classCode = 'class FooBar extends \F3\Fluid\TestingBla {
	public function someMethod($arguments, \F3\Fluid\Subpackage\FooInterface $someFlow3Object) {
		foo();
	}';
		$expectedResult = 'class FooBar extends \F3\Fluid\TestingBla {
	public function somePrefix_someMethod($arguments, \F3\Fluid\Subpackage\FooInterface $someFlow3Object) {
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
		$classCode = 'class FooBar extends \F3\Fluid\TestingBla {
	public function someMethod($arguments, \F3\Fluid\Subpackage\FooInterface $someFlow3Object) {
		foo();
	}';
		$expectedResult = 'class FooBar extends \F3\Fluid\TestingBla {
	public function someMethod_someSuffix($arguments, \F3\Fluid\Subpackage\FooInterface $someFlow3Object) {
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
		$classCode = 'abstract class FooBar extends \F3\Fluid\TestingBla {
	public function someMethod($arguments, \F3\Fluid\Subpackage\FooInterface $someFlow3Object) {
		foo();
	}';
		$expectedResult = 'abstract class SomePrefix_FooBar extends \F3\Fluid\TestingBla {
	public function someMethod($arguments, \F3\Fluid\Subpackage\FooInterface $someFlow3Object) {
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
		$classCode = 'abstract class FooBar extends \F3\Fluid\TestingBla {
	public function someMethod($arguments, \F3\Fluid\Subpackage\FooInterface $someFlow3Object) {
		foo();
	}';
		$expectedResult = 'abstract class FooBar_SomeSuffix extends \F3\Fluid\TestingBla {
	public function someMethod($arguments, \F3\Fluid\Subpackage\FooInterface $someFlow3Object) {
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

abstract class FooBar extends \F3\Fluid\TestingBla {';
		$expectedResult = '<?php
/*
 * Some header comment
 */

// added header line
abstract class FooBar extends \F3\Fluid\TestingBla {';
		$this->codeProcessor->setClassCode($classCode);
		$this->codeProcessor->addClassHeader('// added header line');
		$this->assertEquals($expectedResult, $this->codeProcessor->_get('processedClassCode'));
	}
}


?>
