<?php
   //include ("openSSL.php");
class Database {
private $servername = "localhost";
private $username = "root";
private $password = "q";
private $db_name = "safebook";
private $link;

// constructor
public function __construct() {
}

private function connectToDatabase(){
	if (!$this->link = mysql_connect($this->servername, $this->username, $this->password)) {
	   
	    echo 'Could not connect to mysql';
	    exit;
	}
	if (!mysql_select_db($this->db_name, $this->link)) {
 echo "ERROR 1";
	    echo 'Could not select database';
	    exit;
	}
}
private function disconnectFromDatabase(){
	mysql_close($link);
}
public function checkForNotifications($UserId){
	$this->connectToDatabase();
	$sql    = 'SELECT ultimaMensagem FROM utilizador WHERE idUtilizador = '.$UserId;
	$result = mysql_query($sql, $this->link);
	$this->checkIfValidResult($result);
	$lastMessage = mysql_fetch_assoc($result);
	$sql    = 'SELECT  max(idMensagem) as id FROM mensagem as f WHERE f.idReceptor = '.$UserId;
	$result = mysql_query($sql, $this->link);
	$this->checkIfValidResult($result);
	$lastMessageReceived = mysql_fetch_assoc($result);
	$nrNotifications = $lastMessageReceived['id']-$lastMessage['ultimaMensagem'];
	mysql_free_result($result);
	$this->disconnectFromDatabase();
	return 	$nrNotifications;
}
private function findUserById($UserId){
	$this->connectToDatabase();
	$sql    = 'SELECT * FROM utilizador WHERE idUtilizador = '.$UserId;
	$result = mysql_query($sql, $this->link);

	$this->checkIfValidResult($result);

	$user = mysql_fetch_assoc($result);
	mysql_free_result($result);
	$this->disconnectFromDatabase();
	return $user;
}

public function findUserByCerticateSerialNumber($certId){
	$this->connectToDatabase();
	$sql    = 'SELECT * FROM utilizador WHERE idCertificado = '.$certId;
	$result = mysql_query($sql, $this->link);
	$this->checkIfValidResult($result);
	$user = mysql_fetch_assoc($result);
	mysql_free_result($result);
	$this->disconnectFromDatabase();
	return $user;
}



public function setMessagePublic($id){
	$this->connectToDatabase();
	$sqlUpdate = 'UPDATE mensagem SET encriptado = 0 WHERE idMensagem = '.$id;
	$result = mysql_query($sqlUpdate, $this->link);
	$this->checkIfValidResult($result);
	mysql_free_result($result);
	$this->disconnectFromDatabase();
}

public function insertMessage($FromUser_id, $ToUser_id, $message, $AES_key){
	$this->connectToDatabase();
	//$AES_key = mysql_real_escape_string(bin2hex(openssl_random_pseudo_bytes(32,$cstrong)));
	/* Encrypt Message */
	//$openSSL = new openSSL;
	//$out = mysql_real_escape_string($openSSL->encryptAES256($message, $AES_key));


	//echo "<br>Encrypted Out ".$out."<br>";
	// encode the encrypted text for transport
	//$out = mysql_real_escape_string(chunk_split( base64_encode( $out ), 64 ));
	//echo "<br>Encrypted Out 2 ".$out."<br>";
	//echo "<br>Decrypted Out ".$openSSL->decryptAES256($out, $AES_key)."<br>";
	/* 
	   FALTA
	   ENCRIPTAR A CHAVE DO AES
	*/
	$sqlInsertMessage = "INSERT INTO mensagem (texto, encriptado, chave, idEmissor, idReceptor) VALUES ('$out', '1', '$AES_key', '$FromUser_id', '$ToUser_id')";
	$result = mysql_query($sqlInsertMessage, $this->link);
	$this->checkIfValidResult($result);
	mysql_free_result($result);
	$this->disconnectFromDatabase();
}

public function insertUserDatabase($user, $password, $serialNumber){
	$this->connectToDatabase();
	$salt = mysql_real_escape_string(bin2hex(openssl_random_pseudo_bytes(32,$cstrong)));
	$hashPassword = mysql_real_escape_string(hash("sha256",$salt.$password));
	$lastMessage_id = "0";//ainda nao leu nenhuma mensagem
	//echo "User ".$user."<br/>";
	//echo "Salt ".$salt."<br/>";
	//echo "HashPassword ".$hashPassword."<br/>";
	//echo "Serial Number ".$serialNumber."<br/>";

	$sqlInsert = "INSERT INTO utilizador (nomeUtilizador,salt,password,idCertificado,ultimaMensagem) VALUES ('$user','$salt', '$hashPassword', '$serialNumber', '$lastMessage_id')";
	$result = mysql_query($sqlInsert, $this->link);
	$this->checkIfValidResult($result);
	mysql_free_result($result);
	$this->disconnectFromDatabase();
}
private function checkIfValidResult($result){
	if (!$result) {
	    echo "DB Error, could not query the database\n";
	    echo 'MySQL Error: ' . mysql_error();
	    exit;
	}
}
}
?>
