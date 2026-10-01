<?php

/**
 * Un livre du catalogue : l'entité principale du projet.
 *
 * Relations :
 *   - auteur_id  : clé étrangère vers auteur  
 *   - editeur_id : clé étrangère vers editeur 
 *   - catégories : table de liaison livre_categorie 
 */
class Livre extends Modele
{
    protected static string $table = 'livre';
    protected static string $ordre = 'titre';

    /**
     * Teintes de cuir des couvertures. 
     */
    private const COULEURS_COUVERTURE = ['#5C0F1A', '#2E3B2A', '#1E1A2B', '#3B1230', '#4A1942', '#7A1E2B', '#3A2A1E'];

    /** Sous ce nombre d'exemplaires, le livre est à réapprovisionner. */
    public const SEUIL_STOCK_FAIBLE = 2;

    private string $titre = '';
    private int $auteurId = 0;
    private ?int $editeurId = null;
    private ?int $anneePublication = null;
    private float $prix = 0.0;
    private int $quantiteStock = 0;
    private ?string $resume = null;

    /** Identifiants des catégories cochées. */
    private array $categorieIds = [];

    /** Objets Categorie liés au livre (chargés au besoin). */
    private array $categories = [];

    /* Informations lues par jointure, seulement pour l'affichage. */
    private ?string $auteurNom = null;
    private ?string $editeurNom = null;
    private ?string $categoriesNoms = null;

    /**
     * Remplit le livre à partir d'une ligne de la base ou d'un formulaire.
     */
    public function hydrater(array $donnees): void
    {
        if (isset($donnees['id'])) {
            $this->id = (int) $donnees['id'];
        }

        $this->titre = trim($donnees['titre'] ?? '');
        $this->auteurId = (int) ($donnees['auteur_id'] ?? 0);
        $this->editeurId = self::entierOuNull($donnees['editeur_id'] ?? null);
        $this->anneePublication = self::entierOuNull($donnees['annee_publication'] ?? null);
        $this->prix = (float) str_replace(',', '.', (string) ($donnees['prix'] ?? 0));
        $this->quantiteStock = (int) ($donnees['quantite_stock'] ?? 0);
        $this->resume = self::texteOuNull($donnees['resume'] ?? null);

        // Cases à cocher du formulaire (absentes si aucune n'est cochée)
        $this->categorieIds = array_map('intval', $donnees['categorie_ids'] ?? []);

        // Colonnes ajoutées par la jointure de rechercher()
        $this->auteurNom = $donnees['auteur_nom'] ?? null;
        $this->editeurNom = $donnees['editeur_nom'] ?? null;
        $this->categoriesNoms = $donnees['categories_noms'] ?? null;
    }

    protected function versTableau(): array
    {
        return [
            'titre'              => $this->titre,
            'auteur_id'          => $this->auteurId,
            'editeur_id'         => $this->editeurId,
            'annee_publication'  => $this->anneePublication,
            'prix'               => $this->prix,
            'quantite_stock'     => $this->quantiteStock,
            'resume'             => $this->resume,
        ];
    }

    public function valider(): array
    {
        $erreurs = [];

        if ($this->titre === '') {
            $erreurs['titre'] = 'Entrez le titre du livre.';
        } elseif (mb_strlen($this->titre) > 150) {
            $erreurs['titre'] = 'Le titre doit contenir 150 caractères ou moins.';
        }

        // La clé étrangère doit pointer vers un auteur qui existe
        if (Auteur::trouverParId($this->auteurId) === null) {
            $erreurs['auteur_id'] = 'Choisissez un auteur dans la liste.';
        }

        if ($this->editeurId !== null && Editeur::trouverParId($this->editeurId) === null) {
            $erreurs['editeur_id'] = 'Choisissez un éditeur dans la liste.';
        }

        $anneeMaximale = (int) date('Y') + 1;
        if ($this->anneePublication !== null && ($this->anneePublication < 1450 || $this->anneePublication > $anneeMaximale)) {
            $erreurs['annee_publication'] = "L'année doit être entre 1450 et $anneeMaximale.";
        }

        if ($this->prix < 0 || $this->prix > 9999.99) {
            $erreurs['prix'] = 'Le prix doit être entre 0 et 9 999,99 $.';
        }

        if ($this->quantiteStock < 0) {
            $erreurs['quantite_stock'] = 'La quantité en stock ne peut pas être négative.';
        }

        return $erreurs;
    }

    /* ------------------------------------------------------------
     *  Enregistrement avec les catégories 
     * ------------------------------------------------------------ */

    /**
     * Redéfinit la méthode de Modele : on enregistre le livre, puis ses catégories. La transaction garantit que les deux réussissent ensemble.
     */
    public function sauvegarder(): void
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();

        try {
            parent::sauvegarder();
            $this->synchroniserCategories();
            $pdo->commit();
        } catch (PDOException $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    /**
     * Remplace les liens du livre dans la table livre_categorie par les catégories actuellement cochées.
     */
    private function synchroniserCategories(): void
    {
        $pdo = self::pdo();

        $suppression = $pdo->prepare('DELETE FROM livre_categorie WHERE livre_id = :livre_id');
        $suppression->execute(['livre_id' => $this->id]);

        $ajout = $pdo->prepare('INSERT INTO livre_categorie (livre_id, categorie_id) VALUES (:livre_id, :categorie_id)');
        foreach (array_unique($this->categorieIds) as $categorieId) {
            $ajout->execute(['livre_id' => $this->id, 'categorie_id' => $categorieId]);
        }
    }

    /* ------------------------------------------------------------
     *  Lecture
     * ------------------------------------------------------------ */

    /**
     * Redéfinit la méthode de Modele pour charger aussi les catégories du livre.
     */
    public static function trouverParId(int $id): ?static
    {
        $livre = parent::trouverParId($id);
        $livre?->chargerCategories();

        return $livre;
    }

    /**
     * Charge les objets Categorie liés au livre par la table de liaison.
     */
    private function chargerCategories(): void
    {
        $requete = self::pdo()->prepare(
            'SELECT c.*
             FROM categorie c
             JOIN livre_categorie lc ON lc.categorie_id = c.id
             WHERE lc.livre_id = :livre_id
             ORDER BY c.nom'
        );
        $requete->execute(['livre_id' => $this->id]);

        $this->categories = array_map(fn (array $ligne) => new Categorie($ligne), $requete->fetchAll());
        $this->categorieIds = array_map(fn (Categorie $categorie) => $categorie->getId(), $this->categories);
    }

    /**
     * Recherche des livres avec le nom de l'auteur, de l'éditeur et des
     * catégories, en une seule requête (jointures sur les clés étrangères).
     *
     * Filtres acceptés : texte, auteur_id, categorie_id, stock_max.
     */
    public static function rechercher(array $filtres = [], string $tri = 'l.titre', ?int $limite = null): array
    {
        $conditions = [];
        $parametres = [];

        if (!empty($filtres['texte'])) {
            $conditions[] = '(l.titre LIKE :texte_titre OR CONCAT(a.prenom, \' \', a.nom) LIKE :texte_auteur)';
            $parametres['texte_titre'] = '%' . $filtres['texte'] . '%';
            $parametres['texte_auteur'] = '%' . $filtres['texte'] . '%';
        }

        if (!empty($filtres['auteur_id'])) {
            $conditions[] = 'l.auteur_id = :auteur_id';
            $parametres['auteur_id'] = (int) $filtres['auteur_id'];
        }

        if (!empty($filtres['categorie_id'])) {
            $conditions[] = 'l.id IN (SELECT livre_id FROM livre_categorie WHERE categorie_id = :categorie_id)';
            $parametres['categorie_id'] = (int) $filtres['categorie_id'];
        }

        if (isset($filtres['stock_max'])) {
            $conditions[] = 'l.quantite_stock <= :stock_max';
            $parametres['stock_max'] = (int) $filtres['stock_max'];
        }

        // Seuls ces tris sont permis, pour ne jamais insérer de texte externe dans le SQL
        $trisPermis = ['l.titre', 'l.id DESC', 'l.quantite_stock'];
        if (!in_array($tri, $trisPermis, true)) {
            $tri = 'l.titre';
        }

        $sql = 'SELECT l.*,
                       CONCAT(a.prenom, \' \', a.nom) AS auteur_nom,
                       e.nom AS editeur_nom,
                       GROUP_CONCAT(c.nom ORDER BY c.nom SEPARATOR \', \') AS categories_noms
                FROM livre l
                JOIN auteur a ON a.id = l.auteur_id
                LEFT JOIN editeur e ON e.id = l.editeur_id
                LEFT JOIN livre_categorie lc ON lc.livre_id = l.id
                LEFT JOIN categorie c ON c.id = lc.categorie_id';

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' GROUP BY l.id ORDER BY ' . $tri;

        if ($limite !== null) {
            $sql .= ' LIMIT ' . (int) $limite;
        }

        $requete = self::pdo()->prepare($sql);
        $requete->execute($parametres);

        return array_map(fn (array $ligne) => new Livre($ligne), $requete->fetchAll());
    }

    /** Les derniers livres ajoutés (page d'accueil). */
    public static function trouverRecents(int $nombre = 6): array
    {
        return self::rechercher([], 'l.id DESC', $nombre);
    }

    /** Les livres dont le stock est faible (page d'accueil). */
    public static function trouverStockFaible(): array
    {
        return self::rechercher(['stock_max' => self::SEUIL_STOCK_FAIBLE], 'l.quantite_stock');
    }

    /** Valeur totale de l'inventaire : somme de prix × quantité. */
    public static function valeurInventaire(): float
    {
        return (float) self::pdo()->query('SELECT COALESCE(SUM(prix * quantite_stock), 0) FROM livre')->fetchColumn();
    }

    /* ------------------------------------------------------------
     *  Objets liés
     * ------------------------------------------------------------ */

    public function getAuteur(): ?Auteur
    {
        return Auteur::trouverParId($this->auteurId);
    }

    public function getEditeur(): ?Editeur
    {
        return $this->editeurId === null ? null : Editeur::trouverParId($this->editeurId);
    }

    /* ------------------------------------------------------------
     *  Couverture
     * ------------------------------------------------------------ */

    /**
     * Teinte de la couverture, choisie dans la palette selon l'identifiant
     * (un nouveau livre, sans identifiant, prend la première teinte).
     */
    public function getCouleurCouverture(): string
    {
        $position = ($this->id ?? 0) % count(self::COULEURS_COUVERTURE);

        return self::COULEURS_COUVERTURE[$position];
    }

    /* ------------------------------------------------------------
     *  Accesseurs
     * ------------------------------------------------------------ */

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function getAuteurId(): int
    {
        return $this->auteurId;
    }

    public function getEditeurId(): ?int
    {
        return $this->editeurId;
    }

    public function getAnneePublication(): ?int
    {
        return $this->anneePublication;
    }

    public function getPrix(): float
    {
        return $this->prix;
    }

    public function getQuantiteStock(): int
    {
        return $this->quantiteStock;
    }

    public function getResume(): ?string
    {
        return $this->resume;
    }

    public function getCategorieIds(): array
    {
        return $this->categorieIds;
    }

    /** @return Categorie[] */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * Nom de l'auteur : déjà connu si le livre vient de rechercher().
     */
    public function getAuteurNom(): ?string
    {
        return $this->auteurNom ??= $this->getAuteur()?->getNomComplet();
    }

    public function getEditeurNom(): ?string
    {
        return $this->editeurNom ??= $this->getEditeur()?->getNom();
    }

    public function getCategoriesNoms(): ?string
    {
        return $this->categoriesNoms;
    }

    public function estEnStockFaible(): bool
    {
        return $this->quantiteStock <= self::SEUIL_STOCK_FAIBLE;
    }

    public function __toString(): string
    {
        return $this->titre;
    }
}
