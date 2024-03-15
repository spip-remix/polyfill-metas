# TODO

## Récupération fichiers historiques

```bash
git clone --single-branch --no-tags git@git.spip.net:spip/spip.git polyfill-metas
cd polyfill-metas
git filter-repo \
  --path ecrire/inc/meta.php \
  --path-rename ecrire/inc/meta.php:inc/meta.php \
  --force
git branch -m 0.1
```

## composer

## Dépendances

### Constantes

- `_RENOUVELLE_ALEA`
- `_DEFAULT_CHARSET`
- `_FILE_META`
- `_DIR_CACHE`

### Globales

meta['touch'] marqueur du TTL du fichier de cache -> @internal
`$_GET['renouvelle_alea']`, meta['alea_ephemere_date'] -> renouvellement d'alea
meta['tables_config'] si {table}!=='meta' -> ? (x2)
tables_auxiliaires['spip_meta] -> ?
meta['charset] -> ?

### Fonctions

- `spip_logger()`
  - -> psr/log LoggerAwareInterface et LoggerAwareTrait
  - -> attention, c'est peut-être de la mauvaise ségrégation d'interface d'étendre LoggerAwareInterface avec MetaManagerInterface
- `_request()`, `autoriser_sans_cookie()`, `jeune_fichier()` -> lire le fichier de cache pour initialiser la globale 'meta'
- `test_espace_prive()`, `supprimer_fichier()`, `renouvelle_alea()` -> renouvellement d'alea
- `spip_query()` @deprecated
- `include_spip()` deux cas a priori inutile car pour `'base/abstract_sql'`
  - `'base/auxiliaires'` et `'base/create'` + `'inc/flock'` + `'inc/access'` (x1 chacun)
- `sql_fetch`
- `sql_free()`
- `ecrire_fichier_securise()`
- `sql_delete`
- `sql_select()`
- `sql_quote()`
- `sql_update()`
- `sql_insert()`
- `charger_fonction()` pour `('trouver_table', 'base')` (x2)
- `creer_ou_upgrader_table()`
- `sql_countsel()`
- `sql_drop_table()`
- `supprimer_fichier()`

### Filesystem

- `_FILE_META` -> si {table}==='meta' -> `{tmp/}meta_cache.php` @see meta_cache()
- `_DIR_CACHE` -> si {table}!=='meta' -> `{tmp/cache/}{table}.php`

### SQL

- table `spip_meta` et/ou `spip_{table}`
