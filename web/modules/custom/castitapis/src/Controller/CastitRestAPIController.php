<?php

/**
 * @file
 * Contains \Drupal\castitapis\Controller\CastitRestAPIController.
 */

namespace Drupal\castitapis\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\group\Entity\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use OpenCloud\Rackspace;

// zencoder old pass : abc-def-ghi-jkl-2
// zencoder new pass : C@$T1T-ZeCoDeR

/**
 * Controller routines for castitapis routes.
 */
class CastitRestAPIController extends ControllerBase {

  /**
   * Callback for `my-api/get.json` API method.
   */
  public function get_example( Request $request ) {

    $response['data'] = 'Some test data to return';
    $response['method'] = 'GET';

    return new JsonResponse( $response );
  }

  /**
   * Callback for `my-api/put.json` API method.
   */
  public function put_example( Request $request ) {

    $response['data'] = 'Some test data to return';
    $response['method'] = 'PUT';

    return new JsonResponse( $response );
  }

  /**
   * Callback for `my-api/post.json` API method.
   */
  public function post_example( Request $request ) {

    // This condition checks the `Content-type` and makes sure to 
    // decode JSON string from the request body into array.
    if ( 0 === strpos( $request->headers->get( 'Content-Type' ), 'application/json' ) ) {
      $data = json_decode( $request->getContent(), TRUE );
      $request->request->replace( is_array( $data ) ? $data : [] );
    }

    $response['data'] = 'Some test data to return';
    $response['method'] = 'POST';

    return new JsonResponse( $response );
  }

  public function model( Request $request ) {

    // This condition checks the `Content-type` and makes sure to 
    // decode JSON string from the request body into array.
    if ( 0 === strpos( $request->headers->get( 'Content-Type' ), 'application/json' ) ) {
      $data = json_decode( $request->getContent(), TRUE );
      $request->request->replace( is_array( $data ) ? $data : [] );
    }

    $response['data'] = 'Some test data to return';
    $response['method'] = 'POST';

    return new JsonResponse( $response );
  }
  public function get_files_access(Request $request){
    if ( 0 === strpos( $request->headers->get( 'Content-Type' ), 'application/json' ) ) {
      $filename = json_decode( $request->query->get('filename'), TRUE );
      $request->request->replace( is_array( $data ) ? $data : [] );
    
      $username = 'castit';
      $apikey = '187a515209d0affd473fedaedd6d770b';
      $containerName = 'CASTITFILES';
      $region = 'LON';
      $client = new Rackspace(Rackspace::UK_IDENTITY_ENDPOINT, array(
          'username' => $username,
          'apiKey'   => $apikey,
      ),
      [
        // Guzzle ships with outdated certs
        Rackspace::SSL_CERT_AUTHORITY => 'system',
        Rackspace::CURL_OPTIONS => [
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ],
      ]
      );
      $service = $client->objectStoreService(null, $region);
      $container = $service->getContainer($containerName);

      $object = $container->getObject("");
      $object->setName($filename);
      
      $account = $service->getAccount();
      $account->setTempUrlSecret();
      
      $tempUrl = $object->getTemporaryUrl(1800, 'PUT', TRUE);

      $response['tempUrl'] = $tempUrl;
      return new JsonResponse( $response );
    }
    else{
      return new JsonResponse ( ['error' => 'supply json'] );
    }
  }

  public function complete_registration( Request $request ) {
    if ( 0 === strpos( $request->headers->get( 'Content-Type' ), 'application/json' ) ) {
      $data = json_decode( $request->getContent(), TRUE );
      $request->request->replace( is_array( $data ) ? $data : [] );
    }

    if( $data['uid'] ) {
      $user = User::load($data['uid']);
      $user->addRole($data['role']);
      $user->activate();
      $user->save();
    }

    $response['data'] = 'Some test data to return';
    $response['method'] = 'POST';
    $response['input'] = $data;
    $response['user'] = $user;
    $response['request'] = $request;
    return new JsonResponse( $response );
  }

  public function create_lightbox( Request $request ) {
    if ( 0 === strpos( $request->headers->get( 'Content-Type' ), 'application/json' ) ) {
      $data = json_decode( $request->getContent(), TRUE );
      // $request->request->replace( is_array( $data ) ? $data : [] );
      $lightbox_name = $data['name'];
      $lightbox_owner = (int) $data['uid'];
      // $lightbox_owner = User::load($data['uid'])->id();
      // $lightbox_owner = \Drupal::currentUser()->id();
      $application_group = Group::create(['type' => 'lightbox', 'uid' => $lightbox_owner]);
      $application_group->set('label', $lightbox_name);
      // $application_group->setOwnerId($lightbox_owner);
      $application_group->save();
      $response['message'] = 'Group Created';
      $response['info'] = [$lightbox_name, $lightbox_owner, $data, $application_group];
    }
    else{
      $response['message'] = 'request type is not json';
    }
    return new JsonResponse( $response );
  }

  public function video_zencode($filename) {
    
    $zencoder_input   	= "cf+uk://castit:187a515209d0affd473fedaedd6d770b@CASTITFILES/".$filename;
    $zencoder_output  	= "cf+uk://castit:187a515209d0affd473fedaedd6d770b@CASTITFILES/".$filename.".mp4";
    $zencoder_base_url  = "cf+uk://castit:187a515209d0affd473fedaedd6d770b@CASTITFILES";

    $zencoder_array = [
      "input_file"		=> $zencoder_input,
      "output_file"		=> $zencoder_output,
      "base_url"		=> $zencoder_base_url,
      "filename"		=> $filename,
    ];

    // $zencoder_json = json_encode($zencoder_array);
    $zencoder_json = $this->build_json_zencoder($zencoder_array);


    $url = 'https://app.zencoder.com/api/v2/jobs';
    $ch = curl_init( $url );
    curl_setopt( $ch, CURLOPT_POST, 1);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $zencoder_json);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER , 1);
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json ',
      'Zencoder-Api-Key: 9477541a57e1eb2471b1ff256ca4b92c'
    ));

		$response = curl_exec( $ch );
    return $response;
  }

  public function build_json_zencoder($data_array){
    $json = '{
      "input": "'.$data_array["input_file"].'",
      "outputs": [
        {"thumbnails": [
          {
            "base_url": "'.$data_array["base_url"].'",
            "label": "regular",
            "number": 1,
            "filename": "thumb_'.$data_array["filename"].'",
            "public": "true"
          }]
    },
    {"label": "mp4 high"},
    {"url": "'.$data_array["output_file"].'"},
    {"h264_profile": "high"}
    ]
    }';
    return $json;
  }

  public function trigger_zencoder( Request $request ) {
    if ( 0 === strpos( $request->headers->get( 'Content-Type' ), 'application/json' ) ) {
      $data = json_decode( $request->getContent(), TRUE );
      $request->request->replace( is_array( $data ) ? $data : [] );

      $filename = $data['filename'];
      $response = $this->video_zencode($filename);
    }
    else{
      $response['error'] = 'supply json';
    }

    return new JsonResponse( $response );
  }
  /**
   * Callback for `my-api/delete.json` API method.
   */
  public function delete_example( Request $request ) {

    $response['data'] = 'Some test data to return';
    $response['method'] = 'DELETE';

    return new JsonResponse( $response );
  }

  public function user_update( Request $request ) {
    if ( 0 === strpos( $request->headers->get( 'Content-Type' ), 'application/json' ) ) {
      $data = json_decode( $request->getContent(), TRUE );
      $request->request->replace( is_array( $data ) ? $data : [] );
      if($data['uid_export'] != ''){
        $user = User::load($data['uid_export']);
        $user->set('field_about_me', $data['field_about_me_export']);
        $user->set('field_address', $data['field_address_export']);
        $user->set('field_agreed_to_terms', $data['field_agreed_to_terms_export']);
        // $user->set('field_birthday', $data['']);
        $user->set('field_bra_size', $data['field_bra_size_export']);
        $user->set('field_bureau', $data['field_bureau_export']);
        $user->set('field_category', $data['field_category_export']);
        $user->set('field_cellphone', $data['field_cellphone_export']);
        $user->set('field_city', $data['field_city_export']);
        $user->set('field_country', $data['field_country_export']);
        $user->set('field_dialect_one', $data['field_dialect_one_export']);
        $user->set('field_dialect_two', $data['field_dialect_two_export']);
        $user->set('field_dialect_three', $data['field_dialect_three_export']);
        $user->set('field_ethnic_origin', $data['field_ethnic_origin_export']);
        $user->set('field_eye_color', $data['field_eye_color_export']);
        $user->set('field_fax', $data['field_fax_export']);
        $user->set('field_first_name', $data['field_first_name_export']);
        $user->set('field_gender', $data['field_gender_export']);
        $user->set('field_hair_color', $data['field_hair_color_export']);
        $user->set('field_height', $data['field_height_export']);
        $user->set('field_language_four', $data['field_language_four_export']);
        $user->set('field_language_four_rating', $data['field_language_four_rating_export']);
        $user->set('field_language_one', $data['field_language_one_export']);
        $user->set('field_language_one_rating', $data['field_language_one_rating_export']);
        $user->set('field_language_three', $data['field_language_three_export']);
        $user->set('field_language_three_rating', $data['field_language_three_rating_export']);
        $user->set('field_language_two', $data['field_language_two_export']);
        $user->set('field_language_two_rating', $data['field_language_two_rating_export']);
        $user->set('field_last_name', $data['field_last_name_export']);
        $user->set('field_licenses', $data['field_licenses_export']);
        $user->set('field_nationality', $data['field_nationality_export']);
        $user->set('field_new_from', $data['field_new_from_export']);
        $user->set('field_new_profile', $data['field_new_profile_export']);
        $user->set('field_new_until', $data['field_new_until_export']);
        $user->set('field_occupation', $data['field_occupation_export']);
        $user->set('field_old_profile_id', $data['field_old_profile_id_export']);
        $user->set('field_pant_size_from', $data['field_pant_size_from_export']);
        $user->set('field_pant_size_to', $data['field_pant_size_to_export']);
        $user->set('field_profile_number', $data['field_profile_number_export']);
        $user->set('field_profile_status', $data['field_profile_status_export']);
        $user->set('field_profile_type', $data['field_profile_type_export']);
        $user->set('field_recently_updated', $data['field_recently_updated_export']);
        $user->set('field_shirt_size_from', $data['field_shirt_size_from_export']);
        $user->set('field_shirt_size_to', $data['field_shirt_size_to_export']);
        $user->set('field_shoe_size_from', $data['field_shoe_size_from_export']);
        $user->set('field_shoe_size_to', $data['field_shoe_size_to_export']);
        $user->set('field_skills', $data['field_skills_export']);
        $user->set('field_sports_and_hobby', $data['field_sports_and_hobby_export']);
        $user->set('field_suit_size_from', $data['field_suit_size_from_export']);
        $user->set('field_suit_size_to', $data['field_suit_size_to_export']);
        $user->set('field_telephone', $data['field_telephone_export']);
        $user->set('field_weight', $data['field_weight_export']);
        $user->set('field_zipcode', $data['field_zipcode_export']);
        $user->save();
        $response['message'] = 'update success';
      }
      else{
        $user = User::create([
          'name'=> $data['name_export'],
          'mail'=> $data['name_export'],
          'pass'=> 'pass@123'
        ]);
        $user->set('field_about_me', $data['field_about_me_export']);
        $user->set('field_address', $data['field_address_export']);
        $user->set('field_agreed_to_terms', $data['field_agreed_to_terms_export']);
        // $user->set('field_birthday', $data['']);
        $user->set('field_bra_size', $data['field_bra_size_export']);
        $user->set('field_bureau', $data['field_bureau_export']);
        $user->set('field_category', $data['field_category_export']);
        $user->set('field_cellphone', $data['field_cellphone_export']);
        $user->set('field_city', $data['field_city_export']);
        $user->set('field_country', $data['field_country_export']);
        $user->set('field_dialect_one', $data['field_dialect_one_export']);
        $user->set('field_dialect_two', $data['field_dialect_two_export']);
        $user->set('field_dialect_three', $data['field_dialect_three_export']);
        $user->set('field_ethnic_origin', $data['field_ethnic_origin_export']);
        $user->set('field_eye_color', $data['field_eye_color_export']);
        $user->set('field_fax', $data['field_fax_export']);
        $user->set('field_first_name', $data['field_first_name_export']);
        $user->set('field_gender', $data['field_gender_export']);
        $user->set('field_hair_color', $data['field_hair_color_export']);
        $user->set('field_height', $data['field_height_export']);
        $user->set('field_language_four', $data['field_language_four_export']);
        $user->set('field_language_four_rating', $data['field_language_four_rating_export']);
        $user->set('field_language_one', $data['field_language_one_export']);
        $user->set('field_language_one_rating', $data['field_language_one_rating_export']);
        $user->set('field_language_three', $data['field_language_three_export']);
        $user->set('field_language_three_rating', $data['field_language_three_rating_export']);
        $user->set('field_language_two', $data['field_language_two_export']);
        $user->set('field_language_two_rating', $data['field_language_two_rating_export']);
        $user->set('field_last_name', $data['field_last_name_export']);
        $user->set('field_licenses', $data['field_licenses_export']);
        $user->set('field_nationality', $data['field_nationality_export']);
        $user->set('field_new_from', $data['field_new_from_export']);
        $user->set('field_new_profile', $data['field_new_profile_export']);
        $user->set('field_new_until', $data['field_new_until_export']);
        $user->set('field_occupation', $data['field_occupation_export']);
        $user->set('field_old_profile_id', $data['field_old_profile_id_export']);
        $user->set('field_pant_size_from', $data['field_pant_size_from_export']);
        $user->set('field_pant_size_to', $data['field_pant_size_to_export']);
        $user->set('field_profile_number', $data['field_profile_number_export']);
        $user->set('field_profile_status', $data['field_profile_status_export']);
        $user->set('field_profile_type', $data['field_profile_type_export']);
        $user->set('field_recently_updated', $data['field_recently_updated_export']);
        $user->set('field_shirt_size_from', $data['field_shirt_size_from_export']);
        $user->set('field_shirt_size_to', $data['field_shirt_size_to_export']);
        $user->set('field_shoe_size_from', $data['field_shoe_size_from_export']);
        $user->set('field_shoe_size_to', $data['field_shoe_size_to_export']);
        $user->set('field_skills', $data['field_skills_export']);
        $user->set('field_sports_and_hobby', $data['field_sports_and_hobby_export']);
        $user->set('field_suit_size_from', $data['field_suit_size_from_export']);
        $user->set('field_suit_size_to', $data['field_suit_size_to_export']);
        $user->set('field_telephone', $data['field_telephone_export']);
        $user->set('field_weight', $data['field_weight_export']);
        $user->set('field_zipcode', $data['field_zipcode_export']);
        $user->addRole('model');
        $user->activate();
        $user->save();
        $response['message'] = 'create success';
      }
    }
    else{
      $response['error'] = 'supply json';
    }
    return new JsonResponse( $response );
  }

}
