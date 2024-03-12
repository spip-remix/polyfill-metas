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
