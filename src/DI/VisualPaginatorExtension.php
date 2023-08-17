<?php
/**
 * VisualPaginatorExtension.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:VisualPaginator!
 * @subpackage	DI
 * @since		5.0
 *
 * @date		18.06.14
 */

namespace Munipolis\VisualPaginator\DI;

use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\Config\Helpers;
use Nette\DI\Extensions\InjectExtension;
use Nette\PhpGenerator\PhpLiteral;

class VisualPaginatorExtension extends CompilerExtension
{
	/**
	 * @var mixed[]
	 */
	protected array $defaults = [
		'templateFile'	=> NULL
	];

	public function loadConfiguration()
	{
		$config = Helpers::merge($this->getConfig(), $this->defaults);
		$builder = $this->getContainerBuilder();

		// Define components
		$paginator = $builder->addFactoryDefinition($this->prefix('paginator'))
			->setImplement('Munipolis\VisualPaginator\Components\IControl')
			->addTag('cms.components')
			->getResultDefinition()
			->setType('src\Components\Control')
			->setArguments([new PhpLiteral('$templateFile')])
			->addTag(InjectExtension::TAG_INJECT);

		if ($config['templateFile']) {
			$paginator->addSetup('$service->setTemplateFile(?)', [$config['templateFile']]);
		}
	}

	public static function register(Configurator $config, string $extensionName = 'visualPaginator'): void
	{
		$config->onCompile[] = function (Configurator $config, Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new VisualPaginatorExtension());
		};
	}

	/**
	 * Return array of directories, that contain resources for translator.
	 *
	 * @return string[]
	 */
	function getTranslationResources(): array
	{
		return array(
			__DIR__ . '/../Translations'
		);
	}
}
