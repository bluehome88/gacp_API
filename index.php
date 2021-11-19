<!DOCTYPE html>
<html lang="en">
<head>
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
		<link rel="stylesheet" href="./css/datatables.css">

</head>
<body>
	<div id="wrap">
		<div class="container">
        <h3>MemberClick Data List</h3>
			<table cellpadding="0" cellspacing="0" border="0" class="datatable table table-striped table-bordered">
				<thead>
					<tr>
						<th>No</th>
						<th>Profile ID</th>
						<th>Last Name</th>
						<th>First Name</th>
						<th>Current Title</th>
						<th>Organization</th>
						<th>OKEYID</th>
						<th>Email | Primary</th>
						<th>Expiration Date</th>
						<th>Member Type</th>
					</tr>
				</thead>
			</table>
		</div>
	</div>
	<script src="./js/jquery.min.js"></script>
	<script src="./js/bootstrap.min.js"></script>
	<script src="./js/datatables.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$('.datatable').dataTable({
				"pagingType": "full_numbers",
				"processing": true,
		        "serverSide": true,
		        "ajax": "/sync/ajax_search.php"
			});	
		});
	</script>
</body>
</html>