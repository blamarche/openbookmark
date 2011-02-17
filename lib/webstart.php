<?php

if ( ini_get( 'register_globals' ) ) {
        if ( isset( $_REQUEST['GLOBALS'] ) ) {
                die( '<a href="http://www.hardened-php.net/index.76.html">$GLOBALS overwrite vulnerability</a>');
        }
        $verboten = array(
                'GLOBALS',
                '_SERVER',
                'HTTP_SERVER_VARS',
                '_GET',
                'HTTP_GET_VARS',
                '_POST',
                'HTTP_POST_VARS',
                '_COOKIE',
                'HTTP_COOKIE_VARS',
                '_FILES',
                'HTTP_POST_FILES',
                '_ENV',
                'HTTP_ENV_VARS',
                '_REQUEST',
                '_SESSION',
                'HTTP_SESSION_VARS'
        );
        foreach ( $_REQUEST as $name => $value ) {
                if( in_array( $name, $verboten ) ) {
                        header( "HTTP/1.x 500 Internal Server Error" );
                        echo "register_globals security paranoia: trying to overwrite superglobals, aborting.";
                        die( -1 );
                }
                unset( $GLOBALS[$name] );
        }
}

function &fix_magic_quotes( &$arr ) {
	if ( get_magic_quotes_gpc() ) {
		foreach( $arr as $key => $val ) {
			if( is_array( $val ) ) {
				fix_magic_quotes( $arr[$key] );
			} else {
				$arr[$key] = stripslashes( $val );
			}
		}
	}
	return $arr;
}

fix_magic_quotes( $_COOKIE );
fix_magic_quotes( $_ENV );
fix_magic_quotes( $_GET );
fix_magic_quotes( $_POST );
fix_magic_quotes( $_REQUEST );


?>