function adminRequest(request){
	post('process.php','{"request": "admin", "subRequest": "' + request + '"}');
}