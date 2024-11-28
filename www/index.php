<?php

// Include configuration
    require( __DIR__ . './../config.php' );

// Get the endpoint URL
    $parts = explode( '/', $_SERVER['REQUEST_URI'], 4 );

    $version_err = FALSE;
    $method_err  = FALSE;

    if ( !is_numeric( $parts[2] )) {
	// The first parameter is not numeric, so it can't be a proper version number

        $response = [
            'status'  => 'error',
            'message' => 'Improper API version format specified.',
        ];

		$version_err = TRUE;
	} else {

		$version      = trim( $parts[2] );
        $version_file = PROJECT_ROOT . '/classes.d/API/v' . str_replace( '.', '_', $version ) . '.php';

    // Check if the file exists
        if ( file_exists( $version_file )) {

            require_once( $version_file );
            $call = new \API\v1_0();
        } else {
            
            $response = [
                'status'  => 'error',
                'message' => 'Improper API version format specified.',
            ];

            $version_err = TRUE;
        }
	}

	if ( empty( $parts[3] )) {
	// No method specified

        $response = [
            'status'  => 'error',
            'message' => 'Improper API method call format specified.',
        ];

		$method_err = TRUE;
	} else {

        $method = trim( $parts[3] );
	}

// Check if the $version_err and $method_err are FALSE
    if ( !$version_err && !$method_err ) {

    // Check if the request method is POST
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {

        // Parse the incoming POST data
            $data        = json_decode( file_get_contents( 'php://input' ), true );
            $headers     = getallheaders();
            $auth_header = $headers['Authorization'];
            $auth_data   = substr( $auth_header, 6 );

        // Decode the base64 data and split it into username and password
            list( $username, $password ) = explode( ':', base64_decode( $auth_data ));

        // Check if the username and password exist
            $verify = $call->verify( $username, $password );

            if ( $verify ) { 

                if ( trim( $method ) == 'SendFax' ) {

                // Call the method
                    $call->get_method( $method );
                    $fax = new \FAX\SendFax();
                
                    $fax_data = $fax->insert_fax_data( $data['src'], $data['dst'], $data['update_url'] );

                    if ( !is_null( $fax_data )) {
                        
                    // Retrieve the base64 encoded string from the incoming data
                        $params = [
                            'files_path'            => $config_server['paths']['files'],
                            'base64_encoded_string' => $data['data'],
                            'filename'              => date( 'Y-m-d' ) . 'fax' . $fax_data . '-data.pdf'
                        ];

                        $fax->decode_data( $params );

                        $params_asterisk = [
                            'server'     => $config_server['ami']['server'],
                            'port'       => $config_server['ami']['port'],
                            'username'   => $config_server['ami']['username'],
                            'password'   => $config_server['ami']['password'],
                            'Async'      => $config_server['ami']['Async'],
                            'Channel'    => $config_server['ami']['Channel'],
                            'Context'    => $config_server['ami']['Context'],
                            'Exten'      => $data['dst'],
                            'MaxRetries' => $config_server['ami']['MaxRetries'],
                            'Priority'   => $config_server['ami']['Priority'],
                            'RetryTime'  => $config_server['ami']['RetryTime'],
                            'WaitTime'   => $config_server['ami']['WaitTime'],
                        ];

                        $asterisk         = $fax->asterisk( $params_asterisk );
                        print_r($asterisk );
                        // $original_request = json_decode( $asterisk['original_request'], true );

                        // echo '<pre>';
                        // echo 'Original Request: ' . $asterisk['original_request'];
                        // echo '</pre>';

                        // echo '<pre>';
                        // echo 'Originate Response: ' . $asterisk['OriginateResponse'];
                        // echo '</pre>';
                        
                        /**
                        * This code is for the action status
                        * $params_status = [
                        *    'server'     => $config_server['ami']['server'],
                        *    'port'       => $config_server['ami']['port'],
                        *    'username'   => $config_server['ami']['username'],
                        *    'password'   => $config_server['ami']['password'],
                        *    'action_id'  => $original_request['ActionID'],
                        *    'channel'    => $original_request['Channel'],
                        * ];

                    * // Wait for 10 seconds
                        * sleep(5);
                        * $status = $fax->status( $params_status );

                        * echo '<pre>';
                        * echo 'Status Response: ' . $status;
                        * echo '</pre>';
                        */

                        $response = [
                            'status'  => TRUE,
                            // 'id'      => $original_request['ActionID'],
                            'id'      => 1,
                        ];
                    } else {

                        $response = [
                            'status'  => FALSE,
                            'message' => 'Something went wrong!',
                        ];
                    }
                } else {

                    $response = [
                        'status'  => FALSE,
                        'message' => 'Improper API method call format specified.',
                    ];
                }
            } else {

                $response = [
                    'status'  => FALSE,
                    'message' => 'Incorrect username or password',
                ];
            }
        } else {

        // Respond with an error for non-POST requests
            $response = [
                'status'  => 'error',
                'message' => 'Invalid request method. Only POST requests are allowed.',
            ];
        }
    }

// Set the response headers
    header( 'Content-Type: application/json' );

// Send the JSON response
    echo json_encode( $response );
?>
