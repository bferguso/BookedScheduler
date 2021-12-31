<?php
/**
 * Created by IntelliJ IDEA.
 * User: brett
 * Date: 2018-06-15
 * Time: 11:20 AM
 */

require_once(ROOT_DIR . 'lib/Fnlg/FnlgConfigKeys.php');
require_once(ROOT_DIR . 'lib/Config/Configuration.php');

class FnlgApiCaller
{

   public function __construct()
   {
   }

   /**
    * Marks the FNLG meeting request as "Scheduled" and updates it with the booked reservation ID.
    *
    * @param $referenceNumber string Is the reference number of the booked reservation instance
    * @param $fnlgRequestId int Is the FNLG Meeting request ID to update
    * @return bool <code>true</code> if the update was successful, otherwise <code>false</code>
    */
   public function markRequestAsScheduled($referenceNumber, $fnlgRequestId)
   {
      $endpoint = $this->getFullEndpoint($this->getConfigValue(FnlgConfigKeys::API_MARK_REQUEST_SCHEDULED));
      $fields = array(
        $this->getConfigValue(FnlgConfigKeys::PARAM_MEETING_REQUEST_ID) => $fnlgRequestId,
        $this->getConfigValue(FnlgConfigKeys::PARAM_BOOKED_REFERENCE_NUMBER) => $referenceNumber
      );
      return $this->postFnlgApiEndpoint($endpoint, $fields);
   }

   /**
    * Marks the FNLG meeting request as no longer scheduled, and removes any Booked reservation ID.
    *
    * @param $fnlgRequestId int Is the FNLG Meeting request ID to update
    * @return bool <code>true</code> if the update was successful, otherwise <code>false</code>
    */
   public function markRequestAsUnscheduled($fnlgRequestId)
   {
      $endpoint = $this->getFullEndpoint($this->getConfigValue(FnlgConfigKeys::API_MARK_REQUEST_UNSCHEDULED));
      $fields = array(
         $this->getConfigValue(FnlgConfigKeys::PARAM_MEETING_REQUEST_ID) => $fnlgRequestId,
      );
      return $this->postFnlgApiEndpoint($endpoint, $fields);
   }

   /**
    * Get the user object from the FNLG Admin server
    * @param $username
    * @return mixed
    */
   public function getFnlgUser($username)
   {
      $endpoint = $this->getFullEndpoint($this->getConfigValue(FnlgConfigKeys::API_GET_USER)) . $username;
      Log::Debug("Getting fnlguser ".$username." at ".$endpoint);
      return $this->getFnlgApiEndpoint($endpoint);
   }

   /**
    * Get the user object from the FNLG Admin server
    * @param $username
    * @return mixed
    */
   public function getEventDates()
   {
      $endpoint = $this->getFullEndpoint($this->getConfigValue(FnlgConfigKeys::API_GET_EVENT_DATES));
      return $this->getFnlgApiEndpoint($endpoint);
   }

   /**
    * Get the requests that are ready to be scheduled from teh FNLG Admin server.
    * @param $ministryName string Name of the ministry to fetch requests for. If set to "all" return all available requests.
    * @return array List of fnlg meeting request objects
    */
   public function getUnscheduledRequests($ministryName)
   {
      \Log::Debug("Getting unscheduled requests by minsistry name: {$ministryName}");
      $endpoint = $this->getFullEndpoint($this->getConfigValue(FnlgConfigKeys::API_GET_UNSCHEDULED_REQUESTS)) . $ministryName;
      return $this->getFnlgApiEndpoint($endpoint);
   }

   /**
    * Return the meeting request associated with the booked reference number.
    * @param $referenceNumber string Booked Reference number of the request to fetch
    * @return array List of fnlg meeting request objects
    */
   public function getScheduledRequest($referenceNumber)
   {
      \Log::Debug("Getting request by reference number: {$referenceNumber}");
      $endpoint = $this->getFullEndpoint($this->getConfigValue(FnlgConfigKeys::API_GET_REQUEST_BY_REFERENCE_NO)) . $referenceNumber;
      return $this->getFnlgApiEndpoint($endpoint);
   }

   /**
    * Converts the endpoint to a full qualified enpdoint by prefixing the FNLG Admin server
    * @param $endpoint string unqualified endpoint
    * @return string Fully qualified endpoint including the admin server name and context
    */
   private function getFullEndpoint($endpoint)
   {
      return $this->getConfigValue(FnlgConfigKeys::BASE_URL).$endpoint;
   }

   private function getFnlgApiEndpoint($endpoint)
   {
      $arrContextOptions = array(
         "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
         ),
      );
      Log::Debug("Calling ".$endpoint);
      $data = file_get_contents($endpoint, false, stream_context_create($arrContextOptions));
      return json_decode($data);
   }

   private function getConfigValue($key)
   {
      return Configuration::Instance()->GetKey($key);
   }

   private function postFnlgApiEndpoint($endpoint, $fields)
   {
      $contextOptions = array(
         'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded",
            'method'  => 'POST',
            'content' => http_build_query($fields)
         ),
         "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
         ),
      );

      Log::Debug("Calling endpoint: ".$endpoint);
      $data = file_get_contents($endpoint, false, stream_context_create($contextOptions));
      Log::Debug("Headers: ".var_export($http_response_header, true));
      Log::Debug("Post result: ".var_export($data, true));
     # $result = json_decode($data);
      if (strpos($http_response_header[0], "200" )!== false)
      {
         return true;
      }
      else
      {
         Log::Error("Unable to update request: Status: {$http_response_header[0]}: {$data}");
         return false;
      }
   }

}