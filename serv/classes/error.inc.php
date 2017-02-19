<?php 
	function returnResponse($state = false, $data = ''){
		$result = Array();
		$result['error'] = $state;
		$result['data'] = $data;
		return $result;
	}
?>