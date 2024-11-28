<?php namespace API;

    require_once( PROJECT_ROOT . '/function.d/dbh.php' );

    class v1_0 {
    /**
	 * Version 1.0
	 */

        public function verify( $username, $password ) {
        /**
		 * Authenticate the user
		 *
		 * @return bool - TRUE if the credentials authenticate, FALSE otherwise.
		 */
        
        // Convert string to md5
            $md5_pass = md5( $password );

            $user_query = <<<SQL
  SELECT *
    FROM user
   WHERE username = :username
     AND password = :password
SQL;
            $user_stmt = dbh()->prepare( $user_query );

            $user_stmt->bindParam(':username',   $username,  \PDO::PARAM_STR);
            $user_stmt->bindParam(':password',   $md5_pass,  \PDO::PARAM_STR);

            $user_result = $user_stmt->execute();
            $row_count   = $user_stmt->rowCount();

            if ( $row_count > 0 ) {

            // User exists
                return TRUE;
            } else {

            // User does not exist
                return FALSE;
            }
        }

        public function get_method( $method ) {
            
            switch ( $method ) {

				case 'SendFax' :
				/**
				 * Manage SendFax. Parameters are an array of the following, one per fax:
				 *
                 * @param int    src         - The src of the fax.
				 * @param int    dst         - The dst of the fax.
				 * @param string update_url  - The update_url of the fax.
				 * @param array  params      - An array of string params for decode_data.
                 * 
				 * @return int - A int with the results of the method call, otherwise NULL.
				 */

					return require( PROJECT_ROOT . '/classes.d/API/v1_0/SendFax.php' );

				break;

				default :

					return NULL;

				break;
			}
        }
    }


?>