<?php

/**
 * Fichier inclus au début de chaque page.
 */

session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/fonctions.php';

/**
 * Chargement automatique : quand une classe est utilisée.
 */
spl_autoload_register(function (string $nomClasse): void {
    $fichier = __DIR__ . '/../Classe/' . $nomClasse . '.php';

    if (is_file($fichier)) {
        require_once $fichier;
    }
});

/**
 * Toute exception non attrapée affiche une page d'erreur au lieu d'une page blanche.
 */
set_exception_handler(function (Throwable $exception): void {
    error_log($exception->getMessage());
    http_response_code(500);

    $titrePage = 'Erreur';
    $pageActive = '';
    $detailErreur = MODE_DEBUG ? $exception->getMessage() : null;

    require __DIR__ . '/erreur.php';
});
