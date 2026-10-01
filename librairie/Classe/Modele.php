<?php

/**
 * Classe parente abstraite de toutes les entités de la librairie.
 *
 * Elle regroupe les opérations CRUD communes (lire, ajouter, modifier,
 * supprimer). Chaque classe enfant (Livre, Auteur, Editeur, Categorie)
 * hérite de ces méthodes et indique seulement :
 *   - le nom de sa table ($table);
 *   - comment remplir ses propriétés (hydrater);
 *   - quelles colonnes enregistrer (versTableau);
 *   - comment valider ses données (valider).
 */
abstract class Modele
{
    /** Nom de la table SQL. */
    protected static string $table = '';

    /** Colonne utilisée pour trier les listes. */
    protected static string $ordre = 'id';

    /** Identifiant de l'enregistrement. */
    protected ?int $id = null;

    /**
     * Crée l'objet et le remplit si on lui donne des données.
     */
    public function __construct(array $donnees = [])
    {
        if (!empty($donnees)) {
            $this->hydrater($donnees);
        }
    }

    /* ------------------------------------------------------------
     *  Méthodes que chaque classe enfant doit définir
     * ------------------------------------------------------------ */

    /** Remplit les propriétés de l'objet à partir d'un tableau. */
    abstract public function hydrater(array $donnees): void;

    /** Retourne les colonnes à enregistrer. */
    abstract protected function versTableau(): array;

    /** Retourne les erreurs de validation. */
    abstract public function valider(): array;

    /* ------------------------------------------------------------
     *  Accès à la base de données
     * ------------------------------------------------------------ */

    protected static function pdo(): PDO
    {
        return Database::getConnexion();
    }

    /* ------------------------------------------------------------
     *  Lecture
     * ------------------------------------------------------------ */

    /**
     * Retourne tous les enregistrements de la table, sous forme d'objets.
     */
    public static function trouverTous(): array
    {
        $sql = 'SELECT * FROM ' . static::$table . ' ORDER BY ' . static::$ordre;
        $lignes = static::pdo()->query($sql)->fetchAll();

        return array_map(fn (array $ligne) => new static($ligne), $lignes);
    }

    /**
     * Retourne l'enregistrement correspondant à l'identifiant, ou null.
     */
    public static function trouverParId(int $id): ?static
    {
        $requete = static::pdo()->prepare('SELECT * FROM ' . static::$table . ' WHERE id = :id');
        $requete->execute(['id' => $id]);
        $ligne = $requete->fetch();

        return $ligne ? new static($ligne) : null;
    }

    /**
     * Retourne le nombre total d'enregistrements dans la table.
     */
    public static function compter(): int
    {
        return (int) static::pdo()->query('SELECT COUNT(*) FROM ' . static::$table)->fetchColumn();
    }

    /* ------------------------------------------------------------
     *  Écriture
     * ------------------------------------------------------------ */

    /**
     * Ajoute l'objet s'il est nouveau, sinon met à jour sa ligne.
     */
    public function sauvegarder(): void
    {
        if ($this->estNouveau()) {
            $this->inserer();
        } else {
            $this->mettreAJour();
        }
    }

    /**
     * Supprime l'enregistrement de la base de données.
     */
    public function supprimer(): void
    {
        $requete = static::pdo()->prepare('DELETE FROM ' . static::$table . ' WHERE id = :id');
        $requete->execute(['id' => $this->id]);
        $this->id = null;
    }

    /**
     * INSERT INTO table
     */
    protected function inserer(): void
    {
        $donnees = $this->versTableau();
        $colonnes = array_keys($donnees);
        $marqueurs = array_map(fn (string $colonne) => ':' . $colonne, $colonnes);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            static::$table,
            implode(', ', $colonnes),
            implode(', ', $marqueurs)
        );

        static::pdo()->prepare($sql)->execute($donnees);
        $this->id = (int) static::pdo()->lastInsertId();
    }

    /**
     * UPDATE table
     */
    protected function mettreAJour(): void
    {
        $donnees = $this->versTableau();
        $affectations = array_map(fn (string $colonne) => $colonne . ' = :' . $colonne, array_keys($donnees));

        $sql = sprintf(
            'UPDATE %s SET %s WHERE id = :id',
            static::$table,
            implode(', ', $affectations)
        );

        $donnees['id'] = $this->id;
        static::pdo()->prepare($sql)->execute($donnees);
    }

    /* ------------------------------------------------------------
     *  Accesseurs communs
     * ------------------------------------------------------------ */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function estNouveau(): bool
    {
        return $this->id === null;
    }

    /* ------------------------------------------------------------
     *  Outils pour les classes enfants
     * ------------------------------------------------------------ */

    /**
     * Convertit une valeur de formulaire en entier.
     */
    protected static function entierOuNull(mixed $valeur): ?int
    {
        return ($valeur === null || $valeur === '') ? null : (int) $valeur;
    }

    /**
     * Convertit une valeur de formulaire en texte.
     */
    protected static function texteOuNull(mixed $valeur): ?string
    {
        $texte = trim((string) $valeur);

        return $texte === '' ? null : $texte;
    }
}
