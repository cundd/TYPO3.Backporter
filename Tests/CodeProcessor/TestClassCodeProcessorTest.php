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
 * Testcase for TestClassCodeProcessorTest
 *
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class TestClassCodeProcessorTest extends \F3\Testing\BaseTestCase {

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function simpleTestClassIsBackportedCorrectly() {
		$classCode = '<?php
declare(ENCODING = \'utf-8\');
namespace F3\SomePackage\MySubpackage;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        */

 /**
 */

class SomeTest extends \F3\Testing\BaseTestCase {

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

require_once(t3lib_extMgm::extPath(\'extbase\', \'Tests/Base_testcase.php\'));
class Tx_MyExtension_MySubpackage_SomeTest_testcase extends Tx_Extbase_Base_testcase {

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
		$codeProcessor = new \F3\Backporter\CodeProcessor\TestClassCodeProcessor();
		$codeProcessor->setExtensionKey('MyExtension');
		$codeProcessor->setClassCode($classCode);
		$actualResult = $codeProcessor->processCode(
			array(
				'String to be replaced' => 'The replaced string',
				'F3\Testing\BaseTestCase' => 'Tx_Extbase_Base_testcase'
			)
		);
		$this->assertEquals($expectedResult, $actualResult);
	}
}

?>