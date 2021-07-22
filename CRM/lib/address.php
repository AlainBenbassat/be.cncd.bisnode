<?php
/**
  Address management in CiviCRM
*/

/*
   Invalid an address
*/
function invalid_address($aid) {
  $result = civicrm_api3('Address', 'create', array(
      'sequential' => 1,
      'id' => $aid,
      'location_type_id' => "Invalide3"
  ));
}
  
/*
   Change adresse to principal
*/
function change_address_to_primary($aid, $type_id) {
  $result = civicrm_api3('Address', 'create', array(
      'sequential' => 1,
      'id' => $aid,
      'is_primary' => 1,
      'location_type_id' => $type_id,
  ));
}
  
/*
/*
   Change_address
*/
function change_address($aid, $street, $postalcode=NULL, $city=NULL, $country=NULL) {
  
  // Transcodages
  switch (strtolower($country)) {
    case 'autriche' :
    case 'at':
      $country_id = 1014;
      break;
    case 'allemagne' :
    case 'de':
      $country_id = 1082;
      break;
    case 'belgique' :
    case 'be' :
      $country_id = 1020;
      break;
    case 'espagne' :
    case 'spain' :
    case 'es':
      $country_id = 1198;
      break;
    case 'france' :
    case 'fr':
      $country_id = 1076;
      break;
    case 'luxembourg' :
    case 'lu':
      $country_id = 1126;
      break;
    case 'pologne' :
    case 'poland' :
    case 'pl':
      $country_id = 1172;
      break;
    case NULL :
      break;
    default: 
      break;
      throw new Exception('Unknow country.');   
  }
  if ($postalcode < 1299) {
     $province_id = 5217;		// 'Bruxelles';
  } else if ($postalcode < 1499) {
     $province_id = 1786;		// 'Brabant Wallon';
  } else if ($postalcode < 1999) {
     $province_id = 1793;		// 'Vlaams-Brabant';
  } else if ($postalcode < 2999) {
     $province_id = 1785;		// 'Antwerpen';
  } else if ($postalcode < 3499) {
     $province_id = 1793;		// 'Vlaams-Brabant';
  } else if ($postalcode < 3999) {
     $province_id = 1789;		// 'Limbourg';
  } else if ($postalcode < 4999) {
     $province_id = 1788;		// 'Liège';
  } else if ($postalcode < 5999) {
     $province_id = 1791;		// 'Namur';
  } else if ($postalcode < 6599) {
     $province_id = 1787;		// 'Hainaut';
  } else if ($postalcode < 6999) {
     $province_id = 1790;		// 'Luxembourg';	
  } else if ($postalcode < 7999) {
     $province_id = 1789;		// 'Hainaut';
  } else if ($postalcode < 8999) {
     $province_id = 1794;		// 'West-Vlaanderen';
  } else if ($postalcode < 9999) {
     $province_id = 1792;		// Oost-Vlanderen
  }

  $result = civicrm_api3('Address', 'create', array(
      'sequential' => 1,
      'id' => $aid,
      'street_address' => $street,
      'postal_code' => $postalcode,
      'city' => $city,
      'state_province_id' => $province_id,    
      'country_id' => $country_id,    
  ));

  return $result['id'];
}
