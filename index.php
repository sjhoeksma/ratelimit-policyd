<?php
$db_host = 'localhost'; // Server Name
$db_user = 'policyd'; // Username
$db_pass = '*******'; // Password
$db_name = 'policyd'; // Database Name

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$conn) {
	die ('Failed to connect to MySQL: ' . mysqli_connect_error());	
}

$sql = 'SELECT dbispconfig.mail_user.email, dbispconfig.mail_user.disablesmtp ,ratelimit.quota,
        ratelimit.used, ratelimit.updated , FROM_UNIXTIME(expiry) AS expirytime FROM ratelimit LEFT JOIN dbispconfig.mail_user on
        ratelimit.sender=dbispconfig.mail_user.email
        WHERE disablesmtp="y" or FROM_UNIXTIME(expiry)>=CURRENT_TIMESTAMP() 
        ORDER BY disablesmtp DESC, expirytime DESC';
		
$query = mysqli_query($conn, $sql);

if (!$query) {
	die ('SQL Error: ' . mysqli_error($conn));
}
?>
<html>
<head>
	<title>Mail Policy</title>
        <meta http-equiv="Refresh" content="60">
	<style type="text/css">
		body {
			font-size: 15px;
			color: #343d44;
			font-family: "segoe-ui", "open-sans", tahoma, arial;
			padding: 0;
			margin: 0;
		}
		table {
			margin: auto;
			font-family: "Lucida Sans Unicode", "Lucida Grande", "Segoe Ui";
			font-size: 12px;
		}

		h1 {
			margin: 25px auto 0;
			text-align: center;
			text-transform: uppercase;
			font-size: 17px;
		}

		table td {
			transition: all .5s;
		}
		
		/* Table */
		.data-table {
			border-collapse: collapse;
			font-size: 14px;
			min-width: 537px;
		}

		.data-table th, 
		.data-table td {
			border: 1px solid #e1edff;
			padding: 7px 17px;
		}
		.data-table caption {
			margin: 7px;
		}

		/* Table Header */
		.data-table thead th {
			background-color: #508abb;
			color: #FFFFFF;
			border-color: #6ea1cc !important;
			text-transform: uppercase;
		}

		/* Table Body */
		.data-table tbody td {
			color: #353535;
		}
		.data-table tbody td:first-child,
		.data-table tbody td:nth-child(4),
		.data-table tbody td:last-child {
			text-align: right;
		}

		.data-table tbody tr:nth-child(odd) td {
			background-color: #f4fbff;
		}
		.data-table tbody tr:hover td {
			background-color: #ffffa2;
			border-color: #ffff0f;
		}

		/* Table Footer */
		.data-table tfoot th {
			background-color: #e5f5ff;
			text-align: right;
		}
		.data-table tfoot th:first-child {
			text-align: left;
		}
		.data-table tbody tr.higlight  td ,  .data-table tbody tr:nth-child(odd).highlight td
		{
			background-color: #ffcccc;
		}
	</style>
</head>
<body>
	<h1>Mail Policy</h1>
	<table class="data-table">
		<thead>
			<tr>
				<th>Sender</th>
				<th>Lock</th>
				<th>Quota</th>
				<th>Used</th>
				<th>Update</th>
				<th>Expire</th>
			</tr>
		</thead>
		<tbody>
		<?php
		while ($row = mysqli_fetch_array($query))
		{
			echo '<tr'. ($row['disablesmtp']=='y' ? ' class="highlight"' : '') .'>
					<td>'.$row['email'].'</td>
				    <td>'.$row['disablesmtp'].'</td>
					<td>'.$row['quota'].'</td>
					<td>'.$row['used'].'</td>
					<td>'. date('F d, Y G:H', strtotime($row['updated'])) . '</td>
					<td>'. date('F d, Y G:H', strtotime($row['expirytime'])) . '</td>
				</tr>';
		}?>
		</tbody>
	</table>
</body>
</html>
