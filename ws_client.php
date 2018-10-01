<?php
/*
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
*  @author     Jose Ramon Garcia <jrgarcia@paytpv.com>
*  @copyright  2015 PAYTPV ON LINE S.L.
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

if ( !class_exists( 'nusoap_client' ) ) {

	include_once(_PS_MODULE_DIR_.'/paytpv/lib/nusoap.php');

}
class WS_Client {
	var $client = null;

	var $config = null;
	private function write_log($log){

	  Logger::addLog($log, 1);

	}
	public function __construct( array $config = array( ), $proxyhost = '', $proxyport = '', $proxyusername = '', $proxypassword = '' ) {

		$useFsaveOrderAfterSubmit = isset( $_POST[ 'usecurl' ] ) ? $_POST[ 'usecurl' ] : '0';

		$this->config = $config;

		$this->client = new nusoap_client( 'https://secure.paytpv.com/gateway/xml_bankstore.php', false,

						$proxyhost, $proxyport, $proxyusername, $proxypassword );

		$err = $this->client->getError();

		if ( $err ) {

			$this->write_log($err);

			$this->write_log('Debug: '.$this->client->getDebug());

			exit();

		}

		$useCURL = $useFsaveOrderAfterSubmit;
		$this->client->setUseCurl( $useCURL );

	}
	function execute_purchase( $DS_IDUSER,$DS_TOKEN_USER,$TERMINAL,$DS_MERCHANT_CURRENCY='EUR',$amount,$ref='',$MERCHANT_SCORING,$MERCHANT_DATA ) {


		$DS_MERCHANT_MERCHANTCODE = $this->config[ 'clientcode' ];

		$DS_MERCHANT_TERMINAL = $TERMINAL;

		$DS_MERCHANT_AMOUNT = $amount;

		$DS_MERCHANT_ORDER = $ref;

		$DS_MERCHANT_MERCHANTSIGNATURE = sha1( $DS_MERCHANT_MERCHANTCODE . $DS_IDUSER . $DS_TOKEN_USER . $DS_MERCHANT_TERMINAL . $DS_MERCHANT_AMOUNT . $DS_MERCHANT_ORDER . $this->config[ 'pass' ] );

		$DS_ORIGINAL_IP = $_SERVER['REMOTE_ADDR'];
		if ($DS_ORIGINAL_IP=="::1")	$DS_ORIGINAL_IP = "127.0.0.1";

		

		$p = array(

			'DS_MERCHANT_MERCHANTCODE' => $DS_MERCHANT_MERCHANTCODE,

			'DS_MERCHANT_TERMINAL' => $DS_MERCHANT_TERMINAL,

			'DS_IDUSER' => $DS_IDUSER,

			'DS_TOKEN_USER' => $DS_TOKEN_USER,

			'DS_MERCHANT_AMOUNT' => ( string ) $DS_MERCHANT_AMOUNT,

			'DS_MERCHANT_ORDER' => ( string ) $DS_MERCHANT_ORDER,

			'DS_MERCHANT_CURRENCY' => $DS_MERCHANT_CURRENCY,

			'DS_MERCHANT_MERCHANTSIGNATURE' => $DS_MERCHANT_MERCHANTSIGNATURE,

			'DS_ORIGINAL_IP' => $DS_ORIGINAL_IP,

			'DS_MERCHANT_PRODUCTDESCRIPTION' => '',

			'DS_MERCHANT_OWNER' => ''

		);

		if ($MERCHANT_SCORING!=null)        $p["MERCHANT_SCORING"] = $MERCHANT_SCORING;
        if ($MERCHANT_DATA!=null)           $p["MERCHANT_DATA"] = $MERCHANT_DATA;


		$this->write_log("Petición execute_purchase:\n".print_r($p,true));

		$res = $this->client->call( 'execute_purchase', $p, '', '', false, true );

		$this->write_log("Respuesta execute_purchase:\n".print_r($res,true));

		return $res;

	}
	function info_user( $idUser, $tokeUser) {

		$DS_MERCHANT_MERCHANTCODE = $this->config[ 'clientcode' ];

		$DS_MERCHANT_TERMINAL = $this->config[ 'term' ];

		$DS_IDUSER = $idUser;

		$DS_TOKEN_USER = $tokeUser;

		$DS_MERCHANT_MERCHANTSIGNATURE = sha1( $DS_MERCHANT_MERCHANTCODE . $DS_IDUSER . $DS_TOKEN_USER . $DS_MERCHANT_TERMINAL . $this->config[ 'pass' ] );

		$DS_ORIGINAL_IP = $_SERVER['REMOTE_ADDR'];
		if ($DS_ORIGINAL_IP=="::1")	$DS_ORIGINAL_IP = "127.0.0.1";

		$p = array(

			'DS_MERCHANT_MERCHANTCODE' => $DS_MERCHANT_MERCHANTCODE,

			'DS_MERCHANT_TERMINAL' => $DS_MERCHANT_TERMINAL,

			'DS_IDUSER' => $DS_IDUSER,

			'DS_TOKEN_USER' => $DS_TOKEN_USER,

			'DS_MERCHANT_MERCHANTSIGNATURE' => $DS_MERCHANT_MERCHANTSIGNATURE,

			'DS_ORIGINAL_IP' => $DS_ORIGINAL_IP

		);

		$this->write_log("Petición info_user:\n".print_r($p,true));

		$res = $this->client->call( 'info_user', $p, '', '', false, true );

		$this->write_log("Respuesta info_user:\n".print_r($res,true));

		return $res;

	}
	function remove_user( $idUser, $tokeUser) {

		$DS_MERCHANT_MERCHANTCODE = $this->config[ 'clientcode' ];

		$DS_MERCHANT_TERMINAL = $this->config[ 'term' ];

		$DS_IDUSER = $idUser;

		$DS_TOKEN_USER = $tokeUser;

		$DS_MERCHANT_MERCHANTSIGNATURE = sha1( $DS_MERCHANT_MERCHANTCODE . $DS_IDUSER . $DS_TOKEN_USER . $DS_MERCHANT_TERMINAL . $this->config[ 'pass' ] );

		$DS_ORIGINAL_IP = $_SERVER['REMOTE_ADDR'];
		if ($DS_ORIGINAL_IP=="::1")	$DS_ORIGINAL_IP = "127.0.0.1";

		$p = array(

			'DS_MERCHANT_MERCHANTCODE' => $DS_MERCHANT_MERCHANTCODE,

			'DS_MERCHANT_TERMINAL' => $DS_MERCHANT_TERMINAL,

			'DS_IDUSER' => $DS_IDUSER,

			'DS_TOKEN_USER' => $DS_TOKEN_USER,

			'DS_MERCHANT_MERCHANTSIGNATURE' => $DS_MERCHANT_MERCHANTSIGNATURE,

			'DS_ORIGINAL_IP' => $DS_ORIGINAL_IP

		);
		$this->write_log("Petición remove_user:\n".print_r($p,true));

		$res = $this->client->call( 'remove_user', $p, '', '', false, true );

		$this->write_log("Respuesta remove_user:\n".print_r($res,true));

		return $res;

	}

	function create_subscription_token( $idUser, $tokeUser, $DS_SUSCRIPTION_CURRENCY='EUR',$amount,$ref='',$stardate,$enddate,$peridicity,$MERCHANT_SCORING,$MERCHANT_DATA) {


		$DS_MERCHANT_MERCHANTCODE = $this->config[ 'clientcode' ];

		$DS_MERCHANT_TERMINAL = $this->config[ 'term' ];

		$DS_IDUSER = $idUser;

		$DS_TOKEN_USER = $tokeUser;

		$DS_SUBSCRIPTION_STARTDATE = $stardate;
		$DS_SUBSCRIPTION_ENDDATE = $enddate;

		if($ref=='')
			$DS_SUBSCRIPTION_ORDER = time();
		else
			$DS_SUBSCRIPTION_ORDER = str_pad( $ref, 8, "0", STR_PAD_LEFT ) . round(rand(0,99));

		$DS_SUBSCRIPTION_PERIODICITY = $peridicity;

		$DS_SUSCRIPTION_AMOUNT = $amount;
		
		$DS_MERCHANT_MERCHANTSIGNATURE = sha1( $DS_MERCHANT_MERCHANTCODE . $DS_IDUSER . $DS_TOKEN_USER . $DS_MERCHANT_TERMINAL . $DS_SUSCRIPTION_AMOUNT .  $DS_SUSCRIPTION_CURRENCY . $this->config[ 'pass' ] );

		$DS_ORIGINAL_IP = $_SERVER['REMOTE_ADDR'];
		if ($DS_ORIGINAL_IP=="::1")	$DS_ORIGINAL_IP = "127.0.0.1";

		$p = array(

			'DS_MERCHANT_MERCHANTCODE' => $DS_MERCHANT_MERCHANTCODE,

			'DS_MERCHANT_TERMINAL' => $DS_MERCHANT_TERMINAL,

			'DS_IDUSER' => $DS_IDUSER,

			'DS_TOKEN_USER' => $DS_TOKEN_USER,

			'DS_SUBSCRIPTION_STARTDATE' => $DS_SUBSCRIPTION_STARTDATE,
			'DS_SUBSCRIPTION_ENDDATE' => $DS_SUBSCRIPTION_ENDDATE,

			'DS_SUBSCRIPTION_ORDER' => ( string ) $DS_SUBSCRIPTION_ORDER,
			'DS_SUBSCRIPTION_PERIODICITY' => $DS_SUBSCRIPTION_PERIODICITY,

			'DS_SUBSCRIPTION_AMOUNT' => ( string ) $DS_SUSCRIPTION_AMOUNT,

			'DS_SUBSCRIPTION_CURRENCY' => $DS_SUSCRIPTION_CURRENCY,
			

			'DS_MERCHANT_MERCHANTSIGNATURE' => $DS_MERCHANT_MERCHANTSIGNATURE,

			'DS_ORIGINAL_IP' => $DS_ORIGINAL_IP

		);

		if ($MERCHANT_SCORING!=null)        $p["MERCHANT_SCORING"] = $MERCHANT_SCORING;
        if ($MERCHANT_DATA!=null)           $p["MERCHANT_DATA"] = $MERCHANT_DATA;

		$this->write_log("Petición create_subscription_token:\n".print_r($p,true));

		$res = $this->client->call( 'create_subscription_token', $p, '', '', false, true );

		$this->write_log("Respuesta create_subscription_token:\n".print_r($res,true));

		return $res;

	}

	

	function remove_subscription( $idUser, $tokeUser) {

		$DS_MERCHANT_MERCHANTCODE = $this->config[ 'clientcode' ];

		$DS_MERCHANT_TERMINAL = $this->config[ 'term' ];

		$DS_IDUSER = $idUser;

		$DS_TOKEN_USER = $tokeUser;

		$DS_MERCHANT_MERCHANTSIGNATURE = sha1( $DS_MERCHANT_MERCHANTCODE . $DS_IDUSER . $DS_TOKEN_USER . $DS_MERCHANT_TERMINAL . $this->config[ 'pass' ] );

		$DS_ORIGINAL_IP = $_SERVER['REMOTE_ADDR'];
		if ($DS_ORIGINAL_IP=="::1")	$DS_ORIGINAL_IP = "127.0.0.1";

		$p = array(

			'DS_MERCHANT_MERCHANTCODE' => $DS_MERCHANT_MERCHANTCODE,

			'DS_MERCHANT_TERMINAL' => $DS_MERCHANT_TERMINAL,

			'DS_IDUSER' => $DS_IDUSER,

			'DS_TOKEN_USER' => $DS_TOKEN_USER,

			'DS_MERCHANT_MERCHANTSIGNATURE' => $DS_MERCHANT_MERCHANTSIGNATURE,

			'DS_ORIGINAL_IP' => $DS_ORIGINAL_IP

		);
		$this->write_log("Petición remove_user:\n".print_r($p,true));

		$res = $this->client->call( 'remove_subscription', $p, '', '', false, true );

		$this->write_log("Respuesta remove_user:\n".print_r($res,true));

		return $res;

	}


	function execute_refund( $idUser, $tokeUser, $order, $currency,  $authcode, $amount ) {

		
		$DS_MERCHANT_MERCHANTCODE = $this->config[ 'clientcode' ];
		$DS_MERCHANT_TERMINAL = $this->config[ 'term' ];
		$DS_IDUSER = $idUser;
		$DS_TOKEN_USER = $tokeUser;
		$DS_MERCHANT_ORDER = $order;
		$DS_MERCHANT_AUTHCODE = $authcode;
		$DS_MERCHANT_CURRENCY = $currency;
		$DS_MERCHANT_AMOUNT = $amount;
		$DS_MERCHANT_MERCHANTSIGNATURE = sha1($DS_MERCHANT_MERCHANTCODE . $DS_IDUSER . $DS_TOKEN_USER . $DS_MERCHANT_TERMINAL . $DS_MERCHANT_AUTHCODE . $DS_MERCHANT_ORDER . $this->config[ 'pass' ]);

		$DS_ORIGINAL_IP = $_SERVER['REMOTE_ADDR'];
		if ($DS_ORIGINAL_IP=="::1")	$DS_ORIGINAL_IP = "127.0.0.1";

		$p = array(

			'DS_MERCHANT_MERCHANTCODE' => $DS_MERCHANT_MERCHANTCODE,
			'DS_MERCHANT_TERMINAL' => $DS_MERCHANT_TERMINAL,
			'DS_IDUSER' => $DS_IDUSER,
			'DS_TOKEN_USER' => $DS_TOKEN_USER,
			'DS_MERCHANT_AUTHCODE' => $DS_MERCHANT_AUTHCODE,
			'DS_MERCHANT_ORDER' => $DS_MERCHANT_ORDER,
			'DS_MERCHANT_CURRENCY' => $DS_MERCHANT_CURRENCY,
			'DS_MERCHANT_MERCHANTSIGNATURE' => $DS_MERCHANT_MERCHANTSIGNATURE,
			'DS_ORIGINAL_IP' => $DS_ORIGINAL_IP,
			'DS_MERCHANT_AMOUNT' => $DS_MERCHANT_AMOUNT

		);
		

		$this->write_log("Petición execute_refund:\n".print_r($p,true));

		$res = $this->client->call( 'execute_refund', $p, '', '', false, true );

		$this->write_log("Respuesta execute_refund:\n".print_r($res,true));

		return $res;

	}

	function add_user_token($token){

		
		$DS_MERCHANT_MERCHANTCODE = $this->config[ 'clientcode' ];
		$DS_MERCHANT_TERMINAL = $this->config[ 'term' ];
		$DS_JETID = $this->config[ 'jetid' ];
		$DS_MERCHANT_MERCHANTSIGNATURE = sha1($DS_MERCHANT_MERCHANTCODE.$token.$DS_JETID.$DS_MERCHANT_TERMINAL.$this->config[ 'pass' ]);

		$DS_ORIGINAL_IP = $_SERVER['REMOTE_ADDR'];
		if ($DS_ORIGINAL_IP=="::1")	$DS_ORIGINAL_IP = "127.0.0.1";


		$p = array(

			'DS_MERCHANT_MERCHANTCODE' => $DS_MERCHANT_MERCHANTCODE,
			'DS_MERCHANT_TERMINAL' => $DS_MERCHANT_TERMINAL,
			'DS_MERCHANT_JETTOKEN' => $token,
			'DS_MERCHANT_JETID' => $DS_JETID,
			'DS_MERCHANT_MERCHANTSIGNATURE' => $DS_MERCHANT_MERCHANTSIGNATURE,
			'DS_ORIGINAL_IP' => $DS_ORIGINAL_IP

		);


		$this->write_log("Petición add_user_token:\n".print_r($p,true));

		$res = $this->client->call( 'add_user_token', $p, '', '', false, true );

		$this->write_log("Respuesta add_user_token:\n".print_r($res,true));

		return $res;

	}
}


class CreditCard {
	/**
	 * @var string
	 */

	protected $type;
	/**
	 * @var long
	 */

	protected $pan;
	/**
	 * @var unknown_type
	 */

	protected $exp;
	/**
	 * @var string
	 */

	protected $name;
	/**
	 * @var int
	 */

	protected $cvv;

	public function getType() {

		return $this->type;

	}
	public function getName() {

		return $this->name;

	}
	public function getPan() {

		return $this->pan;

	}
	public function getExp() {

		return $this->exp;

	}
	public function getCvv() {

		return $this->cvv;

	}
	public function setType( $type ) {

		$this->type = $type;

		return $this;

	}
	public function setName( $name ) {

		$this->name = $name;

		return $this;

	}
	public function setPan( $pan ) {

		$this->pan = $pan;

		return $this;

	}
	public function setExp( $exp ) {

		$this->exp = $exp;

		return $this;

	}
	public function setCvv( $cvv ) {

		$this->cvv = $cvv;

		return $this;

	}
}

