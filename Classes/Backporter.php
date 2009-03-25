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
 * @version $Id$
 */

/**
 * Backporter main class
 *
 * @package Backporter
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class Backporter {
	public function __construct($sourcePath, $targetPath) {
		if (!is_dir($sourcePath)) {
			throw new \InvalidArgumentException('sourcePath "' . $sourcePath . '" is no directory');
		}
		if (!is_dir($targetPath)) {
			throw new \InvalidArgumentException('targetPath "' . $targetPath . '" is no directory');
		}
	}
}
?>