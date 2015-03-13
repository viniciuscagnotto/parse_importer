<?php header("Content-Type: text/html; charset=ISO-8859-1",true);?>

<!DOCTYPE html>
<head>
	<title>Gatópolis - Import</title>
	<meta charset="utf-8" />
	<meta name="description" content="Gatopolis - Importação">
	<meta name="viewport" content="width=device-width">
  
<?php
	//PARSE STUFF
	require 'vendor/autoload.php';
	use Parse\ParseClient;
	use Parse\ParseObject;
	use Parse\ParseQuery;
	use Parse\ParseException;
	ParseClient::initialize('53aqM9OxBLBUZb3tlFjWeAyiupJPevW0541c8lQx', 'Nm8C3AeQeXc3tMJxxcj3IWEmqdumFJ9rCFCgu8yQ', 'Xs2akVA0SKSiqugS91jMxc7aXaHZdDQCNENJTUSP');

	//HELPERS
	$arrayData = array("School"=>array(),"Teacher"=>array(),"StudentGroup"=>array(),"Student"=>array(),"StudentGroupTeacher"=>array());
	
	function SchoolExists($name){
		global $arrayData;
		$count = count($arrayData["School"]);
		for($i = 0; $i < $count; $i++){
			$data = $arrayData["School"][$i];
			if($data->get("name") == $name){
				//print "----- JA EXISTE SCHOOL <br />";
				return $data;
			}
		}
		
		return null;
	}	
	
	function TeacherExists($name, $lastName, $schoolID){
		global $arrayData;
		$count = count($arrayData["Teacher"]);
		for($i = 0; $i < $count; $i++){
			$data = $arrayData["Teacher"][$i];
			$school = $data->get("school");
			$school->fetch();
			
			if($data->get("name") == $name && $data->get("last_name") == $lastName && $school->getObjectId() == $schoolID){
				//print "----- JA EXISTE TEACHER <br />";
				return $data;
			}
		}
		
		return null;
	}
	
	function StudentGroupExists($name, $period, $schoolID){
		global $arrayData;
		$count = count($arrayData["StudentGroup"]);
		for($i = 0; $i < $count; $i++){
			$data = $arrayData["StudentGroup"][$i];
			$school = $data->get("school");
			$school->fetch();
			
			if($data->get("name") == $name && $data->get("period") == $period && $school->getObjectId() == $schoolID){
				//print "----- JA EXISTE STUDENT GROUP <br />";
				return $data;
			}
		}
	
		return null;
	}
	
	function StudentExists($name, $period, $birthDate, $gender, $schoolID, $studentGroupID){
		global $arrayData;
		$count = count($arrayData["Student"]);
		for($i = 0; $i < $count; $i++){
			$data = $arrayData["Student"][$i];
			$school = $data->get("school");
			$school->fetch();
			$studentGroup = $data->get("studentgroup");
			$studentGroup->fetch();
			
			if($data->get("name") == $name && $data->get("last_name") == $period && $data->get("birth_date") == $birthDate 
			&& $data->get("gender") == $gender && $school->getObjectId() == $schoolID && $studentGroup->getObjectId() == $studentGroupID){
				//print "----- JA EXISTE STUDENT <br />";
				return $data;
			}
		}
	
		return null;
	}
	
	function StudentGroupTeacherExists($schoolID, $studentGroupID, $teacherID){
		global $arrayData;
		$count = count($arrayData["StudentGroupTeacher"]);
		for($i = 0; $i < $count; $i++){
			$data = $arrayData["StudentGroupTeacher"][$i];
			$school = $data->get("school");
			$school->fetch();
			$studentGroup = $data->get("student_group");
			$studentGroup->fetch();
			$teacher = $data->get("teacher");
			$teacher->fetch();
			
			if($school->getObjectId() == $schoolID && $studentGroup->getObjectId() == $studentGroupID && $teacher->getObjectId() == $teacherID){
				//print "----- JA EXISTE STUDENT GROUP TEACHER <br />";
				return $data;
			}
		}
	
		return null;
	}


	print "<pre>";
	print_r($_FILES);
	print "</pre>";
	
	
	//Loading CSV
	if($_FILES['csv']['tmp_name'] == "")
		print "<script>document.location = 'index.php';</script>";

	$tmpName = $_FILES['csv']['tmp_name'];
	$csvAsArray = array_map('str_getcsv', file($tmpName));
	$linesCount = count($csvAsArray);
	print "$linesCount <br />";
	
	for($i = 0; $i < $linesCount; $i++){
 		$row =  $csvAsArray[$i][0];
 		$cols = explode(";", $row);
 		print "------------------------ LINHA $i - $row<br />";
 		/* COLS
 		 * 0 = nome da escola
 		 * 1 = nome da turma
 		 * 2 = periodo da turma
 		 * 3 = nome do professor
 		 * 4 = nome completo do aluno
 		 * 5 = data de nascimento do aluno
 		 * 6 = sexo do aluno
 		 */

//SCHOOL -------------------------------------------------------------
 		$SchoolObj = SchoolExists(utf8_encode($cols[0]));
 		if($SchoolObj == null){
 			//print "ENTROU SCHOOL <br />";
 			$query = new ParseQuery("School");
	 		$query->equalTo("name", utf8_encode($cols[0]));
	 		$query->limit(1);
	 		$result = $query->find();
	 		
	 		if(count($result) == 0){
	 			$SchoolObj = new ParseObject("School");
	 			$SchoolObj->set("name", utf8_encode($cols[0]));
	 			$SchoolObj->save();
	 		}else{
	 			$SchoolObj = $result[0];
	 		}
	 		
	 		array_push($arrayData["School"],$SchoolObj);	
 		}
 		 		
//TEACHER -------------------------------------------------------------
 		$cutPos = strpos($cols[3], " ");
 		$name = substr($cols[3], 0, $cutPos);
 		$lastName = substr($cols[3], $cutPos);
 			
 		$TeacherObj = TeacherExists(utf8_encode($name), utf8_encode($lastName), $SchoolObj->getObjectId());
 		if($TeacherObj == null){
 			//print "ENTROU TEACHER <br />";
	 		$query = new ParseQuery("Teacher");
	 		$query->equalTo("name", utf8_encode($name));
	 		$query->equalTo("last_name", utf8_encode($lastName));
	 		$query->equalTo("school", $SchoolObj);
	 		$query->limit(1);
	 		$result = $query->find();
	 			
	 		if(count($result) == 0){
	 			$TeacherObj = new ParseObject("Teacher");
	 			$TeacherObj->set("name", utf8_encode($name));
	 			$TeacherObj->set("last_name", utf8_encode($lastName));
	 			$TeacherObj->set("school", $SchoolObj);
	 			$TeacherObj->save();
	 		}else{
	 			$TeacherObj = $result[0];
	 		}
	 		
	 		array_push($arrayData["Teacher"],$TeacherObj);
 		}
 		
//STUDENT GROUP -------------------------------------------------------------
 		$StudentGroupObj = StudentGroupExists(utf8_encode($cols[1]), utf8_encode($cols[2]), $SchoolObj->getObjectId());
 		if($StudentGroupObj == null){
 			//print "ENTROU STUDENT GROUP <br />";
	 		$query = new ParseQuery("StudentGroup");
	 		$query->equalTo("name", utf8_encode($cols[1]));
	 		$query->equalTo("period", utf8_encode($cols[2]));
	 		$query->equalTo("school", $SchoolObj);
	 		$query->limit(1);
	 		$result = $query->find();
	 			
	 		if(count($result) == 0){
	 			$StudentGroupObj = new ParseObject("StudentGroup");
	 			$StudentGroupObj->set("name", utf8_encode($cols[1]));
	 			$StudentGroupObj->set("period", utf8_encode($cols[2]));
	 			$StudentGroupObj->set("school", $SchoolObj);
	 			$StudentGroupObj->save();
	 		}else{
	 			$StudentGroupObj = $result[0];
	 		}
	
	 		array_push($arrayData["StudentGroup"],$StudentGroupObj);
 		}
 		
//STUDENT -------------------------------------------------------------
 		$cutPos = strpos($cols[4], " ");
 		$name = substr($cols[4], 0, $cutPos);
 		$lastName = substr($cols[4], $cutPos);
 			
 		$StudentObj = StudentExists(utf8_encode($name), utf8_encode($lastName), intval($cols[5]), utf8_encode($cols[6]), $SchoolObj->getObjectId(), $StudentGroupObj->getObjectId());
 		if($StudentObj == null){
 			//print "ENTROU STUDENT <br />";
 			$query = new ParseQuery("Student");
	 		$query->equalTo("name", utf8_encode($name));
	 		$query->equalTo("last_name", utf8_encode($lastName));
	 		$query->equalTo("school", $SchoolObj);
	 		$query->equalTo("studentgroup", $StudentGroupObj);
	 		$query->equalTo("birth_date", intval($cols[5]));
	 		$query->equalTo("gender", utf8_encode($cols[6]));
	 		$query->limit(1);
	 		$result = $query->find();
	 		
	 		if(count($result) == 0){
	 			$StudentObj = new ParseObject("Student");
	 			$StudentObj->set("name", utf8_encode($name));
	 			$StudentObj->set("last_name", utf8_encode($lastName));
	 			$StudentObj->set("school", $SchoolObj);
	 			$StudentObj->set("studentgroup", $StudentGroupObj);
	 			$StudentObj->set("birth_date", intval($cols[5]));
	 			$StudentObj->set("gender", utf8_encode($cols[6]));
	 			$StudentObj->save();
	 		}else{
	 			$StudentObj = $result[0];
	 		}
	 		
	 		array_push($arrayData["Student"],$StudentObj);
 		}
 		
//STUDENT GROUP TEACHER -------------------------------------------------------------
 		$StudentGroupTeacherObj = StudentGroupTeacherExists($SchoolObj->getObjectId(), $StudentGroupObj->getObjectId(), $TeacherObj->getObjectId());
 		if($StudentGroupTeacherObj == null){
 			//print "ENTROU STUDENT GROUP TEACHER <br />";
	 		$query = new ParseQuery("studentgroup_teacher");
	 		$query->equalTo("school", $SchoolObj);
	 		$query->equalTo("student_group", $StudentGroupObj);
	 		$query->equalTo("teacher", $TeacherObj);
	 		$query->limit(1);
	 		$result = $query->find();
	 		
	 		if(count($result) == 0){
	 			$StudentGroupTeacherObj = new ParseObject("studentgroup_teacher");
	 			$StudentGroupTeacherObj->set("school", $SchoolObj);
	 			$StudentGroupTeacherObj->set("student_group", $StudentGroupObj);
	 			$StudentGroupTeacherObj->set("teacher", $TeacherObj);
	 			$StudentGroupTeacherObj->save();
	 		}else{
	 			$StudentGroupTeacherObj = $result[0];
	 		}
	 		
	 		array_push($arrayData["StudentGroupTeacher"],$StudentGroupTeacherObj);
 		}
 		
 		//print "NEXT LINE <br /><br />";
	}
	
?>
  
</head>

<body>
  	<div id="main">
	
 	</div>
</body>

</html>
