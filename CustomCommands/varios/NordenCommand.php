<?php
namespace Longman\TelegramBot\Commands\UserCommands;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

class NordenCommand extends UserCommand
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
     * @var string
     */
    protected $name = 'Norden';
    protected $description = 'Ultimos datos del Pilote Norden';
    protected $usage = '/Norden <text>';
    protected $version = '1.2.0';
    function degrees_to_direction ($degrees, $short=true)
     {
         $dir_ary = [
             ['N', 'North'],
             ['NNE', 'North Northeast'],
             ['NE', 'Northeast'],
             ['ENE', 'East Northeast'],
             ['E', 'East'],
             ['ESE', 'East Southeast'],
             ['SE', 'Southeast'],
             ['SSE', 'South Southeast'],
             ['S', 'South'],
             ['SSW', 'South Southwest'],
             ['SW', 'Southwest'],
             ['WSW', 'West Southwest'],
             ['W', 'West'],
             ['WNW', 'West Northwest'],
             ['NW', 'Northwest'],
             ['NNW', 'North Northwest'],
         ];
     
         $idx = round ($degrees / 22.5) % 16;
     
         if ($short)
         {
             return $dir_ary[$idx][0];
         }
     
         return $dir_ary[$idx][1];
     }
    function bajando($data,$indice)
    {
        global $keys;
        global $LECTURAS;
        $value = (float) $data[$keys[$indice]]['T'];
        $v1 = (float) $data[$keys[$indice+1]]['T'];
        $v2 = (float) $data[$keys[$indice+2]]['T'];
        $v3 = (float) $data[$keys[$indice+3]]['T'];
        $v4 = (float) $data[$keys[$indice+4]]['T'];
        
        //echo "$value > $v2 > && $value > $v3 ".PHP_EOL;
        if ($value < $v4) return true;
        if ($value < $v3) return true;
        return $value < $v1  && $value < $v2 ;
    }
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $text    = $message->getText(true);

       // if ($text === '') {
        //    return $this->replyToChat('Command usage: ' . $this->getUsage());
       // }
        $this->headers = [
            'Accept' =>  '*/*',
            'Accept-Encoding' =>  'gzip, deflate, br, zstd',
            'Accept-Language' =>  'es-419,es;q=0.9,en;q=0.8,gl;q=0.7,pt;q=0.6',                   
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',   
            'Cookie' => $this->cook,
            'Host' =>'meteo.comisionriodelaplata.org',
            'Sec-Ch-Ua' => '"Not A(Brand";v="8", "Chromium";v="132", "Google Chrome";v="132"',
            'Sec-Fetch-Site' =>  'none',
            'Sec-Fetch-Mode' =>  'cors',
            'Sec-Fetch-Dest' =>  'empty',
            'Origin' => 'http://meteo.comisionriodelaplata.org',
            'User-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',
            ];
        $this->opciones = ['form_params' => $this->parametros,        
                            'headers'     => $this->headers,];
        //$client = new GuzzleHttp\Client();
        $this->client = new Client();        
        $response = $this->client->request('POST',$this->url_post,$this->opciones);
        $cook=$response->getHeaderLine("Set-Cookie");    
        if (!empty($cook))
            {
            $this->opciones['headers']['Cookie']=$cook;
            $client = new Client();
            $response = $client->request('POST',$this->url_post,$this->opciones);
            }
        $this->parajson =  str_replace('OKupdateStationTelemetry|JSON**', "", $response->getBody());
        $data = json_decode($this->parajson,false); 
        $latest = rawurldecode( $data->wind->latest);

        $dom = new \DOMDocument();
        $dom->loadHTML($latest);    
        $valores = $dom->getElementsByTagName('td');
        $latest_fecha = $valores[0]->nodeValue;
        $latest_viento = $valores[1]->nodeValue;
        $latest_rafaga = $valores[2]->nodeValue;
        $latest_nudos = $valores[3]->nodeValue;
        $latest_direcc = $valores[4]->nodeValue;
        $latest_estacion = $valores[5]->nodeValue;

        global $keys;
        $LECTURAS = -120 ;
        $wind = (array) $data->wind->chart->gust->series{1}->data;
        $uwind = array_reverse(array_slice( $wind, $LECTURAS ));
        $tide = (array) $data->tide->chart->series;
        $utide = array_reverse(array_slice( $tide[0]->data, $LECTURAS ));


        $nada = '- -  - -';
        foreach ($utide as &$u)
                $u[0]=date("Y-m-d H:i",$u[0]/1000);
        foreach ($uwind as &$u)
                $u[0]=date("Y-m-d H:i",$u[0]/1000);
        
        $data = [];
        foreach ($utide as $u )
        {
            if ($u[0]==null) continue;
            $data[$u[0]]['D']=$u[0];
            $data[$u[0]]['T']=number_format(round($u[1],2),2);   
            if (!isset($data[$u[0]]['W'] )) $data[$u[0]]['W']=$nada;
        }
        foreach ($uwind as $u )
        {
            if ($u[0]==null) continue;
            $data[$u[0]]['D']=$u[0];
            $data[$u[0]]['W']=number_format(round($u[1],1),1);
            if (!isset($data[$u[0]]['T'] )) $data[$u[0]]['T']=$nada;
        }
        $texto = '<b>Pilote Norden</b>  Lat -34.62 Lon -57.92'.PHP_EOL."Ultima medicion ".PHP_EOL. $latest_fecha.' '.PHP_EOL.
            '<b>'. $latest_viento.' knts '.      
            self::degrees_to_direction ($latest_direcc, true).
            '</b> <i>('.$latest_direcc.'°)</i>'.PHP_EOL;

        $texto.='<b>Ultimas 2 horas</b>'.PHP_EOL.
                'Date                   Rio       Knots'.PHP_EOL;
        //$texto.='Date                     Rio       Knots'.PHP_EOL;        
        $lineas = 1;
        foreach ($data as $d )
        {               
            #Acá podría fijarme y hacer cuentas si viene subiendo o bajando
            if ($lineas < 22)
                $texto.= str_replace('2025-','',$d['D']).'     '.$d['T'].'      '.$d['W'].PHP_EOL;
            $lineas += 1;
        }
        $keys = array_keys($data);
        $bajando = $this->bajando(  $data,0 );
        if ($bajando ) $texto.= 'Rio bajando';
        
    
        $respuesta = ['cook'=>$this->cook,'altura'=>$utide[0],'viento'=>$uwind[0]];
        return $this->replyToChat($texto ,['parse_mode' => 'HTML',]);
    }
}

