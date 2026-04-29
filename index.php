<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
require __DIR__ . '/vendor/autoload.php';

include_once("lib-config.php");
include_once("clsLocalhubDB.php");

$app = AppFactory::create();

$app->setBasePath('/localhubapi');

$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello Localhub API!");
    return $response;
});

$app->get('/api/events/nearby', function (Request $request, Response $response) {
    // 1. Grab the query string parameters
    $queryParams = $request->getQueryParams();

    // 2. Input Validation (Crucial for GPS searches)
    // If we don't have both coordinates, we must reject the request immediately
    // before wasting a database call.
    if (!isset($queryParams['lat']) || !isset($queryParams['lon'])) {
        $errorPayload = json_encode([
            "status" => "ERROR",
            "code"   => 400,
            "error"  => "Missing required parameters: 'lat' and 'lon'.",
            "data"   => []
        ]);
        
        $response->getBody()->write($errorPayload);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    // 3. Extract and cast the required parameters
    $lat = (float)$queryParams['lat'];
    $lon = (float)$queryParams['lon'];
    
    // 4. Extract optional parameters with safe defaults
    $radius = isset($queryParams['radius']) ? (int)$queryParams['radius'] : 10;
    $limit  = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 50;
    $offset = isset($queryParams['offset']) ? (int)$queryParams['offset'] : 0;

    // 5. Initialize your database class
    $db = new clsLocalhubDB();
    
    // 6. Call the geospatial search function
    $db_result = $db->search_events_by_gps($lat, $lon, $radius, $limit, $offset);

    $response_array["status"] = $db_result["status"];
    $response_array["code"] = $db_result["code"];
    $response_array["error"] = $db_result["error"];
    $response_array["eventid"] = $db_result["eventid"];
    $response_array["data"] = $db_result["data"];
    if($db_result["status"] == "OK")
    {
        $response_code = 200;
    }
    if( ($db_result["status"] == "ERROR") && ($db_result["code"] == 404) )
    {
        $response_code = 404;
    }

    // 7. Return the JSON response
    $response->getBody()->write(json_encode($response_array));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($response_array['code']);

});


// Our single event lookup route
$app->get('/api/events/{id}', function (Request $request, Response $response, array $args) {
    $eventId = $args['id'];

    //As a reminder, response_code and response array are for json response
    //while in the classes, result_arrays are the logic
    $response_code  = 200;
    $response_array = Array();

    $oLocalhubDB = new clsLocalhubDB();
    $bad_id = "12345";
    $good_id = "33355_00000_us-east-1";
    $db_result = $oLocalhubDB->get_single_event($good_id);

    
    $response_array["status"] = $db_result["status"];
    $response_array["code"] = $db_result["code"];
    $response_array["error"] = $db_result["error"];
    $response_array["eventid"] = $db_result["eventid"];
    $response_array["data"] = $db_result["data"];
    if($db_result["status"] == "OK")
    {
        $response_code = 200;
    }
    if( ($db_result["status"] == "ERROR") && ($db_result["code"] == 404) )
    {
        $response_code = 404;
    }

    $response->getBody()->write(json_encode($response_array));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($response_code);

});




$app->get('/api/events', function (Request $request, Response $response) {
    // 1. Grab all the query string variables (e.g., ?city=chicago&limit=10)
    $queryParams = $request->getQueryParams();

    // 2. Extract our known pagination limits (with safe defaults)
    $limit = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 50;
    $offset = isset($queryParams['offset']) ? (int)$queryParams['offset'] : 0;

    // 3. Build a dynamic array of just our geographic filters
    $filters = [];
    if (!empty($queryParams['city']))    $filters['city'] = $queryParams['city'];
    if (!empty($queryParams['country'])) $filters['country'] = $queryParams['country'];
    if (!empty($queryParams['zipcode'])) $filters['zipcode'] = $queryParams['zipcode'];

    // 4. Initialize your DB class (assuming it's loaded into your environment)
    $db = new clsLocalhubDB();
    
    // 5. Call the dynamic search function
    $db_result = $db->search_events($filters, $limit, $offset);

    $response_array["status"] = $db_result["status"];
    $response_array["code"] = $db_result["code"];
    $response_array["error"] = $db_result["error"];
    $response_array["eventid"] = $db_result["eventid"];
    $response_array["data"] = $db_result["data"];
    if($db_result["status"] == "OK")
    {
        $response_code = 200;
    }
    if( ($db_result["status"] == "ERROR") && ($db_result["code"] == 404) )
    {
        $response_code = 404;
    }

    // 6. Return the JSON response
    $response->getBody()->write(json_encode($response_array));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($response_array['code']);
});



$app->run();

?>
