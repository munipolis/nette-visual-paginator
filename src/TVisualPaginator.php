<?php
/**
 * TVisualPaginator.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:VisualPaginator!
 * @subpackage	common
 * @since		5.0
 *
 * @date		01.02.15
 */

namespace Munipolis\VisualPaginator;

use Munipolis\VisualPaginator\Components\IControl;

trait TVisualPaginator
{
	protected IControl $visualPaginatorFactory;

	public function injectVisualPaginator(IControl $visualPaginatorFactory) {
		$this->visualPaginatorFactory = $visualPaginatorFactory;
	}
}
