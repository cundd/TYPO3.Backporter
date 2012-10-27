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

/**
 * Testcase for DefaultClassCodeProcessorTest
 *
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class DefaultClassCodeProcessorTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function simpleClassIsBackportedCorrectly() {
		$classCode = '<?php
namespace TYPO3\SomePackage\MySubpackage;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        */

 /**
 */

class SomeClass extends \TYPO3\MyPackage\MySubpackage\SomeOtherClass implements \TYPO3\MyPackage\SomeInterface {

	/**
	 * String to be replaced.
	 *
	 * @param \ArrayObject $arguments some documentation
	 * @param \TYPO3\MyPackage\SomeInterface $foo some documentation
	 * @return void
	 */
	public function someMethod(\ArrayObject $arguments, \TYPO3\MyPackage\SomeInterface $foo) {
		$bar = $objectFactory->create(\'TYPO3\MyPackage\MySubpackage\ClassNameToBeReplaced\');
	}
}
?>';
		$expectedResult = '<?php

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        */

 /**
 */

class Tx_MyExtension_MySubpackage_SomeClass extends Tx_MyExtension_MySubpackage_SomeOtherClass implements Tx_MyExtension_SomeInterface, t3lib_Singleton {

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
		$codeProcessor = new \TYPO3\Backporter\CodeProcessor\DefaultClassCodeProcessor();
		$codeProcessor->setExtensionKey('MyExtension');
		$codeProcessor->setClassCode($classCode);
		$actualResult = $codeProcessor->processCode(
			array(
				'String to be replaced' => 'The replaced string',
				'TYPO3\MyPackage\MySubpackage\ClassNameToBeReplaced' => 'Tx_MyExtension_MySubpackage_ReplacedClassName'
			)
		);
		$this->assertEquals($expectedResult, $actualResult);
	}
}

?>