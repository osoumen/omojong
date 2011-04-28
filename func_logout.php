<?php

require_once 'globals.php';
require_once 'common.php';

unset( $_SESSION['access_token'] );
setcookie( $gameid_param_name, '', time() - 3600 );

redirect_to_prevpage();
