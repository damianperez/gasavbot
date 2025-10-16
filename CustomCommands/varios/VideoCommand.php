<?php
namespace Longman\TelegramBot\Commands\UserCommands;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;


class VideoCommand extends UserCommand
{
    /**
     * Guzzle Client object
     *
     * @var Client
     */
    private $client;
   
        
 //       'allow_redirects' => true,
 //       'verify' => false,
    
 /**
  * 
 * curl --digest -u "Ver:PlayaGasav" "http://200.114.85.10/cgi-bin/snapshot.cgi?1" -o "snapshot.jpg"
 * 
 * 
 * */
   
    
    protected $name = 'Video';    
    protected $description = 'Snapshot de la cámara';

    protected $usage = '/Video';
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
//  http://Ver:PlayaGasav@200.114.85.10:80/cgi-bin/snapshot.cgi?channel=1
//http://Ver:PlayaGasav@200.114.85.10:80/cgi-bin/mjpg/video.cgi?channel=1

//rtsp://Ver:PlayaGasav@200.114.85.10:80/cam/realmonitor?channel=1&subtype=0

        $output_file = $this->telegram->getDownloadPath() .'/snapshot.jpg';
        $data['text'] = $snapshot_url;        
        $ch = curl_init();        
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
        $now = new \DateTime();
        $targetTimezone = new \DateTimeZone('America/Argentina/Buenos_Aires');
        $now->setTimezone($targetTimezone);
        $data['caption'] = "Gasav - Ensenada - Argentina " . $now->format('Y-m-d H:i');
        $data['photo']   = Request::encodeFile($output_file);	                
        Request::sendPhoto($data);     
    }
}



