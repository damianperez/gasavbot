<?php
namespace Longman\TelegramBot\Commands\UserCommands;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

class FotoCommand extends UserCommand
{
    /**
     * Guzzle Client object
     *
     * @var Client
     */
    private $client;
    





    public $cook = "mariweb_session=1c15115edecae9adeb17ce8b689fcda0; mw_lang=EN";
    public $url_post = 'http://meteo.comisionriodelaplata.org/ecsCommand.php?c=telemetry/updateTelemetry&s=0.8081622188540726';
    public $headers = [ ];
    
    public $parametros = ['p'=> 1,
            'p1' => 2,
            'p2' => '2',            
            'p3' => '1', 
            'p4' => 'update',                      
            ];
    public $opciones =[];
        
 //       'allow_redirects' => true,
 //       'verify' => false,
    
 /**
  * 
 * curl --digest -u "Ver:PlayaGasav" "http://200.114.85.10/cgi-bin/snapshot.cgi?1" -o "snapshot.jpg"
 * 
 * 
 * */
   
    /**
     * @var string
     */
    protected $name = 'Foto';
    
    /**
     * @var string
     */
    protected $description = 'Foto';

    /**
     * @var string
     */
    protected $usage = '/Foto';

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

        $texto='aa';
        return $this->replyToChat($texto ,['parse_mode' => 'HTML',]);
    }
}

