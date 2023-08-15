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

use Nette;
use Nette\Application;
use Nette\ComponentModel\IContainer;
use Nette\Localization;
use Nette\Localization\Translator;
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
	public int $page = 1;

	/**
	 * Events
	 *
	 * @var mixed[]
	 */
	public array $onShowPage;

	protected Paginator $paginator;

	protected string $templateFile;

	protected int $displayRelatedPages;

	protected ?Translator $translator;

	protected bool $useAjax = TRUE;

	public function injectTranslator(?Translator $translator = NULL)
	{
		$this->translator = $translator;
	}

	public function __construct(
		?string $templateFile = NULL,
		$displayRelatedPages = NULL,
		IContainer $parent = NULL,
		?string $name = NULL
	) {
		if ($templateFile) {
			$this->setTemplateFile($templateFile);
		}
       	$this->displayRelatedPages = (int)$displayRelatedPages;
	}

	/**
	 * Render control
	 */
	public function render()
	{
		// Check if control has template
		if ($this->template instanceof Nette\Bridges\ApplicationLatte\Template) {
			// Assign vars to template
			$this->template->steps = $this->getSteps();
			$this->template->paginator = $this->getPaginator();
			$this->template->handle = 'showPage!';
			$this->template->useAjax = $this->useAjax;

			// Check if translator is available
			if ($this->getTranslator() instanceof Translator) {
				$this->template->setTranslator($this->getTranslator());
			}

			// If template was not defined before...
			if ($this->template->getFile() === NULL) {
				// ...try to get base component template file
				$templateFile = !empty($this->templateFile)
					? $this->templateFile
					: __DIR__ . DIRECTORY_SEPARATOR .'template'. DIRECTORY_SEPARATOR .'default.latte';
				$this->template->setFile($templateFile);
			}

			// Render component template
			$this->template->render();

		} else {
			throw new \Munipolis\VisualPaginator\Exceptions\InvalidStateException('Visual paginator control is without template.');
		}
	}

	public function enableAjax(): self
	{
		$this->useAjax = TRUE;

		return $this;
	}

	public function disableAjax(): self
	{
		$this->useAjax = FALSE;

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
	 * @throws \Munipolis\VisualPaginator\Exceptions\FileNotFoundException
	 */
	public function setTemplateFile(string $templateFile): self
	{
		// Check if template file exists...
		if (!is_file($templateFile)) {
			// ...check if extension template is used
			if (is_file(__DIR__ . DIRECTORY_SEPARATOR .'template'. DIRECTORY_SEPARATOR . $templateFile)) {
				$templateFile = __DIR__ . DIRECTORY_SEPARATOR .'template'. DIRECTORY_SEPARATOR . $templateFile;

			} else {
				// ...if not throw exception
				throw new \Munipolis\VisualPaginator\Exceptions\FileNotFoundException('Template file "'. $templateFile .'" was not found.');
			}
		}

		$this->templateFile = $templateFile;

		return $this;
	}

	public function setTranslator(Translator $translator): self
	{
		$this->translator = $translator;

		return $this;
	}

	/**
	 * @return Localization\ITranslator|null
	 */
	public function getTranslator(): ?Translator
	{
		return $this->translator;
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
	 * @throws Application\BadRequestException
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
