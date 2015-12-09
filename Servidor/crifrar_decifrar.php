<?php
/*
Cifrar/Decifrar Mensagem
                       Alice                                      |                     Bob
(FEITO) cria a mensagem e escolhe o recetor                       |
(FEITO) pede o certificado do Bob                                 |
(FEITO) extrai a chave publica do Bob                             |
(FEITO) cria chave AES                                            |
(FEITO) encripta a mensagem com a chave AES                       |
(FEITO) encripta a chave AES com a chave publica do Bob           |
(FEITO) assina a mensagem                                         |
(FEITO) notifica o bob                                            |
                                                                  |          recebe a notificacao
                                                                  |          verifica se a assinatura da mensagem coincide com a do certificado da Alice 
                                                                  |          desincripta a chave AES com chave privada
                                                                  |          usa a chave AES para desincriptar a mensagem
                                                                  |          assina a mensagem
                                                                  |          opta por tornar a mensagem publica
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


//CRIACAO DO USER BOB
// chave de encriptacao da chave privada
	$passphrase_Bob = 'PASSWORD USADA PARA ENCRIPTAR A CHAVE PRIVADA DO UTILIZADOR';
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

echo "terminou bob <br>";
//CRIACAO DO USER ALICE
// chave de encriptacao da chave privada
	$passphrase_Alice = 'PASSWORD USADA PARA ENCRIPTAR A CHAVE PRIVADA DO UTILIZADOR';
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
echo "terminou alice <br>";



//extrai a chave publica do Bob 
	$public = $openSSL_Bob->certificate();
echo "//extrai a chave publica do Bob <br>";
//cria chave AES
	$AES_key = bin2hex(openssl_random_pseudo_bytes(12,$cstrong));
echo "//cria chave AES";
//encripta a mensagem com a chave AES
	$encrypted_message = $openSSL_Bob->encryptAES256($message, $AES_key);
echo "//encripta a mensagem com a chave AES <br>";
//encripta a chave AES com a chave publica do Bob
	$encrypted_AES_key = $openSSL_Bob->encrypt ("encryption_AES_key");
echo "//encripta a chave AES com a chave publica do Bob <br>";
//assina a mensagem com chave privada da Alice
	$mensagem_assinada = $openSSL_Alice->sign ( $encrypted_message, $passphrase_Alice);
echo "//assina a mensagem com chave privada da Alice <br>";
//notifica o bob 
	$Bob = $database->findUserByCerticateSerialNumber($openSSL_Bob->getCertificateSerialNumber());
 	$database->insertMessage($FromUser_id, $Bob['idUtilizador'], $encrypted_message, $encrypted_AES_key);
echo "//notifica o bob <br>";
echo "IMPECAVEL";






?>
