# TODO - Refactoring et Améliorations

Ce document liste les tâches à réaliser pour améliorer la branche en cours en suivant les conventions et bonnes pratiques de Laravel.

## ☐ Phase 1 : Structure de la base de données et Modèles

### ☐ 1. Migration `create_employes_table.php`
- [ ] Renommer et corriger les colonnes pour utiliser les clés étrangères et conventions Laravel.
    ```php
    // ❌ Code actuel à corriger :
    // $table->unsignedBigInteger('idEntreprise');
    // $table->unsignedBigInteger('created_userid')->nullable();
    // $table->date('created_date')->nullable();

    // ✅ Nouveau code attendu :
    // $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
    // $table->foreignId('creator_id')->nullable()->constrained('users')->onDelete('set null');
    // (Supprimer created_date car redondant avec timestamps)
    ```

### ☐ 2. Migration `create_stages_table.php`
- [ ] Renommer les colonnes de dates et clés étrangères.
    ```php
    // ❌ Code actuel à corriger :
    // $table->date('dateDebut');
    // $table->date('dateFin');
    // $table->unsignedBigInteger('idEntreprise');
    // $table->unsignedBigInteger('idMaitreDeStage');
    // $table->unsignedBigInteger('idEtudiant');
    // $table->unsignedBigInteger('idProfesseur')->nullable();

    // ✅ Nouveau code attendu :
    // $table->date('date_debut');
    // $table->date('date_fin');
    // $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
    // $table->foreignId('maitre_de_stage_id')->constrained('employes')->onDelete('cascade');
    // $table->foreignId('etudiant_id')->nullable()->constrained('users')->onDelete('set null');
    // $table->foreignId('professeur_id')->nullable()->constrained('users')->onDelete('set null');
    ```

### ☐ 3. Modèle `Employe.php`
- [ ] Mettre à jour `$fillable` et les relations.
    ```php
    // ❌ Code actuel à corriger :
    // protected $fillable = ['idEntreprise', 'created_userid', 'created_date', ...];
    // public function entreprise() { return $this->belongsTo(Entreprise::class, 'idEntreprise'); }
    // public function stages() { return $this->hasMany(Stage::class, 'idMaitreDeStage'); }

    // ✅ Nouveau code attendu :
    // use HasFactory;
    // protected $fillable = ['entreprise_id', 'creator_id', ...];
    // public function entreprise(): BelongsTo { return $this->belongsTo(Entreprise::class); }
    // public function stages(): HasMany { return $this->hasMany(Stage::class, 'maitre_de_stage_id'); }
    ```

### ☐ 4. Modèle `Stage.php`
- [ ] Mettre à jour `$fillable`, ajouter les `casts` et corriger les relations.
    ```php
    // ❌ Code actuel à corriger :
    // protected $fillable = ['dateDebut', 'dateFin', 'idEmploye', 'idMaitreDeStage', ...];
    // public function entreprise() { return $this->belongsTo(Entreprise::class, 'idEntreprise'); }

    // ✅ Nouveau code attendu :
    // use HasFactory;
    // protected $fillable = ['date_debut', 'date_fin', 'entreprise_id', 'maitre_de_stage_id', ...];
    // protected $casts = ['date_debut' => 'date', 'date_fin' => 'date'];
    // public function entreprise(): BelongsTo { return $this->belongsTo(Entreprise::class); }
    ```

### ☐ 5. Modèle `Entreprise.php`
- [ ] Ajouter les relations inverses manquantes.
    ```php
    // ✅ À ajouter :
    // public function employes(): HasMany { return $this->hasMany(Employe::class); }
    // public function stages(): HasMany { return $this->hasMany(Stage::class); }
    ```

## ☐ Phase 2 : Logique Applicative (Contrôleurs)

### ☐ 1. `EmployeController.php`
- [ ] Mettre à jour la validation dans `store` et `update`.
    ```php
    // ❌ Code actuel à corriger :
    // $request->validate([ 'entreprise' => 'required' ]);

    // ✅ Nouveau code attendu :
    // $request->validate([ 'entreprise_id' => 'required|exists:entreprises,id' ]);
    ```

### ☐ 2. `StageController.php`
- [ ] Mettre à jour la validation dans `store` et `update`.
    ```php
    // ❌ Code actuel à corriger :
    // $request->validate([
    //    'employe_id' => 'required|exists:employes,id',
    // ]);

    // ✅ Nouveau code attendu :
    // $request->validate([
    //    'date_debut' => 'required|date',
    //    'entreprise_id' => 'required|exists:entreprises,id',
    //    'maitre_de_stage_id' => 'required|exists:employes,id',
    // ]);
    ```

## ☐ Phase 3 : Vues

- [ ] Mettre à jour les attributs `name` dans les formulaires Blade (`create.blade.php`, `edit.blade.php`) pour correspondre aux nouveaux noms de colonnes (`entreprise_id`, `maitre_de_stage_id`, `date_debut`, etc.).
- [ ] Vérifier que les `value="{{ old('...') }}"` utilisent aussi les nouveaux noms.