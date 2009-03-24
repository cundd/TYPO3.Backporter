<?php
declare(ENCODING = 'utf-8');
namespace F3\Backporter\Controller;

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
 * @subpackage Controller
 * @version $Id$
 */
/**
 * Packporter Default Controller
 *
 * @package Backporter
 * @subpackage Controller
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class DefaultController extends \F3\FLOW3\MVC\Controller\ActionController {

	protected $defaultViewObjectName = 'F3\Fluid\View\TemplateView';
	
	public function indexAction() {
	}

	/*
	 * @param string $sourcePath
	 * @param string $targetPath
	 */
	public function backportAction($sourcePath, $targetPath) {
		// todo
	}
}
?>