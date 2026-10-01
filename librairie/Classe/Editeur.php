<?php

/**
 * Une maison d'édition.
 * Relation un-à-plusieurs : un éditeur peut publier plusieurs livres.
 */
class Editeur extends Modele
{
    protected static string $table = 'editeur';
    protected static string $ordre = 'nom';

    private string $nom = '';
    private ?string $ville = null;
    private ?string $siteWeb = null;

    public function hydrater(array $donnees): void
    {
        if (isset($donnees['id'])) {
            $this->id = (int) $donnees['id'];
        }

        $this->nom = trim($donnees['nom'] ?? '');
        $this->ville = self::texteOuNull($donnees['ville'] ?? null);
        $this->siteWeb = self::texteOuNull($donnees['site_web'] ?? null);
    }

    protected function versTableau(): array
    {
        return [
            'nom'      => $this->nom,
            'ville'    => $this->ville,
            'site_web' => $this->siteWeb,
        ];
    }

    public function valider(): array
    {
        $erreurs = [];

        if ($this->nom === '') {
            $erreurs['nom'] = 'Entrez le nom de l\'éditeur.';
        } elseif (mb_strlen($this->nom) > 100) {
            $erreurs['nom'] = 'Le nom doit contenir 100 caractères ou moins.';
        }

        if ($this->ville !== null && mb_strlen($this->ville) > 80) {
            $erreurs['ville'] = 'La ville doit contenir 80 caractères ou moins.';
        }

        if ($this->siteWeb !== null && !filter_var($this->siteWeb, FILTER_VALIDATE_URL)) {
            $erreurs['site_web'] = 'Entrez une adresse complète, par exemple https://www.exemple.com.';
        }

        return $erreurs;
    }

    /**
     * Nombre de livres publiés par cet éditeur.
     */
    public function compterLivres(): int
    {
        $requete = self::pdo()->prepare('SELECT COUNT(*) FROM livre WHERE editeur_id = :id');
        $requete->execute(['id' => $this->id]);

        return (int) $requete->fetchColumn();
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function getSiteWeb(): ?string
    {
        return $this->siteWeb;
    }

    public function __toString(): string
    {
        return $this->nom;
    }
}
