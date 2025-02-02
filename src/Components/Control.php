<?php
/**
 * Control.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:VisualPaginator!
 * @subpackage	Components
 * @since		5.0
 *
 * @date		12.03.14
 */

namespace Munipolis\VisualPaginator\Components;

use Munipolis\VisualPaginator\Exceptions\FileNotFoundException;
use Munipolis\VisualPaginator\Exceptions\InvalidStateException;
use Nette\Application;
use Nette\Application\BadRequestException;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\ComponentModel\IContainer;
use Nette\Localization\ITranslator;
use Nette\Utils\Paginator;

/**
 * Visual paginator control
 *
 * @package		iPublikuj:VisualPaginator!
 * @subpackage	Components
 *
 * @method onShowPage(Control $self, $page)
 *
 * @property Application\UI\ITemplate $template
 */
class Control extends Application\UI\Control
{
	/**
	 * @persistent int
	 */
	public int $page = 1;

	/**
	 * Events
	 *
	 * @var mixed[]
	 */
	public array $onShowPage;

	protected ?Paginator $paginator = null;

	protected string $templateFile;

	protected int $displayRelatedPages;

	protected ?ITranslator $translator = null;

	protected bool $useAjax = true;

	public function injectTranslator(ITranslator $translator = null): void
	{
		$this->translator = $translator;
	}

	public function __construct(
		?string $templateFile = null,
		$displayRelatedPages = null,
		IContainer $parent = null,
		?string $name = null
	) {
		if ($templateFile) {
			$this->setTemplateFile($templateFile);
		}
       	$this->displayRelatedPages = (int)$displayRelatedPages;
	}

	/**
	 * Render control
	 */
	public function render(): void
	{
		// Check if control has template
		if ($this->template instanceof Template) {
			// Assign vars to template
			$this->template->steps = $this->getSteps();
			$this->template->paginator = $this->getPaginator();
			$this->template->handle = 'showPage!';
			$this->template->useAjax = $this->useAjax;

			// Check if translator is available
			if ($this->getTranslator() instanceof ITranslator) {
				$this->template->setTranslator($this->getTranslator());
			}

			// If template was not defined before...
			if ($this->template->getFile() === null) {
				// ...try to get base component template file
				$templateFile = !empty($this->templateFile)
					? $this->templateFile
					: __DIR__ . DIRECTORY_SEPARATOR .'template'. DIRECTORY_SEPARATOR .'default.latte';
				$this->template->setFile($templateFile);
			}

			// Render component template
			$this->template->render();

		} else {
			throw new InvalidStateException('Visual paginator control is without template.');
		}
	}

	public function enableAjax(): self
	{
		$this->useAjax = true;

		return $this;
	}

	public function disableAjax(): self
	{
		$this->useAjax = false;

		return $this;
	}

	public function getPaginator(): Paginator
	{
		// Check if paginator is created
		if (!$this->paginator) {
			$this->paginator = new Paginator;
		}

		return $this->paginator;
	}

	/**
	 * Change default control template path
	 *
	 * @throws FileNotFoundException
	 */
	public function setTemplateFile(string $templateFile): Control
	{
		// Check if template file exists...
		if (!is_file($templateFile)) {
			// ...check if extension template is used
			if (is_file(__DIR__ . DIRECTORY_SEPARATOR .'template'. DIRECTORY_SEPARATOR . $templateFile)) {
				$templateFile = __DIR__ . DIRECTORY_SEPARATOR .'template'. DIRECTORY_SEPARATOR . $templateFile;

			} else {
				// ...if not throw exception
				throw new FileNotFoundException('Template file "'. $templateFile .'" was not found.');
			}
		}

		$this->templateFile = $templateFile;

		return $this;
	}

	public function setTranslator(ITranslator $translator): Control
	{
		$this->translator = $translator;

		return $this;
	}

	public function getTranslator(): ?ITranslator
	{
		if ($this->translator instanceof ITranslator) {
			return $this->translator;
		}

		return null;
	}

	/**
	 * @return mixed[]
	 */
	public function getSteps(): array
	{
		// Get Nette paginator
		$paginator = $this->getPaginator();

		// Get actual paginator page
		$page = $paginator->page;

		if ($paginator->pageCount < 2) {
			$steps = [$page];

		} else {
            $relatedPages = $this->displayRelatedPages ?: 3;
			$arr = range(max($paginator->firstPage, $page - $relatedPages), min($paginator->lastPage, $page + $relatedPages));
			$count = 4;
			$quotient = ($paginator->pageCount - 1) / $count;

			for ($i = 0; $i <= $count; $i++) {
				$arr[] = round($quotient * $i) + $paginator->firstPage;
			}

			sort($arr);

			$steps = array_values(array_unique($arr));
		}

		return $steps;
	}

	/**
	 * @param mixed[] $params
	 * @throws BadRequestException
	 */
	public function loadState(array $params): void
	{
		parent::loadState($params);

		$this->getPaginator()->page = $this->page;
	}

	public function handleShowPage(int $page): void
	{
		$this->onShowPage($this, $page);
	}
}
