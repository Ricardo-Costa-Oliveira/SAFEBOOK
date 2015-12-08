<?php
// DEFINE our cipher
	define('AES_256_CBC', 'aes-256-cbc');
class openSSL {
private $certificate;
private $privatekey;
private $dn = array();
private $x509 = array();
private $sigheader = "\n-----BEGIN openSSL.php SIGNATURE-----\n";
private $sigfooter = "-----END openSSL.php SIGNATURE-----\n";
// constructor
public function __construct() {
// no constructor is needed here
}
// make new keys and load them into $this->certificate and $this->privatekey
// certificate will be self-signed
public function makeKeys ( $distinguishedName, $passphrase = NULL ) {
	// keep track of the distinguished name
	$this->dn = $distinguishedName;
	// generate the pem-encoded private key
	$config = array( 'digest_alg'=>'sha1',
	'private_key_bits'=>1024,
	'encrypt_key'=>TRUE,
	);
	$key = openssl_pkey_new( $config );
	// generate the certificate signing request...
	$csr = openssl_csr_new( $this->dn, $key, $config );
	// and use it to make a self-signed certificate
	$cert = openssl_csr_sign( $csr, NULL, $key, 365, $config, time() );
	// export private and public keys
	openssl_pkey_export( $key, $this->privatekey, $passphrase, $config );
	openssl_x509_export( $cert, $this->certificate );
	// parse certificate
	$this->x509 = openssl_x509_parse( $cert );
	return TRUE;
	// end of makeKeys() method
}


// gets (or sets) $this->privatekey
public function privateKey() {
	$out = $this->privatekey;
	if ( func_num_args() > 0 && func_get_arg(0) ) {
	$this->privatekey = func_get_arg(0);
	}
	return $out;
	// end of privateKey() method
}

// gets (or sets) $this->certificate (the public key)
public function certificate() {
	$out = $this->certificate;
	if ( func_num_args() > 0 && func_get_arg(0) ) {
		$this->certificate = func_get_arg(0);
		// create openssl certificate resource
		$cert = openssl_get_publickey( $this->certificate );
		// parse certificate
		$this->x509 = openssl_x509_parse( $cert );
		// free the cert resource
		openssl_free_key( $cert );
	}
	return $out;
// end of certificate() method
}

// uses this->certificate to encrypt using rsa
// input is limited to 56 chars (448 bits)
public function encrypt ( $string ) {
	if ( empty( $this->certificate ) ) {
		exit( 'Cannot encrypt, no active certificate.' );

	}
	if ( strlen( $string ) > 56 ) {
		exit( 'Cannot encrypt, input too long.' );
	}
	// create openssl certificate resource
	$cert = openssl_get_publickey( $this->certificate );
	// encrypt
	openssl_public_encrypt ( $string, $out, $cert );
	// free the cert resource
	openssl_free_key( $cert );
	// encode the encrypted text for transport
	$out = chunk_split( base64_encode( $out ), 64 );
	return $out;
	// end of encrypt() method
}


// uses $this->privatekey to decrypt using RSA
public function decrypt ( $string, $passphrase = NULL ) {
	if ( empty( $this->privatekey ) ) {
		exit( 'Cannot decrypt, no active private key.' );
	}
	// decodes encrypted text from transport
	$string = base64_decode( $string );
	// create openssl pkey resource
	$key = openssl_get_privatekey( $this->privatekey, $passphrase );
	// decrypt
	openssl_private_decrypt( $string, $out, $key );
	// make openssl forget the key
	openssl_free_key( $key );
	return $out;
	// end of decrypt() method
}


// uses private key to sign a string
public function sign ( $string, $passphrase = NULL ) {
	if ( empty( $this->privatekey ) ) {
		exit( 'Cannot decrypt, no active private key.' );
	}
	// create openssl pkey resource
	$key = openssl_get_privatekey( $this->privatekey, $passphrase );
	// find the signature
	$signature = NULL;
	openssl_sign( $string, $signature, $key );
	// make openssl forget the key
	openssl_free_key( $key );
	// base64 encode signature for easy transport
	$signature = chunk_split( base64_encode( $signature ), 64 );
	// finish signing string
	$signedString = $string . $this->sigheader . $signature . $this->sigfooter;
	// return signed string
	return $signedString;
	// end of sign() method
}

// uses key to verify a signature using this->certificate
public function verify ( $signedString ) {
	if ( empty( $this->privatekey ) ) {
		exit( 'Cannot verify, no active certificate.' );
	}
	// split the signature from the string
	$sigpos = strpos( $signedString, $this->sigheader );
	if ( $sigpos === FALSE ) {
	// failed, no signature!
		return FALSE;
	}
	$signature = substr( $signedString, ( $sigpos +
	strlen( $this->sigheader ) ), ( 0 - strlen( $this->sigfooter ) ) );
	$string = substr( $signedString, 0, $sigpos );
	// base64 decode the signature...
	$signature = base64_decode( $signature );
	// create openssl certificate resource
	$cert = openssl_get_publickey( $this->certificate );
	// verify the signature
	$success = openssl_verify( $string, $signature, $cert );
	// free the key resource
	openssl_free_key( $cert );
	// pass or fail
	if ( $success ) {
		return $string;
	}
	return FALSE;
	// end of verify() method
}


// find common name of entity represented by this->certificate
public function getCommonName() {
	if ( isset( $this->x509['subject']['CN'] ) ) {
	return $this->x509['subject']['CN'];

	}
	return NULL;
	// end of getCommonName() method
}


// get all details of the entity represented by this->certificate
// aka, the Distinguished Name
public function getDN() {
	if ( isset( $this->x509['subject'] ) ) {
		return $this->x509['subject'];
	}
	return NULL;
	// end of getDN() method
}


// find common name of the issuer of this->certificate
public function getCACommonName() {
	if ( isset( $this->x509['issuer']['CN'] ) ) {
		return $this->x509['issuer']['CN'];
	}
	return NULL;
	// end of getCACommonName() method
}
// get all details of the issuer of this->certificate
// aka, the Certificate Authority
public function getCA() {
	if ( isset( $this->x509['issuer'] ) ) {
		return $this->x509['issuer'];
	}
	return NULL;
	// end of getCA() method
}



public function encryptAES256($data, $encryption_key){
	
	// Generate an initialization vector
	// This *MUST* be available for decryption as well
	$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(AES_256_CBC));

	// Encrypt $data using aes-256-cbc cipher with the given encryption key and 
	// our initialization vector. The 0 gives us the default options, but can
	// be changed to OPENSSL_RAW_DATA or OPENSSL_ZERO_PADDING
	$encrypted = openssl_encrypt($data, AES_256_CBC, $encryption_key, 0, $iv);

	// If we lose the $iv variable, we can't decrypt this, so append it to the 
	// encrypted data with a separator that we know won't exist in base64-encoded 
	// data
	$encrypted = $encrypted . ':' . $iv;
	//echo "Encrypted: $encrypted\n";
	return $encrypted;
}
public function decryptAES256($encrypted, $encryption_key){
	// To decrypt, separate the encrypted data from the initialization vector ($iv)
	$parts = explode(':', $encrypted);
	// $parts[0] = encrypted data
	// $parts[1] = initialization vector
	$decrypted = openssl_decrypt($parts[0], AES_256_CBC, $encryption_key, 0, $parts[1]);
	//echo "Decrypted: $decrypted\n";
	return $decrypted;
}

// end of openSSL class
}
?>
