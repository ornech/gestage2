# Déploiement Gestage — Premier déploiement

## ─── 1. RÉCUPÉRATION DU CODE ────────────────────────────────────────────────
```bash
cd /var/www                          # ou votre répertoire web
git clone https://github.com/ornech/gestage2.git
cd gestage2
```

## ─── 2. DÉPENDANCES ─────────────────────────────────────────────────────────
```bash
composer install --optimize-autoloader
```

### Node.js (hébergement mutualisé O2Switch)

`npm` n'est pas dans le PATH par défaut. Créer une app Node.js via cPanel
(une seule fois) :

- cPanel → *Software* → **Setup Node.js App** → *Create Application*
- Version : une version récente (ex. 20.x)
- Application mode : `Production`
- Application root : le dossier du projet (ex. `gestage2.btssio17.com`)
- Application URL : peu importe, ce Node.js ne sert pas le site (le site
  reste servi en PHP), il sert uniquement à disposer de `npm` pour le build

cPanel affiche ensuite une commande d'activation du virtualenv, à relancer
à chaque déploiement (le `npm`/`node` activé ne persiste pas entre les
sessions shell) :

```bash
source /home3/<user>/nodevenv/<app-root>/20/bin/activate && cd /home3/<user>/<app-root>
```

Puis, dans ce shell activé :

```bash
npm ci --include=dev
npm run build
```

> `--include=dev` est nécessaire car `NODE_ENV=production` est souvent
> positionné par défaut sur ce type d'hébergement, ce qui fait sauter
> silencieusement les `devDependencies` (`vite`, `tailwindcss`, etc.) lors
> d'un simple `npm ci` — le build échoue alors avec `vite: commande introuvable`.
> Une fois `public/build/` généré, `node_modules` n'est plus nécessaire en
> runtime (le site est servi en PHP, pas en Node).

## ─── 3. BASE DE DONNÉES ─────────────────────────────────────────────────────

Créer la base et l'utilisateur MySQL :

```sql
-- Se connecter en root : mysql -u root -p
CREATE DATABASE gestage2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'gestage_user'@'localhost' IDENTIFIED BY 'mot_de_passe_fort';
GRANT ALL PRIVILEGES ON gestage2.* TO 'gestage_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## ─── 4. FICHIER .ENV ─────────────────────────────────────────────────────────
```bash
cp .env.example .env
nano .env          # ou vim, selon votre préférence
```

Valeurs à renseigner obligatoirement :

```env
APP_NAME="Gestage"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.fr

APP_LOCALE=fr

# Base de données (créée à l'étape 3)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestage2
DB_USERNAME=gestage_user
DB_PASSWORD=mot_de_passe_fort

# Mail SMTP authentifié via une boîte créée dans cPanel (ex. O2Switch).
# Le serveur et le port sont indiqués dans le message « Client configuration » reçu dans cette boîte.
# Mettre le mot de passe entre guillemets (il contient souvent des caractères spéciaux).
MAIL_MAILER=smtp
MAIL_HOST=<serveur sortant de l'hébergeur>
MAIL_PORT=465
MAIL_SCHEME=smtps
MAIL_USERNAME="gestage@votre-domaine.fr"
MAIL_PASSWORD="mot_de_passe_de_la_boite"
MAIL_FROM_ADDRESS="gestage@votre-domaine.fr"
MAIL_FROM_NAME="Gestage BTS SIO"

# Backup — destinataire des notifications (succès + échec).
# Utiliser la boîte de l'application, avec une redirection cPanel vers votre adresse personnelle.
BACKUP_MAIL_TO="gestage@votre-domaine.fr"

# API INSEE Sirene (obtenir une clé sur api.insee.fr)
INSEE_API_KEY=votre_cle_api

# Slack (optionnel)
SLACK_BOT_USER_OAUTH_TOKEN=
SLACK_BOT_USER_DEFAULT_CHANNEL=

LOG_LEVEL=warning
```

## ─── 5. CLÉ ET MIGRATIONS ───────────────────────────────────────────────────
```bash
php artisan key:generate
php artisan migrate --force
```

## ─── 6. STOCKAGE ────────────────────────────────────────────────────────────
```bash
php artisan storage:link
```

## ─── 7. PERMISSIONS ─────────────────────────────────────────────────────────

Sur un serveur dédié avec Apache/Nginx tournant sous `www-data` :

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

Sur hébergement mutualisé O2Switch, l'utilisateur `www-data` n'existe pas :
les fichiers appartiennent déjà au compte cPanel, seul le `chmod` est utile.

```bash
chmod -R 775 storage bootstrap/cache
```

## ─── 8. CACHES ──────────────────────────────────────────────────────────────
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## Courrier sortant : limites connues

- **Les adresses `@ac-poitiers.fr` (et plus largement `education.gouv.fr`) refusent les mails d'un hébergeur externe** (`550 #5.7.1 Your access to submit messages to this e-mail system has been rejected`). Constaté depuis O2Switch avec un domaine correct (SPF, DKIM, expéditeur valide) : c'est une règle de l'académie.
- Conséquence : n'utiliser aucune adresse académique comme destinataire de l'application (alertes de sauvegarde, invitations).
- Créer dans cPanel la boîte qui sert d'expéditeur (`MAIL_FROM_ADDRESS`) : sans elle, certains serveurs vérifient l'adresse d'expédition, la trouvent inexistante et écartent le message.
- Les messages d'échec (« Impossible d'envoyer l'email ») arrivent dans cette boîte : y ajouter une redirection vers une adresse lue régulièrement.
- Vérifier l'envoi : `php artisan tinker --execute="Mail::raw('Test', fn(\$m) => \$m->to('adresse')->subject('Test')); echo 'envoyé';"` puis cPanel › Email › Track Delivery. « envoyé » signifie seulement que le message est parti du serveur, pas qu'il est livré.

## ─── 9. CRON (scheduler Laravel) ────────────────────────────────────────────

Ajouter via `crontab -e` (user `www-data`) :

```cron
* * * * * cd /var/www/gestage2 && php artisan schedule:run >> /dev/null 2>&1
```

Tâches planifiées actives :
- `02:00` — backup BDD quotidien
- `02:30` — nettoyage des vieux backups
- `08:00` chaque lundi — rappel étudiants sans stage
- chaque minute — traitement file de mails

---

# Mise à jour (après chaque git pull)

```bash
cd /var/www/gestage2
git pull origin dev
composer install --no-dev --optimize-autoloader
```

Sur O2Switch, activer le virtualenv Node.js avant le build (voir section 2) :

```bash
source /home3/<user>/nodevenv/<app-root>/20/bin/activate && cd /home3/<user>/<app-root>
npm ci --include=dev
npm run build
```

Puis :

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```
