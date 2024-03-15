# Notes

- Les métas ressemblent à un container PSR-11
- ça fait penser aux variables d'application de JAVA (partage de données entre les sessions/requêtes)
- Dans symfony, ils pourraient être
  - un service
  - un ParameterBag
  - un sous-ensemble des "parameters" du container principale de l'application
  - ...
- on dit une méta (dans les docblocks)
- les métas sont aussi appelées "Les paramètres généraux du site" (dans les docblocks)

## Fichier

- `ecrire/inc/meta.php`
  - surchargeable (appelé par `include_spip('inc/meta')` 11 appels)
  - mais préchargé à chaque hit, via `charger_fonction('meta', 'inc')`, dans le bootstrap SPIP

- -> Plugin (dist)
- -> données stockées dans une globale, récupérer depuis un fichier (+serialisation) sinon en base
  - -> attention au format de serialisation entre tableau et objet (invalider le cache lors d'une miise à jour)
- -> en fait, le cache, c'est la globale, le fichier, c'est le moyen du partage, la base, la persistance
- -> si chaine de responsabilité: globales(cache)->fichier->base en lecture, inverse pour écriture
- -> en lecture, fichier et base sont accédés pour toutes les métas, à l'unité en ecriture

## Constantes

- `_META_CACHE_TIME` non surchargeable = `1 << 24` ~195j.

## Globales

- `'meta'` (si param par défaut de `inc_meta_dist()`) (533 appels avec `$GLOBALS['meta']` dans spip et plugins-dist)
  - -> fonction lire_meta($name) (et $meta->get($name))

## Fonctions (@internal)

- `cache_meta($table = 'meta')` -> `cache_meta(string $table = 'meta'): string`

### Fonctions (@api)

- `inc_meta_dist($table = 'meta')` -> `inc_meta_dist(string $table = 'meta'): ?array`
- `ecrire_meta($nom, $valeur, $importable = null, $table = 'meta')` -> `ecrire_meta(string $name, mixed $value, mixed? $importable = null, strting $table = 'meta'): void`
- `effacer_meta($nom, $table = 'meta')` -> `effacer_meta(string $name, $table = 'meta'): void`

- `lire_metas($table = 'meta')` -> `lire_metas(string $table = 'meta'): ?array` lire en base (11 appels externes)
- `touch_meta($antidate = false, $table = 'meta')` - > `touch_meta(bool $antidate = false, string $table = 'meta'): void` pour 1 appel à l'install

- `installer_table_meta($table)` -> `installer_table_meta(string $table): void`
- `supprimer_table_meta($table, $force = false)` -> `supprimer_table_meta(strinig $table, bool $force = false): void`

## Roadmap

- `1.0` utilisation de `$GLOBALS['meta']`
- `1.x` @deprecated de `$GLOBALS['meta']` au profit d'autre chose
- `2.0` suppression de `$GLOBALS['meta']`
- que faire des fonctions `inc_meta_dist`, `ecrire_meta` et `effacer_meta` dans l'avenir ?
- `lire_metas()` et `touch_meta()` peuvent devenir @internal, mais quand ? pour `1.0` ou `2.0` ?

## Cache

- GLOBALS (le temps de nettoyer les $GLOBALS['meta']) c'est une copie de la mémoisation dans le MetaManager
- RAM pour globale (CachedMetaManager) ne devrait pas rester car c'est les fonctions historiques qui synchronise la globale ci-dessus et qui vont chercher dans le cache  mémoïsé du PersistentMetaManager donc pas de TTL à gérer
- Fichier (FileMetaManager) stocke une sérialisation de all() dans un fichier PHP: TTL commun à toutes les méta: constante _META_CACHE_TIME

- MetaManager final (PersistentMetaManager) cache mémoïsé de base

- le clear(), le unset et le set (écriture)
- le clear() de FileMetaManager efface le fichier. Ça doit forcer le rafraichissement du fichier et de la mémo (et donc de la globale)
- le all() et le get() c'est de la lecture

le fichier `ecrire/base/objets.php` décrit la table SQL `spip_meta`: <https://git.spip.net/spip/spip/-/blob/master/ecrire/base/objets.php#L562>

- clé primaire `nom` VARCHAR(255) NOT NULL
- champ `valeur` text '' par défaut (valeur doit être sérialisée pour être stockée)
- champ `impt` = ENUM 'oui'/'non' 'oui' par défaut (importable est true par défaut quand on set())
- champ `maj` = TIMESTAMP (commun avec articles, auteurs, rubriques et resultats?)
- pas d'autres index que le primary

dans SQLite3, MySQL, MariaDB et PostgreSQL
VARCHAR
text
TIMESTAMP mise à jour automatique à l'INSERT et à l'UPDATE
<https://dev.mysql.com/doc/refman/8.0/en/datetime.html>
<https://www.sourcetrail.com/fr/sql/mise-%C3%A0-jour-automatique-SQL-dupdated_at/>
ENUM dans SQLite3, MySQL, MariaDB et PostgreSQL

```sql
-- Donne la date de dernière mise à jour de la table.
SELECT MAX(maj) AS recent FROM spip_meta;
```

```php
// Lit la date de dernière modification du fichier
// function filemtime(string $filename): int|false
$recent = filemtime($cacheFilename);
```
