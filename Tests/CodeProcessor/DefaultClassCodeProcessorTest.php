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
 * Testcase for DefaultClassCodeProcessorTest
 *
 * @package Backporter
 * @subpackage Tests
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

class DefaultClassCodeProcessorTest extends \F3\Testing\BaseTestCase {

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function simpleClassIsBackportedCorrectly() {
		$classCode = '<?php
declare(ENCODING = \'utf-8\');
namespace F3\Fluid\Core;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        */
 
 /**
 * @package MyPackage
 * @subpackage MySubpackage
 */

class SomeClass extends \F3\MyPackage\MySubpackage\SomeOtherClass implements \F3\MyPackage\SomeInterface {
	/**
	 * String to be replaced.
	 *
	 * @param \ArrayObject $arguments some documentation
	 * @param \F3\MyPackage\SomeInterface $foo some documentation
	 * @return void
	 */
	public function someMethod(\ArrayObject $arguments, \F3\MyPackage\SomeInterface $foo) {
		$bar = $objectFactory->create(\'F3\MyPackage\MySubpackage\ClassNameToBeReplaced\');
	}
}
?>';
		$expectedResult = '<?php

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        */
 
 /**
 * @package MyPackage
 * @subpackage MySubpackage
 */

class Tx_MyExtension_MySubpackage_SomeClass extends Tx_MyExtension_MySubpackage_SomeOtherClass implements Tx_MyExtension_SomeInterface {
	/**
	 * The replaced string.
	 *
	 * @param ArrayObject $arguments some documentation
	 * @param Tx_MyExtension_SomeInterface $foo some documentation
	 * @return void
	 */
	public function someMethod(ArrayObject $arguments, Tx_MyExtension_SomeInterface $foo) {
		$bar = $objectFactory->create(\'Tx_MyExtension_MySubpackage_ReplacedClassName\');
	}
}
?>';
		$codeProcessor = new \F3\Backporter\CodeProcessor\DefaultClassCodeProcessor();
		$codeProcessor->setClassCode($classCode);
		$actualResult = $codeProcessor->processCode(array('String to be replaced' => 'The replaced string', 'Tx_MyExtension_MySubpackage_ClassNameToBeReplaced' => 'Tx_MyExtension_MySubpackage_ReplacedClassName'));
	}
}

?>