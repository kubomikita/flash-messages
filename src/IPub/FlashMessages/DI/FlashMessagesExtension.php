<?php
/**
 * FlashMessagesExtension.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:FlashMessages!
 * @subpackage     DI
 * @since          1.0.0
 *
 * @date           05.02.15
 */

declare(strict_types = 1);

namespace IPub\FlashMessages\DI;

use Nette;
use Nette\DI;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nette\Utils;

use IPub\FlashMessages;
use IPub\FlashMessages\Components;
use IPub\FlashMessages\Events;
use IPub\FlashMessages\Storage;

/**
 * Flash messages extension container
 *
 * @package        iPublikuj:FlashMessages!
 * @subpackage     DI
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class FlashMessagesExtension extends DI\CompilerExtension
{
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'useOverlay' => Expect::bool(false),
			'templateFile' => Expect::string(),
			'useTranslator' => Expect::bool(false),
			'useTitle' => Expect::bool(true)
		]);
	}

	/**
	 * @return void
	 *
	 * @throws Utils\AssertionException
	 */
	public function loadConfiguration() : void
	{
		$config = $this->config;
		$builder = $this->getContainerBuilder();


		// Notifier
		$builder->addDefinition($this->prefix('notifier'))
			->setType(FlashMessages\FlashNotifier::class)
			->setArguments(['useTranslator' => $config->useTranslator]);

		// Session storage
		$builder->addDefinition($this->prefix('storage'))
			->setType(Storage\Session::class);

		// Display components


		$control = $builder->addFactoryDefinition($this->prefix('messages'))
			->setImplement(Components\IControl::class)
			->getResultDefinition()->setFactory(Components\Control::class)
			->setArguments([
				new Nette\PhpGenerator\PhpLiteral('$templateFile'),
			])->addTag(Nette\DI\Extensions\InjectExtension::TAG_INJECT);

		foreach (['useTitle' => ['enableTitle', 'disableTitle'], 'useOverlay' => ['enableOverlay', 'disableOverlay']] as $parameter => $commands) {
			if ($config->$parameter === TRUE) {
				$control->addSetup('$service->' . $commands[0] . '(?)', [$config->$parameter]);
			} else {
				$control->addSetup('$service->' . $commands[1] . '(?)', [$config->$parameter]);
			}
		}

		if ($config->templateFile) {
			$control->addSetup('$service->setTemplateFile(?)', [$config->templateFile]);
		}

		// Extension events
		$builder->addDefinition($this->prefix('onResponseHandler'))
			->setType(Events\OnResponseHandler::class);
		if(PHP_SAPI !== "cli") {
			$application = $builder->getDefinition( 'application' );
			$application->addSetup( '$service->onResponse[] = ?', [ '@' . $this->prefix( 'onResponseHandler' ) ] );
		}
	}

	/**
	 * @param Nette\Configurator $config
	 * @param string $extensionName
	 *
	 * @return void
	 */
	public static function register(Nette\Configurator $config, string $extensionName = 'flashMessages') : void
	{
		$config->onCompile[] = function (Nette\Configurator $config, Nette\DI\Compiler $compiler) use ($extensionName) : void {
			$compiler->addExtension($extensionName, new FlashMessagesExtension());
		};
	}
}
