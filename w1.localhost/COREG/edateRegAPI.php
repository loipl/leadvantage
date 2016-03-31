<?php

define( 'EDateAPI_RequestProtocolPrefix', 'http://' );
define( 'EDateAPI_RequestVersion', '01' );

// Production.
define( 'EDateAPI_RequestBaseURL', 'api.mate1.com/reg-' );

// QA.
//define( 'EDateAPI_RequestBaseURL', 'qa-proxy3.colo:8080/reg-' );

// Development
//define( 'EDateAPI_RequestBaseURL', 'localhost:8080/reg-' );

if (!defined('EDateAPI_DumpRequests'))
	define( 'EDateAPI_DumpRequests', false );

if (!defined('EDateAPI_ValidationStrict'))
	define( 'EDateAPI_ValidationStrict', false );

if (!defined('EDateAPI_LogKeyMismatch'))
	define( 'EDateAPI_LogKeyMismatch', false );

if (!defined('EDateAPI_AutoStripValues'))
	define( 'EDateAPI_AutoStripValues', true );

define( 'EDateAPI_DefaultSearchResultRows', 25 );

define( 'EDateAPI_MaxPhotoUploadSize', 2097152 );	// 2M

define( 'EDateAPI_DefaultLoginURL', null );
define( 'EDateAPI_HomeLoginURL', null );
define( 'EDateAPI_SearchResultsLoginURL', 'RESULTS_PAGE' );
define( 'EDateAPI_CompleteProfileLoginURL', 'COMPLETE_PROFILE' );
define( 'EDateAPI_MailPhotoLoginURL', 'MAIL_PHOTO' );

define( 'EDateAPI_FullPhoto', 'FULL' );
define( 'EDateAPI_PortraitPhoto', 'THUMBNAIL' );
define( 'EDateAPI_ThumbnailPhoto', 'PORTRAIT' );


interface iEDateRegAPI
{
	// Preparation
	public function edateRegAPI( $iCompanyId, $iCompanyPassword,
		$iCampaignId );

	public function authenticate( $iCompanyId, $iCompanyPassword );

	// Utility
	public function isOk( $iDieOnFail = false );
	public function confirmOk();
	public function it();


	public function stripUnknownKeys( $iCommand, $iVals );

	/**************************************************************************/
	/** checkProfile – Validating Profile Information:

		Mate1 profile information can be validated without creating or otherwise
	associating submitted information to any specific profile. Any requests
	sent for validation are validated conditionally, meaning that missing
	fields will not produce errors in almost all cases.

	For more information, please consult the integration documentation you
	were given by your Mate1 contact.
	*/
	public function checkProfile( $iProfile );


	/**************************************************************************/
	/** createProfile – Creating a Profile:

		This call will create a profile in the Mate1 system ready to be logged
	in or shuttled to a further registration step. Any requests sent for
	created are validated unconditionally, meaning that missing fields will
	produce errors in almost all cases.
	*/
	public function createProfile( $iProfile );

	/**************************************************************************/
	/** getProfileKey - Return the currently bound profile key.

			Just what it says.  Any profile specific methods called will operate
		on the profile referred to by this key.
	*/
	public function getProfileKey();


	/**************************************************************************/
	/** forgetProfile – Unbind a profile from this instance:

		This call will drop any profileKey which may currently be attached,
	and clear out any stored key/value pairs set through setProfileData calls.

		After forgetting the profile either a createProfile or attachProfile
	call must be made to attach a profileKey again before calling
	updateProfile, loginURL or any other API call which requires a profile.
	*/
	public function forgetProfile();


	/**************************************************************************/
	/** loginURL – Return a URL on Mate1 to send created profiles to:

		This call requires the edate instance be bound to profileKey.

		If provided, the alternate destination URL will dictate where on
		Mate1 the user will end up on the site.

	*/
	public function loginURL( $iDestURL = EDateAPI_DefaultLoginURL );

	public function loginURLRequest();

	/**************************************************************************/
	public function search( $iSearch );

	/**************************************************************************/
	public function updateProfile( $iProfile );

	/**************************************************************************/
	/** profileInfo – Get a dump of profile info from the server.

		This call requires the edate instance be bound to a profileKey.
	*/
	public function profileInfo();

	/**************************************************************************/
	public function getPixels( $iLocation, $iOptProfileVals = array() );

	/**************************************************************************/
	public function uploadPhotoFile( $iDataFilename );
	public function uploadPhoto( $iPhotoData );

	/**************************************************************************/
	public function getPhotoList();

	/**************************************************************************/
	public function getPhoto( $iPhotoKey, $iType = EDateAPI_FullPhoto );

	/**************************************************************************/
	public function getPostal( $iLocation );

	/**************************************************************************/
	public function attachProfileKey( $iProfileKey );

}

/*******************************************************************************
** EDateRegAPI

	Master registration interface for the creation of Mate1 and EDate landing
pages hosted, and written by affiliate third parties.

	Questions or bug reports regarding this client implementation  may be sent
to Aaron Cameron at aaron@mate1inc.com.

*/
class EDateRegAPI implements iEDateRegAPI, Serializable
{
	/**************************************************************************/
	public function edateRegAPI( $iCompanyId, $iCompanyPassword,
		$iCampaignId )
	{
		// Do a simple compat test to give an English reason for depend failures
		if (!function_exists('curl_init'))
			throw new Exception("Missing required PHP library: php-curl.");

		try {
			$this->setCompanyId( $iCompanyId );
			$this->setCompanyPassword( $iCompanyPassword );
			$this->setCampaignId( $iCampaignId );
		}
		catch ( Exception $iE ) {
			throw new Exception( "Failed to construct edateRegAPI, got error: ".
				$iE->getMessage() );
		}
	}

	/**************************************************************************/
	public function authenticate( $iCompanyId, $iCompanyPassword )
	{
		try {
			$this->setCompanyId( $iCompanyId );
			$this->setCompanyPassword( $iCompanyPassword );
		}
		catch ( Exception $iE ) {
			throw new Exception( "Failed to authenticate edateRegAPI, got ".
				"error: ".$iE->getMessage() );
		}

	}

	/**************************************************************************/
	public function isOk( $iDieOnFail = false ) {
		if (!$this->mPrivRequest['companyId'] ||
			!$this->mPrivRequest['companyPassword'])
		{
			if (!$iDieOnFail) return false;
			throw new Exception("Trying to use a not-okay instance. ".
				"Authenticate or create a new instance.");
		}
		return true;
	}

	/**************************************************************************/
	public function confirmOk() {
		return $this->isOk(true);
	}

	/**************************************************************************/
	public function it() {
		foreach ($this as $k => $v) {
			echo "'$k' => '$v'\n";
		}
	}

	/**************************************************************************/
	private function doStripUnknownKeys( $iCommand, $iVals, $iVersion, $iLog ) {
		$valueMap = $this->getValueValidationMap();

		# FIXME: I'm not sure how to deal with version in this call.
		$valueMap = $valueMap[$iVersion][$iCommand];

		if (!is_array($valueMap))
			throw new Exception( "ValidateValues doesn't know anything about ".
				"command/version '$iCommand/$iVersion'." );

		$tmp = array_merge($valueMap['opt'], $valueMap['req']);

		$r = array();
		foreach ($iVals as $k=>$v) {
			if (in_array($k, $tmp)!==false) {
				$r[$k] = $v;
			} elseif ($iLog){
				error_log("Warning: Unrecognized key: '$k' issuing ".
					"$iCommand/$iVersion.");
			}
		}

		return $r;
	}

	/**************************************************************************/
	public function stripUnknownKeys( $iCommand, $iVals ) {
		return $this->doStripUnknownKeys($iCommand, $iVals, '01', false);
	}


	/**************************************************************************/
	/** checkProfile – Validating Profile Information:
		See the above interface for information.
	*/
	public function checkProfile( $iProfile ) {
		$this->confirmOk();
		// We'll probably want to screen some of the arguments here.

		// Prepare to make the request.
		$url = null;
		$request = null;
		$vars = $iProfile;

		$this->appendAdData($vars);

		$res = array();

		$cmd = 'checkProfile';
		try {
			$this->assembleRequest01( $cmd, $vars, $url, $request );
			$xml = $this->makeRequest( $url, $request );
			$this->processCommonResponseKeys( $xml, $res );

			foreach ($xml->children() as $row) {
				switch ($row->getName()) {
				}
			}

		}
		catch (Exception $iE) {
			throw new Exception( "Failed on '$cmd' request, got: ".
				$iE->getMessage() );
		}

		return $res;
	}

	/**************************************************************************/
	/** createProfile – Creating a Profile:
		See the above interface for information.
	*/
	public function createProfile( $iProfile ) {
		$this->confirmOk();

		if ($this->mProfileKey) {
			throw new Exception( "A profile has already been created with ".
				"this instance." );
		}

		// Prepare to make the request.
		$url = null;
		$request = null;
		$vars = $iProfile;

		if (isset($vars['specialOffer']))
			$vars['specialOffer'] = $vars['specialOffer']?1:0;

		//set the iPAddress of the user
		if(isset($_SERVER['REMOTE_ADDR']))
			$vars['xForwardFor'] = $_SERVER['REMOTE_ADDR'];

		//set the campaign id
		$vars['campaignId'] = $this->getCampaignId();

		$this->appendAdData($vars);

		$res = array();

		$cmd = 'createProfile';
		try {
			$this->assembleRequest01( $cmd, $vars, $url, $request );
			$xml = $this->makeRequest( $url, $request );
			$this->processCommonResponseKeys( $xml, $res );

			foreach ($xml->children() as $row) {
				switch ($row->getName()) {
				}
			}
		}
		catch (Exception $iE) {
			throw new Exception( "Failed on '$cmd' request, got: ".
				$iE->getMessage() );
		}

		return $res;
	}

	/**************************************************************************/
	public function loginURL( $iAltDest = EDateAPI_DefaultLoginURL ) {
		$madeRequest = false;
		if (!$this->mCachedLoginURLs) {
			$this->_digestLoginURLRequest();
			$madeRequest = true;
		}

		// If the requested destination isn't set, or is blank and we haven't
		// refreshed our local cache, try again.
		if ((!isset($this->mCachedLoginURLs[$iAltDest]) ||
			!$this->mCachedLoginURLs[$iAltDest]) && !$madeRequest)
		{
			$this->_digestLoginURLRequest();
		}

		if (!isset($this->mCachedLoginURLs[$iAltDest]) ||
			!$this->mCachedLoginURLs[$iAltDest])
		{
			if ($iAltDest==EDateAPI_DefaultLoginURL) {
				error_log("Got back no default login URL.  ".
					"This is really bad.  There's nothing to do but throw.");

				throw new Exception("loginURL was unable to find a suitable ".
					"URL to return.");
			} else {
				error_log("Requested nonexistant login ".
					"destination: '$iAltDest'.  Returning a standard homepage ".
					"autologin instead.");

				return $this->mCachedLoginURLs[EDateAPI_DefaultLoginURL];
			}
		}

		return $this->mCachedLoginURLs[$iAltDest];
	}

	/**************************************************************************/
	private function _digestLoginURLRequest() {
		try {
			$res = $this->loginURLRequest();

			if ($res['status'] != 'success')
				throw new Exception("Failed to digest loginURL.  ".
					"Request failed.");

			$this->mCachedLoginURLs[null] = $res['loginURL'];
			if ($res['altLoginURL'] && is_array($res['altLoginURL'])) {
				foreach ($res['altLoginURL'] as $k => $v) {
					$this->mCachedLoginURLs[$k] = $v;
				}
			}
		}
		catch (Exception $iE) {
			throw new Exception("Failed to refresh internal loginURL ".
				"cache, got: ".$iE->getMessage());
		}
	}

	/**************************************************************************/
	public function loginURLRequest() {
		$this->confirmOk();

		if (!$this->getProfileKey())
			throw new Exception( "loginURLRequest requires a profile be bound ".
				"to the edate instance.  Create or attach a profile." );

		// Prepare to make the request.
		$url = null;
		$request = null;
		$vars = array(
			'profileKey' => $this->getProfileKey()
		);

		$res = array();

		$lastProfileKey = $this->getProfileKey();
		$cmd = 'loginURL';
		try {
			$this->assembleRequest01( $cmd, $vars, $url, $request );
			$xml = $this->makeRequest( $url, $request );
			$this->processCommonResponseKeys( $xml, $res );

			foreach ($xml->children() as $row) {
				switch ($row->getName()) {
					case 'altLoginURL':
						$res['altLoginURL'][(string)$row['id']] =
							(string)$row;

						$this->mCachedLoginURLs[(string)$row['id']] =
							(string)$row;
						break;
				}
			}
		}
		catch (Exception $iE) {
			throw new Exception( "Failed on '$cmd' request, got: ".
				$iE->getMessage() );
		}

		return $res;
	}


	/**************************************************************************/
	public function search( $iSearch ) {
		$this->confirmOk();

		// Prepare to make the request.
		$url = null;
		$request = null;
		$vars = $iSearch;
		if (!array_key_exists('rows', $vars))
			$vars['rows'] = EDateAPI_DefaultSearchResultRows;

		$res = array();

		$cmd = 'search';
		try {
			$this->assembleRequest01( $cmd, $vars, $url, $request );
			$xml = $this->makeRequest( $url, $request );
			$this->processCommonResponseKeys( $xml, $res );

			foreach ($xml->children() as $row) {
				switch ($row->getName()) {

					case 'profiles':
						foreach ($row->children() as $profRow) {
							$prof = array();
							if ($profRow->getName()=='profile') {
								foreach ($profRow->children() as $k)
									$prof[$k->getName()] = (string)$k;
							} else {
								// FIXME: Handle this.
							}
							$res['profiles'][] = $prof;
						}
						break;
				}
			}
		}
		catch (Exception $iE) {
			throw new Exception( "Failed on '$cmd' request, got: ".
				$iE->getMessage() );
		}

		return $res;
	}

	/**************************************************************************/
	public function updateProfile( $iProfile ) {
		$this->confirmOk();

		if (!$this->mProfileKey) {
			throw new Exception( "This instance has not been bound to a ".
				"profile yet." );
		}

		// Prepare to make the request.
		$url = null;
		$request = null;
		$vars = $iProfile;
		$vars['profileKey'] = $this->getProfileKey();

		if (isset($vars['specialOffer']))
			$vars['specialOffer'] = $vars['specialOffer']?1:0;

		// The server is trained to ignore these, but let's be safe.
		if (isset($vars['email'])) {
			error_log('Warning: Email may not be updated via API call. '.
				'Ignoring the key.');

			unset($vars['email']);
		}

		if (isset($vars['nickName'])) {
			error_log('Warning: NickName may not be updated via API call. '.
				'Ignoring the key.');

			unset($vars['nickName']);
		}

		$res = array();

		$cmd = 'updateProfile';
		try {
			$this->assembleRequest01( $cmd, $vars, $url, $request );
			$xml = $this->makeRequest( $url, $request );
			$this->processCommonResponseKeys( $xml, $res );

			foreach ($xml->children() as $row) {
				switch ($row->getName()) {

				}
			}
		}
		catch (Exception $iE) {
			throw new Exception( "Failed on '$cmd' request, got: ".
				$iE->getMessage() );
		}

		return $res;
	}

	/**************************************************************************/
	public function profileInfo() {
		$this->confirmOk();

		if (!$this->mProfileKey) {
			throw new Exception( "This instance has not been bound to a ".
				"profile yet." );
		}

		// Prepare to make the request.
		$url = null;
		$request = null;

		$vars = array();
		$vars['profileKey'] = $this->getProfileKey();

		$res = array();
		$profile = array();

		$cmd = 'profileInfo';
		try {
			$this->assembleRequest01( $cmd, $vars, $url, $request );
			$xml = $this->makeRequest( $url, $request );
			$this->processCommonResponseKeys( $xml, $res );

			foreach ($xml->children() as $row) {
				switch ($row->getName()) {
					case 'profile':
						foreach ($row->children() as $val) {
							$profile[$val->getName()] = (string)$val;
						}
						break;
				}
			}
		}
		catch (Exception $iE) {
			throw new Exception( "Failed on '$cmd' request, got: ".
				$iE->getMessage() );
		}

		$res['profile'] = $profile;
		return $res;
	}

	/**************************************************************************/
	public function getPixels( $iLocation, $iOptProfileVals = array() ) {
		$this->confirmOk();

		// Check the validity of the location
		$allowedLocations = array( 'BILLING_CONGRATS', 'EMAIL_SUBMISSION',
			'LANDING_PAGE', 'MINI_PROFILE', 'MINI_PROFILE_INTERCEPTOR',
			'PHOTO_UPLOAD','PHOTO_UPLOADED' );

		if (false===array_search($iLocation, $allowedLocations))
			throw new Exception("Unrecognized location provided: '".
				$iLocation."'");

		// Prepare to make the request.
		$url = null;
		$request = null;

		$vars = $iOptProfileVals;

		$vars['location'] = $iLocation;
		$vars['campaignId'] = $this->getCampaignId();

		$this->appendAdData($vars);

		if ($this->getProfileKey())
			$vars['profileKey'] = $this->getProfileKey();

		$cmd = 'getPixels';
		try {
			$this->assembleRequest01( $cmd, $vars, $url, $request );
		}
		catch (Exception $iE) {
			throw new Exception( "Failed on '$cmd' request, got: ".
				$iE->getMessage() );
		}

		// Finally, strip out sensitive information we don't want to send,
		// and replace it with a security token we'll eventually want to use
		$request['sToken'] =
			md5($request['companyId'].$request['companyPassword']);

		unset( $request['companyId'] );
		unset( $request['companyPassword'] );

		// Assemble query string.
		$pairs = array();
		foreach ($request as $k=>$v)
			array_push( $pairs, urlencode($k).'='.urlencode($v) );

		$url .= '?'.join('&',$pairs);

		return "<iframe width=\"1\" frameborder=\"0\" height=\"1\" ".
			"scrolling=\"no\" src=\"$url\"></iframe>";
	}

	/**************************************************************************/
	public function uploadPhoto( $iPhotoData ) {
		throw new Exception( "uploadPhoto is a stub call.  ".
			"Please use uploadPhotoFile()." );
	}

	/**************************************************************************/
	public function uploadPhotoFile( $iDataFilename ) {
		$this->confirmOk();

		if (!$this->getProfileKey())
			throw new Exception( "UploadPhoto requires a profile be bound to ".
				"the edate instance.  Create or attach a profile." );

		// Prepare to make the request.
		$url = null;
		$request = null;
		$vars = array(
			'profileKey' => $this->getProfileKey(),
			'photo' => "@$iDataFilename;type=image/jpeg"
		);

		if (!is_readable($iDataFilename))
			throw new Exception("Provided upload file '$iDataFilename' is ".
				"not readable.");

		if (!filesize($iDataFilename) > EDateAPI_MaxPhotoUploadSize)
			throw new Exception("Provided upload file '$iDataFilename' is ".
				"too large.  The maximum size is: ".
				sprintf("0.2f",	EDateAPI_MaxPhotoUploadSize/1024/1024).
				" megabytes.");

		$res = array(
		);

		$lastProfileKey = $this->getProfileKey();
		$cmd = 'uploadPhoto';
		try {
			$this->assembleRequest01( $cmd, $vars, $url, $request );
			$xml = $this->makeRequest( $url, $request );
			$this->processCommonResponseKeys( $xml, $res );

			foreach ($xml->children() as $row) {
				switch ($row->getName()) {
					case 'photoKey':
						$ioRes['photoKey'] = (string)$row;
						break;
				}
			}
		}
		catch (Exception $iE) {
			throw new Exception( "Failed on '$cmd' request, got: ".
				$iE->getMessage() );
		}

		return $res;
	}

	/**************************************************************************/
	public function getPhotoList() {
		$this->confirmOk();

		if (!$this->getProfileKey())
			throw new Exception( "GetPhotoList requires a profile be bound to ".
				"the edate instance.  Create or attach a profile." );

		// Prepare to make the request.
		$url = null;
		$request = null;
		$vars = array(
			'profileKey' => $this->getProfileKey()
		);

		$res = array(
			'photos' => array()
		);

		$lastProfileKey = $this->getProfileKey();
		$cmd = 'getPhotoList';
		try {
			$this->assembleRequest01( $cmd, $vars, $url, $request );
			$xml = $this->makeRequest( $url, $request );
			$this->processCommonResponseKeys( $xml, $res );

			foreach ($xml->children() as $row) {
				switch ($row->getName()) {
					case 'photos':
						foreach ($row->children() as $ch) {
						if ($ch->getName()=='photo') {
							$res['photos'][] = (string)$ch['photoKey'];
						} else {
							error_log("Warning: Nested photo tag wasn't what ",
								"I expected to see.  Tag was: '",
								$ch->getName(), "'");
						}
					}
				}
			}

		}
		catch (Exception $iE) {
			throw new Exception( "Failed on '$cmd' request, got: ".
				$iE->getMessage() );
		}

		return $res;
	}

	/**************************************************************************/
	public function getPhoto( $iPhotoKey, $iType = EDateAPI_FullPhoto ) {
		$this->confirmOk();

		if (!$this->getProfileKey())
			throw new Exception( "GetPhoto requires a profile be bound to ".
				"the edate instance.  Create or attach a profile." );

		if (false===array_search( $iType, array(EDateAPI_FullPhoto,
			EDateAPI_PortraitPhoto, EDateAPI_ThumbnailPhoto) ))
		{
			error_log("getPhoto doesn't recognize type: '$iType'.  Giving ".
				"you the default instead.");
			$iType = EDateAPI_FullPhoto;
		}

		$url = EDateAPI_RequestProtocolPrefix.EDateAPI_RequestBaseURL;
		$url = rtrim($url,'/');
		$url .= EDateAPI_RequestVersion."/getPhoto?profileKey=".
			$this->getProfileKey()."&photoKey=".
			urlencode($iPhotoKey)."&photoType=".urlencode($iType);

		return $url;
	}

	/**************************************************************************/
	public function getPostal( $iLocation ) {
		$this->confirmOk();

		// Prepare to make the request.
		$url = null;
		$request = null;
		$vars = $iLocation;

		$res = array();

		$cmd = 'getPostal';
		try {
			$this->assembleRequest01( $cmd, $vars, $url, $request );
			$xml = $this->makeRequest( $url, $request );
			$this->processCommonResponseKeys( $xml, $res );

			foreach ($xml->children() as $row) {
				switch ($row->getName()) {
					case 'locationInformation':
						foreach ($row->children() as $ch) {
							switch ($ch->getName()) {
								case 'latitude':
									$res['latitude'] = (string)$ch;
								case 'longitude':
									$res['longitude'] = (string)$ch;
								case 'city':
									$res['city'] = (string)$ch;
								case 'region':
									$res['region'] = (string)$ch;
								case 'country':
									$res['country'] = (string)$ch;
								case 'postalCode':
									$res['postalCode'] = (string)$ch;
						}
					}
				}
			}
		}
		catch (Exception $iE) {
			throw new Exception( "Failed on '$cmd' request, got: ".
				$iE->getMessage() );
		}

		return $res;

	}

	/**************************************************************************/
	public function attachProfileKey( $iProfileKey ) {
		$this->setProfileKey($iProfileKey);
	}


	/**************************************************************************/
	private function processCommonErrors( $iXML, &$ioRes ) {
		throw new Exception("Depricated call to processCommonErrors");
	}


	/**************************************************************************/
	/** Handle the common response keys that appear in many responses and in
		all cases are formatted in the same way.
	*/
	protected function processCommonResponseKeys( $iXML, &$ioRes ) {
		$errorFields = array();

		if ($iXML->getName()!='response')
			throw new Exception("Missing expected tag 'response'");

		$ioRes['status'] = (string)$iXML['status'];

		foreach ($iXML->children() as $row) {
			switch ($row->getName()) {
				case 'allErrors':
					foreach ($row->children() as $err) {
						if ($err->getName()=='error') {
							$ioRes['errors'][(string)$err['code']] =
								(string)$err['text'];

							$errorFields[(string)$err['field']] = 1;
						} else {
							error_log("processCommonResponseKeys: <allErrors> ".
								"contains a tag with an unrecognized name: '".
								$err->getName()."'\n");
						}
					}
					break;

				case 'profileKey':
					$this->setProfileKey((string)$row);
					$ioRes['profileKey'] = (string)$row;
					break;

				case 'loginURL':
					$ioRes['loginURL'] = (string)$row;
					break;
			}
		}

		if ($errorFields) $ioRes['errorFields'] = array_keys($errorFields);
	}

	/**************************************************************************/
	private function processCommonPixels( $iXML, &$ioRes ) {
		$fields = array();
		foreach ($iXML->children() as $k => $pixel) {
			if ($k=='pixel') {
				$ioRes['pixels'][(string)$pixel['id']] =
					(string)$pixel;
			} else {
				// FIXME: Handle this.
			}
		}
	}

	/**************************************************************************/
	public function getProfileKey() {
		return $this->mProfileKey;
	}

	/**************************************************************************/
	public function forgetProfile() {
		// There'll be more than this eventually.
		$this->mProfileKey = null;
	}


	/**************************************************************************/
	/**************************************************************************/
	/**************************************************************************/
	/**************************************************************************/
	/**************************************************************************/
	/**************************************************************************/
	protected function makeRequest( $iURL, $iRequest )
	{
		$result = null;

		try {
			$ch = curl_init();

			// Set the url, number of POST vars, POST data
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_URL, $iURL);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $iRequest );

			if (defined('EDateAPI_DumpRequests') && EDateAPI_DumpRequests) {
				echo "Sending: '$iURL'\n\trequest: '";
				var_dump($iRequest);
				echo "'\n\n";
			}

			$result = curl_exec($ch);

			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if ($code==404)
				throw new Exception("Server access point is returning a ".
					"404: Not Found.");

			if ($code==500)
				throw new Exception("Server access point is returning a ".
					"500: Internal Server Error.");


			if (defined('EDateAPI_DumpRequests') && EDateAPI_DumpRequests)
				echo "Got: '$result'\n\n";

			curl_close($ch);
		}
		catch (Exception $iE) {
			throw new Exception( "Failed to make API curl request, got: ".
				$iE->getMessage() );
		}

		if (!$result)
			throw new Exception( "Received no response from the API server." );

		$xml = null;
		try {
			$xml = new SimpleXMLElement($result);
		}
		catch (Exception $iE) {
			throw new Exception( "Failed to decompose XML response from the ".
				"remote server, got: ".$iE->getMessage() );
		}

		return $xml;
	}

	/**************************************************************************/
	private function appendAdData( &$ioVals )
	{
		//$ioVals['linkId'] = '';
		//$ioVals['adId'] = '';
		//$ioVals['referer'] = '';
		//$ioVals['query'] = '';
		//$ioVals['xForwardedFor'] = '';
		//$ioVals['affiliateId'] = '';
		//$ioVals['userAgent'] = '';
	}

	/**************************************************************************/
	protected function assembleRequest01( $iCommand, $iVals, &$oURL, &$oRequest )
	{
		try {
			$this->validateCommand($iCommand, '01');

			if (defined('EDateAPI_AutoStripValues') && EDateAPI_AutoStripValues)
				$iVals = $this->doStripUnknownKeys($iCommand,$iVals,'01',EDateAPI_LogKeyMismatch);

			else
				$this->doStripUnknownKeys($iCommand,$iVals,'01',EDateAPI_LogKeyMismatch);

			$this->validateValues($iVals, $iCommand, '01');

			$oURL = EDateAPI_RequestProtocolPrefix.EDateAPI_RequestBaseURL;
			$oURL = rtrim($oURL,'/');
			$oURL .= EDateAPI_RequestVersion."/$iCommand";

			$oRequest = array();
			foreach ($this->mPrivRequest as $k=>$v)
				$oRequest[$k] = $v;

			foreach ($this->mRequest as $k=>$v)
				$oRequest[$k] = $v;

			foreach ($iVals as $k=>$v)
				$oRequest[$k] = $v;
		}
		catch ( Exception $iE ) {
			throw new Exception( "assembleRequest01 got error: ".
				$iE->getMessage() );
		}
	}

	/**************************************************************************/
	protected function validateCommand( $iCommand, $iVersion )
	{
		$validCommands = array(
			'01' => array( 'checkProfile', 'createProfile', 'loginURL',
				'search', 'updateProfile', 'getPixels', 'uploadPhoto',
				'getPhotoList', 'getPhoto', 'legacySkipSteps', 'profileInfo', 'getCredentials',
				'getPostal')
		);

		if (!array_key_exists($iVersion, $validCommands)) {
			throw new Exception( "validateCommand cannot find information for ".
				"the version protocol stipulated: '".$iVersion.
				"'");
		}

		if (!in_array($iCommand, $validCommands[$iVersion])) {
			throw new Exception( "The command '$iCommand' is not a supported ".
				"command as of protocol version: ".$iVersion);
		}
	}

	private function getValueValidationMap( $iVerison = null,
		$iCommand = null )
	{
		if ($this->mValueValidationMap)
			return $this->mValueValidationMap;

		$this->mValueValidationMap = array(
			'01' => array(
				'checkProfile' => array(
					'opt' => array(
						'alcohol', 'bodyType', 'children',
						'cityName', 'country', 'dobDay', 'dobMonth', 'dobYear',
						'education', 'educationField', 'email', 'ethnicity',
						'eyeColor', 'gender', 'hairColor', 'height', 'income',
						'lookingGender', 'lookingMaxAge', 'lookingMinAge',
						'nickName', 'occupation', 'password', 'postalCode',
						'relationship', 'religion', 'smoking', 'title',
						'wantChildren', 'campaignId', 'linkId', 'adId',
						'referer', 'query', 'xForwardFor', 'affiliateId',
						'userAgent', 'regionCode', 'iPAddress', 'uUID' ),

					'req' => array(),
				), // check profile.

				'createProfile' => array(
					'opt' => array(
						// These aren't really optional, but we aren't going
						// to do special validation.
						'postalCode', 'cityName', 'lookingMaxAge',
						'lookingMinAge', 'linkId', 'adId', 'referer', 'query',
						'xForwardFor', 'affiliateId', 'userAgent',
						'specialOffer', 'regionCode', 'iPAddress', 'uUID'
					),
					'req' => array(
						'campaignId', 'country', 'dobDay', 'dobMonth',
						'dobYear', 'email', 'gender', 'lookingGender',
						'nickName', 'password',
					),
				), // create profile.

				'loginURL' => array(
					'opt' => array(),
					'req' => array(	'profileKey' ),
				), // loginURL.

				'search' => array(
					'opt' => array( 'ethnicity', 'keywords', 'relationship',
						'rows', 'gender', 'maxAge', 'minAge', 'searchType',
						'bodyType', 'religion' ),

					'req' => array(	'city', 'genderLooking' ),
				), // search.

				'updateProfile' => array(
					'opt' => array(
						'alcohol', 'bodyType', 'children', 'cityName',
						'country', 'dobDay', 'dobMonth', 'dobYear',
						'education', 'educationField', 'email', 'ethnicity',
						'eyeColor', 'gender', 'hairColor', 'height', 'income',
						'lookingGender', 'lookingMaxAge', 'lookingMinAge',
						'nickName', 'occupation', 'password', 'postalCode',
						'relationship', 'religion', 'smoking', 'title',
						'wantChildren', 'specialOffer', 'uUID', 'xForwardFor',
						'campaignId', 'linkId', 'userAgent' ),

					'req' => array( 'profileKey' ),
				), // update profile.

				'getPixels' => array(
					'opt' => array( 'profileKey', 'alcohol', 'bodyType',
						'children', 'cityName', 'country', 'dobDay', 'dobMonth',
						'dobYear', 'education', 'educationField', 'email',
						'ethnicity', 'eyeColor', 'gender', 'hairColor',
						'height', 'income', 'lookingGender', 'lookingMaxAge',
						'lookingMinAge', 'nickName', 'occupation', 'password',
						'postalCode', 'relationship', 'religion', 'smoking',
						'title', 'wantChildren', 'linkId', 'adId', 'referer',
						'query', 'xForwardFor', 'affiliateId', 'userAgent',
						'uUID'),

					'req' => array(	'location', 'campaignId' ),
				), // getPixels.

				'profileInfo' => array(
					'opt' => array( ),
					'req' => array(	'profileKey' ),
				), // profileInfo.

				'getPhotoList' => array(
					'opt' => array( ),
					'req' => array(	'profileKey' ),
				), // getPhotoList.

				'uploadPhoto' => array(
					'opt' => array( ),
					'req' => array(	'profileKey', 'photo' ),
				), // search.

				'getPostal' => array(
					'opt' => array( 'cityId' ),
					'req' => array(	'campaignId', 'countryId', 'regionId' ),
				),  //get postal codes

				'getCredentials' => array(
					'opt' => array( ),
					'req' => array(	'campaignId', 'securityToken' ),
				),

			)
		);

		return $this->mValueValidationMap;
	}

	/**************************************************************************/
	private function validateValues( $iVals, $iCommand, $iVersion )
	{
		$valueMap = $this->getValueValidationMap();

		$opt = $valueMap[$iVersion][$iCommand]['opt'];
		$req = $valueMap[$iVersion][$iCommand]['req'];

		if (!is_array($opt) || !is_array($req))
			throw new Exception( "ValidateValues doesn't know anything about ".
				"command/version '$iCommand/$iVersion'." );

		foreach ($req as $r) {
			if (!array_key_exists($r, $iVals))
				throw new Exception( "Command '$iCommand/$iVersion' is ".
					"missing a required profile key: '$r'" );
		}

		if (EDateAPI_ValidationStrict) {
			foreach (array_keys($iVals) as $k) {
				if (!in_array($k, $opt) && !in_array($k, $req)) {
					throw new Exception( "Command '$iCommand/$iVersion' ".
						"has no idea what to do with value key: '$k'" );
				}
			}
		}
	}

	/**************************************************************************/
	public function serialize() {
		$s = array(
			'mRequest' => $this->mRequest,
			'mProfileKey' => $this->mProfileKey,
			'mCampaignId' => $this->mCampaignId
		);
		return serialize($s);
	}

	/**************************************************************************/
	public function unserialize( $iData ) {
		$s = unserialize($iData);
		if (!$s) return false;

		$this->mRequest = $s['mRequest'];
		$this->mProfileKey = $s['mProfileKey'];
		$this->setCampaignId( $s['mCampaignId'] );
		return true;
	}

	/**************************************************************************/
	protected function setCompanyId( $iCompanyId ) {
		$i = (int)$iCompanyId;
		if (!$i)
			throw new Exception( "CompanyId should be a positive integer." );

		$this->mPrivRequest['companyId'] = $i;
	}

	/**************************************************************************/
	protected function setCompanyPassword( $iCompanyPassword ) {
		if (!is_string($iCompanyPassword) || strlen($iCompanyPassword)<1)
			throw new Exception( "CompanyPassword should be a non-zero length ".
				"string." );

		$this->mPrivRequest['companyPassword'] = md5($iCompanyPassword);
	}

	/**************************************************************************/
	protected function setCampaignId( $iCampaignId ) {
		if (!$iCampaignId || !is_numeric($iCampaignId) || $iCampaignId<=0)
			throw new Exception( "CampaignId should be a natural number ".
				"assigned to you by your Mate1 representative." );

		$this->mCampaignId = (int)$iCampaignId;
	}

	/**************************************************************************/
	private function getCampaignId() {
		return $this->mCampaignId;
	}

	/**************************************************************************/
	private function setProfileKey( $iProfileKey ) {
		if (!is_string($iProfileKey) || strlen($iProfileKey)<1)
			throw new Exception( "Provided ProfileKey does not seem to be ".
				"valid." );

		$this->mProfileKey = $iProfileKey;
	}


	private $mProfileKey = null;
	private $mCampaignId = null;
	private $mRequest = array();
	private $mPrivRequest = array();

	private $mCachedLoginURLs = null;

	private $mValueValidationMap = null;
}


