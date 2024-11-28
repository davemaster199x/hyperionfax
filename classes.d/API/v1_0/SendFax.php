<?php namespace FAX;

    require_once( PROJECT_ROOT . '/function.d/dbh.php' );
    require_once( PROJECT_ROOT . '/../atlas-classes/autoloader.php' );

    class SendFax {

        public function insert_fax_data( $src, $dst, $update_url ) {
        /**
		 * Inserting fax data in the database
		 *
		 * @return int -  If the inserting is success return lastInsertId, otherwise NULL 
		 */

            $pdo = dbh();
            
            $fax_query = <<<SQL
  INSERT INTO fax
    SET src         = :src,
        dst         = :dst,
        update_url  = :update_url
SQL;

            $fax_stmt = $pdo->prepare( $fax_query );

            $fax_stmt->bindParam(':src',        $src,        \PDO::PARAM_STR);
            $fax_stmt->bindParam(':dst',        $dst,        \PDO::PARAM_STR);
            $fax_stmt->bindParam(':update_url', $update_url, \PDO::PARAM_STR);

            $inserted = $fax_stmt->execute();

            if ( $inserted ) {

                return $pdo->lastInsertId();
            } else {

                return NULL;
            }
        }

        public function decode_data( $params ) {
        /**
		 * Convert base64 data to file
		 *
		 * Put the converted file to files folder
		 */

        // Decode the base64 data to its binary representation
            $file_data = base64_decode( $params['base64_encoded_string'] );

        // Write the binary data to a file
            $file_path = PROJECT_ROOT . '/' . $params['files_path'] . '/' . $params['filename'];

            file_put_contents( $file_path, $file_data, LOCK_EX ); 
        }

        public function asterisk( $params ) {
        /**
		 * Communicates with Asterisk to send the fax
		 *
		 * Call the autoloader.php outside the PROJECT_ROOT
		 */

        // Connect to AMI and authenticate
            // $ami = new \Atlas\PBX\Asterisk\Manager\AMI( $params['server'], $params['port'] );

            // $ami->login( $params['username'], $params['password'] );

        // Originate call to speakers
            // $ami_action = new \Atlas\PBX\Asterisk\Manager\AMI\Action( 'Originate' );

        // You can use any unique identifier generation logic
            $action_id = uniqid();

            // $ami_action->packet( 'ActionID',   $action_id );
            // $ami_action->packet( 'Async',      $params['Async'] );
            // $ami_action->packet( 'Channel',    $params['Channel'] );
            // $ami_action->packet( 'Context',    $params['Context'] );
            // $ami_action->packet( 'Exten',      $params['Exten'] );
            // $ami_action->packet( 'MaxRetries', $params['MaxRetries'] );
            // $ami_action->packet( 'Priority',   $params['Priority'] );
            // $ami_action->packet( 'RetryTime',  $params['RetryTime'] );
            // $ami_action->packet( 'WaitTime',   $params['WaitTime'] );

            // $ami->add_action( $ami_action );

            // $result = $ami->submit();

            // $original_request = json_encode( $result['originate'][0]['action'], JSON_PRETTY_PRINT );

            // $parse            = $ami->parse_response( $result['originate'][0]['response'] );
            // $parse_response   = json_encode( $parse, JSON_PRETTY_PRINT );

            // return [ 'original_request' => $original_request, 'OriginateResponse' => $parse_response ] ;

            // Asterisk Manager Interface (AMI) credentials
            $ami_username = $params['username'];
            $ami_password = $params['password'];

            // Asterisk server details
            $ami_host = $params['server'];
            $ami_port = $params['port']; // Default AMI port

            // Build the Originate action
            // $action     = "Action:     Originate\r\n";
            // $action_id  = "ActionID:   " . $action_id .            "\r\n";
            // $async      = "Async:      " . $params['Async'] .      "\r\n";
            // $channel    = "Channel:    " . $params['Channel'] .    "\r\n";
            // $context    = "Context:    " . $params['Context'] .    "\r\n";
            // $exten      = "Exten:      " . $params['Exten'] .      "\r\n";
            // $maxretries = "MaxRetries: " . $params['MaxRetries'] . "\r\n";
            // $priority   = "Priority:   " . $params['Priority'] .   "\r\n";
            // $retrytime  = "RetryTime:  " . $params['RetryTime'] .  "\r\n";
            // $waittime   = "WaitTime:   " . $params['WaitTime'] .   "\r\n";

            // // Concatenate the action parameters
            // $amiCommand = $action . $action_id . $async . $channel . $context . $exten . $maxretries . $priority . $retrytime . $waittime;

            $action = "Action: Originate\r\n";
            $channel = "Channel: Local/sendfax@hyperionworks-fax\r\n"; // Replace with your channel
            $context = "Context: hyperionworks-outbound-domestic\r\n"; // Replace with your context
            $exten = "Exten: " . $params['Exten'] .  "\r\n"; // Replace with your extension
            $priority = "Priority: 1\r\n";
            $callerID = "CallerID: John Doe <1001>\r\n"; // Replace with your Caller ID
            $timeout = "Timeout: 30000\r\n"; // Replace with your desired timeout
            $async = "Async: yes\r\n";

            // Concatenate the action parameters
            $amiCommand = $action . $channel . $context . $exten . $priority . $callerID . $timeout . $async;

            // Create the full AMI request
            $amiRequest  = "POST / HTTP/1.1\r\n";
            $amiRequest .= "Host: $ami_host : $ami_port\r\n";
            $amiRequest .= "Authorization: Basic " . base64_encode( "$ami_username : $ami_password" ) . "\r\n";
            $amiRequest .= "Content-Length: " . strlen( $amiCommand ) . "\r\n\r\n";
            $amiRequest .= $amiCommand;

            // Open a connection to the Asterisk Manager Interface
            $amiConnection = fsockopen ( $ami_host, $ami_port, $errno, $errstr, 10 );

            if (!$amiConnection) {
                die( "Unable to connect to Asterisk Manager Interface: $errstr ($errno)\n" );
            }

            // Send the AMI request
            fwrite( $amiConnection, $amiRequest );

            $response = '';
            while ( !feof( $amiConnection )) {
                $response .= fgets( $amiConnection );
            }

            // Close the connection
            fclose( $amiConnection );

            return $response;
        }

        public function status( $params ) {
        /**
		 * Communicates with Asterisk to request status
		 *
		 * Call the autoloader.php outside the PROJECT_ROOT
		 */

        // Connect to AMI and authenticate
            $ami = new \Atlas\PBX\Asterisk\Manager\AMI( $params['server'], $params['port'] );

            $ami->login( $params['username'], $params['password'] );

            $ami_action_status = new \Atlas\PBX\Asterisk\Manager\AMI\Action( 'Status' );

            $ami_action_status->packet( 'ActionID',     $params['action_id'] );
            $ami_action_status->packet( 'Channel',      $params['channel'] );
            $ami_action_status->packet( 'AllVariables', true );

        // Add Status action to the AMI instance
            $ami->add_action($ami_action_status);

        // Submit the Status action
            $result_status = $ami->submit();

            $parse = $ami->parse_response( $result_status['status'][0]['response'] );

        // Convert the Status result to JSON
            $response_status = json_encode( $parse, JSON_PRETTY_PRINT );

            return $response_status;
        }
    }
?>