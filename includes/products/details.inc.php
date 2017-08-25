<?php
@include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/path.inc.php';
@include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/json.inc.php';

require_once dirname(__FILE__) . "/redirect_destination.inc.php";
require_once dirname(__FILE__) . "/../lib/chromephp/ChromePhp.php";

$json_files = array("firefox" => "product_details_json/firefox_versions.json",
                    "thunderbird" => "product_details_json/thunderbird_versions.json",
                    "android" => "product_details_json/mobile_details.json");

function extract_version_information_from_details_json($product, $data){
    return array(
        "release" => $data->version,
        "beta" => $data->beta_version,
        "alpha" => $data->alpha_version
    );
}

function project_attribute_from($data, $attribute, $default){
  if(property_exists($data, $attribute)){
    return $data->$attribute;
  }
  return $default;
}

function extract_thunderbird_version_information_from_versions_json($data){
    return array(
        "release" => $data->LATEST_THUNDERBIRD_VERSION,
        "beta" => $data->LATEST_THUNDERBIRD_DEVEL_VERSION,
        "alpha" => $data->LATEST_THUNDERBIRD_ALPHA_VERSION,
    );
}

function extract_firefox_version_information_from_versions_json($data){
    $product = "FIREFOX";

    $release = "LATEST_" . $product . "_VERSION";
    $beta = "LATEST_" . $product . "_RELEASED_DEVEL_VERSION";
    $alpha = $product . "_AURORA";
    $esr = $product . "_ESR";
    $esr_next = $product . "_ESR_NEXT";

    return array(
      "release" => project_attribute_from($data, $release, ""),
      "beta" => project_attribute_from($data, $beta, ""),
      "alpha" => project_attribute_from($data, $alpha, ""),
      "esr" => str_replace("esr", "", project_attribute_from($data, $esr, "")),
      "esr_next" => str_replace("esr", "", project_attribute_from($data, $esr_next, ""))
    );
}

function load_version_information($product, $filename){
  $data = load_json(MJ\Path\expand_path($filename, dirname(__FILE__)));
  if(preg_match("/_details.json$/", $filename)){
    return extract_version_information_from_details_json($product, $data);
  }else if($product == "firefox"){
    return extract_firefox_version_information_from_versions_json($data);
  }else{
    return extract_thunderbird_version_information_from_versions_json($data);
  }
}

function load_product_details($file_list){
    $result = array();
    foreach($file_list as $product => $file){
        $result[$product] = load_version_information($product, $file);
    }
    return $result;
}

function define_constants_by_product_channel($product, $channel, $version){
    $product_prefix = strtoupper($product);
    $channel = strtoupper($channel);

    $name_version = $product_prefix . "_" . $channel . "_VERSION";
    $name_major_version = $product_prefix . "_" . $channel . "_MAJOR_VERSION";
    $name_major_version_num = $product_prefix . "_" . $channel . "_MAJOR_VERSION_NUM";

    define("$name_version", $version);
    define("$name_major_version_num", intval($version));
    define("$name_major_version", strval(constant($name_major_version_num)) . ".0");
}

function define_constants_of_esr_versions($product, $channels, $details){
    $index = array_search("esr_release", $channels);
    if($index){
        $release_version = $details[$product]["esr"];
        $previous_version = "";
        if($details[$product]["esr_next"]){
            $previous_version = $release_version;
            $release_version = $details[$product]["esr_next"];
        }
        define_constants_by_product_channel($product, "esr_release", $release_version);
        define_constants_by_product_channel($product, "esr_prev_release", $previous_version);
        unset($channels[$index]);
    }
    return array_values($channels);
}

function define_product_details_of($product, $channels, $details){
    $product_prefix = strtoupper($product);
    $channels = define_constants_of_esr_versions($product, $channels, $details);
    foreach($channels as $channel){
        define_constants_by_product_channel($product, $channel, $details[$product][$channel]);
    }
}

function define_product_details($file_list){
    $json = load_product_details($file_list);
    define_product_details_of("firefox", array("release", "beta", "alpha", "esr_release"), $json);
    define_product_details_of("android", array("release", "beta", "alpha"), $json);
    define_product_details_of("thunderbird", array("release", "beta", "alpha"), $json);
}

define_product_details($json_files);

class Product {}

/*
 * 注：以下の Firefox など子クラス内で宣言された定数は、new Firefox() でインスタンスが
 * 生成されても、親クラス Product で定義された関数内では self::FOO_BAR のような形で
 * 呼び出せない (変数は $this->foo_bar で呼び出せる)。そのため static::FOO_BAR とする。
 * これは遅延静的束縛と呼ばれる機能で PHP 5.3.0 が必要。
 * http://www.php.net/manual/ja/language.oop5.late-static-bindings.php
 */

class Firefox extends Product{
  const NAME  = 'firefox';
  const LABEL = 'Firefox';

  const RELEASE_VERSION                 = FIREFOX_RELEASE_VERSION;
  const RELEASE_MAJOR_VERSION           = FIREFOX_RELEASE_MAJOR_VERSION;
  const RELEASE_MAJOR_VERSION_NUM       = FIREFOX_RELEASE_MAJOR_VERSION_NUM;
  const BETA_VERSION                    = FIREFOX_BETA_VERSION;
  const BETA_MAJOR_VERSION              = FIREFOX_BETA_MAJOR_VERSION;
  const BETA_MAJOR_VERSION_NUM          = FIREFOX_BETA_MAJOR_VERSION_NUM;
  const ALPHA_VERSION                   = FIREFOX_ALPHA_VERSION;
  const ALPHA_MAJOR_VERSION             = FIREFOX_ALPHA_MAJOR_VERSION;
  const ALPHA_MAJOR_VERSION_NUM         = FIREFOX_ALPHA_MAJOR_VERSION_NUM;

  const ESR_PREV_RELEASE_VERSION           = FIREFOX_ESR_PREV_RELEASE_VERSION;
  const ESR_PREV_RELEASE_MAJOR_VERSION     = FIREFOX_ESR_PREV_RELEASE_MAJOR_VERSION;
  const ESR_PREV_RELEASE_MAJOR_VERSION_NUM = FIREFOX_ESR_PREV_RELEASE_MAJOR_VERSION_NUM;
  const ESR_RELEASE_VERSION                = FIREFOX_ESR_RELEASE_VERSION;
  const ESR_RELEASE_MAJOR_VERSION          = FIREFOX_ESR_RELEASE_MAJOR_VERSION;
  const ESR_RELEASE_MAJOR_VERSION_NUM      = FIREFOX_ESR_RELEASE_MAJOR_VERSION_NUM;

  const ALPHA_NAME            = 'aurora';
  const ALPHA_LABEL           = 'Aurora';
  const ALPHA_FTP_DIR         = 'https://releases.mozilla.org/pub/mozilla.org/firefox/nightly/latest-mozilla-aurora/';
  const ALPHA_FTP_L10N_DIR    = 'https://releases.mozilla.org/pub/mozilla.org/firefox/nightly/latest-mozilla-aurora-l10n/';

  const NUMBER_OF_LOCALES     = 85;
  const NUMBER_OF_EXTENSIONS  = 8000;
  const NUMBER_OF_LOCALIZED_EXTENSIONS  = 950;
  const NUMBER_OF_PERSONAS = 300000;

  /*
   * 製品の定義
   * プロパティの宣言ではオブジェクトを設定できないので、ここで行う
   */
  public function __construct()
  {
    $this->major_versions = array(
      self::RELEASE_MAJOR_VERSION_NUM => (object) array(
        'supported_systems' => array(
          'win' => array('xp', 'svr2003', 'vista', '7', '8'),
          'mac' => array('10.x', '10.6', '10.7', '10.8', '10.9', '10.10'),
          'linux' => array('unknown')
        ),
        'locales' => array(
          'release' => array('ja','en-US',
            'af','ar','as','ast','be','bg','bn-BD','bn-IN','br','bs','ca','cs','csb','cy','da','de',
            'el','en-GB','en-ZA','eo','es-AR','es-CL','es-ES','es-MX','et','eu',
            'fa','fi','fr','fy-NL','ga-IE','gd','gl','gu-IN',
            'he','hi-IN','hr','hu','hy-AM','id','is','it','km','kn','ko','lij','lt','lv','mk','ml','mn','mr','nb-NO','nl','nn-NO',
            'or','pa-IN','pl','pt-BR','pt-PT','rm','ro','ru','si','sk','sl','sq','sr','sv-SE','sw','te','th','tr','uk',
            'vi','zh-CN','zh-TW'),
          'beta' => array('ak','kk','ku','lg','mai','nso','son','sw','ta','ta-LK','zu')
        )
      ),
      self::BETA_MAJOR_VERSION_NUM => (object) array(
        'supported_systems' => array(
          'win' => array('xp', 'svr2003', 'vista', '7', '8'),
          'mac' => array('10.x', '10.6', '10.7', '10.8', '10.9', '10.10'),
          'linux' => array('unknown')
        ),
        'locales' => array(
          'release' => array('ja','en-US',
            'af','ar','as','ast','be','bg','bn-BD','bn-IN','br','ca','csb','cs','cy','da','de',
            'el','en-GB','en-ZA','eo','es-AR','es-CL','es-ES','es-MX','et','eu',
            'fa','ff','fi','fr','fy-NL','ga-IE','gd','gl','gu-IN',
            'he','hi-IN','hr','hu','hy-AM','id','is','it','km','kn','ko','lij','lt','lv','mk','ml','mn','mr','nb-NO','nl','nn-NO',
            'or','pa-IN','pl','pt-BR','pt-PT','rm','ro','ru','si','sk','sl','sq','sr','sv-SE','sw','te','th','tr','uk',
            'vi','zh-CN','zh-TW'),
          'beta' => array('ak','bs','kk','ku','lg','mai','nso','son','sw','ta','ta-LK','zu')
        )
      ),
      self::ALPHA_MAJOR_VERSION_NUM => (object) array(
        'supported_systems' => array(
          'win' => array('xp', 'svr2003', 'vista', '7', '8'),
          'mac' => array('10.x', '10.6', '10.7', '10.8', '10.9', '10.10'),
          'linux' => array('unknown')
        ),
        'locales' => array(
          'release' => array('ja','en-US',
            'ach','af','ar','as','ast','be','bg','bn-BD','bn-IN','br','ca','csb','cs','cy','da','de',
            'el','en-GB','en-ZA','eo','es-AR','es-CL','es-ES','es-MX','et','eu',
            'fa','ff','fi','fr','fy-NL','ga-IE','gd','gl','gu-IN',
            'he','hi-IN','hr','hu','hy-AM','id','is','it','km','kn','ko','lij','lt','lv','mk','ml','mn','mr','nb-NO','nl','nn-NO',
            'or','pa-IN','pl','pt-BR','pt-PT','rm','ro','ru','si','sk','sl','sr','sq','sv-SE','sw','te','th','tr','uk',
            'vi','wo','zh-CN','zh-TW'),
          'beta' => array('ak','bs','kk','ku','lg','mai','nso','son','sw','ta','ta-LK','zu')
        )
      ),
      self::ESR_PREV_RELEASE_MAJOR_VERSION_NUM => (object) array(
        'supported_systems' => array(
          'win' => array('2000', 'xp', 'svr2003', 'vista', '7', '8'),
          'mac' => array('10.x', '10.6', '10.7', '10.8', '10.9', '10.10'),
          'linux' => array('unknown')
        ),
        'locales' => array(
          'release' => array('ja','en-US',
            'af','ar','as','ast','be','bg','bn-BD','bn-IN','br','bs','ca','cs','csb','cy','da','de',
            'el','en-GB','en-ZA','eo','es-AR','es-CL','es-ES','es-MX','et','eu',
            'fa','fi','fr','fy-NL','ga-IE','gd','gl','gu-IN',
            'he','hi-IN','hr','hu','hy-AM','id','is','it','kn','ko','lij','lt','lv','mk','ml','mn','mr','nb-NO','nl','nn-NO',
            'or','pa-IN','pl','pt-BR','pt-PT','rm','ro','ru','si','sk','sl','sq','sr','sv-SE','sw','te','th','tr','uk',
            'vi','zh-CN','zh-TW'),
          'beta' => array('ak','kk','ku','lg','mai','nso','son','sw','ta','ta-LK','zu')
        )
      ),
      // http://viewvc.svn.mozilla.org/vc/projects/mozilla.com/trunk/en-US/firefox/organizations/all.html?view=markup
      self::ESR_RELEASE_MAJOR_VERSION_NUM => (object) array(
        'supported_systems' => array(
          'win' => array('xp', 'svr2003', 'vista', '7', '8'),
          'mac' => array('10.x', '10.6', '10.7', '10.8', '10.9', '10.10'),
          'linux' => array('unknown')
        ),
        'locales' => array(
          'release' => array('ja','en-US',
            'af', 'ar', 'as', 'ast', 'be', 'bg', 'bn-BD', 'bn-IN', 'br', 'bs', 'ca', 'cs', 'csb', 'cy', 'da', 'de', 'el', 'en-GB', 'en-ZA', 'eo', 'es-AR', 'es-CL', 'es-ES', 'es-MX', 'et', 'eu', 'fa', 'fi', 'fr', 'fy-NL', 'ga-IE', 'gd', 'gl', 'gu-IN', 'he', 'hi-IN', 'hr', 'hu', 'hy-AM', 'id', 'is', 'it', 'kn', 'ko', 'lt', 'lv', 'mai', 'mk', 'ml', 'mr', 'nb-NO', 'nl', 'nn-NO', 'or', 'pa-IN', 'pl', 'pt-BR', 'pt-PT', 'rm', 'ro', 'ru', 'si', 'sk', 'sl', 'sq', 'sr', 'sv-SE', 'te', 'th', 'tr', 'uk', 'vi', 'zh-CN', 'zh-TW'),
          'beta' => array()
        )
      )
    );
  }
}

class FirefoxAndroid extends Product{
  const NAME  = 'fxandroid';
  const LABEL = 'Android 版 Firefox';

  const RELEASE_VERSION                 = ANDROID_RELEASE_VERSION;
  const RELEASE_MAJOR_VERSION           = ANDROID_RELEASE_MAJOR_VERSION;
  const RELEASE_MAJOR_VERSION_NUM       = ANDROID_RELEASE_MAJOR_VERSION_NUM;
  const BETA_VERSION                    = ANDROID_BETA_VERSION;
  const BETA_MAJOR_VERSION              = ANDROID_BETA_MAJOR_VERSION;
  const BETA_MAJOR_VERSION_NUM          = ANDROID_BETA_MAJOR_VERSION_NUM;
  const ALPHA_VERSION                   = ANDROID_ALPHA_VERSION;
  const ALPHA_MAJOR_VERSION             = ANDROID_ALPHA_MAJOR_VERSION;
  const ALPHA_MAJOR_VERSION_NUM         = ANDROID_ALPHA_MAJOR_VERSION_NUM;

  const APP_STORE_URL         = 'https://play.google.com/store/apps/details?id=org.mozilla.firefox';
  const APP_STORE_BETA_URL    = 'https://play.google.com/store/apps/details?id=org.mozilla.firefox_beta';
  const ALPHA_FTP_DIR         = 'https://releases.mozilla.org/pub/mozilla.org/mobile/nightly/latest-mozilla-aurora-android/';
  const ALPHA_FTP_L10N_DIR    = 'https://releases.mozilla.org/pub/mozilla.org/mobile/nightly/latest-mozilla-aurora-android-l10n/';

  const NUMBER_OF_LOCALES     = 10;
}

class FirefoxIOS extends Product{
    const NAME = "fxios";
    const LABEL = "iOS 版 Firefox";

    const RELEASE_VERSION = "5.0";
    const RELEASE_MAJOR_VERSION_NUM = 5.0;

    const BASEDIR = "/firefox/ios";

    private static $PATTERNS;

    const MOZORG = "https://www.mozilla.org/en-US/firefox/ios";

    private static $latest_release;

    private $_release_version;

    protected function __construct($release_version=self::RELEASE_VERSION){
        $this->_release_version = $release_version;
    }

    public function version($channel="release", $in_number=false){
        if($in_number){
            return floatval($this_release_version);
        }else{
            return $this->_release_version;
        }
    }

    public function releasenotes($channel="release"){
        return self::BASEDIR . "/" . $this->version() . '/releasenotes/';
    }

    protected function releasenotes_dir(){
        return self::BASEDIR . "/releases/" .
                             intval($this->version());
    }

    protected function releasenotes_actual_path(){
        return $this->releasenotes_dir() . "/releasenotes-" . $this->version() . ".html";
    }

    protected function releasenotes_actual_file(){
        return $_SERVER['DOCUMENT_ROOT'] . $this->releasenotes_actual_path();
    }

    protected function requirements_actual_path(){
        return $this->releasenotes_dir() . "/system-requirements.html";
    }

    protected function requirements_actual_file(){
        return $_SERVER['DOCUMENT_ROOT'] . $this->requirements_actual_path();
    }

    protected function equivalent_version_in_mozorg(){
        return self::MOZORG . "/" . $this->version();
    }

    protected function releasenotes_in_english(){
        return $this->equivalent_version_in_mozorg() . "/releasenotes/";
    }

    protected function requirements_in_english(){
        return $this->equivalent_version_in_mozorg() . "/system-requirements/";
    }

    public static function get_latest_release(){
        if(!isset(static::$latest_release)){
            static::$latest_release = new FirefoxIOS();
        }
        return static::$latest_release;
    }

    public static function resolve_releasenotes_path($url){
        $result = null;

        if(!isset(static::$PATTERNS)){
            static::$PATTERNS = self::initializePattenrs();
        }

        if(preg_match(static::$PATTERNS["latest_releasenotes"], $url)){
            $path = self::get_latest_release()->releasenotes();
            $result = new RedirectDestination($path);
        }
        if(preg_match(static::$PATTERNS["releasenotes"], $url, $matches)){
            $release = new FirefoxIOS($matches[1]);
            if(file_exists($release->releasenotes_actual_file())){
                $path = $release->releasenotes_actual_path();
                $result = new RedirectDestination($path, true);
            }else{
                $result = new RedirectDestination($release->releasenotes_in_english());
            }
        }
        if(preg_match(static::$PATTERNS["requirements"], $url, $matches)){
            $release = new FirefoxIOS($matches[1]);
            if(file_exists($release->requirements_actual_file())){
                $path = $release->requirements_actual_path();
                $result = new RedirectDestination($path, true);
            }else{
                $result = new RedirectDestination($release->requirements_in_english());
            }
        }
        return $result;
    }
    private static function initializePattenrs(){
	static::$PATTERNS = array(
	  "latest_releasenotes" => "#^" . self::BASEDIR . "/notes/?#",
          "releasenotes" => "#^" . self::BASEDIR . "/(\d+(\.\d+)*)/releasenotes/?$#",
          "requirements" => "#^" . self::BASEDIR . "/(\d+(\.\d+)+)/system-requirements/?$#"
 	);
	return static::$PATTERNS;
    }
}

class Thunderbird extends Product{
  const NAME  = 'thunderbird';
  const LABEL = 'Thunderbird';

  const RELEASE_VERSION                 = THUNDERBIRD_RELEASE_VERSION;
  const RELEASE_MAJOR_VERSION           = THUNDERBIRD_RELEASE_MAJOR_VERSION;
  const RELEASE_MAJOR_VERSION_NUM       = THUNDERBIRD_RELEASE_MAJOR_VERSION_NUM;
  const BETA_VERSION                    = THUNDERBIRD_BETA_VERSION;
  const BETA_MAJOR_VERSION              = THUNDERBIRD_BETA_MAJOR_VERSION;
  const BETA_MAJOR_VERSION_NUM          = THUNDERBIRD_BETA_MAJOR_VERSION_NUM;
  const ALPHA_VERSION                   = THUNDERBIRD_ALPHA_VERSION;
  const ALPHA_MAJOR_VERSION             = THUNDERBIRD_ALPHA_MAJOR_VERSION_NUM;
  const ALPHA_MAJOR_VERSION_NUM         = THUNDERBIRD_ALPHA_VERSION;

  const ESR_PREV_RELEASE_VERSION           = '31.7.0';
  const ESR_PREV_RELEASE_MAJOR_VERSION     = '31.0';
  const ESR_PREV_RELEASE_MAJOR_VERSION_NUM =  31;
  const ESR_RELEASE_VERSION                = Thunderbird::RELEASE_VERSION;
  const ESR_RELEASE_MAJOR_VERSION          = Thunderbird::RELEASE_MAJOR_VERSION;
  const ESR_RELEASE_MAJOR_VERSION_NUM      = Thunderbird::RELEASE_MAJOR_VERSION_NUM;

  const ALPHA_NAME            = 'earlybird';
  const ALPHA_LABEL           = 'Earlybird';
  const ALPHA_FTP_DIR         = 'https://releases.mozilla.org/pub/mozilla.org/thunderbird/nightly/latest-earlybird/';
  const ALPHA_FTP_L10N_DIR    = 'https://releases.mozilla.org/pub/mozilla.org/thunderbird/nightly/latest-earlybird-l10n/';

  const NUMBER_OF_LOCALES     = 50;

  public function __construct()
  {
    $this->major_versions = array(
      self::RELEASE_MAJOR_VERSION_NUM => (object) array(
        'supported_systems' => array(
          'win' => array('xp', 'svr2003', 'vista', '7', '8'),
          'mac' => array('10.x', '10.6', '10.7', '10.8', '10.9', '10.10'),
          'linux' => array('unknown')
        ),
        'locales' => array(
          'release' => array('ja','en-US',
            'af','ar','be','bn-BD','br','ca','cs','da','de',
            'el','en-GB','es-AR','es-ES','et','eu',
            'fi','fr','fy-NL','ga-IE','gd','gl',
            'he','hu','id','is','it','ka','ko','lt','nb-NO','nl','nn-NO',
            'pa-IN','pl','pt-BR','pt-PT','rm','ro','ru','si','sk','sl','sq','sr','sv-SE','ta-LK','tr','uk',
            'vi','zh-CN','zh-TW'),
          'beta' => array('ast','hr','hy-AM')
        )
      ),
      self::BETA_MAJOR_VERSION_NUM => (object) array(
        'supported_systems' => array(
          'win' => array('xp', 'svr2003', 'vista', '7', '8'),
          'mac' => array('10.x', '10.6', '10.7', '10.8', '10.9', '10.10'),
          'linux' => array('unknown')
        ),
        'locales' => array(
          'release' => array('ja','en-US',
            'af','ar','be','bn-BD','br','ca','cs','da','de',
            'el','en-GB','es-AR','es-ES','et','eu',
            'fi','fr','fy-NL','ga-IE','gd','gl',
            'he','hr','hu','hy-AM','id','is','it','ka','ko','lt','nb-NO','nl','nn-NO',
            'pa-IN','pl','pt-BR','pt-PT','rm','ro','ru','si','sk','sl','sq','sr','sv-SE','ta-LK','tr','uk',
            'vi','zh-CN','zh-TW'),
          'beta' => array('ast')
        )
      ),
      self::ALPHA_MAJOR_VERSION_NUM => (object) array(
        'supported_systems' => array(
          'win' => array('xp', 'svr2003', 'vista', '7', '8'),
          'mac' => array('10.x', '10.6', '10.7', '10.8', '10.9', '10.10'),
          'linux' => array('unknown')
        ),
        'locales' => array(
          'release' => array('ja','en-US',
            'af','ar','be','bn-BD','br','ca','cs','da','de',
            'el','en-GB','es-AR','es-ES','et','eu',
            'fi','fr','fy-NL','ga-IE','gd','gl',
            'he','hr','hu','hy-AM','id','is','it','ka','ko','lt','nb-NO','nl','nn-NO',
            'pa-IN','pl','pt-BR','pt-PT','rm','ro','ru','si','sk','sl','sq','sr','sv-SE','ta-LK','tr','uk',
            'vi','zh-CN','zh-TW'),
          'beta' => array('ast')
        )
      ),
      self::ESR_PREV_RELEASE_MAJOR_VERSION_NUM => (object) array(
        'supported_systems' => array(
          'win' => array('2000', 'xp', 'svr2003', 'vista', '7', '8'),
          'mac' => array('10.x', '10.6', '10.7', '10.8', '10.9', '10.10'),
          'linux' => array('unknown')
        ),
        'locales' => array(
          'release' => array('ja','en-US',
            'af','ar','be','bn-BD','br','ca','cs','da','de',
            'el','en-GB','es-AR','es-ES','et','eu',
            'fi','fr','fy-NL','ga-IE','gd','gl',
            'he','hu','id','is','it','ka','ko','lt','nb-NO','nl','nn-NO',
            'pa-IN','pl','pt-BR','pt-PT','rm','ro','ru','si','sk','sl','sq','sr','sv-SE','ta-LK','tr','uk',
            'vi','zh-CN','zh-TW'),
          'beta' => array('ast','hr','hy-AM')
        )
      ),
      // http://viewvc.svn.mozilla.org/vc/projects/mozilla.org/branches/staging/thunderbird/includes/thunderbirdEsrBuildDetails.php?view=markup
      self::ESR_RELEASE_MAJOR_VERSION_NUM => (object) array(
        'supported_systems' => array(
          'win' => array('xp', 'svr2003', 'vista', '7', '8'),
          'mac' => array('10.x', '10.6', '10.7', '10.8', '10.9', '10.10'),
          'linux' => array('unknown')
        ),
        'locales' => array(
          'release' => array('ja','en-US',
            'ar', 'ast', 'be', 'bg', 'bn-BD', 'br', 'ca', 'cs', 'da', 'de', 'el', 'en-GB', 'es-AR', 'es-ES', 'et', 'eu', 'fi', 'fr', 'fy-NL', 'ga-IE', 'gd', 'gl', 'he', 'hr', 'hu', 'hy-AM', 'id', 'is', 'it', 'ko', 'lt', 'nb-NO', 'nl', 'nn-NO', 'pa-IN', 'pl', 'pt-BR', 'pt-PT', 'rm', 'ro', 'ru', 'si', 'sk', 'sl', 'sq', 'sr', 'sv-SE', 'ta-LK', 'tr', 'uk', 'vi', 'zh-CN', 'zh-TW'),
          'beta' => array()
        )
      )
    );
  }
}
