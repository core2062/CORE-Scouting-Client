
#coffee script compiler
<?php echo getCoffee('admin/coffeeScript.js'); ?>

#coffee table
<?php echo getCoffee('admin/coffeeTable.coffee'); ?>

adminRequest = (request) ->
	post "process.php", '{"request": "admin", "subRequest": "' + request + '"}', true

#TODO: make browser based download of exported CSV
###
function export(){
	json = post('process.php','{"request": "admin", "subRequest": "export"}');

	$('#jGrowl-container').jGrowl('right click -> save link as, to save the exported csv', {
		theme: 'message'
	});
}
###