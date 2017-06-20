<?php 
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "nbp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

if ($_POST){
	$eur = null;
	$usd = null;
	$gbp = null;
	$chf = null;
	
	$date = $_POST['date'];
	
	$newDate = date("Y-m-d", strtotime($date));
	$file = @file_get_contents("http://api.nbp.pl/api/exchangerates/tables/a/" . $newDate . "?format=json");
	
	$json = json_decode($file, TRUE);
	
	for ($i = 0; $i < sizeof($json[0]['rates']); $i++){
		if ($json[0]['rates'][$i]['code'] == "EUR"){
			$eur = $json[0]['rates'][$i]['mid'];
		}elseif ($json[0]['rates'][$i]['code'] == "USD"){
			$usd = $json[0]['rates'][$i]['mid'];
		}elseif ($json[0]['rates'][$i]['code'] == "GBP"){
			$gbp = $json[0]['rates'][$i]['mid'];
		}elseif ($json[0]['rates'][$i]['code'] == "CHF"){
			$chf = $json[0]['rates'][$i]['mid'];
		}
	}
	$aTableName = $json[0]['no'];
	
}
$sql = "INSERT INTO kursy_walut (eur, usd, gbp, chf, nazwa, zapytanie)
VALUES ('" . $eur . "', '" .  $usd . "', '" . $gbp . "', '" . $chf . "', '" . $aTableName . "', '" . $newDate . "')";
$table1 = "";
if (mysqli_query($conn, $sql)) {
    $table1 = 
		"<div class=\"table-nbp\">
		<table> 
			<tr>
				<th>Data</th>
				<td>$newDate</td>
			</tr>
			<tr>
				<th>Tabela</th>
				<td>$aTableName</td>
			</tr>
			<tr>
				<th>EUR</th>
				<td>$eur</td>
			</tr>
			<tr>
				<th>USD</th>
				<td>$usd</td>
			</tr>
			<tr>
				<th>GBP</th>
				<td>$gbp</td>
			</tr>
			<tr>
				<th>CHF</th>
				<td>$chf</td>
			</tr>
		</table>
		</div>";
} 

$sql_get_last_10 = "SELECT * FROM kursy_walut ORDER BY pid DESC LIMIT 10";
$result = mysqli_query($conn, $sql_get_last_10) ;
$table2 = "
			<div class=\"table-nbp\">
			<table> 
			<tr>
				<th>Zapytanie</th>
				<th>Czas</th>
			</tr>";
if (mysqli_num_rows($result) > 0){
	while($row = mysqli_fetch_assoc($result)){
		$table2 .="<tr>
								<td>".$row["zapytanie"]."</td>
								<td>".$row["czas"]."</td>
							</tr>";
	}
}else{
	$table2 = "";
}
$table2 .= "</table></div>";

if ($file === FALSE){
	$table1 = "";
	$table2 = "";
	$sql = "INSERT INTO kursy_walut (zapytanie) VALUES ('$newDate')";
	mysqli_query($conn, $sql);
}
echo $table1;
echo $table2;    
		

$conn->close();

?>