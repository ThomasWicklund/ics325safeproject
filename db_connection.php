
<link rel="stylesheet" type="text/css" href="styleCustom.css">
<script type="text/javascript" src="piplanning_scripts.js"></script>

<?php
/**
*   Database connection PHP Page
*   Bears
 */

 DEFINE('DATABASE_HOST', 'localhost');
 DEFINE('DATABASE_DATABASE', 'ics325safedb');
 DEFINE('DATABASE_USER', 'root');
 DEFINE('DATABASE_PASSWORD', '');
 global $db;
 $db = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_DATABASE);
 $db->set_charset("utf8");
 $hour = 3600;
 $day = (24*$hour);

 //checks sql connection was successful, returns error is connection fails
 if ($db->connect_errno) {
     printf("Connect failed: %s\n", $mysqli->connect_error);
     exit();
    };//database connect check
    //checks the timestamp is over 24 hours old for the pi id cache file before proceeding
    if (filemtime('dataFiles/pi_id_cache.json') < time()-$day) {
        //places the pi_id data into the cache file
        if ($result = $db->query("SELECT DISTINCT PI_id FROM cadence ORDER BY start_date")) {
            $rows = array();
            while($row = $result->fetch_array()) {
                $rows[] = $row;
            }
            file_put_contents('dataFiles/pi_id_cache.json', json_encode($rows));
        }
    };//ends pi id json update
    
    //checks the timestamp is over 24 hours old for the art cache file before proceeding
    if (filemtime('dataFiles/art_cache.json') < time()-$day) {
        //places the art data into the cache file
        if ($result = $db->query("SELECT DISTINCT parent_name FROM trains_and_teams where type = 'AT' ORDER BY parent_name")) {
            $rows = array();
            while($row = $result->fetch_array()) {
                $rows[] = $row;
            }
            file_put_contents('dataFiles/art_cache.json', json_encode($rows));
        }
    };//ends art json update

    //checks the timestamp is over 24 hours old for the at cache file before proceeding
    if (filemtime('dataFiles/at_cache.json') < time()-$day) {
        //places the art data into the cache file
        if ($result = $db->query("SELECT DISTINCT parent_name, team_name FROM trains_and_teams where type = 'AT' ORDER BY parent_name, team_name")) {
            $rows = array();
            while($row = $result->fetch_array()) {
                $rows[] = $row;
            }
            file_put_contents('dataFiles/at_cache.json', json_encode($rows));
        }
    };//ends at cache update

    //checks the timestamp is over 24 hours old for the at cache file before proceeding
    if (filemtime('dataFiles/url_cache.json') < time()-$day) {
        //places the art data into the cache file
        if ($result = $db->query("SELECT value FROM preferences WHERE name='BASE_URL'")) {
            $rows = array();
            while($row = $result->fetch_array()) {
                $rows[] = $row;
            }
            file_put_contents('dataFiles/url_cache.json', json_encode($rows));
        }
    };//ends url json update

function setArtCookie(){
    if( !isset($_COOKIE['artCookie'])){
        //checks the preference table for a Default ART
        $db = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_DATABASE);
        $db->set_charset("utf8");
        $art_default_query = "SELECT value FROM preferences WHERE name='DEFAULT_ART' ORDER BY value LIMIT 1";
        $art_default_results = mysqli_query($db, $art_default_query);
        if ($art_default_results->num_rows > 0) {
            while($art_default = $art_default_results->fetch_assoc()) {
                setcookie("artCookie", $art_default["value"]);
            }//end while
        }//end preference search if
        else {
            //if a Default ART was not found, it checks the first value for the ART
            $art_default_query = "SELECT DISTINCT parent_name FROM trains_and_teams where type = 'AT' ORDER BY parent_name LIMIT 1";
            $art_default_results = mysqli_query($db, $art_default_query);
            //starts loop to check the results and update the cookie if results are returned
            if ($art_default_results->num_rows > 0) {
                while($art_default = $art_default_results->fetch_assoc()) {
                    setcookie("artCookie", $art_default["parent_name"]);
                }//end while
            }//end preference search if
        }
    } //end cookie check
};

function buildArtMenu($art_select){
    //initializes the art variable
    $art="";
    //uses json file to build ART select menu. Updates selected default with the Cookie value
    $art_file = file_get_contents("dataFiles/art_cache.json");
    $art_json = json_decode($art_file, true);
    $x=count($art_json);
    for($i = 0; $i < $x; $i++){
        $art_item = $art_json[$i]['parent_name'];
        //checks if the ART should selected
        if($art_item===$art_select){
            $art = $art.'<option value="'.$art_item.'" selected>'.$art_item.'</option>';
        } else{
            $art = $art.'<option value="'.$art_item.'">'.$art_item.'</option>';
        }
    } 
    return $art;
};

//finds the PI within today's date
function piSelectNow(){
    $pi_id_select = "";
    $db = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_DATABASE);
    $db->set_charset("utf8");
    $pi_id_now_query = "SELECT PI_id FROM cadence where DATE(NOW()) between start_date and end_date + 2";
    $pi_id_select_results = mysqli_query($db, $pi_id_now_query);
    if ($pi_id_select_results->num_rows > 0) {
        while($pi_id_now = $pi_id_select_results->fetch_assoc()) {
            $pi_id_select = $pi_id_now["PI_id"];
        }//end while
    }//end if
    return $pi_id_select;
};

//function for build PI table
function buildPi_idMenu($pi_id_select){
    //initializes variables
    $pi_id_file = file_get_contents("dataFiles/pi_id_cache.json");
    $pi_id_json = json_decode($pi_id_file, true);
    $x=count($pi_id_json);
    $pi_id_menu='';
    for($i = 0; $i < $x; $i++){
        $pi_id_item = $pi_id_json[$i]['PI_id'];
        if($pi_id_item===$pi_id_select){
            $pi_id_menu = $pi_id_menu.'<option value="'.$pi_id_item.'" selected>'.$pi_id_item.'</option>';
        } else{
            $pi_id_menu = $pi_id_menu.'<option value="'.$pi_id_item.'">'.$pi_id_item.'</option>';
        } 
    }
    return $pi_id_menu;
};

//function for building the Team Menu
function buildTeamMenu(){
    //initializes variables
    $artCookie = '';
    $at_menu = '';
    $db = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_DATABASE);
    $db->set_charset("utf8");
    //checks for the artCookie before proceeding
        if(isset($_COOKIE['artCookie'])){
            $artCookie = $_COOKIE['artCookie'];
            $at_query = "SELECT DISTINCT team_name FROM trains_and_teams where type = 'AT' and parent_name='".$artCookie."' order by team_name";
            $at_menu_results = mysqli_query($db, $at_query);
            if ($at_menu_results->num_rows > 0) {
                while($at_item = $at_menu_results->fetch_assoc()) {
                    $at_menu = $at_menu.'<option value="'.printf($at_item['team_name']).'">'.printf($at_item['team_name']).'</option>';
                }//end while
            }//end if 
        } return $at_menu;
    };
    
    //Function for creating a table of employee day
    function buildEmployeeTable($selected_team,$duration,$overhead_percentage){
         echo '<table id="info" cellpadding="2px" cellspacing="0" border="0" class="capacity-table"
         width="100%" style="width: 100%; clear: both; font-size: 15px; margin: 8px 0 15px 0">
         <thead>
            <tr id="capacity-table-first-row">
            <th id="capacity-table-td">Last Name</th>
            <th id="capacity-table-td">First Name</th>
            <th id="capacity-table-td">Role</th>
            <th id="capacity-table-td">% Velocity Available</th>
            <th id="capacity-table-td">Days Off <br/><p style="font-size: 9px;">(Vacation / Holidays / Sick Days)</p></th>
            <th id="capacity-table-td">Story Points</th>
            </tr>
         </thead>
         <tbody>';
         $db = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_DATABASE);
         $db->set_charset("utf8");   
         $sql = "SELECT last_name, first_name, employee_name, role FROM `membership`
         JOIN `employees` on (membership.polarion_id = employees.number)
         WHERE team_name='".$selected_team."';";
        
        $result = $db->query($sql);

        if ($result->num_rows > 0) {
            // output data of each
            $rownum = 0;
            while ($row = $result->fetch_assoc()) {
                if ($row["role"] == "Scrum Master (SM)") {
                    $velocityType = "SCRUM_MASTER_ALLOCATION";
                } else if ($row["role"] == "Product Owner (PO)") {
                    $velocityType = "PRODUCT_OWNER_ALLOCATION";
                } else  {
                    $velocityType = "AGILE_TEAM_MEMBER_ALLOCATION";
                }
                $sql2 = "SELECT * FROM `preferences` WHERE name='".$velocityType."';";
                $result2 = $db->query($sql2);
                if ($result2->num_rows > 0) {
                    $row2 = $result2->fetch_assoc();
                }
                if (isset($teamcapacity[$rownum]) && !isset($_POST['restore']) && isset($_POST['submit0'])){
                    $storypts = $teamcapacity[$rownum];
                }else{
                    $storypts = round(($duration-0)*((100-$overhead_percentage)/100)*($row2["value"]/100));
                }
                //returning the value for JS variable for the autoLoad Javascript function
                $valueForJS = $row2["value"];
            
                if (isset($daysoff[$rownum]) && !isset($_POST['restore'])  && isset($_POST['submit0'])){
                    $doff = $daysoff[$rownum];
                } else {
                    $doff = 0;
                }
                if (isset($velocity[$rownum]) && !isset($_POST['restore']) && isset($_POST['submit0'])){
                    $vel = $velocity[$rownum];
                } else {
                    $vel = $row2["value"];
                }
                echo
                    "<tr>
                        <td id='capacity-table-td' style='font-weight:500;'>" . $row["last_name"] . "</td>
                        <td id='capacity-table-td' style='font-weight:500;'>" . $row["first_name"] . "</td>
                        <td id='capacity-table-td' style='font-weight:500;'>" . $row["role"] . "</td>
                        <td id='capacity-table-td' style='font-weight:500; text-align: center;'><input id='autoin' class='capacity-text-input' type='text' name='velocity[]' value='" . $vel . "' submit='autoLoad();' /> %</td>
                        <td id='capacity-table-td' style='font-weight:500; text-align: center;'><input id='autoin2' class='capacity-text-input' type='text' name='daysoff[]' value='".$doff."' submit='autoLoad();' /></td>
                        <td id='capacity-table-td' style='font-weight:500; text-align: center;  background: #e9e9e9;'><input id='story' class='capacity-text-input' type='text' name='storypoints[]' value='".$storypts."' readonly='readonly' style='border: 0;  background: #e9e9e9;' />&nbsp;pts</td>
                        <input type='hidden' name='rownum[]' value='".$rownum."'/>
                    </tr>";
                    $rownum++;
                }
            } else {
                echo "<tr><td colspan='6' id='capacity-table-td'  style='text-align: center; font-weight: bold; padding: 20px 0 20px 0'>";
                print "NO TEAM MEMBERS ASSIGNED TO TEAM \"".$selected_team."\"";
                echo "</td></tr>";
            }
            echo '</tbody><tfoot></tfoot></table>';
        };

        //function for returning the duration
        function getDuration($pi_id_select){
            $db = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_DATABASE);
            $db->set_charset("utf8");  
            $sql5 = "SELECT * FROM `cadence` WHERE PI_id='".$pi_id_select."';";
            $result5 = $db->query($sql5);
            if ($result5->num_rows > 0) {
                $row5 = $result5->fetch_assoc();
                $duration = $row5["duration"];
            }
            return $duration;
        };
    
        //Function for returning the overhead percentage
        function getOverheadPercentage(){
            $db = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_DATABASE);
            $db->set_charset("utf8");  
            $sql6 = "SELECT * FROM `preferences` WHERE name='OVERHEAD_PERCENTAGE';";
            $result6 = $db->query($sql6);
            if ($result6->num_rows > 0) {
                $row6 = $result6->fetch_assoc();
                $overhead_percentage = $row6["value"];
            }
            return $overhead_percentage;
        };
  

        function buildSummaryTable($header_name,$col1,$col2){
            echo '<table id="info" cellpadding="2px" cellspacing="0" border="0" class="capacity-table"
            width="100%" style="width: 100%; clear: both; font-size: 15px; margin: 8px 0 15px 0">
            <thead>
               <tr id="capacity-table-first-row">
               <th id="capacity-table-td">'.$header_name.'</th>
               <th id="capacity-table-td">Total Capacity for PI (Story Points)</th>
               </tr>
            </thead>

            <tbody>';
            $x=count($col1);
                $row = '';
                for($i = 0; $i < $x; $i++){
                $row = $row.'<tr><td>'.$col1[$i].'</td><td>'.$col2[$i].'<td></tr>';
            };
            echo '</tbody> </table>';
        };
        
        function buildARTTable($pi_id){
            $db = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_DATABASE);
            $db->set_charset("utf8");  
            $sql = "SELECT DISTINCT cap.program_increment, art.parent_name, sum(cap.total) as total
            FROM capacity cap, trains_and_teams art 
            WHERE art.team_id = cap.team_id  
            AND program_increment='".$pi_id."'
            GROUP BY cap.program_increment, art.parent_name
            ORDER BY cap.program_increment, art.parent_name";
            
            $result = $db->query($sql);

            


           echo "<table class='floatLeft'>";
           echo "<th style='text-align: center; background-color: grey'; colspan='2'>Agile Release Trains</th>";
           echo "<tr>";
           echo "<th>Agile Release Train</th>";
           echo "<th>Total Capacity for PI (Story Points)</th>";
           if ($result->num_rows > 0) {
             // output data of each row
             while($row = $result->fetch_assoc()) {
                 echo '<tr>';
                   echo '<td><a href="#" id="testID">'.$row["parent_name"].'</a></td>';
                   echo '<td>'.$row["total"].'</td>';
                 echo '</tr>'; 
             }
           } 

           echo "</table>";

           //Returns first alphabetical ART
           $topArtQuery = "SELECT DISTINCT parent_name 
           FROM trains_and_teams 
           WHERE type='AT' 
           ORDER BY parent_name 
           LIMIT 1";
           $topArtValue = $db->query($topArtQuery);
           if ($topArtValue->num_rows > 0) {
             while($row = $topArtValue->fetch_assoc()) {
                 foreach($row as $key=>$value) {
                   $topArtOutput = $row["parent_name"];
                 }
             }
           } 

           buildTeamTable($pi_id, $topArtOutput);

        };

        function buildTeamTable($pi_id, $parent_name){
            $db = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_DATABASE);
            $db->set_charset("utf8");  
            $sql = "SELECT DISTINCT cap.program_increment, art.team_name, sum(cap.total) as total
            FROM capacity cap, trains_and_teams art 
            WHERE art.team_id = cap.team_id  
            AND art.parent_name ='".$parent_name."'
            AND program_increment='".$pi_id."'
            GROUP BY cap.program_increment, art.team_name
            ORDER BY cap.program_increment, art.team_name";
           $result = $db->query($sql);
           echo "<table class='floatRight'>";
           echo "<th style='text-align: center; background-color: grey'; colspan='2'>Agile Team</th>";
           echo "<tr>";
           echo "<th>Agile Team</th>";
           echo "<th>Total Capacity for PI (Story Points)</th>";
           if ($result->num_rows > 0) {
             // output data of each row
             while($row = $result->fetch_assoc()) {
                 echo '<tr>';
                   echo '<td>',$row["team_name"],'</td>';
                   echo '<td>',$row["total"],'</td>';
                 echo '</tr>';
             }
           } 

           echo "</table>";

        };
?>
