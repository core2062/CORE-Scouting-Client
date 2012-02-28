function getTeams(){
	post('process.php','{"request": "admin", "subRequest": "getTeams"}');
}

function getTeamProfiles(){
	post('process.php','{"request": "admin", "subRequest": "getTeamProfiles"}');
}