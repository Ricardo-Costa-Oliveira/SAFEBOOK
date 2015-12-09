<?php
/*
						    Registo de um novo utilizador(Parte 1)
	                USER                                      |                 SERVIDOR
				                                  |
(FEITO) username e pass do user                                   |
(FEITO) cria pedido de certificado                                |
				                                  |(FEITO) emitir certificado ao utilizador
				                                  |(FEITO) inserir dados do user na BD



			                Cifrar Mensagem (Parte 2)   Decifrar Mensagem (Parte 3)
                       Alice                                      |                     Bob
(FEITO) cria a mensagem e escolhe o recetor                       |
(FEITO) pede o certificado do Bob                                 |
(FEITO) extrai a chave publica do Bob                             |
(FEITO) cria chave AES                                            |
(FEITO) encripta a mensagem com a chave AES                       |
(FEITO) encripta a chave AES com a chave publica do Bob           |
(FEITO) assina a mensagem                                         |
(FEITO) notifica o bob                                            |
                                                                  |  (FEITO) recebe a notificacao
                                                                  |  (FEITO) verifica se a assinatura da mensagem coincide com a do certificado da Alice 
                                                                  |  (FEITO) desincripta a chave AES com chave privada
                                                                  |  (FEITO) usa a chave AES para desincriptar a mensagem
                                                                  |          assina a mensagem
                                                                  |  (FEITO) opta por tornar a mensagem publica
                                                                  |
*/
	include ( 'Database.php' );
	include ( 'openSSL.php' );


	$database = new Database();
	$openSSL_Bob = new openSSL;
	$openSSL_Alice = new openSSL;
	$message = "AINDA ESTOU A TESTAR";
	$username_Alice = "ALICE1";
	$password_Alice = "ALICE";
	$username_Bob = "BOB1";
	$password_Bob = "BOB";


//PRIMEIRA PARTE
echo " <h1>PARTE 1 </h1>";
//CRIACAO DO USER BOB
// chave de encriptacao da chave privada
	$passphrase_Bob = 'PASSWORD USADA PARA ENCRIPTAR A CHAVE PRIVADA DO BOB';
//cria chave privada e certificado para o Bob
//"Distinguished Name" e necessario para a chave publica
	$distinguishedName_Bob = array(
	"countryName" => "US",
	"stateOrProvinceName" => "New York",
	"localityName" => "New York City",
	"organizationName" => "example.net",
	"organizationalUnitName" => "Pro PHP Security",
	"commonName" => "pps.safebook.com",
	"emailAddress" => "csnyder@safebook.com"
	);
	$openSSL_Bob->makeKeys( $distinguishedName_Bob, $passphrase_Bob );
//insere o Bob da Base de Dados
	$database->insertUserDatabase($username_Bob, $password_Bob , $openSSL_Bob->getCertificateSerialNumber());
//FIM CRIACAO DO USER BOB

echo "<h4>registou o Bob <h4>";
//CRIACAO DO USER ALICE
// chave de encriptacao da chave privada
	$passphrase_Alice = 'PASSWORD USADA PARA ENCRIPTAR A CHAVE PRIVADA DO ALICE';
//cria chave privada e certificado para o Alice
//"Distinguished Name" e necessario para a chave publica
	$distinguishedName_Alice = array(
	"countryName" => "US",
	"stateOrProvinceName" => "New York",
	"localityName" => "New York City",
	"organizationName" => "example.net",
	"organizationalUnitName" => "Pro PHP Security",
	"commonName" => "pps.safebook.com",
	"emailAddress" => "csnyder@safebook.com"
	);
	$openSSL_Alice->makeKeys( $distinguishedName_Alice, $passphrase_Alice );
//insere a Alice da Base de Dados
	$database->insertUserDatabase($username_Alice, $password_Alice , $openSSL_Alice->getCertificateSerialNumber());
//FIM CRIACAO DO USER ALICE
echo "<h4>registou a Alice </h4>";


echo " <h1>PARTE 2 </h1>";
//extrai a chave publica do Bob 
	$public = $openSSL_Bob->certificate();
echo "<h4>extrai a chave publica do Bob </h4>";
//cria chave AES
	$AES_key = bin2hex(openssl_random_pseudo_bytes(12,$cstrong));
echo '<h4>cria chave AES </h4>"'.$AES_key.'"';
//encripta a mensagem com a chave AES
	$encrypted_message = $openSSL_Bob->encryptAES256($message, $AES_key);
echo '<h4>encripta a mensagem com a chave AES </h4>"'.$encrypted_message.'"';
//encripta a chave AES com a chave publica do Bob
	$encrypted_AES_key = $openSSL_Bob->encrypt ($AES_key);
echo '<h4>encripta a chave AES com a chave publica do Bob </h4>"'.$encrypted_AES_key.'"';
//assina a mensagem com chave privada da Alice
	$mensagem_assinada = $openSSL_Alice->sign ( $encrypted_message, $passphrase_Alice);
echo '<h4>assina a mensagem com chave privada da Alice </h4>"'.$mensagem_assinada.'"';
//notifica o bob 
	$Bob = $database->findUserByCerticateSerialNumber($openSSL_Bob->getCertificateSerialNumber());
 	$database->insertMessage($FromUser_id, "3", $encrypted_message, $encrypted_AES_key);
echo "<h4>notifica o Bob </h4>";
echo "IMPECAVEL";
// FIM PRIMEIRA PARTE








// SEGUNDA PARTE
//recebe a notificacao
echo " <h1> <br>PARTE 3 </h1>";
echo "<h4>recebe a notificacao </h4>";
echo "<h4>Tem ".$database->checkForNotifications("3")." mensagens por ler </h4>";
//verifica se a assinatura da mensagem coincide com a do certificado da Alice 
if($openSSL_Alice->verify ( $mensagem_assinada )!= FALSE){
echo "<h4>verifica se a assinatura da mensagem coincide com a do certificado da Alice </h4>";
	//desincripta a chave AES com chave privada
	$decrypted_AES_key = $openSSL_Bob->decrypt ( $encrypted_AES_key, $passphrase_Bob);
echo '	<h4>//desincripta a chave AES com chave privada</h4>"'.$decrypted_AES_key.'"<br>';
	//usa a chave AES para desincriptar a mensagem
	$decrypted_message = $openSSL_Bob->decryptAES256($encrypted_message, $decrypted_AES_key);
echo '	<h4>usa a chave AES para desincriptar a mensagem</h4> "'.$decrypted_message.'"<br>';
	//assina a mensagem

	//opta por tornar a mensagem publica
	$database->setMessagePublic("5");
echo "	<h4>opta por tornar a mensagem publica </h4><br>";
}
echo "ESPETACULAR DIRIA EU";
?>
