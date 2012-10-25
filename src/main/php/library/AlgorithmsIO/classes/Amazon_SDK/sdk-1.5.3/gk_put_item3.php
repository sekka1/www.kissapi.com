<?php
// If necessary, reference the sdk.class.php file. 
// For example, the following line assumes the sdk.class.php file is in the same directory as this file
require_once dirname(__FILE__) . '/sdk.class.php';

// Instantiate the class.
$dynamodb = new AmazonDynamoDB();

####################################################################
# Setup some local variables for dates

$one_day_ago = date('Y-m-d H:i:s', strtotime("-1 days"));
$seven_days_ago = date('Y-m-d H:i:s', strtotime("-7 days"));
$fourteen_days_ago = date('Y-m-d H:i:s', strtotime("-14 days"));
$twenty_one_days_ago = date('Y-m-d H:i:s', strtotime("-21 days"));
 
####################################################################
# Adding data to the table
     
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
$total = 5000; // Total items you want to insert
$Write_Capacity_High = 50; // Number of items per batch input job
$Write_Capacity_Low = 10;

echo "Tablename: ".$table_name."\n";

///////////////////////////////////////////////////////////////////////////
// Increase write Throughtput Capacity
/*
$dynamodb->update_table(array(
    'TableName' => $table_name,
    'ProvisionedThroughput' => array(
        'ReadCapacityUnits' => 401,
        'WriteCapacityUnits' => 1000
    )
));
*/
$table_status = $dynamodb->describe_table(array(
    'TableName' => $table_name
));

// Check for success...
if ($table_status->isOK())
{
    print_r($table_status->body->Table->ProvisionedThroughput->to_array()->getArrayCopy());
}
else
{
    print_r($table_status);
}
/////////////////////////////////////////////////////////////////////////

//
// Open Pericomes sales data
//
ini_set('memory_limit', '4024M');
$data1_json = file_get_contents( '/root/pericom-020212/json_data-All.txt' );
$data1 = json_decode( $data1_json, true );

$count = 1;
$datasource_id_seq = '30002';
$type = 'sales-data';
echo 'total count: '.count( $data1['data'] )."\n";


// Loop through the data
foreach( $data1['data'] as $aRow ){

    // Run the batch job every X number of inputs
    for( $i=0; $i<$Write_Capacity_High; $i++){

        // Attribute array for the put_item call
        $put_items_attributes['item_id_seq'] = array( AmazonDynamoDB::TYPE_NUMBER => (string)$count );
        $put_items_attributes['datasource_id_seq'] = array( AmazonDynamoDB::TYPE_NUMBER => (string)$datasource_id_seq );
        $put_items_attributes['type'] = array( AmazonDynamoDB::TYPE_STRING => (string)$type ); // Setting the type

        // Make sure non of the fields are empty if it is set to NULL, Dynamo Dont like it
        foreach( $aRow as $key=>$anItem ){
            if( $anItem == '' )
                $anItem = 'NULL';

            // Put item in $put_items_attributes array
            $put_items_attributes[$key] = array( AmazonDynamoDB::TYPE_STRING => (string)$anItem );
        }

        //print_r( $put_items_attributes );

        $dynamodb->batch($queue)->put_item(array(
                    'TableName' => $table_name,
                    'Item' => $put_items_attributes
                    //array(
                    ///'datasource_id_seq'              => array( AmazonDynamoDB::TYPE_STRING           => '1010'  ), // Hash Key
                    //'INTERNAL_PART_NUMBER'          => array( AmazonDynamoDB::TYPE_STRING           => (string)$aRow['INTERNAL_PART_NUMBER']   )
                    //)
                    ));

        $count++;
    }

    // Execute the batch of requests in parallel.
    $responses = $dynamodb->batch($queue)->send();

    // Check for success...
    if ($responses->areOK())
    {
        echo "The data has been added to the table." . PHP_EOL;

        if( $count > 3000 )
            print_r( $responses );
    }
    else
    {
        print_r($responses);
    }

    sleep( 3 );

    // Only put a certain amount in
    if( $count > $total )
        break;

    echo "Count: ".$count."\n";
}
?>
