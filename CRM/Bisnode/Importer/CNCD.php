<?php
/*-------------------------------------------------------+
| CiviBisnode                                            |
| Copyright (C) 2020 CNCD-11.11.11                       |
| Author: Philippe Sampont                               |
| http://www.cncd.be/                                    |
+--------------------------------------------------------*/

require_once 'CRM/lib/contact.php';
require_once 'CRM/lib/address.php';

class CRM_Bisnode_Importer_CNCD {

  protected $nb_lines;
  protected $nb_errors;
  protected $counters;
  protected $timestamp_start;
  protected $timestamp_end;
  protected $logs;
  protected $dryrun;

  function __construct($dryrun) {
    $this->nb_lines = 0;
    $this->nb_errors = 0;
    $this->logs = array();
    $this->dryrun = $dryrun;

    $this->counters['Category'] = [];
    $this->counters['Address'] = [];
    $this->counters['Name'] = [];
    $this->counters['Sex'] = [];
    $this->counters['Movers'] = [];
  }

  /**
   * Get logs of the last run
  */
  function getLog() {
    return $this->logs;
  }

  /**
   * Get summary of the last run
   */
  function getSummary() {
    $summary = '';

    $summary.= "<p>".ts("Start Date")." : ".$this->timestamp_start."</p>";

    $summary.= "$this->nb_lines fiches reçues : <br>";
    foreach ($this->counters as $categ => $counters_categ) {
       $summary.= "<h4>$categ Check</h4>";
       $summary.= "<ul>";
       foreach ($counters_categ as $key => $value) {
          $summary.="<li>Nb. $key = $value</li>";
       }
       $summary.= "</ul>";
    }

    $summary.= "<p>Nombre d'erreurs rencontrées et annulées : $this->nb_errors.</p>";

    $summary.= "<p>".ts("End Date")." : ".$this->timestamp_end."</p>";

    return $summary;
  }

  /**
   * Address-Check – restructuration et correction de l’adresse
   *
   * Valide ou corrige les informations du contacts en fonction de la valeur du champ OUT_adrs_flg
   *
   * @param array $contact Informations sur le contact
   *
   * @return void
  */
  function address_check($contact) {
    $run = !$this->dryrun;

    $categ_code = array(
      'S' => 'Correct',
      'L' => 'Localité',
      'C' => 'Corrigé',
      'P' => 'Proposition',
      'N' => 'Non reconnu',
      'Z' => 'Adresse étrangère',
      'E' => "Pas d'adresse fournie",
      'X' => 'Non traitée',
   );
    $flag = $contact['OUT_adrs_flg'];
    $log =  "- Address check = $flag : ";
    if (!array_key_exists($categ_code[$flag], $this->counters['Address']))
      $this->counters['Address'][$categ_code[$flag]] = 1;
    else
      $this->counters['Address'][$categ_code[$flag]]++;

    switch ($flag) {
      case 'L' :
        $log.= "<b>Localité</b> -> ".$contact['OUT_city'];
        if ($run) change_address($contact['ID_Adresse'], null, null, $contact['OUT_city'], null);
             else $log.= " DRY RUN !";
        break;
      case 'C' :
      case 'P' :
      $log.= "<b>Corrigé</b> -> ".$contact['OUT_adrs']." - ".$contact['OUT_cp']." - ".$contact['OUT_city']." - ".$contact['OUT_country'];
        if ($run) {
          change_address($contact['ID_Adresse'], $contact['OUT_adrs'], $contact['OUT_cp'], $contact['OUT_city'], $contact['OUT_country']);
          change_address_to_primary($contact['ID_Adresse'], 1); // --> Domicile
        }
        else {
          $log.= " DRY RUN !";
        }
         break;
      case 'S' :
      case 'N' :
      case 'Z' :
      case 'E' :
      case 'X' :
        $log.= $categ_code[$flag];
           // On ne fait rien
        break;
      case null :
        $log.= "Code vide";
           // On ne fait rien
        break;
      default :
        throw new UnexpectedValueException("Flag OUT_nc_flg inconnu : $flag.");
    }
    $this->logs[] = $log;
  }

  /**
   * Name-Check – restructuration et correction des noms, prénoms
   *
   * Valide ou corrige les informations en fonction de la valeur du champ OUT_nc_flg
   *
   * @param array $contact Informations sur le contact
   *
   * @return void
  */
  function name_check($contact) {
    $run = !$this->dryrun;

    $categ_code = array (
      'S' => "Correct",
      'C' => "Correction de l’orthographe",
      'F' => "Correction de la structure",
      'D' => "Correction de la structure et de l’orthographe",
      'N' => "Non reconnu",
      'X' => "Non traité",
    );
    $flag = $contact['OUT_nc_flg'];
    $log = " - Name check = $flag : ";

    if (!array_key_exists($categ_code[$flag], $this->counters['Name']))
      $this->counters['Name'][$categ_code[$flag]] = 1;
    else
      $this->counters['Name'][$categ_code[$flag]]++;

    switch ($flag) {
      case 'S' :
      case 'N' :
      case 'X' :
        $log.= $categ_code[$flag];
           // On ne fait rien
        break;
      case 'C' :
      case 'F' :
      case 'D' :
        $log.= "<b>Corrigé</b> -> ".$contact['OUT_name'].", ".$contact['OUT_chrname'];
        if ($run) change_contact_name($contact['nocli'], $contact['OUT_name'], $contact['OUT_chrname']);
             else $log.= " DRY RUN !";
        break;
      default :
        throw new UnexpectedValueException("Flag OUT_nc_flg inconnu : $flag.");
    }
    $this->logs[] = $log;
  }

  /**
   * Sex-Check – Correction du code sex
   *
   * Valide ou corrige les informations en fonction de la valeur du champ OUT_sex_flg
   *
   * @param array $contact Informations sur le contact
   *
   * @return void
  */
  function sex_check($contact) {
    $run = !$this->dryrun;

    $categ_code = array (
      'S' => 'Correct',
      'C' => 'Correction (non implémentée)',
      'E' => 'Enrichi',
      'D' => 'Mauvaise categorie (non implémentée)',
      'N' => 'Non trouvé',
    );

    $flag = $contact['OUT_sex_flg'];
    $log = " - Sex check     = $flag : ";
    if (!array_key_exists($categ_code[$flag], $this->counters['Sex']))
      $this->counters['Sex'][$categ_code[$flag]] = 1;
    else
      $this->counters['Sex'][$categ_code[$flag]]++;

    switch ($flag) {
      case 'S' :
      case 'N' :
      case 'C' :
        $log.= $categ_code[$flag];
           // On ne fait rien
        break;
/*      case 'C' :
        $log.= "<b>Corrigé</b> -> ".$contact['OUT_sex'];
        if ($run) change_contact_sex($contact['Contact_ID'], $contact['OUT_sex']);
             else $log.= " DRY RUN !";
        break;*/
      case 'E' :
        $log.= "<b>Enrichi</b> -> ".$contact['OUT_sex'];
        if ($run) change_contact_sex($contact['nocli'], $contact['OUT_sex']);
             else $log.= " DRY RUN !";
        break;
      case 'D' :
        $log.= "<b>Mauvaise categorie : cas non géré</b>";
        throw new UnexpectedValueException("Flag OUT_sex_flg non géré : $flag.");
        break;
      default :
        throw new UnexpectedValueException("Flag OUT_sex_flg inconnu : $flag.");
    }

    $this->logs[] = $log;
  }

  /**
   * Check de la mutation
   *
   * Valide ou corrige les informations en fonction de la valeur du champ OUT_muta_flg
   *
   * @param array $contact Informations sur le contact
   *
   * @return void
  */
  function mover_check($contact) {
    $run = !$this->dryrun;

    // --- OUT_muta_flg ---
    $categ_code = array (
      'R' => 'Correct',
      'N' => "Changement d'adresse",
      'Q' => "Changement d'adresse",
      'O' => "Adresse inactive",
      'S' => "Adresse inconnue",
      'P' => "Numero de maison",
      'E' => "Non livrée",
      'X' => "Non traitée",
    );
    $flag = $contact['OUT_muta_flg'];
    $log = " - Movers check   = $flag : ";
    if (!array_key_exists($categ_code[$flag], $this->counters['Movers']))
      $this->counters['Movers'][$categ_code[$flag]] = 1;
    else
      $this->counters['Movers'][$categ_code[$flag]]++;

   switch ($flag) {
      case 'S' :
      case 'R' :
      case 'E' :
      case 'X' :
        $log.= $categ_code[$flag];
           // On ne fait rien
        break;
      case 'N' :
      case 'Q' :
        $log.= "<b>Changement d'adresse </b> -> ".$contact['OUT_adrs']." - ".$contact['OUT_cp']." - ".$contact
['OUT_city']." - ".$contact['OUT_country'];
        if ($run) {
          change_address($contact['ID_Adresse'], $contact['OUT_adrs'], $contact['OUT_cp'], $contact['OUT_city'], $contact['OUT_country']);
          change_address_to_primary($contact['ID_Adresse'], 1); // --> Domicile
        }
        else $log.= " DRY RUN !";
        break;
      case 'O' :
        $log.= "<b>Inactive</b> -> INVALIDE 3x.";
        if ($run) invalid_address($contact['ID_Adresse']);
             else $log.= " DRY RUN !";
        break;
      case 'P' :
        $log.= "<b>Numéro de maison</b> -> ".$contact['OUT_adrs'];
	if ($run) {
          change_address($contact['ID_Adresse'], $contact['OUT_adrs']);
          change_address_to_primary($contact['ID_Adresse'], 1); // --> Domicile
        }
        else $log.= " DRY RUN !";
        break;
      default :
        throw new UnexpectedValueException("Flag OUT_muta_flg inconnu : $flag.");
    }

    $this->logs[] = $log;
  }

  function run($contacts) {
    $this->timestamp_start= date('d-m-Y H:i:s');
    $contacts_errors = array();

    $tx = new CRM_Core_Transaction();

    foreach ($contacts as $contact) {
      $this->nb_lines++;

      $this->logs[] = $this->nb_lines.". <a href='/civicrm/contact/view?reset=1&cid=".$contact["nocli"]."'>".$contact["fnom"].", ".$contact["prenom"]."</a> - ".$contact['adr'].' - '.$contact['cp'].' - '.$contact['loc'];


      // On garde les noms de ville en minuscule avec accents
      if (strcmp(strtoupper($contact['OUT_city']), strtoupper($contact['loc'])) == 0)
        $contact['OUT_city'] = $contact['loc'];
      else
        $contact['OUT_city'] = ucwords(strtolower($contact['OUT_city']),'-');

      // --- OUT_categ_flg ---
      $categ_code = array (
        'C' => 'Particulier',
        'B' => 'Organisation',
        'R' => 'Rejet'
      );
      $categ_flg = $contact['OUT_categ_flg'];

      if (!array_key_exists($categ_code[$categ_flg], $this->counters['Category']))
        $this->counters['Category'][$categ_code[$categ_flg]] = 1;
      else
        $this->counters['Category'][$categ_code[$categ_flg]]++;
      try {
        switch ($categ_flg) {
          case 'C' :
            $this->logs[] = $categ_code[$categ_flg];
            $this->address_check($contact);
            $this->name_check($contact);
            $this->sex_check($contact);
	    $this->mover_check($contact);
	    break;
          case 'B' :
            $this->logs[] = $categ_code[$categ_flg];
            $this->address_check($contact);
            $this->mover_check($contact);
            break;
          case 'R' :
            $this->logs[] = $categ_code[$categ_flg];
            // On ne fait rien
	    break;
          default :
            throw new UnexpectedValueException("Unknow OUT_categ_flg : $categ_flg");
        }
      }
      catch (UnexpectedValueException $e) {
          $contact['ERROR'] = $e->getMessage();
          $this->nb_errors++;
          $contacts_errors[] = $contact;
          $this->logs[] = "<font color=red>ERROR : ".$e->getMessage()."</font> on contact ".$contact['Contact_ID'];
      }
    }
    $this->timestamp_end= date('d-m-Y H:i:s');

    return $contacts_errors;
  }
}
?>
