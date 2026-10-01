<?php

/**
 * Gère la connexion unique à la base de données MySQL avec PDO.
 */
class Database
{
    /** Connexion partagée par tout le projet (null tant qu'elle n'est pas créée). */
    private static ?PDO $connexion = null;

    /**
     * Le constructeur est privé.
     */
    private function __construct()
    {
    }

    /**
     * Retourne la connexion PDO, en la créant au premier appel.
     */
    public static function getConnexion(): PDO
    {
        if (self::$connexion === null) {
            $dsn = 'mysql:host=' . DB_HOTE . ';dbname=' . DB_NOM . ';charset=utf8mb4';

            self::$connexion = new PDO($dsn, DB_UTILISATEUR, DB_MOT_DE_PASSE, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // erreurs SQL = exceptions
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // résultats en tableaux associatifs
                PDO::ATTR_EMULATE_PREPARES   => false,                  // vraies requêtes préparées
            ]);
        }

        return self::$connexion;
    }
}
