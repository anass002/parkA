<?php 
	require_once('cars.class.php');
	require_once('categories.class.php');
	require_once('drivers.class.php');
	require_once('missions.class.php');
	require_once('papers.class.php');
	require_once('purshase.class.php');
	require_once('reservations.class.php');

	$reser = reservations::getReservationById(1);


	print_r($reser);

	

	



	
	
?>