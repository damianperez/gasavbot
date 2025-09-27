<?php

/**
 * This file is part of the PHP Telegram Bot example-bot package.
 * https://github.com/php-telegram-bot/example-bot/
 *
 * (c) PHP Telegram Bot Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * User "/echo" command
 *
 * Simply echo the input back to the user.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

class NordenCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'Norden';

    /**
     * @var string
     */
    protected $description = 'Muestra el estado del pilote Norden';

    /**
     * @var string
     */
    protected $usage = '/Norden <text>';

    /**
     * @var string
     */
    protected $version = '1.2.0';

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $text    = $message->getText(true);

        if ($text === '') {
            return $this->replyToChat('Command usage: ' . $this->getUsage());
        }

        return $this->replyToChat($text);
    }
}
