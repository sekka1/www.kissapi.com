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

// Add items to the batch.
$dynamodb->batch($queue)->put_item(array(
    'TableName' => 'ProductCatalog',
    'Item' => array(
        'Id'              => array( AmazonDynamoDB::TYPE_NUMBER           => '101'              ), // Hash Key
        'Title'           => array( AmazonDynamoDB::TYPE_STRING           => 'Book 101 Title'   ),
        'ISBN'            => array( AmazonDynamoDB::TYPE_STRING           => '111-1111111111'   ),
        'Authors'         => array( AmazonDynamoDB::TYPE_ARRAY_OF_STRINGS => array('Author1')   ),
        'Price'           => array( AmazonDynamoDB::TYPE_NUMBER           => '2'                ),
        'Dimensions'      => array( AmazonDynamoDB::TYPE_STRING           => '8.5 x 11.0 x 0.5' ),
        'PageCount'       => array( AmazonDynamoDB::TYPE_NUMBER           => '500'              ),
        'InPublication'   => array( AmazonDynamoDB::TYPE_NUMBER           => '1'                ),
        'ProductCategory' => array( AmazonDynamoDB::TYPE_STRING           => 'Book'             )
    )
));

$dynamodb->batch($queue)->put_item(array(
    'TableName' => 'ProductCatalog',
    'Item' => array(
        'Id'              => array( AmazonDynamoDB::TYPE_NUMBER           => '102'                       ), // Hash Key
        'Title'           => array( AmazonDynamoDB::TYPE_STRING           => 'Book 102 Title'            ),
        'ISBN'            => array( AmazonDynamoDB::TYPE_STRING           => '222-2222222222'            ),
        'Authors'         => array( AmazonDynamoDB::TYPE_ARRAY_OF_STRINGS => array('Author1', 'Author2') ),
        'Price'           => array( AmazonDynamoDB::TYPE_NUMBER           => '20'                        ),
        'Dimensions'      => array( AmazonDynamoDB::TYPE_STRING           => '8.5 x 11.0 x 0.8'          ),
        'PageCount'       => array( AmazonDynamoDB::TYPE_NUMBER           => '600'                       ),
        'InPublication'   => array( AmazonDynamoDB::TYPE_NUMBER           => '1'                         ),
        'ProductCategory' => array( AmazonDynamoDB::TYPE_STRING           => 'Book'                      )
    )
));

// Execute the batch of requests in parallel.
$responses = $dynamodb->batch($queue)->send();
     
// Check for success...
if ($responses->areOK())
{
    echo "The data has been added to the table." . PHP_EOL;
}
    else
{
    print_r($responses);
}
?>
