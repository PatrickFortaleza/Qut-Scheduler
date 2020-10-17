<?php
require __DIR__ . '/vendor/autoload.php';

// if (php_sapi_name() != 'cli') {
//     throw new Exception('This application must be run on the command line.');
// }

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Google Calendar API PHP Quickstart');
    $client->setScopes(Google_Service_Calendar::CALENDAR);
    $client->setAuthConfig(__DIR__.'/../credentials.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.
    $tokenPath = 'token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    return $client;
}

session_start();
if(!isset($_POST['name']) || 
  !isset($_POST['email']) || 
  !isset($_POST['phone']) ||
  !isset($_POST['date'])){
    $_SESSION['error'] = 'The selected date or time is unavailable.';
    header('location: index.php');
    die();
    
}

//capture form inputs
$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$date = $_POST['date'];
$notes = $_POST['notes'];
date_default_timezone_set('America/Los_Angeles'); //sets timezone for php date()
$date = strtotime($date); //date to unix timestamp
date_default_timezone_set('America/Los_Angeles');
// initiate check to see if submitted date exists to disable double booking
//format unix date to MM/DD/YYYY
$date_mdy = date("m/d/Y", $date);
// format unix date to H
$date_hr = date("H", $date);
$date_human = date("l, F j, Y \a\\t h:i a", $date); //unix date to human readable form
echo $date_human.' -  human<br/>';
$date_after = $date + 3600; //add 60 minutes in unix
$date = date("Y-m-d\TH:i:s-07:00", $date); // format date to match dateTime (Google API)
$date_after = date("Y-m-d\TH:i:s-07:00", $date_after); //unix to date

// create dateobject with date_mdy and date_hr
$dateObjPayload = array('date' => $date_mdy, 'hours' => $date_hr); 

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Calendar($client);


// Print the next 1000 events on the user's calendar.
$calendarId = 'primary';
$optParams = array(
  'maxResults' => 1000,
  'orderBy' => 'startTime',
  'singleEvents' => true,
  'timeMin' => date('c'),
);
$results = $service->events->listEvents($calendarId, $optParams);
$events = $results->getItems();

// Pull all events and format as an array to disable double booking
if (empty($events)) {
  // if no events, display message
  print "Open Schedule";
} else {

  // Initiate array of existing events in google calendar
  $datesArray = [];

  foreach ($events as $event) {
      // Set variables for start and end time (YYYY-MM-DDTHH:MM:SS-07:00)
      $start = $event->start->dateTime;
      $end = $event->end->dateTime;
      if (empty($start)) {
          $start = $event->start->date;
      }

      $start_unix = strtotime($start); //date to unix
      $end_unix = strtotime($end); //date to unix
      date_default_timezone_set('America/Los_Angeles'); //sets timezone for php date()
      $hour = date("H", $start_unix); // sets hours
      $start = date("m/d/Y", $start_unix); //unix to desired format 

      // checks if the event is longer than an hour
      if($end_unix - $start_unix == 3600){
          // if event time is < 1hr, push single dimensional array to $datesArray
          $dateObj = array('date' => $start,
          'hours' => $hour);
      }else{
          // calculate the length of event in unix
          $eHours_unix = $end_unix - $start_unix;
          //define unix value for 1hr
          $unixHour = 3600;
          //calculates how many hours are in $eHours_unix and sets array size
          $arraySize = $eHours_unix / $unixHour;
          // Initiate array for hours within single event
          $hourArray = [];
          // Push the hours into $hourArray 
          for($i = 0; $i < $arraySize; $i++){
              $unixHourItem = $start_unix + ($unixHour*$i);
              $hourItem = date("H", $unixHourItem);
              array_push($hourArray, $hourItem);

          }
          // Push multidimensional array to $datesArray
          $dateObj = array('date' => $start,
                           'hours' => $hourArray);
      }
      // Push it, push it, ah
      array_push($datesArray, $dateObj);
      
  }

}
      

// Debugging? uncomment code:
// print("<pre>".print_r($dateObjPayload,true)."</pre>");
// print("<pre>".print_r($datesArray,true)."</pre>");

// Set variable to detect double booking
$isBooked = false;

// loop through dates
foreach ($datesArray as $dateArray){
  // check if date from payload matches a date in datesArray
  if(array_key_exists('date', $dateArray) && $dateArray['date'] == $dateObjPayload['date']){
    // finds the first match, checks if hours from payload matches hours from matched date
    if(in_array($dateObjPayload['hours'], $dateArray['hours'])){
      // sets double booked variable to true
      $isBooked = true;
    }
    // breaks thr array; we found a double booked date
    break;
  }
}

// checks datesArray if payload exists in array
if(in_array($dateObjPayload, $datesArray)){
  // sets double booked variable to true
  $isBooked = true;
}

// if double booked, throw an error.
if ($isBooked === true) {
  $_SESSION['error'] = 'The selected date or time is unavailable.';
  header('location: index.php');
  die();
}


// Refer to the PHP quickstart on how to setup the environment:
// https://developers.google.com/calendar/quickstart/php
// Change the scope to Google_Service_Calendar::CALENDAR and delete any stored
// credentials.

$event = new Google_Service_Calendar_Event(array(
    'summary' => 'Haircut with '.$name.'',
    'location' => 'Coquitlam, BC',
    'description' => $name.' booked a haircut! | phone: '.$phone.' | email: '.$email,
    'start' => array(
      'dateTime' => $date,
      'timeZone' => 'America/Los_Angeles',
    ),
    'end' => array(
      'dateTime' => $date_after,
      'timeZone' => 'America/Los_Angeles',
    ),
    'attendees' => array(
      array('email' => ''.$email.'',),
      array('email' => 'mickjay.qutbot@gmail.com'),
    ),
    'reminders' => array(
      'useDefault' => FALSE,
      'overrides' => array(
        array('method' => 'email', 'minutes' => 24 * 60),
        array('method' => 'popup', 'minutes' => 60),
      ),
    ),
  ));
  
  $calendarId = 'primary';
  $event = $service->events->insert($calendarId, 
                                    $event,
                                    ['sendUpdates' => 'all']);
  // $event = $service->events->insert($calendarId, $event);
  // printf('Event created: %s\n', $event->htmlLink);

  $_SESSION['success'] = "".$name. " you've booked a haircut for <span class='b-date'>".$date_human."</span>." ;
  header('location: index.php');
  die();



?>
