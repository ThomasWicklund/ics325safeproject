

<script type="text/javascript" src="piplanning_scripts.js"></script>
<link rel="stylesheet" type="text/css" href="styleCustom.css">



<?php
/**
*   Database connection PHP Page
*   Bears
 */

 global $db;

 //checks sql connection was successful, returns error is connection fails
 if ($db->connect_errno) {
     printf("Connect failed: %s\n", $mysqli->connect_error);
     exit();
    };//database connect check

    //checks the timestamp is over 24 hours old for the at cache file before proceeding
    function getBaseURL() {
        $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        $db->set_charset("utf8");
        if ($result = $db->query("SELECT value FROM preferences WHERE name='BASE_URL'")) {
            $base_url = '';
            while($row = $result->fetch_array()) {
                $base_url = $row['value'];
            }
        } return $base_url;
    };


function setArtCookie(){
    //this function will set a cookie and return the value so it can be applied to a variable
    if( !isset($_COOKIE['artCookie'])){
        //checks the preference table for a Default ART
        $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        $db->set_charset("utf8");
        $art_default_query = "SELECT value FROM preferences WHERE name='DEFAULT_ART' ORDER BY value LIMIT 1";
        $art_default_results = mysqli_query($db, $art_default_query);
        if ($art_default_results->num_rows > 0) {
            while($art_default = $art_default_results->fetch_assoc()) {
                setcookie("artCookie", $art_default["value"], time()-3600);
                $artCookie = $art_default["value"];
            }//end while
        }//end preference search if
        else {
            //if a Default ART was not found, it checks the first value for the ART
            $art_default_query = "SELECT DISTINCT parent_name FROM trains_and_teams where type = 'AT' ORDER BY parent_name LIMIT 1";
            $art_default_results = mysqli_query($db, $art_default_query);
            //starts loop to check the results and update the cookie if results are returned
            if ($art_default_results->num_rows > 0) {
                while($art_default = $art_default_results->fetch_assoc()) {
                    setcookie("artCookie", $art_default["parent_name"], time()-3600);
                    $artCookie = $art_default["parent_name"];
                }//end while
            }//end preference search if
        } return $artCookie;
        $art_default_results->close();
    } //end cookie check
};
  //Show Next TableFunction
//function nextIteration($storypts) {
  //document.getElementsByClassName("next1");
//};

function buildArtMenu($art_select){
    //initializes the art variable
    $art="";
    $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        $db->set_charset("utf8");
    if ($result = $db->query("SELECT DISTINCT parent_name FROM trains_and_teams where type = 'AT' ORDER BY parent_name")) {
        $rows = array();
        while($row = $result->fetch_array()) {
            $rows[] = $row;
            $art_item = $row['parent_name'];
            //checks if the ART should selected
            if($art_item===$art_select){
                $art = $art.'<option value="'.$art_item.'" selected>'.$art_item.'</option>';
            } else{
                $art = $art.'<option value="'.$art_item.'">'.$art_item.'</option>';
            }
        }
        return $art;
        $results->close();
    }
};

//finds the PI within today's date
function piSelectNow(){
    $pi_id_select = "";
    $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
    $db->set_charset("utf8");
    $pi_id_now_query = "SELECT PI_id FROM cadence where DATE(NOW()) between start_date and end_date";
    $pi_id_select_results = mysqli_query($db, $pi_id_now_query);
    if ($pi_id_select_results->num_rows > 0) {
        while($pi_id_now = $pi_id_select_results->fetch_assoc()) {
            $pi_id_select = $pi_id_now["PI_id"];
        }//end while
    }//end if
    return $pi_id_select;
    $pi_id_select_results->close();
};

//function for build PI table
function buildPi_idMenu($pi_id_select, $exclude_past){
    //initializes variables
    $pi_id_menu='';
    $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
    $db->set_charset("utf8");
    if($exclude_past){
        $sql = "SELECT DISTINCT PI_id FROM cadence where start_date >= NOW() ORDER BY start_date";
    } else{
        $sql = "SELECT DISTINCT PI_id FROM cadence ORDER BY start_date";
    }
    $result = $db->query($sql);
    if ($result->num_rows > 0) {

        while ($row = $result->fetch_assoc()) {
            $pi_id_item = $row["PI_id"];
            if($pi_id_item===$pi_id_select){
                $pi_id_menu = $pi_id_menu.'<option value="'.$pi_id_item.'" selected>'.$pi_id_item.'</option>';
          }else{
            $pi_id_menu = $pi_id_menu.'<option value="'.$pi_id_item.'">'.$pi_id_item.'</option>';
          }

        }
    }
    return $pi_id_menu;
    $result->close();
};

//function for building the Team Menu
function buildTeamMenu(){
    //initializes variables
    $artCookie = '';
    $at_menu = '';
    $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
    $db->set_charset("utf8");
    //checks for the artCookie before proceeding
        if(isset($_COOKIE['artCookie'])){
            $artCookie = $_COOKIE['artCookie'];
        } else{
            //if a cookie is not available it will is the setArtCookie function to find the default Art value and set the cookie
            $artCookie = setArtCookie();
        }
        $at_query = "SELECT DISTINCT team_name FROM trains_and_teams where type = 'AT' and parent_name='".$artCookie."' order by team_name";
            $at_menu_results = mysqli_query($db, $at_query);
            if ($at_menu_results->num_rows > 0) {
                while($at_item = $at_menu_results->fetch_assoc()) {
                    $at_menu = $at_menu.'<option value="'.printf($at_item['team_name']).'">'.printf($at_item['team_name']).'</option>';
                }//end while
            }//end if
        return $at_menu;
        $at_menu_result->close();
    };


        //function for returning the duration
        function getDuration($iteration){
            $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
            $db->set_charset("utf8");
            $sql5 = "SELECT * FROM `cadence` WHERE iteration_id='".$iteration."';";
            $result5 = $db->query($sql5);
            if ($result5->num_rows > 0) {
                $row5 = $result5->fetch_assoc();
                $duration = $row5["duration"];
            } elseif(substr($iteration, -1) == 6){
                $duration = 5;
            } else {
                $duration = 10;
            }
            return $duration;
            $result5->close();
        };

        //Function for returning the overhead percentage
        function getOverheadPercentage(){
            $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
            $db->set_charset("utf8");
            $sql6 = "SELECT * FROM `preferences` WHERE name='OVERHEAD_PERCENTAGE';";
            $result6 = $db->query($sql6);
            if ($result6->num_rows > 0) {
                $row6 = $result6->fetch_assoc();
                $overhead_percentage = $row6["value"];
            }
            return $overhead_percentage;
            $result6->close();
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
            $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
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
                   echo '<td><a href="#" id="'.$row["parent_name"].'" onclick = "document.cookie = escape(\'artCookie\') + \'=\' + escape(\''.$row["parent_name"].'\'); location.reload();">'.$row["parent_name"].'</a></td>';
                   echo '<td>'.$row["total"].'</td>';
                 echo '</tr>';
             }
           }
           $result->close();

           $sql2 = "SELECT DISTINCT cap.program_increment, sum(cap.total) as final_total
           FROM capacity cap, trains_and_teams art
           WHERE art.team_id = cap.team_id
           AND program_increment='".$pi_id."'
           GROUP BY cap.program_increment
           ORDER BY cap.program_increment, art.team_name;";
           $result2 = $db->query($sql2);
           $final_total ='';
           if ($result2->num_rows > 0) {
               while($row = $result2->fetch_assoc()) {
               $final_total = $row["final_total"];
               }
             }
             if($final_total > 0){
                echo "<td style='background-color:lightgrey; font-weight:bold;'>Final Total of ".$pi_id."</td><td style='background-color:lightgrey; font-weight:bold;'>".$final_total."</td></table>";
             }
             $result2->close();
;

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
                    setcookie("artCookie", $row["parent_name"], time()-3600);
                 }
             }
           }
           $topArtValue->close();
        };

        function buildTeamTable($pi_id, $parent_name){
            $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
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
                   echo '<td id="test";>',$row["team_name"],'</td>';
                   echo '<td id="test";>',$row["total"],'</td>';
                 echo '</tr>';
             }
           }

           $sql2 = "SELECT DISTINCT cap.program_increment, sum(cap.total) as final_total
           FROM capacity cap, trains_and_teams art
           WHERE art.team_id = cap.team_id
           AND art.parent_name ='".$parent_name."'
           AND program_increment='".$pi_id."'
           GROUP BY cap.program_increment
           ORDER BY cap.program_increment, art.team_name;";
           $result2 = $db->query($sql2);
           $final_total ='';
           if ($result2->num_rows > 0) {
               while($row = $result2->fetch_assoc()) {
               $final_total = $row["final_total"];
               }
             }
             if($final_total > 0){
                echo "<td style='background-color:lightgrey; font-weight:bold;'>Final Total of ".$parent_name." in ".$pi_id."</td><td style='background-color:lightgrey; font-weight:bold;'>".$final_total."</td></table>";
             }
            };

            //function for returning the default team name for a given ART
      function getDefaultTeamName($art_name){
        $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        $db->set_charset("utf8");
        $sql = "SELECT DISTINCT team_name FROM trains_and_teams where type = 'AT'  and parent_name = '".$art_name."' ORDER BY parent_name LIMIT 1;";
        $result = $db->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $team_name = $row["team_name"];
        }
        return $team_name;
    };
    //function for returning the team id for a given team name
    function getTeamID($team){
        $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        $db->set_charset("utf8");
        $team_id= '';
        $sql = "SELECT DISTINCT team_id FROM trains_and_teams where team_name = '".$team."' ORDER BY parent_name LIMIT 1;";
        $result = $db->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $team_id= $row["team_id"];
        }
        return $team_id;
    }

    function getTotalCapacity($program_increment, $selected_team, $sequence){

      //$db = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_DATABASE);
      $sql = "SELECT * FROM `capacity` WHERE program_increment='".$program_increment."' AND team_id='".$selected_team."'";
      $result = $db->query($sql);
      if ($result->num_rows > 0) {
          $row = $result->fetch_assoc();
          if (isset($teamcapacity)  && !isset($_POST['restore'.$sequence])  && !isset($_POST['submit0'])){
            $icapacity = array_sum($teamcapacity);
            $totalcapacity = $row["total"] + ($icapacity - $row["iteration_".substr($iteration, -1)]);
          }else{
            $icapacity = $row["iteration_".substr($iteration, -1)];
            $totalcapacity = $row["total"];
          }
      } else {

        if (!isset($teamcapacity)  && !isset($_POST['restore'.sequence])  && !isset($_POST['submit0'])){
          $icapacity = array_sum($teamcapacity);
          $totalcapacity = ($default_total*6) + ($icapacity - $default_total);
        }else{
          $icapacity = $default_total;
          $totalcapacity = $default_total*6;
        }
      }
    }
    ;

            ///////////////////////////Funtion Start/////////////////////////////////////////////////////////
            function creatTables($program_increment, $selected_team, $overhead_percentage){
              $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
              $db->set_charset("utf8");

              $sequenceArray = array();
              $iterationArray = array();
              if ($result = $db->query("SELECT sequence, iteration_id as iteration, start_date, end_date, duration FROM `cadence` WHERE PI_id ='".$program_increment."';")) {
                $rows = array();
                while($row = $result->fetch_array()) {
                  $sequenceArray[]=$row["sequence"];
                  $iterationArray[]=$row["iteration"];
                }
              };
              $count_sequence = count($sequenceArray);
              for($i = 0; $i < $count_sequence; $i++){
                $iteration = $iterationArray[$i];
                $sequence = $sequenceArray[$i];
                $rownum='';
                $valueForJS='';
                $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
                $db->set_charset("utf8");
                $duration = getDuration($iteration);
                $sql_member = "SELECT last_name, first_name, role FROM `membership`
                JOIN `employees` on (membership.polarion_id = employees.number)
                JOIN `trains_and_teams` on (membership.team_name = trains_and_teams.team_name)
                WHERE trains_and_teams.team_id = '".$selected_team."';";
         
        $result_member = $db->query($sql_member);
        $sql_alt_member = "SELECT last_name, first_name, role FROM `membership`
                JOIN `employees` on (membership.polarion_id = employees.number)
                WHERE membership.team_name = '".$selected_team."';";
                $result_alt = $db->query($sql_alt_member);
        //adds the number of rows found for team names into a variable to calculate the number of employees 
        if ($result_member->num_rows > 0) {
          $member_count = $result_member->num_rows;
        } elseif ($result_member->num_rows > 0) {
          $member_count = $result_member->num_rows;
        } else {
          $member_count = 0;
        }
        //checks the iteration number before calculating the default total
        if(substr($iteration, -1)==='P'){
                  $default_total = 0;
                }else{
                  //creates a general total for the default number
                  $default_total = (($duration * .8) * ($member_count - 1));
                }

                $sql = "SELECT * FROM `capacity` WHERE program_increment='".$program_increment."' AND team_id='".$selected_team."';";

                $result = $db->query($sql);
  
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
  
                    if (isset($teamcapacity)  && !isset($_POST['restore'.$sequence])  && !isset($_POST['submit0'])){
                      $icapacity = array_sum($teamcapacity);
                      $totalcapacity = $row["total"] + ($icapacity - $row["iteration_".substr($iteration, -1)]);
                    }else{
                      $icapacity = $row["iteration_".substr($iteration, -1)];
                      $totalcapacity = $row["total"];
                    }
  
                } else {
                  if (isset($teamcapacity)  && !isset($_POST['restore'.$sequence])  && !isset($_POST['submit0'])){
                    $icapacity = array_sum($teamcapacity);
                    $totalcapacity = ($default_total*6) + ($icapacity - $default_total);
                  }else{
                    $icapacity = $default_total;
                    $totalcapacity = $default_total*5.5;
                  }
                }

               echo'<table width="100%">';


               echo '<tr><td width="25%" style="vertical-align: top; font-weight: bold; color: #01B0F1; line-height: 130%; font-size: 18px;">
               &nbsp;&nbsp;Iteration (I): &nbsp;</td><td style="vertical-align: top; font-weight: bold; color: #01B0F1; line-height: 130%; font-size: 18px;">'.$iteration.'</td>';
               echo '<td id="filler" rowspan="3">
               <p style="vertical-align: top; text-align:center; font-weight: bold; line-height: 130%; font-size: 18px;">Total Capacity for Iteration '.$iteration.' <br/>of team id '.$selected_team.'</p>
               <div id="capacity-calc-bignum" name="icap'.$sequence.'" id="icap'.$sequence.'">'.$icapacity.'</div>
               </td></tr>';
               echo '<tr><td width="25%" style="vertical-align: top; font-weight: bold; color: #01B0F1; line-height: 130%; font-size: 18px;">
               &nbsp;&nbsp;No. of Days in Iteration: &nbsp;</td><td style="vertical-align: top; font-weight: bold; color: #01B0F1; line-height: 130%; font-size: 18px;">'.$duration.'</td></tr>';
               echo '<tr><td width="25%" style="vertical-align: top; font-weight: bold; color: #01B0F1; line-height: 130%; font-size: 18px;">
               &nbsp;&nbsp;Overhead Percentage: &nbsp;</td><td style="vertical-align: top; font-weight: bold; color: #01B0F1; line-height: 130%; font-size: 18px;">'.$overhead_percentage.'%</td></tr>';
               
         //adding the calculated capacity for this table into a cookie  
         setcookie("icap".$sequence, $icapacity);
         echo '<td width="50%"  style="font-weight: bold;">';
         ?>

            <tr>
              <td colspan="3">

            <form method="post" action="#" class="form1" id="maincap<?php echo $sequence; ?>">
            <table id="<?php echo $sequence; ?>" cellpadding="2px" cellspacing="0" border="0" class="capacity-table"
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

              <tbody>

              <?php
              $sql = "SELECT last_name, first_name, role FROM `membership`
                      JOIN `employees` on (membership.polarion_id = employees.number)
                      JOIN `trains_and_teams` on (membership.team_name = trains_and_teams.team_name)
                      WHERE trains_and_teams.team_id = '".$selected_team."';";
               
               $result = $db->query($sql);
              
              $sql_alt = "SELECT last_name, first_name, role FROM `membership`
                      JOIN `employees` on (membership.polarion_id = employees.number)
                      WHERE membership.team_name = '".$selected_team."';";
                      
              $result_alt = $db->query($sql_alt);
              if ($result->num_rows > 0) {
                // output data of each
                $rownum = 0;
                  while ($row = $result->fetch_assoc()) {
                    if ($row["role"] == "SM") {
                      $velocityType = "SCRUM_MASTER_ALLOCATION";
                    } else if ($row["role"] == "PO") {
                      $velocityType = "PRODUCT_OWNER_ALLOCATION";
                    } else  {
                      $velocityType = "AGILE_TEAM_MEMBER_ALLOCATION";
                    }

                    $sql2 = "SELECT * FROM `preferences` WHERE name='".$velocityType."';";
                    $result2 = $db->query($sql2);
                    if ($result2->num_rows > 0) {
                      $row2 = $result2->fetch_assoc();
                    }
                    if (substr($iteration, -1)==='P')
                    {
                      $storypts = 0;
                    }elseif(isset($teamcapacity[$rownum]) && !isset($_POST['restore'.$sequence]) && isset($_POST['submit0'])){
                      $storypts = $teamcapacity[$rownum];
                      echo 'document.cookie = escape("icap'.$sequence.'") + "=" + escape(icap'.$sequence.');';
                    }else{
                      $storypts = round(($duration-0)*((100-$overhead_percentage)/100)*($row2["value"]/100));
                    }
                    $valueForJS = $row2["value"];
                    if (isset($daysoff[$rownum]) && !isset($_POST['restore'.$sequence])  && isset($_POST['submit0'])){
                      $doff = $daysoff[$rownum];
                    } else {
                      $doff = 0;
                    }
                    if (isset($velocity[$rownum]) && !isset($_POST['restore'.$sequence]) && isset($_POST['submit0'])){
                      $vel = $velocity[$rownum];
                    } else {
                      $vel = $row2["value"];
                    }

                    echo
                    "
                    <tr>
                          <td id='capacity-table-td' style='font-weight:500;'>" . $row["last_name"] . "</td>
                          <td id='capacity-table-td' style='font-weight:500;'>" . $row["first_name"] . "</td>
                          <td id='capacity-table-td' style='font-weight:500;'>" . $row["role"] . "</td>
                          <td id='capacity-table-td' style='font-weight:500; text-align: center;'><input id='autoin_".$sequence."' class='capacity-text-input' type='text' name='velocity_".$sequence."[]' value='" . $vel . "' onchange='autoLoad".$sequence."();' /> %</td>
                          <td id='capacity-table-td' style='font-weight:500; text-align: center;'><input id='autoin2_".$sequence."' class='capacity-text-input' type='text' name='daysoff_".$sequence."[]' value='".$doff."' onchange='autoLoad".$sequence."();' /></td>
                          <td id='capacity-table-td' style='font-weight:500; text-align: center;  background: #e9e9e9;'><input id='story_".$sequence."' class='capacity-text-input' type='text' name='storypoints_".$sequence."[]' value='".$storypts."' readonly='readonly' style='border: 0;  background: #e9e9e9;' />&nbsp;pts</td>
                          <input type='hidden' name='rownum_".$sequence."[]' id='autoin3_".$sequence."' value='".$rownum."'/>
                      </tr>";
                      $rownum++;
                    }
              } elseif($result_alt->num_rows > 0) {
                // output data of each
                $rownum = 0;
                while ($row = $result->fetch_assoc()) {
                  if ($row["role"] == "SM") {
                    $velocityType = "SCRUM_MASTER_ALLOCATION";
                  } else if ($row["role"] == "PO") {
                    $velocityType = "PRODUCT_OWNER_ALLOCATION";
                  } else  {
                    $velocityType = "AGILE_TEAM_MEMBER_ALLOCATION";
                  }
                  $sql2 = "SELECT * FROM `preferences` WHERE name='".$velocityType."';";
                  $result2 = $db->query($sql2);
                  if ($result2->num_rows > 0) {
                    $row2 = $result2->fetch_assoc();
                  }
                  if (isset($teamcapacity[$rownum]) && !isset($_POST['restore'.$sequence]) && isset($_POST['submit0'])){
                    $storypts = $teamcapacity[$rownum];
                    echo 'document.cookie = escape("icap'.$sequence.'") + "=" + escape(icap'.$sequence.');';
                    
                  }else{
                    $storypts = round(($duration-0)*((100-$overhead_percentage)/100)*($row2["value"]/100));

                  }
                  $valueForJS = $row2["value"];
                  if (isset($daysoff[$rownum]) && !isset($_POST['restore'.$sequence])  && isset($_POST['submit0'])){
                    $doff = $daysoff[$rownum];
                  } else {
                    $doff = 0;
                  }
                  if (isset($velocity[$rownum]) && !isset($_POST['restore'.$sequence]) && isset($_POST['submit0'])){
                    $vel = $velocity[$rownum];
                  } else {
                    $vel = $row2["value"];
                  }

                    echo
                    "
                    <tr>
                        <td id='capacity-table-td' style='font-weight:500;'>" . $row["last_name"] . "</td>
                        <td id='capacity-table-td' style='font-weight:500;'>" . $row["first_name"] . "</td>
                        <td id='capacity-table-td' style='font-weight:500;'>" . $row["role"] . "</td>
                        <td id='capacity-table-td' style='font-weight:500; text-align: center;'><input id='autoin_".$sequence."' class='capacity-text-input' type='text' name='velocity_".$sequence."[]' value='" . $vel . "' onchange='autoLoad".$sequence."();' /> %</td>
                        <td id='capacity-table-td' style='font-weight:500; text-align: center;'><input id='autoin2_".$sequence."' class='capacity-text-input' type='text' name='daysoff_".$sequence."[]' value='".$doff."' onchange='autoLoad".$sequence."();' /></td>
                        <td id='capacity-table-td' style='font-weight:500; text-align: center;  background: #e9e9e9;'><input id='story_".$sequence."' class='capacity-text-input' type='text' name='storypoints_".$sequence."[]' value='".$storypts."' readonly='readonly' style='border: 0;  background: #e9e9e9;' />&nbsp;pts</td>
                        <input type='hidden' name='rownum_".$sequence."[]' id='autoin3_".$sequence."' value='".$rownum."'/>
                    </tr>";
                    $rownum++;

                    //echo "vel is: " .$vel . ", days off: " . $doff .".";
                    // submit the values to correlating tables to TEST
                }

              } else {
                echo "<tr><td colspan='6' id='capacity-table-td'  style='text-align: center; font-weight: bold; padding: 20px 0 20px 0'>";
                  print "NO TEAM MEMBERS ASSIGNED TO TEAM \"".$selected_team."\"";
                  echo "</td></tr>";
              }

              $result->close();

              echo '</tbody>';
              echo '<tfoot>';
              echo '</tfoot>';

              echo '</table>';
              echo '<input type="submit" id="capacity-button-blue" name="submit0" value="Submit">
              <input type="reset" id="capacity-button-blue" name="restore" value="Restore Defaults">
             </form>';

          echo '<script type="text/javascript">
          $(document).ready(function () {

              $(\'#'.$sequence.'\').DataTable({
                  paging: false,
                  searching: false,
                  infoCallback: false
              });

          });

          function autoForm'.$sequence.'() {
            document.getElementById(\'maincap'.$sequence.'\').submit();
            
          }

          function autoLoad'.$sequence.'() {
            var velocity'.$sequence.' = $("input[name=\'velocity_'.$sequence.'[]\']")
                .map(function(){return $(this).val();}).get();
            var daysoff'.$sequence.' = $("input[name=\'daysoff_'.$sequence.'[]\']")
                .map(function(){return $(this).val();}).get();
            var rownum'.$sequence.' = $("input[name=\'rownum_'.$sequence.'[]\']")
                .map(function(){return $(this).val();}).get();

            var overhead = "'.$overhead_percentage.'";
            var duration'.$sequence.' = "'.$duration.'";
            var value = "'.$valueForJS.'";
            var totalcap_old = "'.$totalcapacity.'";
            var icap'.$sequence.'_old = "'.$icapacity.'";
            var icap'.$sequence.' = 0;

            for (var i in rownum'.$sequence.') {
                var storypts'.$sequence.'_'.$rownum.' = Math.round( ( duration'.$sequence.' - daysoff'.$sequence.'[i] ) * ( ( 100-overhead ) / 100 ) * ( velocity'.$sequence.'[i] / 100 ) );
                $("input[name=\'storypoints_'.$sequence.'[]\']").eq(i).val(storypts'.$sequence.'_'.$rownum.');
                icap'.$sequence.' += storypts'.$sequence.'_'.$rownum.';

            }

            document.getElementsByName("icap'.$sequence.'")[0].innerHTML = icap'.$sequence.';
            document.cookie = escape("icap'.$sequence.'") + "=" + escape(icap'.$sequence.');
            $( "icap'.$sequence.'" ).replaceWith( icap'.$sequence.' );
              var capdiff'.$sequence.' = icap'.$sequence.' - icap'.$sequence.'_old;
              var tcap = parseInt(capdiff'.$sequence.') + parseInt(totalcap_old);

              document.getElementsByName("totalcap")[0].innerHTML = tcap;
              document.cookie = escape("totalcapCookie") + "=" + escape(tcap);
              console.log("icap_old: " +  icap'.$sequence.'_old);
              console.log("icap: " +  icap'.$sequence.');
              console.log("storypoints'.$sequence.'_'.$rownum.': " + storypts'.$sequence.'_'.$rownum.');

          }




      </script>';
        }

          ///////////////////////////Funtion End/////////////////////////////////////////////////////////


        };
            ?>
