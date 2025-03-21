<?php
$privateKey = openssl_pkey_get_private('file:///var/www/symfony_docker/config/jwt/private.pem', 'fau');
if (!$privateKey) {
    echo "Erreur: Impossible de charger la clé privée\n";
    print_r(openssl_error_string());
} else {
    echo "Succès: Clé privée chargée correctement\n";
}
?>
