<?php

/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\AdminCommands;

use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use PDO;
use PDOException;


class SocioCommand extends AdminCommand
{
    /**
     * @var string
     */
    protected $name = 'chats';

    /**
     * @var string
     */
    protected $description = 'List or search all chats stored by the bot';

    /**
     * @var string
     */
    protected $usage = '/chats, /chats * or /chats <search string>';

    /**
     * @var string
     */
    protected $version = '1.2.0';

    /**
     * @var bool
     */
    protected $need_mysql = true;

    /**
     * Command execute method
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public static function initialize(
        array $credentials,
        Telegram $telegram,
        $table_prefix = '',
        $encoding = 'utf8mb4'
    ): PDO {
        if (empty($credentials)) {
            throw new TelegramException('MySQL credentials not provided!');
        }

        $dsn = 'mysql:host=' . $credentials['host'] . ';dbname=' . $credentials['database'];
        if (!empty($credentials['port'])) {
            $dsn .= ';port=' . $credentials['port'];
        }

        $options = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $encoding];
        try {
            $pdo = new PDO($dsn, $credentials['user'], $credentials['password'], $options);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }

        self::$pdo               = $pdo;
        self::$telegram          = $telegram;
        self::$mysql_credentials = $credentials;
        self::$table_prefix      = $table_prefix;

        self::defineTables();

        return self::$pdo;
    }
    function query($sql, $params = [])
    {        
        $config = require __DIR__ . '/../../config.php';       
        try {
            $pdo = new PDO('mysql:host=' . $config['mysql']['host'] . ';dbname=gasav' , $config['mysql']['user'], $config['mysql']['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new TelegramException('Database error: ' . $e->getMessage());
        }
    }
    public function execute(): ServerResponse
    {
        $message = $this->getMessage() ?: $this->getEditedMessage() ?: $this->getChannelPost() ?: $this->getEditedChannelPost();
        $chat_id = $message->getChat()->getId();
        $text    = trim($message->getText(true));       
        $data = [
            'chat_id' => $chat_id,
            'text'    => 'Buscando socios...',
            'parse_mode' => 'HTML',
        ];
        $SQL = "SELECT * FROM socios";
        if (is_numeric($text)) 
                $SQL .= " WHERE Nro_socio='$text'";
                else
                $SQL .= " WHERE (`Apellido y nombre` like '%$text%' OR `Lugar de pago` like '%$text%' OR Actividad like '%$text%')";        

        $results = $this->query($SQL);
        if (empty($results)) {
            $data['text'] = 'No se encontraron socios con ese nombre.';
        } else {
            $data['text'] = "Socios encontrados:\n";
            foreach ($results as $socio) {
                $data['text'] .='<b>' . $socio['Nro_socio'] . ' - ' . $socio['Apellido y nombre'].'</b>'.PHP_EOL.
                $socio['Actividad'].' - ' . $socio['Domicilio'] . ' - ' . $socio['telefono 1'] . ' - ' . $socio['E-Mail'].PHP_EOL.
                '<i>' . $socio['Estado'].' - ' . $socio['Observaciones Comision Directiva'] . '</i>'.PHP_EOL;
            }
        }        
        return Request::sendMessage($data);
    }
    
}