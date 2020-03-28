
.PHONY: generate-self-signed
generate-self-signed:
	openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout ./docker/openid-connect.test.key -out ./docker/openid-connect.test.crt  -subj '/CN=openid-connect.test'
