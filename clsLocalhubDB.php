<?php
//should already have lib-config file loaded
//namespace Localhub\Shared;

use OpenSearch\ClientBuilder;
use OpenSearch\Common\Exceptions\OpenSearchException;
use OpenSearch\Common\Exceptions\Missing404Exception;

class clsLocalhubDB
{
    public $os_client;
    public $os_index;
    public $connection;
    public $awsregion;
    public $dbcode;

    function __construct()
    {
        global $ap_opensearch_server_array, $ap_awsregion, $ap_dbcode, $connection;

        $this->os_client = (new ClientBuilder())
            ->setHosts([$ap_opensearch_server_array[$ap_awsregion]["os_host"]])
            ->setBasicAuthentication($ap_opensearch_server_array[$ap_awsregion]["os_user"],$ap_opensearch_server_array[$ap_awsregion]["os_pass"])
            ->setSSLVerification(false)
            ->build();
        $this->os_index = $ap_opensearch_server_array[$ap_awsregion]["os_index"];            

        $this->connection = $connection;
        $this->awsregion = $ap_awsregion;
        $this->dbcode = $ap_dbcode;//this works on workercron, but might need to pass into construct for webserver  

    }

    function get_single_event($eventid)
    {   
        //Always default to an empty result array
        $result_array = [
            "status" => "UNKNOWN",
            "code"   => 400,        
            "error"  => "",
            "data"   => [] 
        ];        
    
        try
        {
            $os_result = $this->os_client->get([
                'index' => 'localhub',
                'id'    => $eventid
            ]);

            $result_array["status"] = "OK";
            $result_array["code"] = 200;            
            $result_array["eventid"] = $os_result["_id"];
            $result_array["data"] = $os_result["_source"];
        }
        
        catch(\Exception $exc)
        {
            $result_array["status"] = "ERROR";
            $result_array["error"] = $exc->getMessage();            
            
            if ($exc instanceof Missing404Exception) 
            {   
                $result_array["code"] = 404;
            }
            elseif ($exc instanceof BadRequest400Exception) 
            {            
                $result_array["code"] = 400;
            }
            else
            {
                $result_array["code"] = 500;
            }
        }            

        return $result_array;

    }

    function search_events($filters=[], $limit = 50, $offset = 0)
    {   
        //Always default to an empty result array
        $result_array = [
            "status" => "UNKNOWN",
            "code"   => 400,        
            "error"  => "",
            "data"   => [] 
        ];

        try
        {

            // 1. Dynamically build the OpenSearch "must" array
            $must_clauses = [];
            
            // Loop through the array passed (city, country, etc.)
            foreach ($filters as $field => $value) 
            {
                $must_clauses[] = [
                    'match' => [
                        $field => $value 
                    ]
                ];
            }

            // 2. If no filters were passed, default to a "match_all" query
            if (empty($must_clauses)) 
            {
                $query_body = ['match_all' => (object)[]]; // Empty object for match_all
            } 
            else 
            {
                $query_body = [
                    'bool' => [
                        'must' => $must_clauses
                    ]
                ];
            }

            // 3. Build the final OpenSearch payload
            $params = [
                'index' => $this->os_index, 
                'body'  => [
                    'from'  => $offset,
                    'size'  => $limit,
                    'query' => $query_body
                ]
            ];

            // 4. Execute the search
            $os_result = $this->os_client->search($params);

            //final event results
            $clean_events = [];

            //loop through OpenSearch hits array
            if (isset($os_result['hits']['hits'])) 
            {
                foreach ($os_result['hits']['hits'] as $hit) 
                {
                    $event_data = $hit['_source'];          //event _doc record
                    $event_data['eventid'] = $hit['_id'];   //global event id
                    $clean_events[] = $event_data;          
                }
            }

            $result_array["status"] = "OK";
            $result_array["code"] = 200; 
            //Pass back the total number of matches found as well or 0
            $result_array["total_found"] = $os_result['hits']['total']['value'] ?? 0; 
            $result_array["data"] = $clean_events;
        }
        catch(\Exception $exc)
        {
            $result_array["status"] = "ERROR";
            $result_array["error"] = $exc->getMessage();
            
            if ($exc instanceof Missing404Exception) 
            {   
                $result_array["code"] = 404;
            }            
            if ($exc instanceof BadRequest400Exception) 
            {            
                $result_array["code"] = 400;
            }
            else
            {
                $result_array["code"] = 500;
            }
        }            

        return $result_array;
    }


    function search_events_by_gps($lat, $lon, $distance_miles = 10, $limit = 50, $offset = 0)
    {   
        $result_array = [
            "status" => "UNKNOWN",
            "code"   => 400,        
            "error"  => "",
            "data"   => [] 
        ];

        try
        {
            // Build the geospatial query
            $params = [
                'index' => $this->os_index, 
                'body'  => [
                    'from'  => $offset,
                    'size'  => $limit,
                    'query' => [
                        'bool' => [
                            // We use 'filter' instead of 'must' here because filters skip 
                            // relevance scoring, making the query mathematically faster.
                            'filter' => [
                                'geo_distance' => [
                                    'distance' => $distance_miles . 'mi', // e.g., '10mi'
                                    'coordinates' => [
                                        'lat' => (float)$lat,
                                        'lon' => (float)$lon
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $os_result = $this->os_client->search($params);

            // Flatten the results just like our other search functions
            $clean_events = [];
            if (isset($os_result['hits']['hits'])) {
                foreach ($os_result['hits']['hits'] as $hit) {
                    $event_data = $hit['_source'];          
                    $event_data['eventid'] = $hit['_id'];   
                    $clean_events[] = $event_data;          
                }
            }

            $result_array["status"] = "OK";
            $result_array["code"] = 200; 
            $result_array["total_found"] = $os_result['hits']['total']['value'] ?? 0; 
            $result_array["data"] = $clean_events;
        }
        catch(\Exception $exc)
        {
            $result_array["status"] = "ERROR";
            $result_array["error"] = $exc->getMessage();
            
            if ($exc instanceof \OpenSearch\Common\Exceptions\BadRequest400Exception) 
            {            
                $result_array["code"] = 400;
            } 
            else 
            {
                $result_array["code"] = 500;
            }
        }            

        return $result_array;
    }    



}



?>

