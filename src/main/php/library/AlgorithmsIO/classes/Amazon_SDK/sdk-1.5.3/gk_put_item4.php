<?php
// If necessary, reference the sdk.class.php file. 
// For example, the following line assumes the sdk.class.php file is in the same directory as this file
require_once dirname(__FILE__) . '/sdk.class.php';

// Instantiate the class.
$dynamodb = new AmazonDynamoDB();

echo PHP_EOL . PHP_EOL;
echo "# Adding data to the table..." . PHP_EOL;

# Adding data to the table
echo "# Adding data to the table..." . PHP_EOL;

// Set up batch requests.
$queue = new CFBatchRequest();
$queue->use_credentials($dynamodb->credentials);

// Disable SSL
$dynamodb->disable_ssl();

$table_name = 'Datasources';
echo "Tablename: ".$table_name."\n";
$datasource_id_seq = '30001';

/////////////////////////////////////////////////////////////////////////

//
// Open Pericomes sales data
//
ini_set('memory_limit', '4024M');
$data1_json = file_get_contents( '/root/pericom-020212/json_data-All.txt' );
$data1 = json_decode( $data1_json, true );

$count = 1;

for( $n=0; $n<1000; $n++ ){

    for( $i=0; $i<=50; $i++ ){

        // Add items to the batch.
        $dynamodb->batch($queue)->put_item(array(
                    'TableName' => $table_name, 
                    'Item' => array(
                        'item_id_seq'              => array( AmazonDynamoDB::TYPE_NUMBER           => (string)$count              ), // Hash Key
                        'datasource_id_seq'           => array( AmazonDynamoDB::TYPE_NUMBER           => (string)$datasource_id_seq   ),
                        'ISBN'            => array( AmazonDynamoDB::TYPE_STRING           => '111-1111111111'   ),
                        'Authors'         => array( AmazonDynamoDB::TYPE_ARRAY_OF_STRINGS => array('Author1')   ),
                        'Price'           => array( AmazonDynamoDB::TYPE_NUMBER           => '2'                ),
                        'Dimensions'      => array( AmazonDynamoDB::TYPE_STRING           => '8.5 x 11.0 x 0.5' ),
                        'PageCount'       => array( AmazonDynamoDB::TYPE_NUMBER           => '500'              ),
                        'InPublication'   => array( AmazonDynamoDB::TYPE_NUMBER           => '1'                ),
                        'ProductCategory' => array( AmazonDynamoDB::TYPE_STRING           => 'Book'             )
                        )
                    ));

        $count++;
    }

    // Execute the batch of requests in parallel.
    $responses = $dynamodb->batch($queue)->send();

    // Check for success...
    if ($responses->areOK())
    {
        echo "The data has been added to the table." . PHP_EOL;
        echo "Count: " . $count ."\n";
    }
    else
    {
        print_r($responses);
    }

    sleep( 1 ); 
}

?>
