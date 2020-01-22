<?php

if(!AUT)
	{
		header('Location: login.php?not_aut');
		exit;
	}

setcookie('sid', '', time() - 86400);
header('Location: login.php');