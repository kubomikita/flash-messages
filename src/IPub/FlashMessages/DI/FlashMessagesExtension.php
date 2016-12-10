<?php
/**
 * FlashMessagesExtension.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
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
use Nette\Utils;
use Nette\PhpGenerator as Code;

use IPub;
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
class FlashMessagesExtension extends DI\CompilerExtension
{
	/**
	 * @var array
	 */
	protected $defaults = [
		'useTitle'     => TRUE,
		'useOverlay'   => FALSE,
		'templateFile' => NULL
	];

	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		Utils\Validators::assertField($config, 'useTitle', 'bool');
		Utils\Validators::assertField($config, 'useOverlay', 'bool');

		// Notifier
		$builder->addDefinition($this->prefix('notifier'))
			->setClass(FlashMessages\FlashNotifier::class);

		// Session storage
		$builder->addDefinition($this->prefix('storage'))
			->setClass(Storage\Session::class);

		// Display components
		$control = $builder->addDefinition($this->prefix('messages'))
			->setClass(Components\Control::class)
			->setImplement(Components\IControl::class)
			->setArguments([
				new Nette\PhpGenerator\PhpLiteral('$templateFile'),
			])
			->setInject(TRUE);

		if ($config['useTitle'] === TRUE) {
			$control->addSetup('$service->enableTitle(?)', [$config['useTitle']]);
		} else {
			$control->addSetup('$service->disableTitle(?)', [$config['useTitle']]);
		}

		if ($config['useOverlay'] === TRUE) {
			$control->addSetup('$service->enableOverlay(?)', [$config['useOverlay']]);
		} else {
			$control->addSetup('$service->disableOverlay(?)', [$config['useOverlay']]);
		}

		if ($config['templateFile']) {
			$control->addSetup('$service->setTemplateFile(?)', [$config['templateFile']]);
		}

		// Extension events
		$builder->addDefinition($this->prefix('onResponseHandler'))
			->setClass(Events\OnResponseHandler::class);

		$application = $builder->getDefinition('application');
		$application->addSetup('$service->onResponse[] = ?', ['@' . $this->prefix('onResponseHandler')]);
	}

	/**
	 * @param Nette\Configurator $config
	 * @param string $extensionName
	 */
	public static function register(Nette\Configurator $config, string $extensionName = 'flashMessages')
	{
		$config->onCompile[] = function (Nette\Configurator $config, Nette\DI\Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new FlashMessagesExtension());
		};
	}
}
