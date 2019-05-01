
<?php

  $nav_selected = "PIPLANNING";
  $left_buttons = "YES";
  $left_selected = "CALCULATE";

  include("./nav.php");
  include("./db_connection.php");
  global $db;
   $db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
   $db->set_charset("utf8");


  echo'<!--Copies in Bears custom stylesheet-->
  <link rel="stylesheet" type="text/css" href="styleCustom.css">';
  //Checks for ART Cookie, if it is not available it will update the cookie with a default value using the artCookie function
//initializes remaining variables
$program_increment="";
$art="";
$program_increment_menu='';
$program_increment_select='';
$duration = '';
$overhead_percentage = getOverheadPercentage();
$default_total = 56;

  //Checks for ART Cookie, if it is not available it will update the cookie with a default value using the artCookie function
  if(!isset($_COOKIE['artCookie'])){
    //established finds the value to use for the ART variable and sets the cookie
    $art_select = setArtCookie();
  } else {
    $art_select = $_COOKIE['artCookie'];
  };

  //checks if a team has been selected. If it has not then if finds the default team name and applies it to the team variable
  if(!isset($_COOKIE['teamSelectCookie'])){
  //sets the default team name
  $team = getDefaultTeamName($art_select);
  $selected_team = getTeamID($team);
  setcookie('teamSelectCookie', $selected_team, time()-3600 );
  } else {
    $selected_team  = $_COOKIE['teamSelectCookie'];
  };

//finds the team id for the team name for the selected team script

//Function to build ART select menu. Updates selected default with the Cookie value
$art = buildArtMenu($art_select);


//uses the pi Select Now function to identify the PI ID within the current date and adds it to the pi id select variable for the default
$program_increment_select = piSelectNow();

//capturing the pi id cookie to use for the array and build the menu list
if(!isset($_COOKIE['piCookie'])){
  $program_increment=$program_increment_select;
  setcookie('piCookie', $program_increment_select, time()-3600);
  $program_increment_menu = buildPi_idMenu($program_increment, true);
}elseif(isset($_COOKIE['piCookie']) && ($_COOKIE['piCookie'] != $program_increment_select)){
  $sql = "SELECT * FROM
  (SELECT PI_id, MIN(start_date) as start_date, MAX(end_date) as end_date FROM cadence 
  WHERE start_date <=  NOW()  OR end_date >=  NOW() GROUP BY PI_id ) as PI
  WHERE PI.start_date <=  NOW() 
  AND PI.PI_id ='".$_COOKIE['piCookie']."';";
$result = $db->query($sql);
if ($result->num_rows > 0) {
  $program_increment=$program_increment_select;
  setcookie('piCookie', $program_increment_select, time()-3600);
  $program_increment_menu = buildPi_idMenu($program_increment, true);
} else {
  $program_increment = $_COOKIE['piCookie'];
  $program_increment_menu = buildPi_idMenu($program_increment, true);
}
} else {
  $program_increment = $_COOKIE['piCookie'];
  $program_increment_menu = buildPi_idMenu($program_increment, true);
};


//assigning duration with a default value
$duration = 10;
//initializes the totalcapacity variable
$sql = "SELECT * FROM `capacity` WHERE program_increment='".$program_increment."' AND team_id='".$selected_team."'";
$result = $db->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (isset($teamcapacity)  && !isset($_POST['restore'])  && !isset($_POST['submit0'])){
      $totalcapacity = $row["total"] ;
    }else{
      $totalcapacity = $row["total"];
    }
} else {

  if (!isset($teamcapacity)  && !isset($_POST['restore'])  && !isset($_POST['submit0'])){
          //figures out if there how many team members then uses the value to calculate the total capacity for displat
          $sql_member = "SELECT last_name, first_name, role FROM `membership`
          JOIN `employees` on (membership.polarion_id = employees.number)
          JOIN `trains_and_teams` on (membership.team_name = trains_and_teams.team_name)
          WHERE trains_and_teams.team_id = '".$selected_team."';";
          $result_member = $db->query($sql_member);
          $sql_alt_member = "SELECT last_name, first_name, role FROM `membership`
          JOIN `employees` on (membership.polarion_id = employees.number)
          WHERE membership.team_name = '".$selected_team."';";
          $result_alt = $db->query($sql_alt_member);

    if ($result_member->num_rows > 0) {
      $member_count = $result_member->num_rows;
    } elseif ($result_member->num_rows > 0) {
      $member_count = $result_member->num_rows;
    } else {
      $member_count = 0;
    }
    $default_total = (($duration * .8) * ($member_count - 1));
    $totalcapacity = ($default_total*6);
  }else{
    $totalcapacity = $default_total*6;
  }
};
//Function for assigning the overhead percentage
$overhead_percentage = getOverheadPercentage();

  //Creates an array of the active sequences and iterations to use for loops that will build the tables and Javascripts for each iteration
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

?>

<!--
form for submitting data that will be prepopulated with data from the variables
-->
<div class="right-content" >
    <div class="container">
  <form  method="POST" id="PI_form" name="PI_form">
    <table id="form_table" class="container">
    <tr>
<div style="float: right; margin-right: 10px; text-align: center; font-size: 12px;">
              <div id="capacity-calc-bignum" name="totalcap" id="tcap"><?php echo $totalcapacity ?></div>
              <b>Total Capacity for the Program Increment</b>
            </div>
          </td>

</td></tr>

<div style="float: left; text-align: center; font-size: 12px;">

      <tr>
        <td>Agile Release Train:</td>
        <td>
          <select id="art" name="art" onchange="
          //sets art select to selected value
          var art_select = this.value;
          //sets the selected value as the cookie
          document.cookie = escape('artCookie') + '=' + escape(art_select) ;
          location.reload();
          ">
          <option value="">-- Select --</option>
          <?php echo $art; ?>
        </select>
      </td>
    </tr>
    <tr>
    <td>Program Increment (PI):</td>
    <td>
      <select id="PI_ID" name="pi_id" onchange="
      //sets pi_select to selected value
      var pi_select = this.value;
      //sets the selected value as the cookie
      document.cookie = escape('piCookie') + '=' + escape(pi_select) ;
      location.reload();">
      <?php echo $program_increment_menu; ?>
    </select>
  </td>
</tr>
<tr>
            <td>Names of Teams:</td>
            <td><select name="select-team" onchange="
            //sets team_select to selected value
            var team_select = this.value;
            //sets the selected value as the cookie
            document.cookie = escape('teamSelectCookie') + '=' + escape(team_select);
            location.reload();" >
              <?php

              $sql = "SELECT DISTINCT t.team_id, t.team_name FROM trains_and_teams t where t.parent_name = '".$art_select."';";
              //checks if there is a selected team in the cookie variable. If there is it will update the detault to the cookie value
              if(isset($_COOKIE['teamSelectCookie'])){
                $selected_team = $_COOKIE['teamSelectCookie'];
              }
              $result = $db->query($sql);

              if ($result->num_rows > 0) {

                  while ($row = $result->fetch_assoc()) {
                    if ( trim($selected_team) == trim($row["team_id"]) ) {
                      echo '<option value="'.$row["team_id"].'" selected>'.$row["team_name"].'</option>';
                      $team_name = $row["team_name"];
                    }else{
                      echo '<option value="'.$row["team_id"].'">'.$row["team_name"].'</option>';
                    }

                  }
              }
              ?>
            </select>
            </td>
        </tr>
<tr>
<td><input type="submit" id="submit" name="submit" class="button" value="Generate" onclick="generateStuff()"></td>
<td><input type="hidden" name="current-team-selected" value="<?php echo $selected_team; ?>"></td>
</tr>

</table>
</form><br>


<script>

  //function for capturing the cookie
  function getCookie(cookieName) {
    var name = cookieName + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  };
    ("PI Cookie: " + getCookie('piCookie'));
    ("ART Cookie: "+getCookie('artCookie'));
    ("Team Cookie: " + getCookie('teamSelectCookie'));
  </script>
  <?php

  date_default_timezone_set('America/Chicago');
  echo '<div class="right-content" >
    <div class="container">

      <h3 style=" color: #01B0F1; font-weight: bold;">Capacity Calculations for the Agile Team</h3>';

    if( isset( $_POST['submit'] ) )
    {//checks if the program increment is valid before generating the tables
      $pi_now=piSelectNow();
      if(!isset($_COOKIE['piCookie'])){
        $program_increment=piSelectNow();
      }elseif(isset($_COOKIE['piCookie']) && ($_COOKIE['piCookie'] != $pi_now)){
        $sql = "SELECT * FROM
        (SELECT PI_id, MIN(start_date) as start_date, MAX(end_date) as end_date FROM cadence 
        WHERE start_date <=  NOW()  OR end_date >=  NOW() GROUP BY PI_id ) as PI
        WHERE PI.start_date <=  NOW() 
        AND PI.PI_id ='".$_COOKIE['piCookie']."';";
      $result = $db->query($sql);
      if ($result->num_rows > 0) {
        $program_increment=piSelectNow();
      } else {
        $program_increment = $_COOKIE['piCookie'];
      }
      } else {
        $program_increment = $_COOKIE['piCookie'];
      };
        creatTables($program_increment, $selected_team, $overhead_percentage);
      
    }
///////////////////////////////////////////////////////////////////////////////////////////////////////////

  if (isset($_POST['submit0']) && isset($_COOKIE['totalcapCookie'])) {
   
    //checks for existing capacity entry for the FI 
    $sql = "SELECT * FROM `capacity` WHERE program_increment='".$program_increment."' AND team_id='".$selected_team."'";
    $result = $db->query($sql);
    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      }else{
        //if capacity entry is not found then it builds an insert statement that initializes the iteration values at 0
        $result_id = $db->query("SELECT max(c.id) +1 as next_int FROM capacity c LIMIT 1;");
        if ($result_id->num_rows > 0) {
          $row = $result_id->fetch_assoc();
          $sql_sequence = $row["next_int"];
      }
      $result_name = $db->query("SELECT DISTINCT team_id, team_name FROM trains_and_teams where team_id = '".$selected_team."' Limit 1;");
        if ($result_name->num_rows > 0) {
          $row2 = $result_name->fetch_assoc();
          $team_name = $row2["team_name"];
      }
      $sqlinsert = "INSERT INTO capacity (id, team_id,team_name,program_increment,iteration_1,iteration_2,iteration_3,iteration_4,iteration_5,iteration_6,iteration_P,total) 
      VALUES ('".$sql_sequence."', 
      '".$selected_team."', 
      '".$team_name."',
      '".$program_increment."'
      ,0,0,0,0,0,0,0,0);";
      $result_insert = $db->query($sqlinsert);
      };
//starts values that will update the capacity table with the iteration values
    $pi_capacity = $_COOKIE['totalcapCookie'];
    $count_sequence = count($sequenceArray);
    $PI_array = array();
    echo '<script>document.getElementsByName("totalcap")[0].innerHTML = '.$pi_capacity.';</script>';
    echo '<table width="100%">';
    for($s=0; $s < $count_sequence; $s++ ){
      if(isset($_COOKIE['icap'.$sequenceArray[$s]])){
      $iterationcapacity = $_COOKIE['icap'.$sequenceArray[$s]];
      $sqliter = "UPDATE `capacity` SET iteration_".substr($iterationArray[$s], -1)."='".$iterationcapacity."' WHERE program_increment='".$program_increment."' AND team_id='".$selected_team."';";
      $result_iter = $db->query($sqliter);
    }
    $sqlup = "UPDATE `capacity` SET total='$pi_capacity' WHERE program_increment='".$program_increment."' AND team_id='".$selected_team."';";
    $result_up = $db->query($sqlup);
}
  }
echo '</table>';
?>
      <div id="capacity-footnote">
        Note 1: Closed Iterations will NOT be shown.  The capacity cannot be changed for such iterations.  Show only the active iterations.<br/>
        Note 2: This page can be reached in two ways:
        <ul>
          <li>Capacity > Calculate</li>
          <li>Capacity > Summary > By clicking on one of the numbers</li>
        </ul>
      </div>

      </td>
      </tr>
      </table>



  <?php
  $db->close();

?>
<?php include("./footer.php"); ?>
