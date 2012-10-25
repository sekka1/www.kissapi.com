<?php
// If necessary, reference the sdk.class.php file. 
// For example, the following line assumes the sdk.class.php file is in the same directory as this file
 require_once dirname(__FILE__) . '/sdk.class.php';

$table_name = 'Datasources';
echo "Tablename: ".$table_name."\n";
$datasource_id_seq = '30001';
echo "datasource_id_seq: ".$datasource_id_seq."\n";

// Instantiate the class.
$dynamodb = new AmazonDynamoDB();

// Disable SSL
$dynamodb->disable_ssl();

/*
// Works, pulls the first 100 back of the datasource_id_seq given
$response = $dynamodb->query(array(
    'TableName' => $table_name,
    'HashKeyValue' => array( AmazonDynamoDB::TYPE_NUMBER => (string)$datasource_id_seq ),
    'RangeKeyCondition' => array(
        'ComparisonOperator' => AmazonDynamoDB::CONDITION_LESS_THAN_OR_EQUAL,
        'AttributeValueList' => array(
            array( AmazonDynamoDB::TYPE_NUMBER => '100' )
        )
    )
));
*/

// This will get a count of how many items this query will return and return the count
$response = $dynamodb->query(array(
    'TableName' => $table_name,
    'Count' => 'true',
    'HashKeyValue' => array( AmazonDynamoDB::TYPE_NUMBER => (string)$datasource_id_seq ),
    'RangeKeyCondition' => array(
        'ComparisonOperator' => AmazonDynamoDB::CONDITION_GREATER_THAN,
        'AttributeValueList' => array(
            array( AmazonDynamoDB::TYPE_NUMBER => '0' )
        )
    )
));

/*
// This limits the items return to the number given to 'Limit'
$response = $dynamodb->query(array(
    'TableName' => $table_name,
    'Limit' => 4,
    'HashKeyValue' => array( AmazonDynamoDB::TYPE_NUMBER => (string)$datasource_id_seq ),
    'RangeKeyCondition' => array(
        'ComparisonOperator' => AmazonDynamoDB::CONDITION_LESS_THAN_OR_EQUAL,
        'AttributeValueList' => array(
            array( AmazonDynamoDB::TYPE_NUMBER => '100' )
        )
    )
));

// Starts at the key given - for pagination
if (isset($response->body->LastEvaluatedKey))
{
    $query2_response = $dynamodb->query(array(
                'TableName' => 'Reply',
                'Limit' => 2,
                'ExclusiveStartKey' => $response->body->LastEvaluatedKey->to_array()->getArrayCopy(),
                'HashKeyValue' => array( AmazonDynamoDB::TYPE_STRING => 'Amazon DynamoDB#DynamoDB Thread 2' ),
                'RangeKeyCondition' => array(
                    'ComparisonOperator' => AmazonDynamoDB::CONDITION_GREATER_THAN_OR_EQUAL,
                    'AttributeValueList' => array(
                        array( AmazonDynamoDB::TYPE_STRING => $fourteen_days_ago )
                        )
                    )
                ));
}
*/

print_r($response);
$body = $response->body->to_array()->getArrayCopy();
echo 'Item Count: ' . $body['Count']."\n"; 

?>
