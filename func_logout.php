<?php

require_once 'globals.php';

unset( $_SESSION['access_token'] );
setcookie( $gameid_param_name, '', time() - 3600 );

header('Location: ' . $g_scripturl);
