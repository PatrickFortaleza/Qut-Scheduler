<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule | Mickjay Qutz</title>
    <link rel="apple-touch-icon" sizes="57x57" href="./favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="./favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="./favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="./favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="./favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="./favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="./favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="./favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="./favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="./favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="./favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="./favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="./favicon/favicon-16x16.png">
    <link rel="manifest" href="./favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="./favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <script
  src="https://code.jquery.com/jquery-3.5.1.min.js"
  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
  crossorigin="anonymous"></script>
  <script src="./scripts/scripts.js"></script>
  <link rel="stylesheet" type="text/css" href="./styles/jquery.datetimepicker.css"/>
    <script src="./scripts/jquery.datetimepicker.full.min.js"></script>
    <script src="./scripts/jquery.dateformat.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;400;700;900&display=swap" rel="stylesheet">
    <link href="./styles/normalize.css" rel="stylesheet" type="text/css"/>
    <link href="./styles/resets.css" rel="stylesheet" type="text/css"/>
    <link href="./styles/style.css" rel="stylesheet" type="text/css"/>
</head>
<body>

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
    $client->setAuthConfig(__DIR__.'/credentials.json');
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
?>

<div class="background">
    <div class="bg-section">
        <img src="images/mq-logo.jpg" alt="mq-logo">
    </div>
    
    <div class="form_container">
        <?php include 'mq-icon.php' ?>
        <div class="form_head">
            <div class="profile"> 
                <img src="./images/profile-pic.jpg" alt="profile picture"/>
                 <div class="nametag">
                    <h3>Mickjay Qutz</h3>
                    <p>Schedule a Qut below!</p>
                 </div>   
            </div>
        </div><!--end form_head-->
        <div class='message'>
        <?php
            $errorMessages	= "";
            session_start();
            if( isset($_SESSION['error']) ){
                $errorMessages = $_SESSION['error']; ?>
                
                <div class='error'>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M16.971 0h-9.942l-7.029 7.029v9.941l7.029 7.03h9.941l7.03-7.029v-9.942l-7.029-7.029zm-1.402 16.945l-3.554-3.521-3.518 3.568-1.418-1.418 3.507-3.566-3.586-3.472 1.418-1.417 3.581 3.458 3.539-3.583 1.431 1.431-3.535 3.568 3.566 3.522-1.431 1.43z"/></svg>
                    <h3> Appointment Unsuccessful </h3>
                    <p> <?php echo $errorMessages ?> </p>
                </div> 
                
            <?php } 
            
            //clear the error message after we display it,
            //so that we dont later on read the same error and think its new 
            unset($_SESSION['error']);
            
            $successMessage = "";
            if ( isset($_SESSION['success'])){
                $successMessage = $_SESSION['success']; ?>
                <div class="success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1.25 17.292l-4.5-4.364 1.857-1.858 2.643 2.506 5.643-5.784 1.857 1.857-7.5 7.643z"/></svg>
                    <h3> Qut Successfully Booked </h3>
                    <p> <?php  echo $successMessage?> </p>
                </div>
            <?php } 
            
            //clear the error message after we display it,
            //so that we dont later on read the same error and think its new 
            unset($_SESSION['success']);
        ?>
        </div><!--end message-->


        <form action="postEvents.php" id="schedule_form" method="post">
            <div class="fieldset">
                <label for="name">Full Name:</label>
                <input  type="text" 
                        id="name" 
                        name="name" 
                        size="35"
                        required>
            </div><!--end fieldset-->


            <div class="fieldset">
                <label for="email">Email:</label>
                <input  type="email" 
                        id="email" 
                        name="email" 
                        size="35"
                        required>
            </div><!--end fieldset-->

            <div class="fieldset">
                <label for="phone">Phone Number:</label>
                <input  type="text" 
                        id="phone" 
                        name="phone" 
                        size="35"
                        required>
            </div><!--end fieldset-->


            <div class="fieldset">
                <label for="date">Date:</label>
                <input id="date" 
                    name="date" 
                    readonly="readonly" 
                    type="text" />
            </div><!--end fieldset-->

            <input type="submit" value="Book My Qut">
        </form>
        <div class="form-footer">
            <p>Have a question for me?</p>
            <a href="mailto: mickjquiamco@gmail.com"> Email me instead <span><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M24 0l-6 22-8.129-7.239 7.802-8.234-10.458 7.227-7.215-1.754 24-12zm-15 16.668v7.332l3.258-4.431-3.258-2.901z"/></svg></span></a>
        
        </div>
    </div><!--end form_container-->
</div><!--end background-->



        <?php 
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
                // Debugging? uncomment code:
                // print("<pre>".print_r($datesArray,true)."</pre>");
                // echo json_encode($datesArray, JSON_UNESCAPED_SLASHES);

                // Opens json file with write access
                $datesJson = fopen("dates.json", "w");
                // encodes $datesArray as json and assigns $contents variable
                $contents = json_encode($datesArray, JSON_UNESCAPED_SLASHES);

                // Debugging
                // echo $contents;


                // Writes $contents to json file
                fwrite($datesJson, $contents);
                // close the json file or else it won't let you write to it
                fclose($datesJson);

        
            }
        
        ?>


<script>
    $(document).ready(function(){

        // Request dates.json data
        $.getJSON('dates.json', function(data) {                     
            var array = data;

            // debugging
            console.log(array.length);

            // Initialize output array
            var output = [];

            // this function searches for like terms across item.date and combines arrays within item.hours
            array.forEach(function(item) {
                var existing = output.filter(function(v, i) {
                    return v.date == item.date;
                });
                if (existing.length) {
                    var existingIndex = output.indexOf(existing[0]);
                    output[existingIndex].hours = output[existingIndex].hours.concat(item.hours);
                } else {
                    if (typeof item.hours == 'string')
                    item.hours = [item.hours];
                    output.push(item);
                }
            });

            // initialize dates Array
            var datesArray = [];
            // initialize hours Array (strings)
            var hoursArrayString = [];

            // splits output into the two above arrays
            output.forEach(function(i){
                datesArray.push(i.date);
                hoursArrayString.push(i.hours);

            });
            // Converts strings in item.date into integers
            var hoursArray = hoursArrayString.map(subarray => subarray.map(item => parseInt(item)));

            // assigns variables for xdsoft's calendar, these dates will be disabled.
            var specificDates = datesArray;
            var hoursToTakeAway = hoursArray;

            var lastDate;
            $('#date').datetimepicker({
                format:'m/d/Y H:i',
                timepicker: true,
                lang: 'en',
                onGenerate:function(ct,$i){
                $('.xdsoft_time_variant .xdsoft_time').prop('disabled',false).fadeTo("fast",1).removeClass('disabled');
                //$('.xdsoft_time_variant .xdsoft_time').prop('disabled',false);
                var ind = specificDates.indexOf($.format.date(ct, 'MM/dd/yyyy'));
                console.log($.format.date(ct, 'MM/dd/yyyy'));
                if(ind !== -1) {
                    $('.xdsoft_time_variant .xdsoft_time').each(function(index){
                        if(hoursToTakeAway[ind].indexOf(parseInt($(this).text())) !== -1){
                            $(this).addClass('disabled');
                            $(this).fadeTo("fast",.3);
                            $(this).prop('disabled',true);
                        }
                    });
                }
                }
            });
        });


    });
</script>
</body>
</html>