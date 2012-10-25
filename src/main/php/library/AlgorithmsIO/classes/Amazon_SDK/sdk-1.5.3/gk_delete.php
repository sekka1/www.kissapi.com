<?php
// If necessary, reference the sdk.class.php file. 
// For example, the following line assumes the sdk.class.php file is in the same directory as this file
 require_once dirname(__FILE__) . '/sdk.class.php';

$table_name = 'Datasources';
echo "Tablename: ".$table_name."\n";
$datasource_id_seq = '1010';

// Instantiate the class.
$dynamodb = new AmazonDynamoDB();


///////////////////////////////////
// Describe table
/*
$response = $dynamodb->describe_table(array(
    'TableName' => $table_name
));
 
print_r($response->body);
*/

// Deleting an item
$response = $dynamodb->delete_item(array(
    'TableName' => $table_name,
    'Key' => array(
        'HashKeyElement' => array( // "id" column
            AmazonDynamoDB::TYPE_NUMBER => '1'
        ),
        'RangeKeyElement' => array( // "date" column
            AmazonDynamoDB::TYPE_NUMBER => (string)$datasource_id_seq
        )
    )
));
 
// Check for success...
if ($response->isOK())
{
    echo 'Deleting the item...' . PHP_EOL;
}
else
{
    print_r($response);
}

?>
