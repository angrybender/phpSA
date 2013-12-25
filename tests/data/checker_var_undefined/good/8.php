<?php
/**
 *
 * @author k.vagin
 */
function build_signature($request, $consumer, $token) {
	$base_string = $request->get_signature_base_string();
	$request->base_string = $base_string;

	// Fetch the private key cert based on the request
	$cert = $this->fetch_private_cert($request);

	// Pull the private key ID from the certificate
	$privatekeyid = openssl_get_privatekey($cert);

	// Sign using the key
	$ok = openssl_sign($base_string, $signature, $privatekeyid);

	// Release the key resource
	openssl_free_key($privatekeyid);

	return base64_encode($signature);
}