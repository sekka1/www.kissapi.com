<?php

class Paypal
{
    private $paypal_reply_url;
    private $generic_db;
    private $utilities;
    private $fp;

    public function __construct(){

        // Paypal sandbox test IPN
        //$this->paypal_reply_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        // Paypal production IPN
        $this->paypal_reply_url = 'https://www.paypal.com/cgi-bin/webscr';

        require_once('AlgorithmsIO/model/Generic.php');
        $this->generic_db = new Generic();

        require_once('AlgorithmsIO/classes/Utilities.php');
        $this->utilities = new Utilities();

        // Open a file to append transaction to
        $this->fp = fopen('/opt/paypal/paypal_transactions.txt', 'a');
    }
    public function validateMessage( $post_params ){
    // This takes all the POST params and builds the correct response to Paypal to validate this
    // message really came from them

        $isValid = false;

        // Read the post from PayPal and add 'cmd'                                                                   
        $req['cmd'] = '_notify-validate';                                                                            
        $reply_params = 'cmd=_notify-validate';

        foreach ( $post_params  as $key => $value){
        // Add each of the POST param by the IPN into an array to be passed to curl for the reply                
                                                                                                                     
            $reply_params .= "&$key=$value";                                                                         
            $req[$key] = $value;                                                                                     
        } 

        fwrite($this->fp, "\n-----POST PARAMS-----".$reply_params . ' -- ' . $this->paypal_reply_url);

        // POST reply to Paypal to verify this is a legit message from them                                          
        $post_respond = $this->utilities->curlPost( $this->paypal_reply_url, $req );

        fwrite($this->fp, "\n-----POST RESPONSE-----".$post_respond);

        if( $post_respond == 'VERIFIED' )
            $isValid = true;

        return $isValid;
    }
    public function validatePaymentStatus( $post_params ){
    // Check that the "payment_status" in the post parame is "Completed"
    // This means that the money has gone through into our account

        $paymentIsCompleted = false;

        if( isset( $post_params['payment_status'] ) ){
            if( $post_params['payment_status'] == 'Completed' ){
                $paymentIsCompleted = true;
            }
            
            fwrite($this->fp, "\n-----payment_status-----".$post_params['payment_status']);
        }

        return $paymentIsCompleted;
    }
    public function validatePurchasedItem( $post_params ){
    // Make sure that the item that was purchases is an actual item and the price matches to it

        $isValidPurchase = false;

        if( ( isset( $post_params['mc_gross'] ) && isset( $post_params['item_number'] ) )
            || ( isset( $post_params['mc_gross_1'] ) && isset( $post_params['item_number1'] ) )
            ){

            //
            // Insert each item below here
            //

            // Test Item from the paypal dev page test sending an IPN message
            if( $post_params['mc_gross'] > 9.34 && $post_params['item_number'] == 'AK-1234' ){
                $isValidPurchase = true;
                fwrite($this->fp, "\n-----TEST ITEM: mc_gross, item_number-----".$post_params['mc_gross']." - ".$post_params['item_number']);
            }
            // Item 1 algo credit
            if( $post_params['mc_gross'] >= 1.00 && $post_params['item_number'] == 'AC-000001-000' ){
                $isValidPurchase = true;
            }
            // Item 500 algo credit
            if( $post_params['mc_gross'] >= 25.00 && $post_params['item_number'] == 'AC-000500-000' ){
                $isValidPurchase = true;
            }
            // Item 1,000 algo credit
            if( $post_params['mc_gross'] >= 50.00 && $post_params['item_number'] == 'AC-001000-000' ){
                $isValidPurchase = true;
            }
            // Item 5k algo credit
            if( $post_params['mc_gross'] >= 250.00 && $post_params['item_number'] == 'AC-005000-000' ){
                $isValidPurchase = true;
            }
            // Item 10k algo credit
            if( $post_params['mc_gross'] >= 450.00 && $post_params['item_number'] == 'AC-010000-000' ){
                $isValidPurchase = true;
            }
            // Item 25k algo credit
            if( $post_params['mc_gross'] >= 1000.00 && $post_params['item_number'] == 'AC-025000-000' ){
                $isValidPurchase = true;
            }
            // Item 50k algo credit
            if( $post_params['mc_gross'] >= 1875.00 && $post_params['item_number'] == 'AC-050000-000' ){
                $isValidPurchase = true;
            }
            // Item 100k algo credit
            if( $post_params['mc_gross'] >= 3500.00 && $post_params['item_number'] == 'AC-100000-000' ){
                $isValidPurchase = true;
            }            

            fwrite($this->fp, "\n-----mc_gross (aka the $ amount): ".$post_params['mc_gross']." ---- item_number:  ".$post_params['item_number']);
        }else{
            fwrite($this->fp, "\n-----mc_gross, item_number is not set-----");
        }

        return $isValidPurchase;
    }
    public function getTransactionId( $post_params ){
    // Retrieves the transaction id

        $transaction_id = 'unknown';

        if( isset( $post_params['txn_id'] ) )
            $transaction_id = $post_params['txn_id'];

        fwrite($this->fp, "\n-----Transaction Id-----".$transaction_id); 

        return $transaction_id;
    }
    public function getUsersEmail( $post_params ){
    // Retrieves the user's account that is attached to this transaction.  Credits should be placed into
    // this user's account

        $users_email = 'unknown';

        if( isset( $post_params['custom'] ) ){

            $temp_split = preg_split( '/:/', $post_params['custom'] );

            $users_email = $temp_split[1];             
        }

        fwrite($this->fp, "\n-----User's Email-----".$users_email);

        return $users_email;
    }
    public function getPurchasedItemNumber( $post_params ){
    // Retrieves the purchased item number that the user bought from Paypal

        $output = 'unknown';

        if( isset( $post_params['item_number'] ) )
            $output = $post_params['item_number'];

        return $output;
    }
    public function insertCreditIntoUsersAccount( $user_id_seq, $transaction_id, $item_purchased ){
    // This function will insert the credits the user purchased into their account and make a record of it

        $credit_to_insert = $this->getItemCreditsWorth( $item_purchased );

        // Insert a record of this transaction into api_calls table
        $data1['authToken'] = 'system';
        $data1['action'] = 'purchased_credit_txn_'.$transaction_id;
        $data1['file_size'] = '0';
        $data1['created'] = 'now()';
        $data1['lastModified'] = 'now()';
        $api_calls_id = $this->generic_db->save( 'api_calls', $data1 );

        // Insert the credit into the credits table
        $data2['user_id'] = $user_id_seq;
        $data2['credits'] = $credit_to_insert;
        $data2['API_Calls_id'] = $api_calls_id;
        $data2['created'] = 'now()';
        $data2['lastModified'] = 'now()';
        $credits_id_seq = $this->generic_db->save( 'credits', $data2 );

        fwrite($this->fp, "\n-----Inserted Credits - user_id: ".$user_id_seq."----credits: ".$credit_to_insert."-----api_calls_id: ".$api_calls_id."----item: ".$item_purchased);

        return $credits_id_seq;
    }
    private function getItemCreditsWorth( $item ){
    // Returns a credit value for a given item number

        $credit = 0;

        switch( $item ){
            
            case 'AC-000001-000':
            $credit = 1;
            break;
            case 'AC-000500-000':
            $credit = 500;
            break;
            case 'AC-001000-000':
            $credit = 1000;
            break;
            case 'AC-005000-000':
            $credit = 5000;
            break;
            case 'AC-010000-000':
            $credit = 10000;
            break;
            case 'AC-025000-000':
            $credit = 25000;
            break;
            case 'AC-050000-000':
            $credit = 50000;
            break;
            case 'AC-100000-000':
            $credit = 100000;
            break;
        }

        return $credit;
    }
    public function endofTransaction(){
    // Appends and end to this current transaction

        fwrite($this->fp, "\n-----END-----");
    }
}
