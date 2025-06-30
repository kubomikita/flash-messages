<?php
/**
 * TFlashMessages.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:FlashMessages!
 * @subpackage     common
 * @since          1.0.0
 *
 * @date           01.02.15
 */

namespace IPub\FlashMessages;

use IPub\FlashMessages\Components;
use IPub\FlashMessages\Entities;

/**
 * Flash message helper trait
 *
 * @package        iPublikuj:FlashMessages!
 * @subpackage     common
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
trait TFlashMessages
{
	/**
	 * @var Components\IControl
	 */
	protected $flashMessagesFactory;

	/**
	 * @var FlashNotifier
	 */
	protected $flashNotifier;

	/**
	 * @param Components\IControl $flashMessagesFactory
	 * @param FlashNotifier $flashNotifier
	 *
	 * @return void
	 */
	public function injectFlashMessages(Components\IControl $flashMessagesFactory, FlashNotifier $flashNotifier) : void
	{
		$this->flashMessagesFactory = $flashMessagesFactory;
		$this->flashNotifier = $flashNotifier;
	}

	/**
	 * @param string $message
	 * @param string $type
	 * @param string|null $title
	 * @param bool  $overlay
	 * @param int|null  $count
	 * @param array|NULL  $parameters
	 *
	 * @return \stdClass|Entities\IMessage
	 */
	public function flashMessage($message, string $type = 'info', ?string $title = null, bool $overlay = false, ?int $count = null, ?array $parameters = []): \stdClass
	{
		return $this->flashNotifier->message($message, $type, $title, $overlay, $count, $parameters);
	}

	/**
	 * Flash messages component
	 *
	 * @return Components\Control
	 */
	protected function createComponentFlashMessages() : Components\Control
	{
		return $this->flashMessagesFactory->create();
	}
}
