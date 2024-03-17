<?php

/**
 * SPIP, Système de publication pour l'internet
 *
 * Copyright © avec tendresse depuis 2001
 * Arnaud Martin, Antoine Pitrou, Philippe Rivière, Emmanuel Saint-James
 *
 * Ce programme est un logiciel libre distribué sous licence GNU/GPL.
 */

declare(strict_types=1);

use Spip\Component\Filesystem\Filesystem;
use SpipRemix\Component\Serializer\NativeSerializer;
use SpipRemix\Component\Serializer\SecuredFileSerializer;
use SpipRemix\Contracts\MetaHandlerInterface;
use SpipRemix\Polyfill\Meta\FileMetaHandler;
use SpipRemix\Polyfill\Meta\PersistentMetaHandler;

/**
 * @internal Service d'appel à une table de métas.
 */
function _service_metas(string $table = 'meta'): MetaHandlerInterface
{
	/**
	 * @todo Manque le context SPIP
	 * @todo Traiter la constante _DEFAULT_CHARSET en amont
	 * @todo Traiter les constantes _DIR_TMP et _DIR_CACHE en amont
	 * @todo Traiter la constante _RENOUVELLE_ALEA en amont
	 */
	$tmpDir = constant('_DIR_TMP') ?? 'tmp/';
	$cacheDir = constant('_DIR_CACHE') ?? 'tmp/cache/';

	$table = $table == '' ? 'meta' : $table;

	/** @var array<string,MetaHandlerInterface> $_meta */
	static $_meta = [];

	if (!isset($_meta[$table])) {
		$GLOBALS[$table] = $GLOBALS[$table] ?? [];
		$metas = new PersistentMetaHandler($GLOBALS[$table]); // Pas de data à l'instanciation !
		$metas->setLogger(spip_logger());
		$cache = new FileMetaHandler($metas);
		$cache->setSerializer(new SecuredFileSerializer(
			new Filesystem(),
			new NativeSerializer,
			$cache->getCacheFilename($tmpDir, $cacheDir, $table),
		));

		$_meta[$table] = $cache;
	}

	return $_meta[$table];
}

/**
 * Renvoie la valeur d'une méta.
 *
 * @api
 *
 * @param string $nom     Nom de la méta
 * @param mixed  $default Valeur par défaut si la méta n'existe pas
 * @param string $table   Table SQL d'enregistrement de la méta.
 */
function lire_meta(string $nom, mixed $default = null, string $table = 'meta'): mixed
{
	$table = $table == '' ? 'meta' : $table;
	$valeur = $GLOBALS[$table][$nom] = _service_metas($table)->read($nom, $default);

	return $valeur;
}

/**
 * Met à jour ou crée une méta avec la clé et la valeur indiquée.
 *
 * @api
 *
 * @param string $nom        Nom de la méta
 * @param mixed  $valeur     Valeur à enregistrer
 * @param bool   $importable Cette méta s'importe-elle avec une restauration de sauvegarde, 'oui' ou 'non' ?
 * @param string $table      Table SQL d'enregistrement de la méta.
 */
function ecrire_meta(
	string $nom,
	mixed $valeur = null,
	bool $importable = false,
	string $table = 'meta'
): void {
	$table = $table == '' ? 'meta' : $table;
	_service_metas($table)->write($nom, $valeur, $importable);
	$GLOBALS[$table][$nom] =  _service_metas($table)->read($nom);
}

/**
 * Supprime une méta.
 *
 * @api
 *
 * @param string $nom Nom de la meta
 * @param string $table Table SQL d'enregistrement de la méta.
 */
function effacer_meta(string $nom, string $table = 'meta'): void
{
	$table = $table == '' ? 'meta' : $table;
	_service_metas($table)->erase($nom);
	unset($GLOBALS[$table][$nom]);
}

/**
 * Boot des métas.
 *
 * Les paramètres généraux du site sont dans une table SQL
 * mis en cache dans un fichier
 * et recopiés dans le tableau PHP global `meta`, car on en a souvent besoin
 */
function inc_meta_dist(string $table = 'meta'): void
{
	_service_metas($table)->boot();
}

/**
 * @deprecated 0.1
 */
function _inc_meta_dist($table = 'meta')
{
	/** @var FileMetaHandler $service_meta */
	$service_meta = _service_metas($table);
	$new = null;
	// Lire les meta, en cache si present, valide et lisible
	// en cas d'install ne pas faire confiance au meta_cache eventuel
	$cache = $service_meta->getCacheFilename(constant('_DIR_TMP') ?? '', constant('_DIR_CACHE') ?? '', $table);

	if (
		(!($exec = _request('exec')) || !autoriser_sans_cookie($exec))
		&& ($new = jeune_fichier($cache, $service_meta::CACHE_PERIOD))
		&& lire_fichier_securise($cache, $meta)
		&& ($meta = @unserialize($meta))
	) {
		$GLOBALS[$table] = $meta;
	}

	if (
		isset($GLOBALS[$table]['touch'])
		&& $GLOBALS[$table]['touch'] < time() - $service_meta::CACHE_PERIOD
	) {
		$GLOBALS[$table] = [];
	}
	// sinon lire en base
	if (!$GLOBALS[$table]) {
		$new = !lire_metas($table);
	}

	// renouveller l'alea general si trop vieux ou sur demande explicite
	/**
	 * @todo Traiter la constante _RENOUVELLE_ALEA en amont
	 */
	$renouvelleAlea = constant('_RENOUVELLE_ALEA') ?? 12 * 3600;
	if (
		(test_espace_prive() || isset($_GET['renouvelle_alea']))
		&& $GLOBALS[$table]
		&& time() > $renouvelleAlea + ($GLOBALS['meta']['alea_ephemere_date'] ?? 0)
	) {
		// si on n'a pas l'acces en ecriture sur le cache,
		// ne pas renouveller l'alea sinon le cache devient faux
		if (supprimer_fichier($cache)) {
			include_spip('inc/acces');
			renouvelle_alea();
			$new = false;
		} else {
			spip_logger()->info("impossible d'ecrire dans " . $cache);
		}
	}
	// et refaire le cache si on a du lire en base
	if (!$new) {
		touch_meta(null, $table);
	}
}

// fonctions aussi appelees a l'install ==> spip_query en premiere requete
// pour eviter l'erreur fatale (serveur non encore configure)

/**
 * Undocumented function
 * 
 * @api
 * @deprecated 0.1
 *
 * @param string $table
 * @return void
 */
function lire_metas($table = 'meta')
{

	if ($result = spip_query("SELECT nom,valeur FROM spip_$table")) {
		include_spip('base/abstract_sql');
		$GLOBALS[$table] = [];
		while ($row = sql_fetch($result)) {
			$GLOBALS[$table][$row['nom']] = $row['valeur'];
		}
		sql_free($result);

		if (
			!isset($GLOBALS[$table]['charset'])
			|| !$GLOBALS[$table]['charset']
			|| $GLOBALS[$table]['charset'] == '_DEFAULT_CHARSET' // hum, correction d'un bug ayant abime quelques install
		) {
			/**
			 * @todo Traiter la constante _DEFAULT_CHARSET en amont
			 */
			ecrire_meta('charset', constant('_DEFAULT_CHARSET') ?? 'utf-8', true, $table);
		}

		// noter cette table de configuration dans les meta de SPIP
		if ($table !== 'meta') {
			$liste = [];
			if (isset($GLOBALS['meta']['tables_config'])) {
				$liste = unserialize($GLOBALS['meta']['tables_config']);
			}
			if (!$liste) {
				$liste = [];
			}
			if (!in_array($table, $liste)) {
				$liste[] = $table;
				ecrire_meta('tables_config', serialize($liste));
			}
		}
	}

	return $GLOBALS[$table] ?? null;
}

/**
 * Mettre en cache la liste des meta, sauf les valeurs sensibles
 * pour qu'elles ne soient pas visibiles dans un fichier (souvent en 777).
 * 
 * @api
 * @todo Appels à remplacer dans ecrire/plugins/installer.php::plugins_installer_dist()
 * @todo À supprimer
 * 
 * @deprecated 0.1
 *
 * @param int|null $antidate Date de modification du fichier à appliquer si indiqué (timestamp)
 * @param string   $table    Table SQL d'enregistrement des métas.
 */
function touch_meta(?int $antidate = null, string $table = 'meta'): void
{
	/** @var FileMetaHandler $service_meta */
	$service_meta = _service_metas($table);

	$file = $service_meta->getCacheFilename(constant('_DIR_TMP') ?? '', constant('_DIR_CACHE') ?? '', $table);
	if (!$antidate || !@touch($file, $antidate)) {
		$r = $GLOBALS[$table] ?? [];
		if ($table == 'meta') {
			unset($r['alea_ephemere']);
			unset($r['alea_ephemere_ancien']);
			// le secret du site est utilise pour encoder les contextes ajax que l'on considere fiables
			// mais le sortir deu cache meta implique une requete sql des qu'on a un form dynamique
			// meme si son squelette est en cache
			//unset($r['secret_du_site']);
			if ($antidate) {
				$r['touch'] = $antidate;
			}
		}
		ecrire_fichier_securise($file, serialize($r));
	}
}

/**
 * Supprime une meta
 * 
 * @deprecated 0.1
 *
 * @see ecrire_config()
 * @see effacer_config()
 * @see lire_config()
 *
 * @param string $nom
 *     Nom de la meta
 * @param string $table
 *     Table SQL d'enregistrement de la meta.
 */
function _effacer_meta($nom, $table = 'meta')
{
	/** @var FileMetaHandler $service_meta */
	$service_meta = _service_metas($table);

	// section critique sur le cache:
	// l'invalider avant et apres la MAJ de la BD
	// c'est un peu moins bien qu'un vrai verrou mais ca suffira
	// et utiliser une statique pour eviter des acces disques a repetition
	static $touch = [];
	/**
	 * @todo À supprimer ici après s'être assuré que l'algo est respecté dans $metaHandler->erase($name)
	 */
	$antidate = time() - ($service_meta::CACHE_PERIOD << 4);
	if (!isset($touch[$table])) {
		touch_meta($antidate, $table);
	}
	sql_delete('spip_' . $table, "nom='$nom'", '', 'continue');
	unset($GLOBALS[$table][$nom]);
	if (!isset($touch[$table])) {
		touch_meta($antidate, $table);
		$touch[$table] = false;
	}
}

/**
 * Met à jour ou crée une meta avec la clé et la valeur indiquée
 * 
 * @deprecated 0.1
 *
 * @see ecrire_config()
 * @see effacer_config()
 * @see lire_config()
 *
 * @param string $nom
 *     Nom de la meta
 * @param string $valeur
 *     Valeur à enregistrer
 * @param string|null $importable
 *     Cette meta s'importe-elle avec une restauration de sauvegarde ?
 *     'oui' ou 'non'
 * @param string $table
 *     Table SQL d'enregistrement de la meta.
 */
function _ecrire_meta($nom, $valeur, $importable = null, $table = 'meta')
{
	/** @var FileMetaHandler $service_meta */
	$service_meta = _service_metas($table);

	static $touch = [];
	if (!$nom) {
		return;
	}
	include_spip('base/abstract_sql');
	$res = sql_select('*', 'spip_' . $table, 'nom=' . sql_quote($nom), '', '', '', '', '', 'continue');
	// table pas encore installee, travailler en php seulement
	if (!$res) {
		$GLOBALS[$table][$nom] = $valeur;

		return;
	}
	$row = sql_fetch($res);
	sql_free($res);

	// ne pas invalider le cache si affectation a l'identique
	// (tant pis si impt aurait du changer)
	if (
		$row
		&& $valeur == $row['valeur']
		&& isset($GLOBALS[$table][$nom])
		&& $GLOBALS[$table][$nom] == $valeur
	) {
		return;
	}

	$GLOBALS[$table][$nom] = $valeur;
	// cf effacer pour comprendre le double touch
	/**
	 * @todo À supprimer ici après s'être assuré que l'algo est respecté dans $metaHandler->write($name, $value, $importable)
	 */
	$antidate = time() - ($service_meta::CACHE_PERIOD << 1);
	if (!isset($touch[$table])) {
		touch_meta($antidate, $table);
	}
	$r = ['nom' => sql_quote($nom, '', 'text'), 'valeur' => sql_quote($valeur, '', 'text')];
	// Gaffe aux tables sans impt (vieilles versions de SPIP notamment)
	// ici on utilise pas sql_updateq et sql_insertq pour ne pas provoquer trop tot
	// de lecture des descriptions des tables
	if ($importable && isset($row['impt'])) {
		$r['impt'] = sql_quote($importable, '', 'text');
	}
	if ($row) {
		sql_update('spip_' . $table, $r, 'nom=' . sql_quote($nom));
	} else {
		sql_insert('spip_' . $table, '(' . implode(',', array_keys($r)) . ')', '(' . implode(',', array_values($r)) . ')');
	}
	if (!isset($touch[$table])) {
		touch_meta($antidate, $table);
		$touch[$table] = false;
	}
}

/**
 * Installer une table de configuration supplementaire
 * 
 * @api
 * @deprecated 0.1
 *
 * @param string $table
 */
function installer_table_meta($table)
{
	$trouver_table = charger_fonction('trouver_table', 'base');
	if (!$trouver_table("spip_$table")) {
		include_spip('base/auxiliaires');
		include_spip('base/create');
		creer_ou_upgrader_table("spip_$table", $GLOBALS['tables_auxiliaires']['spip_meta'], false, false);
		$trouver_table('');
	}
	lire_metas($table);
}

/**
 * Supprimer une table de configuration supplémentaire
 * 
 * @api
 * @deprecated 0.1
 *
 * Si $force=true, on ne verifie pas qu'elle est bien vide
 *
 * @param string $table
 * @param bool $force
 */
function supprimer_table_meta($table, $force = false)
{
	/** @var FileMetaHandler $service_meta */
	$service_meta = _service_metas($table);
	$cache = $service_meta->getCacheFilename(constant('_DIR_TMP') ?? '', constant('_DIR_CACHE') ?? '', $table);

	if ($table !== 'meta') {
		// Vérifier le contenu restant de la table
		$nb_variables = sql_countsel("spip_$table");

		// Supprimer si :
		// - la table est vide
		// - ou limitée à la variable charset
		// - ou qu'on force la suppression
		if (
			$force
			|| !$nb_variables
			|| $nb_variables == 1 && isset($GLOBALS[$table]['charset'])
		) {
			// Supprimer la table des globaleset de la base
			unset($GLOBALS[$table]);
			sql_drop_table("spip_$table");
			// Supprimer le fichier cache
			supprimer_fichier($cache);

			// vider le cache des tables
			$trouver_table = charger_fonction('trouver_table', 'base');
			$trouver_table('');

			// Supprimer la table de la liste des tables de configuration autres que spip_meta
			if (isset($GLOBALS['meta']['tables_config'])) {
				$liste = unserialize($GLOBALS['meta']['tables_config']);
				$cle = array_search($table, $liste);
				if ($cle !== false) {
					unset($liste[$cle]);
					if ($liste) {
						ecrire_meta('tables_config', serialize($liste));
					} else {
						effacer_meta('tables_config');
					}
				}
			}
		}
	}
}
