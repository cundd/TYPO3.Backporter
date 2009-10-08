<?php
declare(ENCODING = 'utf-8');
namespace F3\Backporter;

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
 * Testcase for Backporter
 *
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class BackporterTest extends \F3\Testing\BaseTestCase {

	protected $sourceFixturePath;
	protected $targetFixturePath;
	protected $backporter;

	public function setUp() {
		$this->markTestSkipped();
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

}



?>
