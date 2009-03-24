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

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function UTF8DeclarationCanBeRemoved() {
		$codeProcessor = $this->getMock('F3\Backporter\CodeProcessor\AbstractCodeProcessor', array('processString'), array(), '', FALSE);
		
		$inputString = '<?php
declare(ENCODING = \'utf-8\');
namespace F3\FLOW3\Cache\Frontend;

foobar';
		$expectedResult = '<?php
namespace F3\FLOW3\Cache\Frontend;

foobar';
		$actualResult = $codeProcessor->removeUTF8Declaration($inputString);
		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function NamespaceDeclarationsCanBeRemoved() {
		$codeProcessor = $this->getMock('F3\Backporter\CodeProcessor\AbstractCodeProcessor', array('processString'), array(), '', FALSE);
		
		$inputString = '<?php
declare(ENCODING = \'utf-8\');
namespace F3\FLOW3\Cache\Frontend;

foobar';
		$expectedResult = '<?php
declare(ENCODING = \'utf-8\');

foobar';
		$actualResult = $codeProcessor->removeNamespaceDeclarations($inputString);
		$this->assertEquals($expectedResult, $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function GlobalNamespaceSeparatorsCanBeRemoved() {
		$codeProcessor = $this->getMock('F3\Backporter\CodeProcessor\AbstractCodeProcessor', array('processString'), array(), '', FALSE);
		
		$inputString = 'class FooBar extends \ArrayObject {
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
		$actualResult = $codeProcessor->removeGlobalNamespaceSeparators($inputString);
		$this->assertEquals($expectedResult, $actualResult);
	}
}


?>
