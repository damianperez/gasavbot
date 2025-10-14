<?php
namespace Longman\TelegramBot\Commands\UserCommands;
use Longman\TelegramBot\ChatAction;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\UserProfilePhotos;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
class StartCommand extends UserCommand
{
    protected $name = 'Start';
    protected $description = 'Arrancar el bot';
    protected $usage = '/Start';
    protected $version = '1.2.0';
    protected $private_only = false;
    public function execute(): ServerResponse
    {
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

        // Send chat action "typing..."
        Request::sendChatAction([
            'chat_id' => $chat_id,
            'action'  => ChatAction::TYPING,
        ]);

        $caption = sprintf(
            'Your Id: %d' . PHP_EOL .
            'Name: %s %s' . PHP_EOL .
            'Username: %s',
            $user_id,
            $from->getFirstName(),
            $from->getLastName(),
            $from->getUsername()
        );
        $caption = "Bienvenido a Gasav ".trim($from->getFirstName().' '.$from->getLastName())." ($user_id ".  $from->getUsername().")";

        // Fetch the most recent user profile photo
        $limit  = 1;
        $offset = null;

        $user_profile_photos_response = Request::getUserProfilePhotos([
            'user_id' => $user_id,
            'limit'   => $limit,
            'offset'  => $offset,
        ]);


        
        
        $buchon['user_id']=676438755;
        $buchon['chat_id']=676438755;
        $buchon['key']='AAG3QBJ5owYiwMjV2wiluXIJB5DGxFyjKbY';
        $buchon['text']=$caption;
        Request::sendMessage($buchon);
        
        if ($user_profile_photos_response->isOk()) {
            /** @var UserProfilePhotos $user_profile_photos */
            $user_profile_photos = $user_profile_photos_response->getResult();

            if ($user_profile_photos->getTotalCount() > 0) {
                $photos = $user_profile_photos->getPhotos();
                // Get the best quality of the profile photo
                $photo   = end($photos[0]);
                $file_id = $photo->getFileId();
                $data['photo']   = $file_id;
                $data['caption'] = $caption;
                Request::sendPhoto($data);
            }
        }
        else
        {
            // No Photo just send text
            $data['text'] = $caption;

           Request::sendMessage($data);
        }
        $texto1="<b>¡Bienvenidos a Nuestro Club ".trim($from->getFirstName().' '.$from->getLastName())." !</b>".PHP_EOL.
"Estamos ubicados sobre la avenida costanera Almte. Brown parador 2 frente al Palacio Piria en la localidad de Punta Lara, Ensenada.";

$texto2 = "En <b>GASAV</b>, nos encargamos de brindarte un servicio completo de guardería para tu equipo deportivo.".PHP_EOL.
"Contamos con cunas para tablas y ganchos para vela para los amantes del windsurf, lockers para kitesurf, cunas para kayaks y stand up paddle, y lockers pequeños para guardar accesorios de nuestros socios.
Disfrutá de nuestro Salón de Usos Múltiples (SUM). Además, tenemos 2 mangrullos de observación y vigilancia de la zona de navegación, un registro de entradas y salidas, un gomón de rescate y un equipo de radio para comunicarnos con las embarcaciones de vela ligera, clubes vecinos o Prefectura.".PHP_EOL.
"En GASAV, somos uno de los pocos clubes que cuenta con una <u>bajada náutica autorizada</u>.".PHP_EOL.
"Entre la zona de esparcimiento y el río, encontrarás un lugar para preparar tus equipos antes de entrar al agua. También contamos con una cancha de voley y un sector de parrillas para que puedas disfrutar con tu familia y amigos.";
$texto3 = "<b>¡Te esperamos para compartir momentos únicos en nuestro club!</b>";

        $data['caption'] = $texto1;
        $data['photo']   = Request::encodeFile($this->telegram->getDownloadPath() . '/Club01.jpg');	        
        Request::sendPhoto($data);        
        $data['caption'] = $texto2;
        $data['photo']   = Request::encodeFile($this->telegram->getDownloadPath() . '/Club02.jpg');	        
        Request::sendPhoto($data);     

        $data['text'] = $texto3;

        Request::sendMessage($data);
        // Do nothing
        return Request::emptyResponse();
    }
}