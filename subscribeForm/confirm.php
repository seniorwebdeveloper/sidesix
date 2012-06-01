<?php

	session_start();
	echo unserialize( $_SESSION['code'] );
	exit(0);
?>