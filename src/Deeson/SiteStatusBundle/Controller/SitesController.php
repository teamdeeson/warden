<?php

namespace Deeson\SiteStatusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SitesController extends Controller
{
  /**
   * Default action for listing the sites available.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function IndexAction() {
    $params = array(
      'sites' => $this->getSitesList(),
    );

    return $this->render('DeesonSiteStatusBundle:Sites:index.html.twig', $params);
  }

  /**
   * Show the detail of the specific site
   *
   * @param int $id
   *   The id of the site to view
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function DetailAction($id) {
    $site_data = $this->getSiteData($id);
    //printf('<pre>%s</pre>', print_r($site_data, true));

    //$data_request = Request::create($site_data->url . '/admin/reports/system_status/' . $site_data->system_status_token);
    //$data_request = Request::create('http://www.google.com');
    //$data_request = '{"system_status":"encrypted","data":"ZmhxdbNzjNQkVHNuz\/5qO+aw\/ssxA0olJCTbuLpNmvejsw\/1gMKnhV0EVl3eWbdNO+cKYk6j9cD1wWQLVOlM8p5TRd3I+B\/9VqLBJK7Ta9+4Q0X2CsknJn8Zi6FbWQfVM6pt4rw3P\/zKKl7GWh7Yp57S2MxnFvo2PQU5no6YZPhghtpUc8UcDzVabKMcp\/CACVChsjAw1tHciwvFArdWlvUB74IHvTgOlOu7\/pBSYmTqOSpq92AmMJv4iLsuLjy91ZP31a\/rGXDLu+Spy0+CWXwKBCn9u4sZ91UziLXhDBajZ+nK14IQ+5rmNvxZr6hQAim2SgsJPUKD6Px1WDBHCHQK9V5pxdMItWwMkws\/tJIn2BTCrNHgdeOk4CyvKa7w7G6CcrQ1tktuj+zGOrHxUhCO07mvjc1KjP0bjLYpx94AfufKa152j1O5pCHnRaAdhpUMnio6WhdZAH1KS3QN73znU3VkB8T8jOiTiqDrLr2vcrlqpYmuEO0TYaSggOeAie3JaRNgIJdj6GeR1bYFBRZUze59KE+qypQbgukUC6zzvTp6cg+d7iTJGalRc8SYhWTBzf1Bc9gazVvmLvFIduGeBVMXDrwduRNp2cMtQwSluA8nW+tdPrSQjbSo+jyOtLwm0O6PJCTv\/pXBmVPdtSOBlH\/P+EM4dcFC+hbFEOd9pcBKC6HlbSHkqLLGiRAIy74b\/ag3WaSJffMm6VgIILdjanS4643FuQVo5ey5rlU8c1FWIUve2PqAtBXg7sZAfS\/y0TutaY6PlUnsvLQtr\/eNgZh6oI9GdcOcxVT2imvzG4VeovEROM8CAygI0SOqFIvAYANQRb4yDSyWyZFL++aY49EuNwSJPosqvviK6NyoSThH5VjspNtnoUZzxkNPWz+veeXN44Q+z5mtZb4iXKY3zqPgWhuiWOvc4MiiT7Cqi2GNdNRyKyu6te+HlDX4nk1flh7m7HLdaWRE+9\/7ebz+oP+tcM3BQmE\/tnp5COYMRusYnmt75TofScJtM31RCpbwWlbXJw3gzgVbezprXk4RaQiLmvhh6SwHo30YjEwDaagFyRYjMEcZQWGd+gq5XSee5oGNzMCpcoHV6nQJxovN6NagXqU7sRWofgdLtFlBVyoyr1Az1y4ghum7\/3Nrppv1O389rY2CJ9Y1PnLVqwzuO0BxFKxQZbHMRvRfctjZuCVQbYXN8rCXPjMnmJKzypM0GvYi5Z57TiytgAD6efRVVN34rMTsbHQEjKFU8DS8008gtlILrhWkwNwunc4UW+CEwhf38sc1Fqp7+KDZ46S+96OBcfrDjQLUMw2snyZpUxNaokYGbUfiEoApqIUW7rf2awKXD0gRYEiFG91sM3NLFz1UJ+u4gu8zjRdVVf3wZd\/eb6STkvrx+8VoVdrcfCeoT7fLZetgkLK33xZPwC\/TrwX5K7Gg734SBGglt8PGS5RKRjzFePVw\/Q4+ytLZ997qEeCuwWcg5i1+SN2\/GlWbg1z6EAEvxYIGUI22twynF3cGgy2VyRnAhT2yO2DwkRJ\/3TUsBO4PWwEGNDtX03yrxYHNY7wBYfg17LNU292e+tAjoqjqJsH+dquwty9mZLqBmYhtoLRbs1gSYgui9epaSWMZCeW74deBc7doR6tNgQ5+RTr8tin2PjZLLJgBss1VmgnfTOm\/UyU4DLK+eXyuS+fNzwKkSU08fgutlQN5VONs0fhTo5cVscXKc6u6uUGhmPhIDC6y0HUBScVcGjILy81riiI98iDEvnHPfWpcEforuPF5dB+lqXUNVbNLfbecT4VKTYL\/f36M+QYvxJ5xMrLHNNi+zpvgr2lFWYu0VEy+zus4NBaG5qVs467CHHWXqxyqzGX9d\/AL\/1r4mNHH1RcCbDFTxeUzRH+2Y59TpjUINENxk+1BOjxujOrZRTNINGb13DMXWkPiNWb0lX4LpW1Fl+oT5JhymoRh96X+WICb8Evuvwf\/\/XqZy17DNtQ4pry5AOVppS9lSTDUUEZqtuI7Wi5uSNq0po1ZFwnykD\/eH1dsoBGOmMoF1NWbESQVsH5aRLb85b3JoDlMUmpY\/GepAvGSX8HyH\/r\/5DI1JAhd07JKq42fKomBmY7hqevKbaPYXONwY0bRT2ujE3va810s3IZxuCUbe5xvpNkDE832XFmp2dGgymwZjc6KhO3FJLhT4x+z2oUDWpJupCukt6ny\/NMuw6V9eEay4Iiw0X9x1KgU8R9SbSIHDzrK5MOJRzLhKpQPWN84GlFx\/tPSa\/M2BRSbRssh8pptNkR2uAWN8H7edOGoWYNH17Y9QP84J3D+xKUA90FMDuj2ovDR\/Q88i+qsGut5+uvlJMSrxU\/LLLhd7M1Daha0qFv+9LGC0hzmGAHfKWZAZqXsYLiMSDgJ34rFceeCOPoxq9Z4FC08O1MGxz\/gKrZvAkG\/Jnq+4dpd+jHe5FiHIeLZFHNPe1awWxosGCv7O4bMX4lvkxagr2rmYicTl5ZedCr+WHowZ8VY7HwfD6gleAhtrvpmKRd2c4ME8APKgmsKdRd+g1VPTxoBQtp53qYWDrAG4uK6y+Ybm5hJQ7axutdBzgJgnuYfoARXD4rPNwQ+Z18fFyzkeZSdP8YtC5csSzpaQKJA2QTBv5xdHwtDcy1OtlKlZJ4I+qqsm2yEIipq8q76wBrPD49WbaNFK949hVRvcgwJU0AJmf6nleRI0JP\/mF28ceD5Oy4hOI7gB+tI45O0Uxr+LGeE85qH+UhevfLA0xpIPY3TW2H9bX4NPZpP1TmFUW99gk6W4qK0t7o97twtoY7AYU7n9ssXSd4UMPaULwN0tiGj+XufQ5njQy3gGjEfZMFUPZB3aqc8ENXqlaObQkAYhOgL799NRSbLeIB8mMLoasU8UygC1X4hPmeGgXIyGkTU00nF82NHTFXd9GpGFdJWOKC0zHTcS2CwL\/6nDMNH1OrufHg+8le4+kR8HWHZusxj\/NfWALWKizS5YuGFndxxBkXJvtzoGSE1Z9BEBjMl\/swwe56i9AG\/JEikoV4sCohyxa9Os8emU3Q4SGXlOYg+Ewb81R8s3M3yr1Y0qKGQY4Gv1ter03ZRNKmJ4OB4o96LVv9Z2tA8dGoyfknUeB6CL\/7KPjDbErnHplS4e4R8KqaEv3k1BZkqbd8bhiVV\/rSlssmmsPKvJ1dIRxr6VP32BPJZzdxQqXHFkGY4AkaOgGZVVXp34LZ0WSJvrL8mYkkTlJtj\/3tproYlLQ1h9xsPQu3EMaUXdicPKiKmRjenk7DtcGgoHSlGzPJY+suWkuG0BGxLTD7eiOFkgbGrfxzQVBWfNQCw9jsrUyK7abAijd8SDDl9VUwotnfTaTuNr+aTkA1D7M8GcF9XpmEhsGuC\/QDycyf9ls7zbU+c\/VGhvJ6cx30qblWxmRsT2agnxWVvNpYE1y5UJbnCwfdJWJCkv90Lp5Hu5XeoEQTVoVQWNtLNh7xYJW4P0YZv6QdjcG9I7Fp7pOYUjqZJFVe+bz+7hIjhucGxV9G842jpUL\/\/Vhu9bbL9Z9IuSwKBkg=="}';
    $data_request = '{"system_status":{"core":{"drupal":{"version":"7.26"}},"contrib":{"adminimal":{"version":"7.x-1.5"},"bootstrap":{"version":"7.x-3.0"},"strongarm":{"version":"7.x-2.0"},"masquerade":{"version":"7.x-1.0-rc5"},"administerusersbyrole":{"version":"7.x-1.0-beta1"},"angularjs":{"version":"7.x-1.3"},"apachesolr":{"version":"7.x-1.6"},"behat_testing":{"version":"7.x-1.0-beta6"},"cdn":{"version":"7.x-2.6"},"ckeditor":{"version":"7.x-1.13"},"coder":{"version":"7.x-2.1"},"coffee":{"version":"7.x-2.0"},"context":{"version":"7.x-3.2"},"ctools":{"version":"7.x-1.3"},"date":{"version":"7.x-2.7"},"diff":{"version":"7.x-3.2"},"ds":{"version":"7.x-2.6"},"eck":{"version":"7.x-2.0-rc2"},"email":{"version":"7.x-1.2"},"entity":{"version":"7.x-1.3"},"entitycache":{"version":"7.x-1.2"},"entityreference":{"version":"7.x-1.1"},"facetapi":{"version":"7.x-1.3"},"facetapi_taxonomy_sort":{"version":"7.x-1.0-beta1"},"features_override":{"version":"7.x-2.0-rc1"},"feeds":{"version":"7.x-2.0-alpha8"},"feeds_jsonpath_parser":{"version":"7.x-1.0-beta2"},"field_collection":{"version":"7.x-1.0-beta5"},"file_entity":{"version":"7.x-2.0-alpha3"},"geofield":{"version":"7.x-2.1"},"geophp":{"version":"7.x-1.7"},"globalredirect":{"version":"7.x-1.5"},"google_analytics":{"version":"7.x-2.x-dev"},"inline_entity_form":{"version":"7.x-1.5"},"job_scheduler":{"version":"7.x-2.0-alpha3"},"jquery_update":{"version":"7.x-2.3"},"libraries":{"version":"7.x-2.1"},"link":{"version":"7.x-1.2"},"master":{"version":"7.x-2.0-beta3"},"media":{"version":"7.x-2.0-alpha3"},"metatag":{"version":"7.x-1.0-beta9"},"migrate":{"version":"7.x-2.6-rc1"},"module_filter":{"version":"7.x-1.8"},"rabbit_hole":{"version":"7.x-2.22"},"redirect":{"version":"7.x-1.0-rc1"},"reroute_email":{"version":"7.x-1.1"},"respondjs":{"version":"7.x-1.2"},"restws":{"version":"7.x-2.1"},"restws_search_api":{"version":"7.x-1.1"},"search_api":{"version":"7.x-1.12"},"seckit":{"version":"7.x-1.8"},"securepages":{"version":"7.x-1.0-beta2"},"site_verify":{"version":"7.x-1.0"},"styleguide":{"version":"7.x-1.1"},"system_status":{"version":"7.x-2.7"},"token":{"version":"7.x-1.5"},"token_tweaks":{"version":"7.x-1.x-dev"},"variable":{"version":"7.x-2.4"},"views_bulk_operations":{"version":"7.x-3.2"},"views_geojson":{"version":"7.x-1.0-alpha2"},"views":{"version":"7.x-3.7"},"wsif":{"version":"7.x-1.0-rc3"},"xmlsitemap":{"version":"7.x-2.0-rc2"},"field_group":{"version":"7.x-1.3"},"pathauto":{"version":"7.x-1.2"},"admin_views":{"version":"7.x-1.2"},"features":{"version":"7.x-2.0"},"rules":{"version":"7.x-2.6"},"devel":{"version":"7.x-1.3"},"admin_menu":{"version":"7.x-3.0-rc4"}},"custom":"disabled"}}';
    printf('<pre>%s</pre>', print_r($data_request, true));
    $data_request_object = json_decode($data_request);
    printf('<pre>%s</pre>', print_r($data_request_object, true));
    //$module_data = $this->decrypt($data_request_object->data, $site_data->system_status_encrypt_token);
//printf('<pre>%s</pre>', print_r($module_data, true));

    $params = array(
      'site' => $site_data,
    );

    return $this->render('DeesonSiteStatusBundle:Sites:detail.html.twig', $params);
  }

  /**
   * Add a new site to the system.
   *
   * @param Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function AddAction(Request $request) {
    $query_site_url = $request->query->get('siteUrl');
    //printf('<pre>%s</pre>', print_r($query_site_url, true));
    $site_data = explode('|', $query_site_url);

    $site_list = $this->getSitesList();
    //printf('<pre>%s</pre>', print_r($site_list, true));

    $site_status_details = array(
      'id' => count($site_list) + 1,
      'url' => $site_data[0],
      'system_status_token' => $site_data[1],
      'system_status_encrypt_token' => $site_data[2],
      'core_version' => '7.' . rand(22, 31),
    );
    //printf('<pre>%s</pre>', print_r($site_status_details, true));

    $site_list[] = $site_status_details;

    //file_put_contents('site-list.json', json_encode($site_list));
    $this->updateSiteList($site_list);
    //die();
    return $this->redirect('/sites');
  }

  /**
   * Get the list of sites.
   *
   * @return mixed
   */
  protected function getSitesList() {
    return json_decode(file_get_contents('site-list.json'));
  }

  /**
   * Update the list of sites.
   *
   * @param $site_data
   */
  protected function updateSiteList($site_data) {
    file_put_contents('site-list.json', json_encode($site_data));
  }

  /**
   * Get the specific site data.
   *
   * @param int $id
   *   The site id.
   *
   * @return mixed
   */
  protected function getSiteData($id) {
    $site_list = $this->getSitesList();
    return $site_list[$id - 1];
  }



  /**
   * System Status: decrypt an encrypted message.
   */
  protected function decrypt($ciphertext_base64, $encrypt_token) {
    /*$key = hash("SHA256", variable_get('system_status_encrypt_token', 'Error-no-token'), TRUE);

    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

    $plaintext_utf8 = utf8_encode($plaintext);

    $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $plaintext_utf8, MCRYPT_MODE_CBC, $iv);

    $ciphertext = $iv . $ciphertext;

    return base64_encode($ciphertext);*/

    $key = hash("SHA256", $encrypt_token, TRUE);

    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);

    $ciphertext_dec = base64_decode($ciphertext_base64);

    $iv_dec = substr($ciphertext_dec, 0, $iv_size);

    $ciphertext_dec = substr($ciphertext_dec, $iv_size);

    $plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);

    return $plaintext_dec;
    //return utf8_decode($plaintext_dec);
  }
}
