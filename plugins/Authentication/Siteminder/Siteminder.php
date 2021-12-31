<?php
/**
 * Copyright 2018 QED Systems Inc.
 * Authentication plugin that pulls the IDIR username from the Siteminder headers.
 */

require_once(ROOT_DIR . 'lib/Application/Authentication/namespace.php');
require_once(ROOT_DIR . 'lib/Fnlg/FnlgConfigKeys.php');
require_once(ROOT_DIR . 'lib/Config/Configuration.php');
require_once(ROOT_DIR . 'lib/Fnlg/FnlgApiCaller.php');
require_once(ROOT_DIR . 'lib/Fnlg/FnlgUserSession.php');

class Siteminder extends Authentication implements IAuthentication
{
   private $authToDecorate;
   private $_registration;

   // User object from FNLG system
   public $fnlgUser;

   protected $server;
   protected $sm_header_key;

   private function GetRegistration()
   {
      if ($this->_registration == null)
      {
         $this->_registration = new Registration();
      }

      return $this->_registration;
   }

   public function __construct(Authentication $authentication)
   {
      Log::Debug('Siteminder __construct() start');
      $this->authToDecorate = $authentication;
      $this->server = ServiceLocator::GetServer();
      $this->sm_header_key = Configuration::Instance()->GetKey(FnlgConfigKeys::SM_USERNAME_KEY);
   }

   public function HandleLoginFailure(IAuthenticationPage $loginPage)
       	{
       	//var_dump($loginPage);

       	    /*if(!isset($_COOKIE["laravel_session"])) {
       	        header("Location: ".Configuration::Instance()->GetKey(FnlgConfigKeys::BASE_URL));
       	    }*/
       		Log::Debug('Siteminder HandleLoginFailure() start'.json_encode($loginPage));
       		$loginPage->SetShowLoginError();
       	}

   public function Validate($username, $password)
   {
      Log::Debug('Siteminder Validate() start');

      if ($this->isLocalCall())
      {
         return true;
      }
      $username = $this->getSmUsername();
      Log::Debug("Got user %s", $username);
      if ($username)
      {
         $this->fnlgUser = $this->getFnlgUser($username);
         if ($this->fnlgUser != null)
         {
            $this->addSiteminderData();
            return true;
         }
         else {
            Log::Debug('Could not find '.$username.' in fnlg');
         }
      }
      return false;
   }

   public function Login($username, $loginContext)
   {
      Log::Debug("\n\nStart of Login (Siteminder)");

      // Local calls are system->system API calls so return the system user
      if ($this->isLocalCall())
      {
         $plainUser = $this->buildApiUser($loginContext);
         Log::Debug("Regular user: ".var_export($plainUser, true));
         return WebServiceUserSession::FromSession($plainUser);
      }
      $username = $this->getSmUsername();

      if ($this->fnlgUser != null)
      {
         $this->addSiteminderData();
         $this->Synchronize($username);
      }

      /*
      $repo = new UserRepository();
      $user = $repo->LoadByUsername($username);
      $user->Deactivate();
      $user->Activate();
      $repo->Update($user);
      */

      Log::Debug('Trying to login ' . $username . ' with context' . print_r($loginContext, true));
      $returnValue = $this->authToDecorate->Login($username, $loginContext);
      $fnlgSession = new FnlgUserSession($returnValue->UserId);
      $fnlgSession->setParentValues($returnValue);

      $fnlgSession->eventDates = $this->getEventDates();

      $fnlgSession->Username = $this->fnlgUser->username;
      $fnlgSession->Organization = $this->fnlgUser->ministry->ministry_short_name;

      return $fnlgSession;
   }

   private function buildApiUser($loginContext)
   {
      $systemUser = Configuration::Instance()->GetKey(FnlgConfigKeys::API_SYSTEM_USERNAME);
      Log::Debug("Context: ".var_export($loginContext, true));
      return $this->authToDecorate->Login($systemUser ,$loginContext);
   }

   public function Logout(UserSession $user)
   {
      Log::Debug("Siteminder: Logging out");
      $server = ServiceLocator::GetServer();
      $server->EndSession(SessionKeys::USER_SESSION);
   }

   private function addSiteminderData()
   {
      $headers = apache_request_headers();
      $this->fnlgUser->email = $headers[Configuration::Instance()->GetKey(FnlgConfigKeys::SM_EMAIL_KEY)];
      $this->fnlgUser->display_name = $headers[Configuration::Instance()->GetKey(FnlgConfigKeys::SM_DISPLAY_NAME_KEY)];
   }

   private function Synchronize($username)
   {
      $registration = $this->GetRegistration();

      $registration->Synchronize(
         new AuthenticatedUser(
            $username,
            $this->fnlgUser->email,
            $this->fnlgUser->first_name,
            $this->fnlgUser->last_name,
            null,
            Configuration::Instance()->GetKey(ConfigKeys::LANGUAGE),
            Configuration::Instance()->GetDefaultTimezone(),
            null,
            $this->fnlgUser->ministry->ministry_short_name,
            $this->fnlgUser->title)
      );
   }

   public function AreCredentialsKnown()
   {
      return (bool)$this->getSmUsername();
   }

   private function getSmUsername()
   {
      $headers = apache_request_headers();
      if (key_exists($this->sm_header_key, $headers))
      {
         Log::Debug('Username in header: %s', $headers[$this->sm_header_key]);
         return strtolower($headers[$this->sm_header_key]);
      }
      return null;
   }

   private function isLocalCall()
   {
      $localhosts  = str_getcsv(preg_replace('/\s+/', '', Configuration::Instance()->GetKey(FnlgConfigKeys::API_LOCAL_IP_ADDRESSES)));
      $headers = apache_request_headers();
      Log::Debug("Headers: ".json_encode($headers)." Host: ".$headers['Host']);
      $host = preg_replace('/:.*/','', $headers['Host']);

      if (is_null($this->getSmUsername()) && in_array($host, $localhosts))
      {
         Log::Debug("Request coming from {$host} - It's a local call");
         return true;
      }
      Log::Debug("Request coming from {$host} - It's not in localhost array or SiteMinder headers present");
      return false;
   }

   private function getFnlgUser($username)
   {
      $caller = new FnlgApiCaller();
      $ret = $caller->getFnlgUser($username);
      Log::Debug($username.' returns '.json_encode($ret));
      return $ret;
   }

   private function getEventDates()
   {
      $caller = new FnlgApiCaller();
      $ret = $caller->getEventDates();
      Log::Debug('Event Dates: '.json_encode($ret));
      return $ret;
   }

   public function ShowUsernamePrompt()
   {
      return false;
   }

   public function ShowPasswordPrompt()
   {
      return false;
   }

   public function ShowPersistLoginPrompt()
   {
      return false;
   }

   public function ShowForgotPasswordPrompt()
   {
      return false;
   }
}

?>