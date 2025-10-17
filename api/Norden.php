<?php


require_once __DIR__ . '/../../vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

$cook = "mariweb_session=1c15115edecae9adeb17ce8b689fcda0; mw_lang=EN";
$url_post = 'http://meteo.comisionriodelaplata.org/ecsCommand.php?c=telemetry/updateTelemetry&s=0.8081622188540726';
$headers = [ ];

$parametros = ['p'=> 1,
        'p1' => 2,
        'p2' => '2',            
        'p3' => '1', 
        'p4' => 'update',                      
        ];
$opciones =[];
    

 
    $headers = [
        'Accept' =>  '*/*',
        'Accept-Encoding' =>  'gzip, deflate, br, zstd',
        'Accept-Language' =>  'es-419,es;q=0.9,en;q=0.8,gl;q=0.7,pt;q=0.6',                   
        'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',   
        'Cookie' => $cook,
        'Host' =>'meteo.comisionriodelaplata.org',
        'Sec-Ch-Ua' => '"Not A(Brand";v="8", "Chromium";v="132", "Google Chrome";v="132"',
        'Sec-Fetch-Site' =>  'none',
        'Sec-Fetch-Mode' =>  'cors',
        'Sec-Fetch-Dest' =>  'empty',
        'Origin' => 'http://meteo.comisionriodelaplata.org',
        'User-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36',
        ];
    $opciones = ['form_params' => $parametros,        
                        'headers'     => $headers,];
    //$client = new GuzzleHttp\Client();
    $client = new Client();
    //var_dump($opciones);
    $response = $client->request('POST',$url_post,$opciones);

    $cook=$response->getHeaderLine("Set-Cookie");    
    if (!empty($cook))
        {
        $opciones['headers']['Cookie']=$cook;
        $client = new Client();
        $response = $client->request('POST',$url_post,$opciones);
        }
    
    
    $parajson =  str_replace('OKupdateStationTelemetry|JSON**', "", $response->getBody());
    $data = json_decode($parajson,false); 
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


    $wind = (array) $data->wind->chart->gust->series{1}->data;
    $uwind = array_reverse(array_slice( $wind,-15));
    $tide = (array) $data->tide->chart->series;
    $utide = array_reverse(array_slice( $tide[0]->data,-15));

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
        $data[$u[0]]['T']=$u[1];       
        if (!isset($data[$u[0]]['W'] )) $data[$u[0]]['W']=$nada;
    }
    foreach ($uwind as $u )
    {
        if ($u[0]==null) continue;
        $data[$u[0]]['D']=$u[0];
        $data[$u[0]]['W']=$u[1];
        if (!isset($data[$u[0]]['T'] )) $data[$u[0]]['T']=$nada;
    }
    $texto = "Ultima medicion ".PHP_EOL. $latest_fecha.' '.PHP_EOL.
        '<b>'. $latest_viento.' knts </b>'.      
        degrees_to_direction ($latest_direcc, true).
        ' <i>('.$latest_direcc.'°)</i>'.PHP_EOL;

    $texto.='<b>Ultima hora</b>'.PHP_EOL.'Date                     Rio       Knots'.PHP_EOL;

    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    foreach ($data as $d )
    {              
        
            $texto.= str_replace('2025-','',$d['D']).'     '.$d['T'].'      '.$d['W'].PHP_EOL;
    }
    $respuesta = ['cook'=>$cook,'altura'=>$utide[0],'viento'=>$uwind[0]];   
   // echo '<pre>';    var_dump($respuesta);    echo '</pre>';

    // Get an indexed array of keys
    $keys = array_keys($data);
    $alto = -3;
    $bajo = 3;
    for ($i = 0; $i < count($keys); $i++) {
        $key = $keys[$i];
        $value = (float) $data[$key]['T'];
        if ( is_numeric($value))
        {
            if ($value > $alto) $alto=$i;
            if ($value < $bajo) $bajo=$i;
        }
        echo "Index: $i, Key: $key, Value: $value\n";
    }
    $primer_valor = $data[array_key_first($data)]['T'];
    $ultimo_valor = $data[array_key_last($data)]['T'];
    echo '<pre>';
    echo "De $ultimo_valor a $primer_valor ".PHP_EOL;
    echo 'Mas bajo '.$data[$keys[$bajo]]['T'].' a las '.$data[$keys[$bajo]]['D']. PHP_EOL;
    echo 'Mas alto '.$data[$keys[$alto]]['T'].' a las '.$data[$keys[$alto]]['D']. PHP_EOL;
    echo '</pre>';



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
