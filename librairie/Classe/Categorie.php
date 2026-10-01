<?php

/**
 * Une catégorie de livres (roman, théâtre, etc.).
 * Relation plusieurs-à-plusieurs avec Livre, par la table livre_categorie.
 */
class Categorie extends Modele
{
    protected static string $table = 'categorie';
    protected static string $ordre = 'nom';

    private string $nom = '';
    private ?string $description = null;

    public function hydrater(array $donnees): void
    {
        if (isset($donnees['id'])) {
            $this->id = (int) $donnees['id'];
        }

        $this->nom = trim($donnees['nom'] ?? '');
        $this->description = self::texteOuNull($donnees['description'] ?? null);
    }

    protected function versTableau(): array
    {
        return [
            'nom'         => $this->nom,
            'description' => $this->description,
        ];
    }

    public function valider(): array
    {
        $erreurs = [];

        if ($this->nom === '') {
            $erreurs['nom'] = 'Entrez le nom de la catégorie.';
        } elseif (mb_strlen($this->nom) > 60) {
            $erreurs['nom'] = 'Le nom doit contenir 60 caractères ou moins.';
        } elseif ($this->nomExisteDeja()) {
            $erreurs['nom'] = 'Une catégorie porte déjà ce nom.';
        }

        if ($this->description !== null && mb_strlen($this->description) > 255) {
            $erreurs['description'] = 'La description doit contenir 255 caractères ou moins.';
        }

        return $erreurs;
    }

    /**
     * Vérifie qu'aucune autre catégorie ne porte déjà ce nom
     * (la colonne nom est UNIQUE dans la base).
     */
    private function nomExisteDeja(): bool
    {
        $requete = self::pdo()->prepare('SELECT COUNT(*) FROM categorie WHERE nom = :nom AND id <> :id');
        $requete->execute(['nom' => $this->nom, 'id' => $this->id ?? 0]);

        return (int) $requete->fetchColumn() > 0;
    }

    /**
     * Nombre de livres classés dans cette catégorie.
     */
    public function compterLivres(): int
    {
        $requete = self::pdo()->prepare('SELECT COUNT(*) FROM livre_categorie WHERE categorie_id = :id');
        $requete->execute(['id' => $this->id]);

        return (int) $requete->fetchColumn();
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function __toString(): string
    {
        return $this->nom;
    }
}
