<?php
namespace Longman\TelegramBot\Commands\UserCommands;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;


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

        $message = $this->getMessage();
        $from       = $message->getFrom();
        $user_id    = $from->getId();
        $chat_id    = $message->getChat()->getId();
        $message_id = $message->getMessageId();

        $data = [
            'chat_id'             => $chat_id,
 //           'reply_to_message_id' => $message_id,
            'parse_mode' => 'HTML',
        ];


        $ip_address = '200.114.85.10'; // Replace with your camera's IP
        $port = '80'; // Replace with your camera's HTTP port if different
        $channel = '0'; // Adjust if needed for specific channel
        $username = 'Ver'; // Replace with your camera username
        $password = 'PlayaGasav'; // Replace with your camera password
        $snapshot_url = "http://{$username}:{$password}@{$ip_address}:{$port}/cgi-bin/snapshot.cgi?channel={$channel}";
        $output_file = $this->telegram->getDownloadPath() .'/snapshot.jpg';
        $data['text'] = $snapshot_url;        
        $ch = curl_init();
        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $snapshot_url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST); // Dahua cameras often use Digest authentication
        curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the transfer as a string
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow any redirects
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For HTTPS, if self-signed certs are used (use with caution)
        $image_data = curl_exec($ch);
        if (curl_errno($ch)) {
            return $this->replyToChat( 'cURL error: ' . curl_error($ch));
        } else {
            // Save the image data to a file
            if ($image_data !== false && !empty($image_data)) {
                file_put_contents($output_file, $image_data);
               // $this->replyToChat( "Snapshot saved to {$output_file}");
            } else {
                return $this->replyToChat( "Failed to retrieve image data. Check camera IP, credentials, and URL.");
            }
        }
        curl_close($ch);
        $data['caption'] = 'Gasav - Ensenada - Argentina '.date("Y-m-d H:i:s");
        $data['photo']   = Request::encodeFile($output_file);	                
        Request::sendPhoto($data);     
    }
}



