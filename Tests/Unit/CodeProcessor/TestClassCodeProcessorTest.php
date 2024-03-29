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
 * Testcase for TestClassCodeProcessorTest
 *
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class TestClassCodeProcessorTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function simpleTestClassIsBackportedCorrectly() {
		$classCode = '<?php
namespace TYPO3\SomePackage\MySubpackage;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        */

 /**
 */

class SomeTest extends \TYPO3\Testing\BaseTestCase {

	/**
	 * String to be replaced
	 *
	 * @test
	 * @author John Doe <john@doe.com>
	 */
	public function someTestMethod() {
		$someMock = $this->getMock(\'Tx_MyPackage_MySubpackage_Foo\', array(\'someMethod\'));
		$this->assertEquals($expectedResult, $actualResult);
	}
}
?>';
		$expectedResult = '<?php

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        */

 /**
 */

class Tx_MyExtension_MySubpackage_SomeTest extends Tx_Extbase_Base_testcase {

	/**
	 * The replaced string
	 *
	 * @test
	 * @author John Doe <john@doe.com>
	 */
	public function someTestMethod() {
		$someMock = $this->getMock(\'Tx_MyPackage_MySubpackage_Foo\', array(\'someMethod\'));
		$this->assertEquals($expectedResult, $actualResult);
	}
}
?>';
		$codeProcessor = new \TYPO3\Backporter\CodeProcessor\TestClassCodeProcessor();
		$codeProcessor->setExtensionKey('MyExtension');
		$codeProcessor->setClassCode($classCode);
		$actualResult = $codeProcessor->processCode(
			array(
				'String to be replaced' => 'The replaced string',
				'TYPO3\Testing\BaseTestCase' => 'Tx_Extbase_Base_testcase'
			)
		);
		$this->assertEquals($expectedResult, $actualResult);
	}
}

?>