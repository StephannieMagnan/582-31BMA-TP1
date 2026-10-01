<?php

/**
 * Un auteur de la librairie.
 * Relation un-à-plusieurs : un auteur peut avoir écrit plusieurs livres.
 */
class Auteur extends Modele
{
    protected static string $table = 'auteur';
    protected static string $ordre = 'nom, prenom';

    private string $prenom = '';
    private string $nom = '';
    private ?int $anneeNaissance = null;
    private ?string $biographie = null;

    /**
     * Remplir l'auteur à partir d'une ligne de la base ou d'un formulaire.
     */
    public function hydrater(array $donnees): void
    {
        if (isset($donnees['id'])) {
            $this->id = (int) $donnees['id'];
        }

        $this->prenom = trim($donnees['prenom'] ?? '');
        $this->nom = trim($donnees['nom'] ?? '');
        $this->anneeNaissance = self::entierOuNull($donnees['annee_naissance'] ?? null);
        $this->biographie = self::texteOuNull($donnees['biographie'] ?? null);
    }

    protected function versTableau(): array
    {
        return [
            'prenom'          => $this->prenom,
            'nom'             => $this->nom,
            'annee_naissance' => $this->anneeNaissance,
            'biographie'      => $this->biographie,
        ];
    }

    public function valider(): array
    {
        $erreurs = [];

        if ($this->prenom === '') {
            $erreurs['prenom'] = 'Entrez le prénom de l\'auteur.';
        } elseif (mb_strlen($this->prenom) > 80) {
            $erreurs['prenom'] = 'Le prénom doit contenir 80 caractères ou moins.';
        }

        if ($this->nom === '') {
            $erreurs['nom'] = 'Entrez le nom de l\'auteur.';
        } elseif (mb_strlen($this->nom) > 80) {
            $erreurs['nom'] = 'Le nom doit contenir 80 caractères ou moins.';
        }

        $anneeCourante = (int) date('Y');
        if ($this->anneeNaissance !== null && ($this->anneeNaissance < 1000 || $this->anneeNaissance > $anneeCourante)) {
            $erreurs['annee_naissance'] = "L'année de naissance doit être entre 1000 et $anneeCourante.";
        }

        return $erreurs;
    }

    /* ------------------------------------------------------------
     *  Relation avec les livres
     * ------------------------------------------------------------ */

    /**
     * Nombre de livres écrits par cet auteur.
     */
    public function compterLivres(): int
    {
        $requete = self::pdo()->prepare('SELECT COUNT(*) FROM livre WHERE auteur_id = :id');
        $requete->execute(['id' => $this->id]);

        return (int) $requete->fetchColumn();
    }

    /**
     * Un auteur ne peut pas être supprimé tant que des livres lui sont liés
     * (la clé étrangère livre.auteur_id est en ON DELETE RESTRICT).
     */
    public function peutEtreSupprime(): bool
    {
        return $this->compterLivres() === 0;
    }

    /* ------------------------------------------------------------
     *  Accesseurs
     * ------------------------------------------------------------ */

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getNomComplet(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function getAnneeNaissance(): ?int
    {
        return $this->anneeNaissance;
    }

    public function getBiographie(): ?string
    {
        return $this->biographie;
    }

    public function __toString(): string
    {
        return $this->getNomComplet();
    }
}
