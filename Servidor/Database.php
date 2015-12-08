<?php
   include ("openSSL.php");
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

public function getMessages(){
	$openSSL = new openSSL;
	$this->connectToDatabase();
	$sql    = 'SELECT * FROM mensagem';
	$result = mysql_query($sql, $this->link);
	$this->checkIfValidResult($result);
	$i=1;
	while ($row = mysql_fetch_assoc($result)) {
		echo '<div class="panel panel-default">';
		echo '<div class="panel-heading id="panel-heading'.$i.'">';
	$mensagem = $row['texto'];
		if ($row['encriptado'] > 0){//se maior que 0 ainda nao desincriptou a mensagem
			echo '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
			<a class="pull-right" id="pull-right'.$i.'">Decrypt</a>
			<script type="text/javascript">
				$("a#pull-right'.$i.'").click(function(){ 
					var div = document.getElementById("message'.$i.'"); 
					div.innerHTML = "'.$openSSL->decryptAES256($row['texto'], $row['chave']).'";
					var node = document.getElementById("panel-heading2'.$i.'"); 
					$("a#pull-right'.$i.'").toggle();

				})
				$("a#pull-right'.$i.'").click(function (e) {
				      e.stopPropagation();
				      $("a#makePublic'.$i.'").toggle();
				  });
			
			</script>';

			if (isset($_GET['public'])) {
				$this->setMessagePublic($_GET['row']);
			}
			echo '<a id="makePublic'.$i.'" class="pull-right" href="index.php?public=true&row='.$i.'" style="display:none;">Share</a>';
			
		}else{
			$mensagem = $openSSL->decryptAES256($row['texto'], $row['chave']);
		}
		$FromUser = $this->findUserById($row['idEmissor']);
		$ToUser_id = $this->findUserById($row['idReceptor']);
		echo '<h6>'.$FromUser['nomeUtilizador'].' > '.$ToUser_id['nomeUtilizador'].'</h6></div>';
		echo '<div class="panel-body id="panel-body'.$i.'">';
		/* 
			FALTA
			FICOU DEFINIDO QUE TODAS AS MENSAGENS FICAM ENCRIPTADAS NA BD.
			LOGO E PRECISO SABER SE O UTILIZADOR QUER QUE A MENSAGEM APARECA
			EM TEXTO LIMPO PARA TODOS E SE SIM DESINCRIPTAR A MENSAGEM
			CASO CONTRARIO BASTA FAZER O OUTPUT DO TEXTO OBTIDO DA BD
		*/
		echo  '<h6 id="message'.$i.'">'.$mensagem.'</h6>';
		/*<div class="clearfix"></div><hr>Design, build, test, and prototype using Bootstrap in real-time from your Web browser. Bootply combines the power of hand-coded HTML, CSS and JavaScript with the benefits of responsive design using Bootstrap. Find and showcase Bootstrap-ready snippets in the 100% free Bootply.com code repository.*/
		echo '</div></div>';
		$i++;
	}
	mysql_free_result($result);
	$this->disconnectFromDatabase();
}

private function setMessagePublic($id){
	$this->connectToDatabase();
	$sqlUpdate = 'UPDATE mensagem SET encriptado = 0 WHERE idMensagem = '.$id;
	echo "Publica ".$id;
	$result = mysql_query($sqlUpdate, $this->link);
	$this->checkIfValidResult($result);
	mysql_free_result($result);
	$this->disconnectFromDatabase();
}

public function insertMessage($FromUser_id, $ToUser_id, $message){
	$this->connectToDatabase();
	$AES_key = mysql_real_escape_string(bin2hex(openssl_random_pseudo_bytes(32,$cstrong)));
	/* Encrypt Message */
	$openSSL = new openSSL;
	$out = mysql_real_escape_string($openSSL->encryptAES256($message, $AES_key));


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

public function insertUserDatabase($user, $password){
	$this->connectToDatabase();
	$serialNumber = mysql_real_escape_string(bin2hex(openssl_random_pseudo_bytes(8,$cstrong)));
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
