<!doctype html> 
<html> 
<head> 
<meta charset="utf-8"> 
<title>Police Emergency Service System</title> 
</head> 
<body bgcolor="#F70B0F"> 
<?php require_once 'nav.php'; ?> 
  
<?php // if post back 
if (isset($_POST["btnDispatch"])) 
{ 
  
 require_once 'db.php'; 
  
 // create database connection 
 $mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE); 
    // check connection 
 if ($mysqli->connect_errno) 
 { 
  die("Failed to connect to MySQL: ".$mysqli->connect_errno); 
 } 
  
 $patrolcarDispatched = $_POST["chkPatrolCar"]; // array of patrolcar being dispatched from post back 
 $numOfPatrocarDispatched = count($patrolcarDispatched); 
  
 //insert new incident 
 $incidentStatus; 
 if ($numOfPatrocarDispatched > 0) { 
  $incidentStatus='2'; // incident status to be set as Dispatched 
 } else { 
  $incidentStatus='1'; // incident status to be set as Pending 
 } 
  
 $sql = "INSERT INTO incident (callerName, phoneNumber, incidentTypeId, incidentLocation,    incidentDesc, incidentStatusId) VALUES (?, ?, ?, ?, ?, ?)"; 
  
 if (!($stmt = $mysqli->prepare($sql))) 
 { 
  die("Prepare failed: ".$mysqli->errno); 
 } 
 
if (!$stmt->bind_param('ssssss', $_POST['callerName'],
						             $_POST['contactNo'],
						             $_POST['incidentType'],
						             $_POST['location'],
						             $_POST['incidentDesc'],
						             $incidentStatus))
		

 {
	 die("Binding parameters failed: ".$stmt->errno);
 }
   
 if(!$stmt->execute())
 {
	 die("Insert incident table failed: ".$stmt->errno);
 }
	
 //retrieve incident_id for new reported incident
        $incidentId=mysqli_insert_id($mysqli);;

        //update the patrolcar status and add it to the table
        for($i=0; $i<$numOfPatrocarDispatched; $i++){
            //update the car
            $sql = "UPDATE patrolcar SET patrolcarStatusId = '1' WHERE patrolcarid = ?";
            
            if (!($stmt = $mysqli->prepare($sql))) {
                die("Prepare failed: ".$mysqli->errno);
            }

            if(!($stmt->bind_param('s',$patrolcarDispatched[$i]))){
                die("Binding parameters failed:" . $stmt->errno);
            }

            if(!($stmt->execute())){
                die("Update patrolcar_status table failed: ".$stmt->errno);
            }

            //insert dispatch data
            $sql = "INSERT INTO dispatch(incidentId, patrolcarId, timeDispatched) VALUES(?,?,NOW())";

            if (!($stmt = $mysqli->prepare($sql))) {
                die("Prepare failed: ".$mysqli->errno);
            }

            if(!($stmt->bind_param('ss', $incidentId, $patrolcarDispatched[$i]))){
                die("Binding parameters failed: ".$stmt->errno);
            }
            if(!($stmt->execute())){
                die("Insert dispatch table failed: ".$stmt->errno);
            }

        }

        $stmt->close();
	
        $mysqli->close();


    }
    
    ?>
  
 
 

  
<!-- display the incident information passed from logcall.php --> 
<form name="form1" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?> "> 
  
<table  width="40%" border="1" align="center" cellpadding="4" cellspacing="4"> 
  
 <tr> 
    <td align="center" colspan="2" bgcolor="#E5E5E5"><strong>Incident Detail</strong></td> 
 </tr> 
  
 <tr> 
    <td width="50%" bgcolor="#E5E5E5">Caller's Name:</td> 
    <td width="50%" bgcolor="#E5E5E5"><?php echo $_POST['callerName'] ?> 
   <input type="hidden" name="callerName" id="callerName" value="<?php echo $_POST['callerName'] ?>"></td> 
 </tr> 
  
 <tr> 
    <td width="50%"bgcolor="#E5E5E5">Contact No :</td> 
    <td width="50%" bgcolor="#E5E5E5"><?php echo $_POST['contactNo'] ?> 
  <input type="hidden" name="contactNo" id="contactNo" value="<?php echo $_POST['contactNo'] ?>"> </td> 
 </tr> 
  
 <tr> 
    <td width="50%" bgcolor="#E5E5E5">Location :</td> 
    <td width="50%" bgcolor="#E5E5E5"><?php echo $_POST['location'] ?> 
  <input type="hidden" name="location" id="location" value="<?php echo $_POST['location'] ?>"> </td> 
 </tr> 
  
 <tr> 
    <td width="50%" bgcolor="#E5E5E5">Incident Type :</td> 
    <td width="50%" bgcolor="#E5E5E5"> <?php echo $_POST['incidentType'] ?>


<input type="hidden" name="incidentType" id="incidentType" value="<?php echo $_POST['incidentType'] ?>"></td> 
 </tr> 
  
 <tr> 
    <td width="50%" bgcolor="#E5E5E5">Description :</td> 
    <td width="50%" bgcolor="#E5E5E5"><textarea name="incidentDesc" cols="45" rows="5" readonly id="incidentDesc"> 
     <?php echo $_POST['incidentdesc'] ?></textarea>  
  <input name="incidentdesc" type="hidden" id="incidentdesc" value="<?php echo $_POST['incidentdesc'] ?>"></td> 
 </tr> 
  
</table> 
 <?php 
 //connect to a database 
 require_once 'db.php'; 
   
 // create a database connection 
    $mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE); 
 // check connection 
    if ($mysqli->connect_errno) { 
  die("Failed to connect to MySQL: ".$mysqli->connect_errno); 
 } 
      
 //retrieve from patrolcar table those patrol cars that are 2:Patrol or 3:Free 
 $sql = "SELECT patrolcarId, statusDesc FROM patrolcar JOIN patrolcar_status ON patrolcar.patrolcarStatusId=patrolcar_status.StatusId WHERE patrolcar.patrolcarStatusId='2' OR patrolcar.patrolcarStatusId='3'"; 
  
 if (!($stmt = $mysqli->prepare($sql))) { 
  die("Prepare failed: ".$mysqli->errno); 
 } 
      
 if (!$stmt -> execute()) { 
  die("Execute failed: ".$stmt->errno); 
 } 
      
 if (!($resultset = $stmt->get_result())) { 
  die("Getting result set failed: ".$stmt->errno); 
 } 
      
 $patrolcarArray; 
      
 while ($row = $resultset->fetch_assoc()) { 
  $patrolcarArray[$row['patrolcarId']] = $row['statusDesc']; 
 } 
      
     $stmt->close(); 
      
     $resultset->close(); 
      
     $mysqli->close(); 
    ?> 
  
 <!-- populare table with patrol car table --> 
 <br><br><table border="1" align="center" width="25%"> 
 <tr> 
  <td colspan="3" bgcolor="#E5E5E5" align="center" >Dispatch Patrolcar Panel</td> 
 </tr> 
 <?php  
    foreach($patrolcarArray as $key=>$value) { 
  ?> 
  <tr> 
    <td align="center" bgcolor="#E5E5E5"><input type="checkbox" name="chkPatrolCar[]" 
      value="<?php echo $key?>"></td> 
   <td bgcolor="#E5E5E5" align="center"><?php echo $key ?></td> 
   <td bgcolor="#E5E5E5" align="center"><?php echo $value ?></td> 
 </tr> 
  <?php  }    ?> 
 <tr> 
    <td bgcolor="#E5E5E5" align="center"><input type="reset" name="btnCancel" id="btnCancel" value="Reset"></td> 
  <td bgcolor="#E5E5E5" colspan="2" align="center" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="btnDispatch" id="btnDispatch" value="Dispatch"> 
     </td> 
 </tr> 
 </table> 
</form> 
</body> 
</html>