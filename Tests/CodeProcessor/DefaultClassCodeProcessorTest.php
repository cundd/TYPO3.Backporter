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
 * Testcase for DefaultClassCodeProcessorTest
 *
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
namespace F3\SomePackage\MySubpackage;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        */

 /**
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
		$codeProcessor->setExtensionKey('MyExtension');
		$codeProcessor->setClassCode($classCode);
		$actualResult = $codeProcessor->processCode(
			array(
				'String to be replaced' => 'The replaced string',
				'F3\MyPackage\MySubpackage\ClassNameToBeReplaced' => 'Tx_MyExtension_MySubpackage_ReplacedClassName'
			)
		);
		$this->assertEquals($expectedResult, $actualResult);
	}
}

?>