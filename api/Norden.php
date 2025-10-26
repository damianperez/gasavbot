<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
//date_default_timezone_set('America/Argentina/Buenos_Aires');

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


    $LECTURAS = -120;
    $wind = (array) $data->wind->chart->gust->series{1}->data;
    $uwind = array_reverse(array_slice( $wind, $LECTURAS  ));
    $tide = (array) $data->tide->chart->series;
    $utide = array_reverse(array_slice( $tide[0]->data,$LECTURAS ));
    $nada = '- -  - -';

    //->setTimezone(new DateTimeZone('America/Argentina/Buenos_Aires'))/

    foreach ($utide as &$u)
            $u[0] = DateTime::createFromFormat('U', $u[0]/1000 )->format("Y-m-d H:i");
            //$u[0]=date("Y-m-d H:i",$u[0]/1000);

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
    $texto = "Ultima medicion ".PHP_EOL. $latest_fecha.' '.PHP_EOL.
        '<b>'. $latest_viento.' knts </b>'.      
        degrees_to_direction ($latest_direcc, true).
        ' <i>('.$latest_direcc.'°)</i>'.PHP_EOL;

    $texto.='<b>Ultima hora</b>'.PHP_EOL.'Date                     Rio       Knots'.PHP_EOL;
    echo '<pre>';
    //    var_dump($data);
    echo '</pre>';
    $i=0;
    foreach ($data as $d )
    {              
            $i=$i+1;
            $texto.= str_replace('2025-','',$d['D']).'     '.$d['T'].'      '.$d['W'].PHP_EOL;
            if ($i>40) break;
    }
    $respuesta = ['cook'=>$cook,'altura'=>$utide[0],'viento'=>$uwind[0]];   
    echo '<pre>'.$texto.'</pre>';

    $keys = array_keys($data);
    $ialto = 0;
    $ibajo = 0 ;
    $alto = -3;
    $bajo = 3 ;
    $bajodesde=0;
    $bajohasta=0;
    $subiendo=false;
    $bajando=false;
    $estable=false;
    echo '<pre>';
    $tide_ahora= (float) $data[$keys[0]]['T'];

    $keys = array_keys($data);
    $bajando = bajando(  $data,0 );
    $subiendo = !$bajando;
    
    if ($bajando) echo '<hr>Bajando<hr>';
    for ($i = 0; $i < count($keys); $i++) {
        $key = $keys[$i];
        $value = (float) $data[$key]['T']; 
        if ( ! is_numeric($value))  continue;
        $hora = $data[$key]['D'];
        if ($bajando && !bajando($data, $i) )
            {
                $ialto=$i; $alto = $value;
                break;
            } 
        if ($subiendo && bajando($data, $i) )
            {
                $ibajo=$i; $bajo = $value;
                break;
            } 
        /*
        if ($value > $alto) { 
            $ialto=$i; $alto = $value; }
        else
            {
                if ($bajando) break;
            }
        if ($value < $bajo && bajando($data, $i)) 
            { $ibajo=$i; $bajo = $value; }
        else
            { if ($subiendo) break ;}

        echo "Key: $key, Index: $i  Value: $value $alto $bajo \n";
        */
    }
    $primer_valor = $data[array_key_first($data)]['T'];
    $ultimo_valor = $data[array_key_last($data)]['T'];
    $startDateTime = new DateTime('now',new DateTimeZone('America/Argentina/Buenos_Aires'));
    
    if ( $bajando ) 
        {
        
        $endDateTime = new DateTime($data[$keys[$ialto]]['D'],new DateTimeZone('America/Argentina/Buenos_Aires'));    
        $interval = $startDateTime->diff($endDateTime);
        $difference = $interval->format('%h horas y %i minutos');        
        echo "Bajó ".round($alto - $primer_valor,1). " cms en $difference   (". $endDateTime->format("H:i").")" .PHP_EOL;
        }
    if ( $subiendo ) 
        {
        $endDateTime = new DateTime($data[$keys[$ibajo]]['D']);    
        $interval = $startDateTime->diff($endDateTime);
        $difference = $interval->format('%h horas y %i minutos');        
        echo "Subiendo desde hace $difference ".$data[$keys[$ibajo]]['D']. PHP_EOL;       
        }

    echo "De $ultimo_valor a $primer_valor    $bajo  - $alto".PHP_EOL;
    echo 'Mas bajo '.$data[$keys[$ibajo]]['T'].' a las '.$data[$keys[$ibajo]]['D']. PHP_EOL;
    echo 'Mas alto '.$data[$keys[$ialto]]['T'].' a las '.$data[$keys[$ialto]]['D']. PHP_EOL;
    echo '</pre>';


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
    
    function subiendo($data,$indice)
    {
        global $keys;
        global $LECTURAS;
        $value = (float) $data[$keys[$indice]]['T'];
        $v2 = (float) $data[$keys[$indice+1]]['T'];
        $v3 = (float) $data[$keys[$indice+1]]['T'];
        //echo "$value > $v2 > && $value > $v3 ".PHP_EOL;
        return $value > $v2  && $value > $v3 ;
    }
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
