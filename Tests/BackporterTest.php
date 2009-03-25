<?php
declare(ENCODING = 'utf-8');
namespace F3\Backporter;

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
 * Testcase for Backporter
 *
 * @package Backporter
 * @subpackage Tests
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class BackporterTest extends \F3\Testing\BaseTestCase {

	protected $sourceFixturePath;
	protected $targetFixturePath;
	protected $backporter;

	public function setUp() {
		$this->sourceFixturePath = \F3\FLOW3\Utility\Files::concatenatePaths(array(__DIR__, 'Fixture/Source'));
		$this->targetFixturePath = \F3\FLOW3\Utility\Files::concatenatePaths(array(__DIR__, 'Fixture/Target'));
		$this->backporter = $this->getMock($this->buildAccessibleProxy('F3\Backporter\Backporter'), array('dummy'), array(), '', FALSE);
	}

	/**
	 * @test
	 * @expectedException \F3\Backporter\Exception\InvalidPathException
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function SetSourcePaththrowsExceptionIfSourcePathDoesNotExist() {
		$this->backporter->setSourcePath('NonExistingPath');
	}

	/**
	 * @test
	 * @expectedException \F3\Backporter\Exception\InvalidPathException
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function SetTargetPaththrowsExceptionIfTargetPathDoesNotExist() {
		$this->backporter->setTargetPath('NonExistingPath');
	}

	/**
	 * @test
	 * @expectedException \F3\Backporter\Exception\InvalidPathException
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function SetTargetPaththrowsExceptionIfTargetPathIsNotEmpty() {
		$this->backporter->setTargetPath(__DIR__);
	}
}



?>
