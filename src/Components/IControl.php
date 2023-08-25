<?php
/**
 * IControl.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:VisualPaginator!
 * @subpackage	Components
 * @since		5.0
 *
 * @date		18.06.14
 */

namespace Munipolis\VisualPaginator\Components;

interface IControl
{
	/**
	 * @return mixed
	 */
	public function create(?string $templateFile = null, $displayRelatedPages = 3);
}
