<?php
/**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author     PAYCOMET <info@paycomet.com>
*  @copyright  2019 PAYTPV ON LINE ENTIDAD DE PAGO S.L
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class PaytpvApi
{
    public function __construct()
    {
    }


    // main script, uses two other functions, getOsData() and getItemVersion() as needed
    // Optional $test_excludes is either null or one of the above values

    public function browserDetection($which_test, $test_excludes = '', $external_ua_string = '')
    {
        /**
        uncomment the global variable declaration if you want the variables to be available on
        a global level throughout your php page, make sure that php is configured to support
        the use of globals first!
        Use of globals should be avoided however, and they are not necessary with this script
        **/
        /*
        global $a_full_assoc_data, $a_mobile_data, $a_moz_data, $a_engine_data, $a_webkit_data, $b_dom_browser,
        $b_repeat, $b_safe_browser, $browser_name, $browser_number, $browser_math_number, $browser_user_agent,
        $browser_working, $html_type, $ie_version, $mobile_test, $moz_type_number, $moz_rv, $moz_rv_full,
        $moz_release_date, $moz_type, $os_number, $os_type, $layout_engine, $layout_engine_nu, $layout_engine_nu_full,
        $true_ie_number, $ua_type, $webkit_type, $webkit_type_number;
        */
        $this->scriptTime(); // set script timer to start timing
        
        static $a_full_assoc_data, $a_khtml_data, $a_mobile_data, $a_moz_data, $a_engine_data, $a_blink_data,
        $a_trident_data, $a_webkit_data, $b_dom_browser, $b_repeat, $b_safe_browser, $blink_type, $blink_type_number,
        $browser_name, $browser_number, $browser_math_number, $browser_user_agent, $browser_working, $html_type,
        $ie_version, $khtml_type, $khtml_type_number, $mobile_test, $moz_type_number, $moz_rv, $moz_rv_full,
        $moz_release_date, $moz_type, $os_number, $os_type, $layout_engine, $layout_engine_nu, $layout_engine_nu_full,
        $trident_type, $trident_type_number, $true_ie_number, $ua_type, $webkit_type, $webkit_type_number;
        
        // switch off the optimization for external ua string testing.
        if ($external_ua_string) {
            $b_repeat = false;
        }
        /**
        this makes the test only run once no matter how many times you call it since
        all the variables are filled on the first run through, it's only a matter of
        returning the the right ones
        **/
        if (!$b_repeat) {
            //initialize all variables with default values to prevent error
            $a_blink_data = '';
            // $a_browser_math_number = '';
            $a_full_assoc_data = '';
            $a_full_data = '';
            $a_khtml_data = '';
            $a_mobile_data = '';
            $a_moz_data = '';
            $a_os_data = '';
            $a_trident_data = '';
            // $a_unhandled_browser = '';
            $a_webkit_data = '';
            $b_dom_browser = false;
            $b_os_test = true;
            $b_mobile_test = true;
            $b_safe_browser = false;
            $b_success = false;// boolean for if browser found in main test
            $blink_type = '';
            $blink_type_number = '';
            $browser_math_number = '';
            $browser_temp = '';
            $browser_working = '';
            $browser_number = '';
            $html_type = '';
            // $html_type_browser_nu = '';
            $ie_version = '';
            $layout_engine = '';
            $layout_engine_nu = '';
            $layout_engine_nu_full = '';
            $khtml_type = '';
            $khtml_type_number = '';
            $mobile_test = '';
            $moz_release_date = '';
            $moz_rv = '';
            $moz_rv_full = '';
            $moz_type = '';
            $moz_type_number = '';
            $os_number = '';
            $os_type = '';
            $run_time = '';
            $trident_type = '';
            $trident_type_number = '';
            $true_ie_number = '';
            $ua_type = 'bot';// default to bot since you never know with bots
            $webkit_type = '';
            $webkit_type_number = '';
            // set the excludes if required
            if ($test_excludes) {
                switch ($test_excludes) {
                    case '1':
                        $b_os_test = false;
                        break;
                    case '2':
                        $b_mobile_test = false;
                        break;
                    case '3':
                        $b_os_test = false;
                        $b_mobile_test = false;
                        break;
                    default:
                        die('Error: bad $test_excludes parameter 2 used: ' . $test_excludes);
                        // break;
                }
            }
            /**
            make navigator user agent string lower case to make sure all versions get caught
            isset protects against blank user agent failure. tolower also lets the script use
            strstr instead of stristr, which drops overhead slightly.
            **/
            if ($external_ua_string) {
                $browser_user_agent = Tools::strtolower($external_ua_string);
            } elseif (isset($_SERVER['HTTP_USER_AGENT'])) {
                $browser_user_agent = Tools::strtolower($_SERVER['HTTP_USER_AGENT']);
            } else {
                $browser_user_agent = '';
            }
            /**
            pack the browser type array, in this order
            the order is important, because opera must be tested first, then omniweb [which has safari
            data in string], same for konqueror, then safari, then gecko, since safari navigator user
            agent id's with 'gecko' in string.
            Note that $b_dom_browser is set for all  modern dom browsers, this gives you a default to use.

            array[0] = id string for useragent, array[1] is if dom capable, array[2] is working name
            for browser, array[3] identifies navigator useragent type

            Note: all browser strings are in lower case to match the strtolower output, this avoids
            possible detection errors

            Note: These are the navigator user agent types:
            bro - modern, css supporting browser.
            bbro - basic browser, text only, table only, defective css implementation
            bot - search type spider
            dow - known download agent
            lib - standard http libraries
            mobile - handheld or mobile browser, set using $mobile_test
            **/
            // known browsers, list will be updated routinely, check back now and then
            $a_browser_types = array(
            array( 'edge', true, 'edge', 'bro' ),
            array( 'msie', true, 'ie', 'bro' ),
            array( 'trident', true, 'ie', 'bro' ),
            array( 'blink', true, 'blink', 'bro' ),
            // opera blink: OPR/<number>, no opera in ua
            array( 'opr/', true, 'blink', 'bro' ),
            array( 'vivaldi', true, 'blink', 'bro' ),
            // webkit before gecko because some webkit ua strings say: like gecko
            // and before khtml because some still use khtml with webkit
            array( 'webkit', true, 'webkit', 'bro' ),
            # note: 2013 sees opera moving to webkit, so needs to go after webkit
            array( 'opera', true, 'op', 'bro' ),
            // konq seems to be sticking with khtml still
            array( 'khtml', true, 'khtml', 'bro' ),
            // covers Netscape 6-7, K-Meleon, Most linux versions, uses moz array below
            array( 'gecko', true, 'moz', 'bro' ),
            array( 'netpositive', false, 'netp', 'bbro' ),// beos browser
            array( 'lynx', false, 'lynx', 'bbro' ), // command line browser
            array( 'elinks ', false, 'elinks', 'bbro' ), // new version of links
            array( 'elinks', false, 'elinks', 'bbro' ), // alternate id for it
            array( 'links2', false, 'links2', 'bbro' ), // alternate links version
            array( 'links ', false, 'links', 'bbro' ), // old name for links
            array( 'links', false, 'links', 'bbro' ), // alternate id for it
            array( 'w3m', false, 'w3m', 'bbro' ), // open source browser, more features than lynx/links
            array( 'webtv', false, 'webtv', 'bbro' ),// junk ms webtv
            array( 'amaya', false, 'amaya', 'bbro' ),// w3c browser
            array( 'dillo', false, 'dillo', 'bbro' ),// linux browser, basic table support
            array( 'ibrowse', false, 'ibrowse', 'bbro' ),// amiga browser
            array( 'icab', false, 'icab', 'bro' ),// mac browser
            array( 'crazy browser', true, 'ie', 'bro' ),// uses ie rendering engine
            
            // search engine spider bots, primary:
            array( 'answerbus', false, 'answerbus', 'bot' ),// http://www.answerbus.com/, web questions
            array( 'ask jeeves', false, 'ask', 'bot' ), //jeeves/teoma
            array( 'teoma', false, 'ask', 'bot' ),//jeeves teoma - leave in this order
            array( 'baiduspider', false, 'baidu', 'bot' ),// Baiduspider asian search spider
            array( 'bingbot', false, 'bing', 'bot' ),// bing
            array( 'boitho.com-dc', false, 'boitho', 'bot' ),//norwegian search engine
            array( 'exabot', false, 'exabot', 'bot' ),// exabot
            array( 'fast-webcrawler', false, 'fast', 'bot' ),// Fast AllTheWeb
            array( 'ia_archiver', false, 'ia_archiver', 'bot' ),// ia archiver
            array( 'googlebot', false, 'google', 'bot' ),// google
            array( 'google web preview', false, 'googlewp', 'bot' ),// google preview
            array( 'mediapartners-google', false, 'adsense', 'bot' ),// google adsense
            array( 'msnbot', false, 'msn', 'bot' ),// msn search
            array( 'objectssearch', false, 'objectsearch', 'bot' ),// open source search engine
            array( 'scooter', false, 'scooter', 'bot' ),// altavista
            // leave the yahoo/slurp bots in this order to get right detections
            array( 'yahoo-verticalcrawler', false, 'yahoo', 'bot' ),// old yahoo bot
            array( 'yahoo! slurp', false, 'yahoo', 'bot' ), // new yahoo bot
            array( 'yahoo-mm', false, 'yahoomm', 'bot' ), // gets Yahoo-MMCrawler and Yahoo-MMAudVid bots
            array( 'inktomi', false, 'inktomi', 'bot' ), // inktomi bot
            array( 'slurp', false, 'inktomi', 'bot' ), // inktomi bot
            array( 'zyborg', false, 'looksmart', 'bot' ),// looksmart
            
            // misc bots
            array( 'almaden', false, 'ibm', 'bot' ),// ibm almaden web crawler
            array( 'comodospider', false, 'comodospider', 'bot' ),
            array( 'facebookexternalhit', false, 'facebook', 'bot' ),
            array( 'facebot', false, 'facebook', 'bot' ),
            array( 'gigabot', false, 'gigabot', 'bot' ),// gigabot crawler
            array( 'iltrovatore-setaccio', false, 'il-set', 'bot' ),
            array( 'lexxebotr', false, 'lexxebotr', 'bot' ),
            array( 'magpie-crawlero', false, 'magpie-crawler', 'bot' ),
            array( 'naverbot', false, 'naverbot', 'bot' ),// naverbot crawler, bad bot, block
            array( 'omgilibot', false, 'omgilibot', 'bot' ),
            array( 'openbot', false, 'openbot', 'bot' ),// openbot, from taiwan
            array( 'psbot', false, 'psbot', 'bot' ),// psbot image crawler
            array( 'sogou', false, 'sogou', 'bot' ), // asian bot
            array( 'sosospider', false, 'sosospider', 'bot' ), // http://help.soso.com/webspider.htm
            array( 'sohu-search', false, 'sohu', 'bot' ),// chinese media company, search component
            array( 'surveybot', false, 'surveybot', 'bot' ),//
            array( 'vbseo', false, 'vbseo', 'bot' ),
            
            // various http utility libaries
            array( 'w3c_validator', false, 'w3c', 'lib' ), // uses libperl, make first
            array( 'wdg_validator', false, 'wdg', 'lib' ), //
            array( 'libwww-perl', false, 'libwww-perl', 'lib' ),
            array( 'jakarta commons-httpclient', false, 'jakarta', 'lib' ),
            array( 'python-urllib', false, 'python-urllib', 'lib' ),
            
            // download apps
            array( 'getright', false, 'getright', 'dow' ),
            array( 'wget', false, 'wget', 'dow' ),// open source downloader, obeys robots.txt
            
            // netscape 4 and earlier tests, put last so spiders don't get caught
            array( 'mozilla/4.', false, 'ns', 'bbro' ),
            array( 'mozilla/3.', false, 'ns', 'bbro' ),
            array( 'mozilla/2.', false, 'ns', 'bbro' )
            );
            
            //array( '', false ); // browser array template
            // note: not using this because chrome < 28 = webkit, >=28 == blink, so can't do normal handling
            // for now doing a case by case for layout engine
            $a_blink_types = array('opr/', 'vivaldi', 'chromium', 'chrome', 'blink');
            /**
            moz types array
            note the order, netscape6 must come before netscape, which  is how netscape 7 id's itself.
            rv comes last in case it is plain old mozilla. firefox/netscape/seamonkey need to be later
            Thanks to: http://www.zytrax.com/tech/web/firefox-history.html
            **/
            $a_gecko_types = array('bonecho', 'camino', 'conkeror', 'epiphany', 'fennec', 'firebird', 'flock',
            'galeon', 'iceape', 'icecat', 'k-meleon', 'minimo', 'multizilla', 'phoenix', 'skyfire', 'songbird',
            'swiftfox', 'seamonkey', 'shadowfox', 'shiretoko', 'iceweasel', 'firefox', 'minefield', 'netscape6',
            'netscape', 'rv' );
            
            $a_khtml_types = array( 'konqueror', 'khtml' );
            
            // note: trident only is up to msie 11, they have changed to edge with webkit though MSIE
            // browser remains present as an option but not default in windows 10
            $a_trident_types = array( 'ucbrowser', 'ucweb', 'msie' );
            
            /**
            webkit types, this is going to expand over time as webkit browsers spread
            konqueror is probably going to move to webkit, so this is preparing for that
            It will now default to khtml. gtklauncher is the temp id for epiphany, might
            change. Defaults to applewebkit, and will all show the webkit number.
            uc browsers are webkit need to be before safari; puffin before chrome
            IMPORTANT: microsoft edge is webkit ua:
            Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko)
            Chrome/42.0.2311.135 Safari/537.36 Edge/12.<OS build number>
            edge must come before chrome
            **/
            $a_webkit_types = array( 'arora', 'bolt', 'beamrise', 'chromium', 'puffin', 'chrome', 'crios', 'dooble',
            'epiphany', 'gtklauncher', 'icab', 'konqueror', 'maxthon',  'midori', 'omniweb', 'opera', 'qupzilla',
            'rekonq', 'rocketmelt', 'samsungbrowser', 'silk', 'uzbl', 'ucbrowser', 'ucweb', 'shiira', 'sputnik',
            'steel', 'teashark', 'safari',  'applewebkit', 'webos', 'xxxterm','vivaldi', 'webkit' );
            /**
            run through the browser_types array, break if you hit a match, if no match, assume old browser
            or non dom browser, assigns false value to $b_success.
            **/
            $i_count = count($a_browser_types);
            for ($i = 0; $i < $i_count; $i++) {
                //unpacks browser array, assigns to variables, need to not assign til found in string
                $browser_temp = $a_browser_types[$i][0];// text string to id browser from array
                if (strstr($browser_user_agent, $browser_temp)) {
                    /**
                    it defaults to true, will become false below if needed
                    this keeps it easier to keep track of what is safe, only
                    explicit false assignment will make it false.
                    **/
                    $b_safe_browser = true;
                    $browser_name = $browser_temp;// text string to id browser from array
                    // assign values based on match of user agent string
                    $b_dom_browser = $a_browser_types[$i][1];// hardcoded dom support from array
                    $browser_working = $a_browser_types[$i][2];// working name for browser
                    $ua_type = $a_browser_types[$i][3];// sets whether bot or browser
                    switch ($browser_working) {
                        // this is modified quite a bit, now will return proper netscape version number
                        // check your implementation to make sure it works
                        case 'ns':
                            $b_safe_browser = false;
                            $browser_number = $this->getItemVersion($browser_user_agent, 'mozilla');
                            break;
                        /**
                        note: webkit returns always the webkit version number, not the specific user
                        agent version, ie, webkit 583, not chrome 0.3
                        **/
                        case 'blink':
                            // to get full number
                            if ($browser_name == 'opr/') {
                                $this->getSetCount('set', 0);
                            }
                            $browser_number = $this->getItemVersion($browser_user_agent, $browser_name);
                            // assign rendering engine data
                            $layout_engine = 'blink';
                            if (strstr($browser_user_agent, 'blink')) {
                                $layout_engine_nu_full = $this->getItemVersion($browser_user_agent, 'blink');
                            } else {
                                $layout_engine_nu_full = $this->getItemVersion($browser_user_agent, 'webkit');
                            }
                            $layout_engine_nu = $this->getItemMathNumber($browser_number);
                            // this is to pull out specific webkit versions, safari, google-chrome etc..
                            $j_count = count($a_blink_types);
                            for ($j = 0; $j < $j_count; $j++) {
                                if (strstr($browser_user_agent, $a_blink_types[$j])) {
                                    $blink_type = $a_blink_types[$j];
                                    if ($browser_name == 'opr/') {
                                        $this->getSetCount('set', 0);
                                    }
                                    $blink_type_number = $this->getItemVersion($browser_user_agent, $blink_type);
                                    $browser_name = $a_blink_types[$j];
                                    if ($browser_name == 'opr/') {
                                        $this->getSetCount('set', 0);
                                    }
                                    $browser_number = $this->getItemVersion($browser_user_agent, $browser_name);
                                    break;
                                }
                            }
                            if ($browser_name == 'opr/') {
                                $browser_name = 'opera';
                            }
                            break;
                        case 'dillo':
                            $browser_number = $this->getItemVersion($browser_user_agent, $browser_name);
                            // assign rendering engine data
                            $layout_engine = 'dillo';
                            $layout_engine_nu = $this->getItemMathNumber($browser_number);
                            $layout_engine_nu_full = $browser_number;
                            break;
                        case 'edge':
                            $browser_number = $this->getItemVersion($browser_user_agent, $browser_name);
                            // assign rendering engine data
                            $layout_engine = 'edgehtml';
                            $layout_engine_nu = $this->getItemMathNumber($browser_number);
                            $layout_engine_nu_full = $browser_number;
                            break;
                        case 'khtml':
                            // note that this is the KHTML version number
                            $browser_number = $this->getItemVersion($browser_user_agent, $browser_name);
                            // assign rendering engine data
                            $layout_engine = 'khtml';
                            $layout_engine_nu = $this->getItemMathNumber($browser_number);
                            $layout_engine_nu_full = $browser_number;
                            // this is to pull out specific khtml versions, konqueror
                            $j_count = count($a_khtml_types);
                            for ($j = 0; $j < $j_count; $j++) {
                                if (strstr($browser_user_agent, $a_khtml_types[$j])) {
                                    $khtml_type = $a_khtml_types[$j];
                                    $khtml_type_number = $this->getItemVersion($browser_user_agent, $khtml_type);
                                    $browser_name = $a_khtml_types[$j];
                                    $browser_number = $this->getItemVersion($browser_user_agent, $browser_name);
                                    break;
                                }
                            }
                            break;
                        case 'moz':
                            /**
                            note: The 'rv' test is not absolute since the rv number is very different on
                            different versions, for example Galean doesn't use the same rv version as Mozilla,
                            neither do later Netscapes, like 7.x. For more on this, read the full mozilla
                            numbering conventions here: http://www.mozilla.org/releases/cvstags.html
                            **/
                            // this will return alpha and beta version numbers, if present
                            $this->getSetCount('set', 0);
                            $moz_rv_full = $this->getItemVersion($browser_user_agent, 'rv:');
                            // this slices them back off for math comparisons
                            $moz_rv = (float)($moz_rv_full);
                            // this is to pull out specific mozilla versions, firebird, netscape etc..
                            $j_count = count($a_gecko_types);
                            for ($j = 0; $j < $j_count; $j++) {
                                if (strstr($browser_user_agent, $a_gecko_types[$j])) {
                                    $moz_type = $a_gecko_types[$j];
                                    $moz_type_number = $this->getItemVersion($browser_user_agent, $moz_type);
                                    break;
                                }
                            }
                            /**
                            this is necesary to protect against false id'ed moz'es and new moz'es.
                            this corrects for galeon, or any other moz browser without an rv number
                            **/
                            if (!$moz_rv) {
                                // you can use this if you are running php >= 4.2
                                $moz_rv = (float)($moz_type_number);
                                $moz_rv_full = $moz_type_number;
                            }
                            // this corrects the version name in case it went to the default 'rv' for the test
                            if ($moz_type == 'rv') {
                                $moz_type = 'mozilla';
                            }
                            //the moz version will be taken from the rv number, see notes above for rv problems
                            $browser_number = $moz_rv;
                            // gets the actual release date, necessary if you need to do functionality tests
                            $this->getSetCount('set', 0);
                            $moz_release_date = $this->getItemVersion($browser_user_agent, 'gecko/');
                            // assign rendering engine data
                            $layout_engine = 'gecko';
                            $layout_engine_nu = $moz_rv;
                            $layout_engine_nu_full = $moz_rv_full;
                            /**
                            Test for mozilla 0.9.x / netscape 6.x
                            test your javascript/CSS to see if it works in these mozilla releases, if it
                            does, just default it to: $b_safe_browser = true;
                            **/
                            if (($moz_release_date < 20020400) || ($moz_rv < 1)) {
                                $b_safe_browser = false;
                            }
                            break;
                        case 'ie':
                            $b_gecko_ua = false;
                            /**
                            note we're adding in the trident/ search to return only first instance in case
                            of msie 8, and we're triggering the  break last condition in the test, as well
                            as the test for a second search string, trident/
                            Sample: Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; Trident/6.0)

                            Handle the new msie 11 ua syntax (search for rv:), sample:
                            Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko
                            so assign msie value back here
                            **/
                            if (strstr($browser_user_agent, 'rv:')) {
                                $browser_name = 'msie';
                                $b_gecko_ua = true;
                                $this->getSetCount('set', 0);
                                $browser_number = $this->getItemVersion($browser_user_agent, 'rv:', '', '');
                            } else {
                                $browser_number =
                                $this->getItemVersion($browser_user_agent, $browser_name, true, 'trident/');
                            }
                            $this->getSetCount('set', 0);
                            $layout_engine_nu_full = $this->getItemVersion($browser_user_agent, 'trident/', '', '');
                            // construct the proper real number. For example, trident 4 is msie 8
                            if ($layout_engine_nu_full) {
                                $layout_engine_nu = $this->getItemMathNumber($layout_engine_nu_full);
                                $layout_engine = 'trident';
                                // in compat mode, browser shows as msie 7, for now, check in future msie
                                // versions. Note that this isn't used in new gecko type ua, so no compat mode switch
                                if (strstr($browser_number, '7.') && !$b_gecko_ua) {
                                    $true_ie_number =
                                    $this->getItemMathNumber($browser_number) + ((int)($layout_engine_nu) - 3);
                                } else {
                                    $true_ie_number = $browser_number;
                                }
                                // this is to pull out specific trident versions, ucbrowser, etc..
                                $j_count = count($a_trident_types);
                                for ($j = 0; $j < $j_count; $j++) {
                                    if (strstr($browser_user_agent, $a_trident_types[$j])) {
                                        $trident_type = $a_trident_types[$j];
                                        $trident_type_number =
                                        $this->getItemVersion($browser_user_agent, $trident_type);
                                        break;
                                    }
                                }
                                // note the string msie does not appear in gecko type msie useragents
                                if (!$trident_type && $b_gecko_ua) {
                                    $trident_type = 'msie';
                                    $trident_type_number = $browser_number;
                                }
                            } elseif ((int)($browser_number) <= 7 && (int)($browser_number) >= 4) {
                                // note: trident is engine from ie 4 onwards, but only shows after ie 8
                                // but msie 7 is trident 3.1, and no trident numbers are known for earlier
                                $layout_engine = 'trident';
                                if ((int)($browser_number) == 7) {
                                    $layout_engine_nu_full = '3.1';
                                    $layout_engine_nu = '3.1';
                                }
                            }
                            // the 9 series is finally standards compatible, html 5 etc, so worth a new id
                            if ($browser_number >= 9) {
                                $ie_version = 'ie9x';
                            } elseif ($browser_number >= 7) {
                                // 7/8 were not yet quite to standards levels but getting there
                                $ie_version = 'ie7x';
                            } elseif (strstr($browser_user_agent, 'mac')) {
                                // then test for IE 5x mac, that's the most problematic IE out there
                                $ie_version = 'ieMac';
                            } elseif ($browser_number >= 5) {
                                // ie 5/6 are both very weak in standards compliance
                                $ie_version = 'ie5x';
                            } elseif (($browser_number > 3) && ($browser_number < 5)) {
                                $b_dom_browser = false;
                                $ie_version = 'ie4';
                                // this depends on what you're using the script for, make sure this fits your needs
                                $b_safe_browser = true;
                            } else {
                                $ie_version = 'old';
                                $b_dom_browser = false;
                                $b_safe_browser = false;
                            }
                            break;
                        case 'op':
                            if ($browser_name == 'opr/') {
                                $browser_name = 'opr';
                            }
                            $browser_number = $this->getItemVersion($browser_user_agent, $browser_name);
                            // opera is leaving version at 9.80 (or xx) for 10.x - see this for explanation
                            // http://dev.opera.com/articles/view/opera-ua-string-changes/
                            if (strstr($browser_number, '9.')
                                 && strstr($browser_user_agent, 'version/')) {
                                $this->getSetCount('set', 0);
                                $browser_number = $this->getItemVersion($browser_user_agent, 'version/');
                            }
                            $this->getSetCount('set', 0);
                            $layout_engine_nu_full = $this->getItemVersion($browser_user_agent, 'presto/');
                            if ($layout_engine_nu_full) {
                                $layout_engine = 'presto';
                                $layout_engine_nu = $this->getItemMathNumber($layout_engine_nu_full);
                            }
                            if (! $layout_engine_nu_full && $browser_name == 'opr') {
                                if (strstr($browser_user_agent, 'blink')) {
                                    $layout_engine_nu_full = $this->getItemVersion($browser_user_agent, 'blink');
                                } else {
                                    $layout_engine_nu_full = $this->getItemVersion($browser_user_agent, 'webkit');
                                }
                                $layout_engine_nu = $this->getItemMathNumber($layout_engine_nu_full);
                                // assign rendering engine data
                                $layout_engine = 'blink';
                                $browser_name = 'opera';
                            }
                            // opera 4 wasn't very useable.
                            if ($browser_number < 5) {
                                $b_safe_browser = false;
                            }
                            
                            break;
                        /**
                        note: webkit returns always the webkit version number, not the specific user
                        agent version, ie, webkit 583, not chrome 0.3
                        **/
                        case 'webkit':
                            // note that this is the Webkit version number
                            $browser_number = $this->getItemVersion($browser_user_agent, $browser_name);
                            // assign rendering engine data
                            $layout_engine = 'webkit';
                            $layout_engine_nu = $this->getItemMathNumber($browser_number);
                            $layout_engine_nu_full = $browser_number;
                            // this is to pull out specific webkit versions, safari, google-chrome etc..
                            $j_count = count($a_webkit_types);
                            for ($j = 0; $j < $j_count; $j++) {
                                if (strstr($browser_user_agent, $a_webkit_types[$j])) {
                                    $webkit_type = $a_webkit_types[$j];
                                    // fixes a glitch: new safaris uses version/x.x.x for the safari number
                                    // however because safari number is NOT the same as webkit number, going
                                    // to keep returning the safari number, not the version/ number.
                                    /*
                                    if ( $a_webkit_types[$j] == 'safari'
                                         && strstr( $browser_user_agent, 'version/' ) ) {
                                        $this->getSetCount( 'set', 0 );
                                        $webkit_type_number = $this->getItemVersion( $browser_user_agent, 'version/' );
                                    }
                                    else {
                                    */
                                    /**
                                    and this is the webkit type version number, like: chrome 1.2
                                    if omni web, we want the count 2, not default 1
                                    **/
                                    if ($webkit_type == 'omniweb') {
                                        $this->getSetCount('set', 2);
                                    }
                                    $webkit_type_number = $this->getItemVersion($browser_user_agent, $webkit_type);
                                    
                                    // }
                                    // epiphany hack
                                    if ($a_webkit_types[$j] == 'gtklauncher') {
                                        $browser_name = 'epiphany';
                                    } else {
                                        $browser_name = $a_webkit_types[$j];
                                    }
                                    if (($a_webkit_types[$j] == 'chrome' || $a_webkit_types[$j] == 'chromium') &&
                                         $this->getItemMathNumber($webkit_type_number) >= 28) {
                                        if (strstr($browser_user_agent, 'blink')) {
                                            $layout_engine_nu_full =
                                            $this->getItemVersion($browser_user_agent, 'blink');
                                            $layout_engine_nu = $this->getItemMathNumber($layout_engine_nu_full);
                                        }
                                        // assign rendering engine data
                                        $layout_engine = 'blink';
                                    }
                                    $browser_number = $this->getItemVersion($browser_user_agent, $browser_name);
                                    break;
                                }
                            }
                            break;
                        default:
                            $browser_number = $this->getItemVersion($browser_user_agent, $browser_name);
                            break;
                    }
                    // the browser was id'ed
                    $b_success = true;
                    break;
                }
            }
            //assigns defaults if the browser was not found in the loop test
            if (!$b_success) {
                /**
                this will return the first part of the browser string if the above id's failed
                usually the first part of the browser string has the navigator useragent name/version in it.
                This will usually correctly id the browser and the browser number if it didn't get
                caught by the above routine.
                If you want a '' to do a if browser == '' type test, just comment out all lines below
                except for the last line, and uncomment the last line. If you want undefined values,
                the browser_name is '', you can always test for that
                **/
                // delete this part if you want an unknown browser returned

                // then uncomment this part
                $browser_name = '';//deletes the last array item in case the browser was not a match
            }
            // get os data, mac os x test requires browser/version information, this is a change from older scripts
            if ($b_os_test) {
                $a_os_data = $this->getOsData($browser_user_agent, $browser_working, $browser_number);
                $os_type = $a_os_data[0];// os name, abbreviated
                $os_number = $a_os_data[1];// os number or version if available
            }
            /**
            this ends the run through once if clause, set the boolean
            to true so the function won't retest everything
            **/
            $b_repeat = true;
            /**
            pulls out primary version number from more complex string, like 7.5a,
            use this for numeric version comparison
            **/
            $browser_math_number = $this->getItemMathNumber($browser_number);
            if ($b_mobile_test) {
                $mobile_test = $this->checkIsMobile($browser_user_agent);
                if ($mobile_test) {
                    $a_mobile_data = $this->getMobileData($browser_user_agent);
                    $ua_type = 'mobile';
                }
            }
        }
        //$browser_number = Tools::getRemoteAddr();
        /**
        This is where you return values based on what parameter you used to call the function
        $which_test is the passed parameter in the initial browserDetection('os') for example returns
        the os version only.

        Update deprecated parameter names to new names
        **/
        switch ($which_test) {
            case 'math_number':
                $which_test = 'browser_math_number';
                break;
            case 'number':
                $which_test = 'browser_number';
                break;
            case 'browser':
                $which_test = 'browser_working';
                break;
            case 'moz_version':
                $which_test = 'moz_data';
                break;
            case 'true_msie_version':
                $which_test = 'true_ie_number';
                break;
            case 'type':
                $which_test = 'ua_type';
                break;
            case 'webkit_version':
                $which_test = 'webkit_data';
                break;
        }
        /**
        assemble these first so they can be included in full return data, using static variables
        Note that there's no need to keep repacking these every time the script is called
        **/
        if (!$a_engine_data) {
            $a_engine_data = array( $layout_engine, $layout_engine_nu_full, $layout_engine_nu );
        }
        if (!$a_blink_data) {
            $a_blink_data = array( $blink_type, $blink_type_number, $browser_number );
        }
        if (!$a_khtml_data) {
            $a_khtml_data = array( $khtml_type, $khtml_type_number, $browser_number );
        }
        if (!$a_moz_data) {
            $a_moz_data = array( $moz_type, $moz_type_number, $moz_rv, $moz_rv_full, $moz_release_date );
        }
        if (!$a_trident_data) {
            $a_trident_data = array( $trident_type, $trident_type_number, $layout_engine_nu, $browser_number );
        }
        if (!$a_webkit_data) {
            $a_webkit_data = array( $webkit_type, $webkit_type_number, $browser_number );
        }
        $run_time = $this->scriptTime();
        // now send the actual engine number to the html type function
        if ($layout_engine_nu) {
            $html_type = $this->getHTMLLevel($layout_engine, $layout_engine_nu);
        }
        // then pack the primary data array
        if (!$a_full_assoc_data) {
            $a_full_assoc_data = array(
                'browser_working' => $browser_working,
                'browser_number' => $browser_number,
                'ie_version' => $ie_version,
                'dom' => $b_dom_browser,
                'safe' => $b_safe_browser,
                'os' => $os_type,
                'os_number' => $os_number,
                'browser_name' => $browser_name,
                'ua_type' => $ua_type,
                'browser_math_number' => $browser_math_number,
                'moz_data' => $a_moz_data,
                'webkit_data' => $a_webkit_data,
                'mobile_test' => $mobile_test,
                'mobile_data' => $a_mobile_data,
                'true_ie_number' => $true_ie_number,
                'run_time' => $run_time,
                'html_type' => $html_type,
                'engine_data' => $a_engine_data,
                'trident_data' => $a_trident_data,
                'blink_data' => $a_blink_data
            );
        }
        // return parameters, either full data arrays, or by associative array index key
        switch ($which_test) {
            // returns all relevant browser information in an array with standard numeric indexes
            case 'full':
                $a_full_data = array(
                    $browser_working,
                    $browser_number,
                    $ie_version,
                    $b_dom_browser,
                    $b_safe_browser,
                    $os_type,
                    $os_number,
                    $browser_name,
                    $ua_type,
                    $browser_math_number,
                    $a_moz_data,
                    $a_webkit_data,
                    $mobile_test,
                    $a_mobile_data,
                    $true_ie_number,
                    $run_time,
                    $html_type,
                    $a_engine_data,
                    $a_trident_data,
                    $a_blink_data
                );
                // print_r( $a_full_data );
                return $a_full_data;
                // break;
            // returns all relevant browser information in an associative array
            case 'full_assoc':
                return $a_full_assoc_data;
                // break;
            case 'header_data':
                // check for xhtml support, note that this test can only run if headers have
                // NOT already been sent.
                if (!headers_sent()) {
                    if (stristr($_SERVER["HTTP_ACCEPT"], "application/xhtml+xml")) {
                    } else {
                    }
                }
                break;
            default:
                # check to see if the data is available, otherwise it's user typo of unsupported option
                if (isset($a_full_assoc_data[$which_test])) {
                    return $a_full_assoc_data[$which_test];
                } else {
                    die("You passed the browser detector an unsupported option for parameter 1: " . $which_test);
                }
                break;
        }
    }


    public function getItemMathNumber($pv_browser_number)
    {
        $browser_math_number = '';
        if ($pv_browser_number
               && preg_match('/^[0-9]*\.*[0-9]*/', $pv_browser_number, $a_browser_math_number)) {
            $browser_math_number = $a_browser_math_number[0];
            //print_r($a_browser_math_number);
        }
        return $browser_math_number;
    }

    // gets which os from the browser string
    public function getOsData($pv_browser_string, $pv_browser_name, $pv_version_number)
    {
        // initialize variables
        $os_working_type = '';
        $os_working_number = '';
        /**
        packs the os array. Use this order since some navigator user agents will put 'macintosh'
        in the navigator user agent string which would make the nt test register true
        **/
        // $a_mac = array( 'intel mac', 'OS X', 'ppc mac', 'mac68k' );// this is not used currently
        // same logic, check in order to catch the os's in order, last is always default item
        $a_unix_types = array( 'dragonfly', 'freebsd', 'openbsd', 'netbsd', 'bsd', 'unixware', 'solaris', 'sunos',
        'sun4', 'sun5', 'suni86', 'sun', 'irix5', 'irix6', 'irix', 'hpux9', 'hpux10', 'hpux11', 'hpux', 'hp-ux',
        'aix1', 'aix2', 'aix3', 'aix4', 'aix5', 'aix', 'sco', 'unixware', 'mpras', 'reliant', 'dec', 'sinix',
        'unix' );
        // only sometimes will you get a linux distro to id itself...
        $a_linux_distros = array( ' cros ', 'ubuntu', 'kubuntu', 'xubuntu', 'mepis', 'xandros', 'linspire', 'winspire',
        'jolicloud', 'sidux', 'kanotix', 'debian', 'opensuse', 'suse', 'fedora', 'redhat', 'slackware', 'slax',
        'mandrake', 'mandriva', 'gentoo', 'sabayon', 'linux' );
        // $a_linux_process = array ( 'i386', 'i586', 'i686', 'x86_64' );// not use currently
        // note, order of os very important in os array, you will get failed ids if changed
        // windows 10 shows android in ua string
        $a_os_types = array( 'blackberry', 'iphone', 'palmos', 'palmsource', 'symbian', 'beos', 'os2', 'amiga',
        'webtv', 'macintosh', 'mac_', 'mac ', 'nt', 'win','android',  $a_unix_types, $a_linux_distros );
        
        //os tester
        $i_count = count($a_os_types);
        for ($i = 0; $i < $i_count; $i++) {
            // unpacks os array, assigns to variable $a_os_working
            $os_working_data = $a_os_types[$i];
            /**
            assign os to global os variable, os flag true on success
            !strstr($pv_browser_string, "linux" ) corrects a linux detection bug
            **/
            if (!is_array($os_working_data)
                 && strstr($pv_browser_string, $os_working_data)
                 && !strstr($pv_browser_string, "linux")) {
                $os_working_type = $os_working_data;
                
                switch ($os_working_type) {
                    // most windows now uses: NT X.Y syntax
                    case 'nt':
                        // This returns either a number, like 3, or 5.1. It does not
                        // return any alpha/beta type data for the os version.
                        preg_match('/nt ([0-9]+[\.]?[0-9]?)/', $pv_browser_string, $a_nt_matches);
                        if (isset($a_nt_matches[1])) {
                            $os_working_number = $a_nt_matches[1];
                        }
                        break;
                    case 'win':
                        // windows vista, for opera ID
                        if (strstr($pv_browser_string, 'vista')) {
                            $os_working_number = 6.0;
                            $os_working_type = 'nt';
                        } elseif (strstr($pv_browser_string, 'xp')) {
                            // windows xp, for opera ID
                            $os_working_number = 5.1;
                            $os_working_type = 'nt';
                        } elseif (strstr($pv_browser_string, '2003')) {
                            // windows server 2003, for opera ID
                            $os_working_number = 5.2;
                            $os_working_type = 'nt';
                        } elseif (strstr($pv_browser_string, 'windows ce')) {
                            // windows CE
                            $os_working_number = 'ce';
                            $os_working_type = 'nt';
                        } elseif (strstr($pv_browser_string, '95')) {
                            $os_working_number = '95';
                        } elseif ((strstr($pv_browser_string, '9x 4.9'))
                             || (strstr($pv_browser_string, ' me'))) {
                            $os_working_number = 'me';
                        } elseif (strstr($pv_browser_string, '98')) {
                            $os_working_number = '98';
                        } elseif (strstr($pv_browser_string, '2000')) {
                            // windows 2000, for opera ID
                            $os_working_number = 5.0;
                            $os_working_type = 'nt';
                        }
                        break;
                    case 'mac ':
                    case 'mac_':
                    case 'macintosh':
                        $os_working_type = 'mac';
                        if (strstr($pv_browser_string, 'os x')) {
                            // if it doesn't have a version number, it is os x;
                            if (strstr($pv_browser_string, 'os x ')) {
                                // numbers are like: 10_2.4, others 10.2.4
                                $os_working_number =
                                str_replace('_', '.', $this->getItemVersion($pv_browser_string, 'os x'));
                            } else {
                                $os_working_number = 10;
                            }
                        } elseif ($pv_browser_name == 'saf'
                        /**
                        this is a crude test for os x, since safari, camino, ie 5.2, & moz >= rv 1.3
                        are only made for os x
                        **/
                                 || $pv_browser_name == 'cam'
                                 || (($pv_browser_name == 'moz') && ($pv_version_number >= 1.3))
                                 || (($pv_browser_name == 'ie') && ($pv_version_number >= 5.2))) {
                            $os_working_number = 10;
                        }
                        break;
                    case 'iphone':
                        $os_working_number = 10;
                        break;
                    default:
                        break;
                }
                break;
            } elseif (is_array($os_working_data) && ($i == ($i_count - 2))) {
                /**
                check that it's an array, check it's the second to last item
                in the main os array, the unix one that is
                **/
                $j_count = count($os_working_data);
                for ($j = 0; $j < $j_count; $j++) {
                    if (strstr($pv_browser_string, $os_working_data[$j])) {
                        $os_working_type = 'unix'; //if the os is in the unix array, it's unix, obviously...
                        $os_working_number = ($os_working_data[$j] != 'unix') ? $os_working_data[$j] : '';
                        // assign sub unix version from the unix array
                        break;
                    }
                }
            } elseif (is_array($os_working_data) && ($i == ($i_count - 1))) {
                /**
                check that it's an array, check it's the last item
                in the main os array, the linux one that is
                **/
                $j_count = count($os_working_data);
                for ($j = 0; $j < $j_count; $j++) {
                    if (strstr($pv_browser_string, $os_working_data[$j])) {
                        $os_working_type = 'lin';
                        // assign linux distro from the linux array, there's a default
                        //search for 'lin', if it's that, set version to ''
                        $os_working_number = ($os_working_data[$j] != 'linux') ? $os_working_data[$j] : '';
                        break;
                    }
                }
            }
        }
        // pack the os data array for return to main function
        $a_os_data = array( $os_working_type, $os_working_number );
        
        return $a_os_data;
    }

    /**
    Function Info:
    function returns browser number, gecko rv number, or gecko release date
    function getItemVersion( $browser_user_agent, $search_string, $substring_length )
    $pv_extra_search='' allows us to set an additional search/exit loop parameter, but we
    only want this running when needed
    **/
    public function getItemVersion(
        $pv_browser_user_agent,
        $pv_search_string,
        $pv_b_break_last = '',
        $pv_extra_search = ''
    ) {
        // 12 is the longest that will be required, handles release dates: 20020323; 0.8.0+
        $substring_length = 15;
        $start_pos = 0; // set $start_pos to 0 for first iteration
        //initialize browser number, will return '' if not found
        $string_working_number = '';
        /**
        use the passed parameter for $pv_search_string
        start the substring slice right after these moz search strings
        there are some cases of double msie id's, first in string and then with then number
        $start_pos = 0;
        this test covers you for multiple occurrences of string, only with ie though
        with for example google bot you want the first occurance returned, since that's where the
        numbering happens
        **/
        for ($i = 0; $i < 4; $i++) {
            //start the search after the first string occurrence
            if (strpos($pv_browser_user_agent, $pv_search_string, $start_pos) !== false) {
                // update start position if position found
                $start_pos =
                strpos($pv_browser_user_agent, $pv_search_string, $start_pos) + Tools::strlen($pv_search_string);
                /**
                msie (and maybe other userAgents requires special handling because some apps inject
                a second msie, usually at the beginning, custom modes allow breaking at first instance
                if $pv_b_break_last $pv_extra_search conditions exist. Since we only want this test
                to run if and only if we need it, it's triggered by caller passing these values.
                **/
                if (!$pv_b_break_last
                     || ($pv_extra_search && strstr($pv_browser_user_agent, $pv_extra_search))) {
                    break;
                }
            } else {
                break;
            }
        }
        /**
        Handles things like extra omniweb/v456, gecko/, blackberry9700
        also corrects for the omniweb 'v'
        **/
        
        $start_pos += $this->getSetCount('get');
        $string_working_number = Tools::substr($pv_browser_user_agent, $start_pos, $substring_length);
        // Find the space, ;, or parentheses that ends the number
        $string_working_number = Tools::substr($string_working_number, 0, strcspn($string_working_number, ' );/'));
        //make sure the returned value is actually the id number and not a string
        // otherwise return ''
        // strcspn( $string_working_number, '0123456789.') == strlen( $string_working_number)
        //  if ( preg_match("/\\d/", $string_working_number) == 0 )
        if (!is_numeric(Tools::substr($string_working_number, 0, 1))) {
            $string_working_number = '';
        }
        //$string_working_number = strrpos( $pv_browser_user_agent, $pv_search_string );
        return $string_working_number;
    }

    public function getSetCount($pv_type, $pv_value = '')
    {
        static $slice_increment;
        $return_value = '';
        switch ($pv_type) {
            case 'get':
                // set if unset, ie, first use. note that empty and isset are not good tests here
                if (is_null($slice_increment)) {
                    $slice_increment = 1;
                }
                $return_value = $slice_increment;
                $slice_increment = 1; // reset to default
                return $return_value;
                // break;
            case 'set':
                $slice_increment = $pv_value;
                break;
        }
    }

    /**
    Special ID notes:
    Novarra-Vision is a Content Transformation Server (CTS)
    Some interesting notes on detection of actual mobile devices
    **/
    public function checkIsMobile($pv_browser_user_agent)
    {
        $mobile_working_test = '';
        /**
        these will search for basic mobile hints, this should catch most of them, first check
        known hand held device os, then check device names, then mobile browser names
        This list is almost the same but not exactly as the 4 arrays in function below
        **/
        $a_mobile_search = array(
        /**
        Make sure to use only data here that always will be a mobile, so this list is not
        identical to the list of getMobileData
        armv may be a laptop like cros so don't use 'armv' or 'linux armv'
        **/
        // os
        'android', 'blackberry', 'epoc', 'palmos', 'palmsource', 'windows ce', 'windows phone os', 'windows phone',
        'symbianos', 'symbian os', 'symbian', 'webos',
        // devices - ipod before iphone or fails
        'benq', 'blackberry', 'danger hiptop', 'ddipocket', ' droid', 'ipad', 'ipod', 'iphone', 'kindle', 'kobo',
        'lge-cx', 'lge-lx', 'lge-mx', 'lge vx', 'lge ', 'lge-', 'lg;lx', 'nexus', 'nintendo wii', 'nokia', 'nook',
        'palm', 'pdxgw', 'playstation', 'rim', 'sagem', 'samsung', 'sec-sgh', 'sharp', 'sonyericsson', 'sprint',
        'zune', 'j-phone', 'n410', 'mot 24', 'mot-', 'htc-', 'htc_', 'htc ', 'playbook', 'sec-', 'sie-m', 'sie-s',
        'spv ', 'touchpad', 'vodaphone', 'smartphone', 'midp', 'mobilephone',
        // browsers
        'avantgo', 'blazer', 'elaine', 'eudoraweb', 'fennec', 'iemobile',  'minimo', 'mobile safari', 'mobileexplorer',
        'opera mobi', 'opera mini', 'netfront', 'opwv', 'polaris', 'puffin', 'samsungbrowser', 'semc-browser',
        'skyfire', 'up.browser', 'ucweb', 'ucbrowser', 'webpro/', 'wms pie', 'xiino',
        // services - astel out of business
        'astel', 'docomo', 'novarra-vision', 'portalmmm', 'reqwirelessweb', 'vodafone'
        );
        
        // then do basic mobile type search, this uses data from: getMobileData()
        $j_count = count($a_mobile_search);
        for ($j = 0; $j < $j_count; $j++) {
            if (strstr($pv_browser_user_agent, $a_mobile_search[$j])) {
                # this handles compat/pre msie 9 mode zune embedded in ua via registry
                if ($a_mobile_search[$j] != 'zune' || strstr($pv_browser_user_agent, 'iemobile')) {
                    $mobile_working_test = $a_mobile_search[$j];
                    break;
                }
            }
        }
        
        return $mobile_working_test;
    }

    /**
    thanks to this page: http://www.zytrax.com/tech/web/mobile_ids.html
    for data used here
    **/
    public function getMobileData($pv_browser_user_agent)
    {
        $mobile_browser = '';
        $mobile_browser_number = '';
        $mobile_device = '';
        $mobile_device_number = '';
        $mobile_os = ''; // will usually be null, sorry
        $mobile_os_number = '';
        $mobile_server = '';
        $mobile_server_number = '';
        $mobile_tablet = '';
        
        // browsers, show it as a handheld, but is not the os
        // note: crios is actuall chrome on ios, uc need to be before safari
        // Mozilla/5.0 (Windows Phone 10.0; Android 4.2.1; DEVICE INFO) AppleWebKit/537.36 (KHTML, like Gecko)
        // Chrome/42.0.2311.135 Mobile Safari/537.36 Edge/12.<OS build number>
        $a_mobile_browser = array( 'avantgo', 'blazer', 'crios', 'elaine', 'eudoraweb', 'fennec', 'iemobile',
        'minimo', 'ucweb', 'ucbrowser', 'mobile safari', 'mobileexplorer', 'opera mobi', 'opera mini', 'netfront',
        'opwv', 'polaris', 'puffin', 'samsungbrowser', 'semc-browser', 'silk', 'steel', 'ultralight', 'up.browser',
        'webos', 'webpro/', 'wms pie', 'xiino' );
        /**
        This goes from easiest to detect to hardest, so don't use this for output unless you
        clean it up more is my advice.
        Special Notes: do not include milestone in general mobile type test above, it's too generic
        Note: we can safely now test for zune because the initial test shows zune with iemobile in ua
        **/
        $a_mobile_device = array( 'benq', 'blackberry', 'danger hiptop', 'ddipocket', ' droid', 'htc_dream',
        'htc espresso', 'htc hero', 'htc halo', 'htc huangshan', 'htc legend', 'htc liberty', 'htc paradise',
        'htc supersonic', 'htc tattoo', 'ipad', 'ipod', 'iphone', 'kindle', 'kobo', 'lge-cx', 'lge-lx', 'lge-mx',
        'lge vx', 'lg;lx', 'nexus', 'nintendo wii', 'nokia', 'nook', 'palm', 'pdxgw', 'playstation', 'sagem',
        'samsung', 'sec-sgh', 'sharp', 'sonyericsson', 'sprint', 'j-phone', 'milestone', 'n410', 'mot 24', 'mot-',
        'htc-', 'htc_',  'htc ', 'lge ', 'lge-', 'sec-', 'sie-m', 'sie-s', 'spv ', 'smartphone', 'midp', 'mobilephone',
        'wp', 'zunehd', 'zune'  );
        /**
        note: linux alone can't be searched for, and almost all linux devices are armv types
        ipad 'cpu os' is how the real os number is handled : could be an arm laptop, don't use 'linux armv'
        note: the new windows 10 phone os ua has 'android' in it for some reason, so it goes first.
        Mozilla/5.0 (Windows Phone 10.0; Android 4.2.1; DEVICE INFO) AppleWebKit/537.36 (KHTML, like Gecko)
        Chrome/42.0.2311.135 Mobile Safari/537.36 Edge/12.0
        **/
        $a_mobile_os = array( 'windows phone os', 'windows phone', 'android', 'blackberry', 'epoc', 'cpu os',
        'iphone os', 'palmos', 'palmsource', 'windows ce', 'symbianos', 'symbian os', 'symbian', 'webos');
        
        // sometimes there is just no other id for the unit that the CTS type service/server
        $a_mobile_server = array( 'astel', 'docomo', 'novarra-vision', 'portalmmm', 'reqwirelessweb', 'vodafone' );
        
        /**
        basic tablet detection. Note, android 3 was a tablet only release, android 4 is
        mobile/tablet. gt-p is samsung galaxy tablet (eg, gt-p = gt-p1000); verizon galaxy: SCH-I(xxx)
        sm-t is SM-T211 galaxy tab 3. Highly unreliable user agents though.
        note: android 4 is a special case, and is only a tablet if the word 'mobile' is NOT in the string.
        Rather than loop through everything we'll test this manually below and only run the loop if not found.
        NOTE that silk can only be tested for AFTER it's determined it's an android device, changed below to kindle
        https://amazonsilk.wordpress.com/useful-bits/silk-user-agent/
        cros: X11; CrOS armv7l 6680.81.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36
        ' sch-i' is not accurate for tablets
        **/
        $a_mobile_tablet = array( 'ipad', 'android 3', 'cros', ' gt-p', 'sm-t', 'kindle', 'kobo', 'nook', 'playbook',
        'silk', 'touchpad', 'tablet', 'xoom' );
        
        $k_count = count($a_mobile_browser);
        for ($k = 0; $k < $k_count; $k++) {
            if (strstr($pv_browser_user_agent, $a_mobile_browser[$k])) {
                $mobile_browser = $a_mobile_browser[$k];
                // this may or may not work, highly unreliable because mobile ua strings are random
                $mobile_browser_number = $this->getItemVersion($pv_browser_user_agent, $mobile_browser);
                break;
            }
        }
        $k_count = count($a_mobile_device);
        for ($k = 0; $k < $k_count; $k++) {
            if (strstr($pv_browser_user_agent, $a_mobile_device[$k])) {
                $mobile_device = trim($a_mobile_device[$k], '-_'); // but not space trims yet
                if ($mobile_device == 'blackberry') {
                    $this->getSetCount('set', 0);
                }
                $mobile_device_number = $this->getItemVersion($pv_browser_user_agent, $mobile_device);
                $mobile_device = trim($mobile_device); // some of the id search strings have white space
                break;
            }
        }
        $k_count = count($a_mobile_os);
        for ($k = 0; $k < $k_count; $k++) {
            if (strstr($pv_browser_user_agent, $a_mobile_os[$k])) {
                $mobile_os = $a_mobile_os[$k];
                if ($mobile_os != 'blackberry') {
                    // this may or may not work, highly unreliable
                    $mobile_os_number =
                    str_replace('_', '.', $this->getItemVersion($pv_browser_user_agent, $mobile_os));
                } else {
                    $mobile_os_number =
                    str_replace('_', '.', $this->getItemVersion($pv_browser_user_agent, 'version'));
                    // eg: BlackBerry9000/5.0.0.93 Profile/M....
                    if (empty($mobile_os_number)) {
                        $this->getSetCount('set', 5);
                        $mobile_os_number =
                        str_replace('_', '.', $this->getItemVersion($pv_browser_user_agent, $mobile_os));
                    }
                }
                break;
            }
        }
        $k_count = count($a_mobile_server);
        for ($k = 0; $k < $k_count; $k++) {
            if (strstr($pv_browser_user_agent, $a_mobile_server[$k])) {
                $mobile_server = $a_mobile_server[$k];
                // this may or may not work, highly unreliable
                $mobile_server_number = $this->getItemVersion($pv_browser_user_agent, $mobile_server);
                break;
            }
        }
        // special case, google isn't showing tablet in the UA, but if it does not say 'mobile' in the ua,
        // the device is tablet. This will probably change over time since mobile ua's are not settled.
        // using regex (?!mobile) did not work in my tests, not sure why.
        $pattern = '/android[[:space:]]*[4-9]/';
        if (preg_match($pattern, $pv_browser_user_agent) && !stristr($pv_browser_user_agent, 'mobile')) {
            $mobile_tablet = 'android tablet';
        } else {
            $k_count = count($a_mobile_tablet);
            for ($k = 0; $k < $k_count; $k++) {
                if (strstr($pv_browser_user_agent, $a_mobile_tablet[$k])) {
                    $mobile_tablet = trim($a_mobile_tablet[$k]);
                    if ($mobile_tablet == 'gt-p' || $mobile_tablet == 'sch-i' || $mobile_tablet == 'sm-t') {
                        $mobile_tablet = 'galaxy-' . $mobile_tablet;
                    } elseif ($mobile_tablet == 'silk') {
                        $mobile_tablet = 'kindle fire';
                    }
                    break;
                }
            }
        }
        // just for cases where we know it's a mobile device already
        if (!$mobile_os
             && ($mobile_browser || $mobile_device || $mobile_server)
             && strstr($pv_browser_user_agent, 'linux')) {
            $mobile_os = 'linux';
            $mobile_os_number = $this->getItemVersion($pv_browser_user_agent, 'linux');
        }
        
        $a_mobile_data = array( $mobile_device, $mobile_browser, $mobile_browser_number, $mobile_os, $mobile_os_number,
        $mobile_server, $mobile_server_number, $mobile_device_number, $mobile_tablet );
        return $a_mobile_data;
    }

    public function getHTMLLevel($pv_render_engine, $pv_render_engine_nu)
    {
        $html_return = 1;
        $engine_nu = $pv_render_engine_nu;
        /**
        Until further notice, this is the primary comparison table/data used for determining
        browser support: http://en.wikipedia.org/wiki/Comparison_of_layout_engines_%28HTML5%29

        array holding start of browser support types.
        note; gecko/webkit we know about, trident is msie >= 8 , presto opera >= 10
        trident numbers are msie 8 or more number - 4; presto is just what it is for that release
        these are all multiplied by ten to avoid locale math/decimal errors below
        http://w3c-test.org/html/tests/harness/harness.htm

        NOTE: presto numbers went from 2.8 to 2.12, so you can't use this method, set to 20
        **/
        $a_html5_basic = array(
        'blink' => 10,
        'edgehtml' => 4,
        'gecko' => 20,
        'khtml' => 45,
        'presto' => 20, // 26
        'trident' => 50,
        'webkit' => 5250
        );
        $a_html5_forms = array(
        'blink' => 10,
        'edgehtml' => 4,
        'gecko' => 20,
        'khtml' => 50,
        'presto' => 20, // 28
        'trident' => 60,
        'webkit' => 5280
        );
        // (float) is not locale aware, so it will spit out a . type decimal separator
        // but php says that internally it should work fine as intended, ie, locale agnostic
        // (float)/locales: https://bugs.php.net/bug.php?id=40653
        $engine_nu = (int)(10 * (float)($engine_nu));
        
        if (array_key_exists($pv_render_engine, $a_html5_forms)
             && $a_html5_forms[$pv_render_engine] <= $engine_nu) {
            $html_return = 3;
        } elseif (array_key_exists($pv_render_engine, $a_html5_basic)
                 && $a_html5_basic[$pv_render_engine] <= $engine_nu) {
            $html_return = 2;
        }
        return $html_return;
    }

    // track total script execution time
    public function scriptTime()
    {
        static $script_time;
        $elapsed_time = '';
        /**
        note that microtime(true) requires php 5 or greater for microtime(true)
        **/
        if (sprintf("%01.1f", phpversion()) >= 5) {
            if (is_null($script_time)) {
                $script_time = microtime(true);
            } else {
                // note: (string)$var is same as strval($var)
                // $elapsed_time = (string)( microtime(true) - $script_time );
                $elapsed_time = (microtime(true) - $script_time);
                $elapsed_time = sprintf("%01.8f", $elapsed_time);
                $script_time = null; // can't unset a static variable
                return $elapsed_time;
            }
        }
    }

    public function validatePaycomet($merchantCode, $idterminal, $password, $terminales)
    {

        $merchantCode = trim($merchantCode);
        $idterminal = trim($idterminal);
        $password = trim($password);

        $endpoint = "https://api.paycomet.com/gateway/json_product_url_check.php";
        $signature = hash('sha512', $merchantCode . $idterminal . $password);

        $arrParams = array(
            'DS_MERCHANT_MERCHANTCODE' => $merchantCode,
            'DS_MERCHANT_TERMINAL' => $idterminal,
            'DS_MERCHANT_MERCHANTSIGNATURE' => $signature,
            'DS_MERCHANT_TERMINALES' => $terminales
        );

        $json = json_encode($arrParams);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_VERBOSE, true);


        $ret = curl_exec($ch);
        if (!$ret) {
            $ret = json_encode(array("DS_RESPONSE" => 2, "DS_ERROR_ID" => "28"));
        }
        curl_close($ch);

        return json_decode($ret, true);
    }
}
