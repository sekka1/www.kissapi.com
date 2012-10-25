<?php
// If necessary, reference the sdk.class.php file. 
// For example, the following line assumes the sdk.class.php file is in the same directory as this file
 require_once dirname(__FILE__) . '/sdk.class.php';

$table_name = 'Datasources';
echo "Tablename: ".$table_name."\n";

// Instantiate the class.
$dynamodb = new AmazonDynamoDB();

$fourteen_days_ago = date('Y-m-d H:i:s', strtotime("-14 days"));
/* 
$response = $dynamodb->query(array(
    'TableName' => 'Reply',
    'HashKeyValue' => array(
        AmazonDynamoDB::TYPE_STRING => 'Amazon DynamoDB#DynamoDB Thread 2',
    ),
    'RangeKeyCondition' => array(
        'ComparisonOperator' => AmazonDynamoDB::CONDITION_GREATER_THAN_OR_EQUAL,
        'AttributeValueList' => array(
            array(
                AmazonDynamoDB::TYPE_STRING => $fourteen_days_ago
            )
        )
    )
));

//    print_r($response);
    
$get_response = $dynamodb->get_item(array(
    'TableName' => 'Data',
    'Key' => array(
        'HashKeyElement' => array( AmazonDynamoDB::TYPE_NUMBER => '1010' )
    )
));
*/

// status code 200 indicates success
//print_r($get_response);
//$out1 = toArray( $get_response );
//print_r( $out1 );
//echo $out1['body']['Item']['ISBN']['S'];


$scan_response = $dynamodb->scan(array(
'TableName' => $table_name,
'ConsistentRead' => true,
));

// 200 response indicates Success
//print_r($scan_response);
$body = $scan_response->body->to_array()->getArrayCopy();
echo 'Item Count: ' . $body['Count']."\n"; 

/*
function toArray($data) {
    // Converts the Amazon Dynamo return into an array
    if (is_object($data)) $data = get_object_vars($data);
    return is_array($data) ? array_map(__FUNCTION__, $data) : $data;
}
*/
?>
