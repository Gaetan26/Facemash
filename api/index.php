<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

header("Content-Type: application/json");


$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];

$url = $scheme . "://" . $host;

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$path = parse_url($uri, PHP_URL_PATH);
$query = parse_url($uri, PHP_URL_QUERY);

$query ? parse_str($query, $params) : $params = null;

$data_file = "data.json";


// functions
function get_data() 
{
  global $data_file;
  if (!file_exists($data_file)) return [];
  $json = file_get_contents($data_file);
  return json_decode($json, true);
}

function get_element_index($id, $data)
{
  for ($i=0; $i < count($data); $i++) { 
    
    if ($data[$i]['id'] === $id)
    {
      return $i;
    }
  }
  
  return null;
}

function save_data($data)
{
  global $data_file;
  file_put_contents($data_file, json_encode($data, JSON_PRETTY_PRINT));
}

function get_param(string $param)
{
  global $params;
  if (isset($params[$param]) && !empty($params[$param]))
  {
    return $params[$param];
  }
  return null;
}


// ELO Manager
function update_elo(string $id0, int $real_score0, string $id1, int $real_score1)
{
  $data = get_data();
  
  $index0 = get_element_index($id0, $data);
  $index1 = get_element_index($id1, $data);

  $a = $data[$index0];
  $b = $data[$index1];

  $score0 = $a['elo'];
  $score1 = $b['elo'];


  $ea = 1 / (1 + 10**( ($score1 - $score0) / 400 ) );
  $eb = 1 - $ea;

  $score0 = round( $score0 + 32 * ($real_score0 - $ea), 2 );
  $score1 = round( $score1 + 32 * ($real_score1 - $eb), 2 );

  $a['elo'] = $score0;
  $b['elo'] = $score1;

  $data[$index0] = $a;
  $data[$index1] = $b;

  save_data($data);

  return true;

}


// routes
if ($path === '/' && $method === 'GET')
{
  echo json_encode(
    [
      'hello ' => 'world',
      'who i am? ' => 'facemash!'
    ]
  );

  exit;
}

if ($path === '/start' && $method == 'GET')
{
  $data = get_data();
  
  $opponents = array_rand($data, 2);
  $response = array();

  foreach($opponents as $i)
  {
    $response[] = [
      'id' => $data[$i]['id'],
      'name' => $data[$i]['name'],
      'image' => $url . '/images/' . $data[$i]['id'] . '.' . $data[$i]['image_format']
    ];
  }

  echo json_encode($response);

  exit;
}

if ($path === '/next' && $method === 'GET')
{
  $winner = (string) get_param('winner');
  $loser = (string) get_param('loser');

  if (isset($winner) && !empty($loser) && isset($loser) && !empty($loser))
  {
    $data = get_data();
    $opponent = array_rand($data, 1);

    while ($data[$opponent]['id'] === $loser || $data[$opponent]['id'] === $winner)
    {
      $opponent = array_rand($data, 1);
    }

    $reponse = [
      'id' => $data[$opponent]['id'],
      'name' => $data[$opponent]['name'],
      'image' => $url . '/images/' . $data[$opponent]['id'] . '.' . $data[$opponent]['image_format']
    ];

    update_elo($winner, 1, $loser, 0);
    
    echo json_encode($reponse);

  }

  else
  {
    http_response_code(400);
    echo json_encode([
      'error' => 'bad call from this endpoint `'. $method .' - '. $uri .'`',
      'code' => 400
    ]);
  }

  exit;

}

if ($path === '/ranking' && $method === 'GET')
{
  $data = get_data();
  $ranking = array();

  foreach($data as $e)
  {
    $ranking[] = [
      'id' => $e['id'],
      'elo' => $e['elo'],
      'name' => $e['name'],
      'image' => $url . '/images/' . $e['id'] . '.' . $e['image_format']
    ];
  }

  usort($ranking, function($a, $b)
  {
    return $b['elo'] <=> $a['elo'];
  });

  echo json_encode($ranking);

  exit;
}


// Error 404
http_response_code(404);
echo json_encode([
  'error' => 'this endpoint `'. $method . ' - ' . $uri . '` not found!',
  'code' => 404
]);