<?
function webhook($method, $params){
    $queryUrl = 'https://stazhirovka24.bitrix24.ru/rest/1/lku60ih8tb3mw9u6/'.$method.'.json';
    $queryData = http_build_query($params);
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_SSL_VERIFYPEER => 0,
    CURLOPT_POST => 1,
    CURLOPT_HEADER => 0,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => $queryUrl,
    CURLOPT_POSTFIELDS => $queryData,
    )); 
    $result = curl_exec($curl);
    curl_close($curl);
    $result = json_decode($result, 1);
    return $result ;
}
function executeHook($params) {
    $queryUrl = 'https://stazhirovka24.bitrix24.ru/rest/1/lku60ih8tb3mw9u6/batch.json';
    $queryData = http_build_query($params);
  
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_SSL_VERIFYPEER => 0,
      CURLOPT_POST => 1,
      CURLOPT_HEADER => 0,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_URL => $queryUrl,
      CURLOPT_POSTFIELDS => $queryData
    ));
  
    $result = curl_exec($curl);
    curl_close($curl);
  
    //sleep(0.1);
    return json_decode($result, true);
}
echo "<pre>";
// $res = webhook('user.get', [ "filter"=>[]]);

// print_r($res_x);


$batch = [];
$arrToChat = [];
$i =0; $a = 1;
$res = webhook('tasks.task.list', [ "filter"=>["status"=> -1]]);
while( $i <= $res['total']){//211
    $batch['cmd_'.$a] = 'tasks.task.list?' . http_build_query(
        [ "filter"=>
            [
                "status"=>-1
            ],
            "select"=> [],
            'start'=>$i
        ]
        );
    $i = $i + 50;
    sleep(1);
    $a++;
}
$result = executeHook(array('cmd' => $batch));
foreach ($result['result']['result'] as $cmd) {
    foreach ($cmd['tasks'] as $task) {
        $arrToChat[$task['responsible']['id']]['NAME']=$task['responsible']['name'];
        $arrToChat[$task['responsible']['id']]['ID']=$task['responsible']['id'];
        $arrToChat[$task['responsible']['id']]['TASKS'][]=$task['id'];
    }
}


foreach ($arrToChat as $user) {
    $count_task = count($user['TASKS']);
    if($count_task ==1){
        $frase = " просроченную задачу.";
    }else if($count_task >=2 && $count_task <= 4){
        $frase = " просроченные задачи.";
    }else{
        $frase = " просроченных задач.";
    }
    $div .= "Пользователь: [B]".$user['NAME']."[/B] имеет [B]".$count_task."[/B]".$frase."[BR]";
}

$result = webhook('im.message.add', Array(

    'DIALOG_ID' => 'chat1',
    'MESSAGE' => $div,
    'SYSTEM' => 'N',
    )
);

?>