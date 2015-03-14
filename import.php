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
	
	//Vinicius Parse
	ParseClient::initialize('53aqM9OxBLBUZb3tlFjWeAyiupJPevW0541c8lQx', 'Nm8C3AeQeXc3tMJxxcj3IWEmqdumFJ9rCFCgu8yQ', 'Xs2akVA0SKSiqugS91jMxc7aXaHZdDQCNENJTUSP');
	//Test
	//ParseClient::initialize('sttB7b180sLTuH6HC7UqI32dbtXFGTlCq0pExKC2', 'aHh2T53yx3rVZBjsPtgBpo7CUTkkzopHAMCvt6JY', 'CvT7EwhkS4H3eRqPEWqN92geoYGFUROravUjCTRF');
	//Dedinhos
	//ParseClient::initialize('gcE3I08oQzulToJoW1aW4sPga3PpBdeqWNkY4wMh', 'fPXSwer7se5781hhwrNGNLlpEHtVyv1pjXkrK56H', 'Hbs5KxubjlWKQ9YY8hxvU9a9FQVUISwfM1xYVKVi');
	
	//SAVE ARRAYS
	$arraySchools = array();
	$arrayTeachers = array();
	$arrayStudentGroups = array();
	$arrayStudents = array();
	$arrayStudentGroupTeachers = array();
	
	//HELPERS
	$generalArray = array("Schools"=>array(),
						  "Teachers"=>array(),
						  "StudentGroups"=>array(),
						  "StudentGroupTeachers"=>array());
	
	function SchoolExists($name){
		global $generalArray;
		$count = count($generalArray["Schools"]);
		for($i = 0; $i < $count; $i++){
			$data = $generalArray["Schools"][$i];
			if($data["obj"]->get("name") == $name){
				return $i;
			}
		}
	
		return -1;
	}
	
	function TeacherExists($name, $lastName, $SchoolObj){
		global $generalArray;
		$count = count($generalArray["Teachers"]);
		for($i = 0; $i < $count; $i++){
			$data = $generalArray["Teachers"][$i];
			$school = $data["school"];
				
			if($data["obj"]->get("name") == $name && $data["obj"]->get("last_name") == $lastName && $school == $SchoolObj){
				return $i;
			}
		}
	
		return -1;
	}
	
	function StudentGroupExists($name, $period, $SchoolObj){
		global $generalArray;
		$count = count($generalArray["StudentGroups"]);
		for($i = 0; $i < $count; $i++){
			$data = $generalArray["StudentGroups"][$i];
			$school = $data["school"];
				
			if($data["obj"]->get("name") == $name && $data["obj"]->get("period") == $period && $school == $SchoolObj){
				return $i;
			}
		}
	
		return -1;
	}
	
	function StudentGroupTeacherExists($SchoolObj, $StudentGroupObj, $TeacherObj){
		global $generalArray;
		$count = count($generalArray["StudentGroupTeachers"]);
		for($i = 0; $i < $count; $i++){
			$data =$generalArray["StudentGroupTeachers"][$i];
			$school = $data["school"];
			$studentGroup = $data["student_group"];
			$teacher = $data["teacher"];
				
			if($school == $SchoolObj && $studentGroup == $StudentGroupObj && $teacher == $TeacherObj){
				return $i;
			}
		}
	
		return -1;
	}
	
	
	//LOADING XML
	if($_FILES['csv']['tmp_name'] == "")
		print "<script>document.location = 'index.php';</script>";
	
	$tmpName = $_FILES['csv']['tmp_name'];
	$csvAsArray = array_map('str_getcsv', file($tmpName));
	$linesCount = count($csvAsArray);
	
	for($i = 0; $i < $linesCount; $i++){
		$row =  $csvAsArray[$i][0];
		$cols = explode(";", $row);
		print "------------------------ LINHA $i - $row<br />";
	
//  	* COLS
//  	* 0 = nome da escola
//  	* 1 = nome da turma
//  	* 2 = periodo da turma
//  	* 3 = nome do professor
//  	* 4 = nome completo do aluno
//  	* 5 = data de nascimento do aluno
//  	* 6 = sexo do aluno
		
//SCHOOL -------------------------------------------------------------
		$schoolIndex = SchoolExists(utf8_encode($cols[0]));
		$SchoolObj = ($schoolIndex == -1 ? null : $generalArray["Schools"][$schoolIndex]["obj"]);
		if($schoolIndex == -1){
			$SchoolObj = new ParseObject("School");
	 		$SchoolObj->set("name", utf8_encode($cols[0]));
	 		$arraySchools[] = $SchoolObj;
	 		
	 		$newSchool = array("obj"=>$SchoolObj);
	 		$generalArray["Schools"][] = $newSchool;
		}
		
//TEACHER -------------------------------------------------------------		
		$cutPos = strpos($cols[3], " ");
		$name = substr($cols[3], 0, $cutPos);
		$lastName = substr($cols[3], $cutPos);
		
		$teacherIndex = TeacherExists(utf8_encode($name), utf8_encode($lastName), $SchoolObj);
		$TeacherObj = ($teacherIndex == -1 ? null : $generalArray["Teachers"][$teacherIndex]["obj"]);
		if($teacherIndex == -1){
			$TeacherObj = new ParseObject("Teacher");
			$TeacherObj->set("name", utf8_encode($name));
			$TeacherObj->set("last_name", utf8_encode($lastName));
			$TeacherObj->set("school", $SchoolObj);
			$arrayTeachers[] = $TeacherObj;
			
			$newTeacher = array("obj"=>$TeacherObj, "school"=>$SchoolObj);
			$generalArray["Teachers"][] = $newTeacher;
		}
	
//STUDENT GROUP -------------------------------------------------------------
		$studentGroupIndex = StudentGroupExists(utf8_encode($cols[1]), utf8_encode($cols[2]), $SchoolObj);
		$StudentGroupObj = ($studentGroupIndex == -1 ? null : $generalArray["StudentGroups"][$studentGroupIndex]["obj"]);
		if($studentGroupIndex == -1){
			$StudentGroupObj = new ParseObject("StudentGroup");
			$StudentGroupObj->set("name", utf8_encode($cols[1]));
			$StudentGroupObj->set("period", utf8_encode($cols[2]));
			$StudentGroupObj->set("school", $SchoolObj);
			$arrayStudentGroups[] = $StudentGroupObj;
			
			$newStudentGroup = array("obj"=>$StudentGroupObj, "school"=>$SchoolObj);
			$generalArray["StudentGroups"][] = $newStudentGroup;
		}
		
//STUDENT -------------------------------------------------------------
		$cutPos = strpos($cols[4], " ");
		$name = substr($cols[4], 0, $cutPos);
		$lastName = substr($cols[4], $cutPos);

//STUDENTS DONT NEED ANY CHECK
// 		$studentIndex = StudentExists(utf8_encode($name), utf8_encode($lastName), intval($cols[5]), utf8_encode($cols[6]), $SchoolObj->getObjectId(), $StudentGroupObj->getObjectId());
// 		$StudentObj = ($studentIndex == -1 ? null : $arrayStudents[$studentIndex]);
// 		if($studentIndex == -1){
			$StudentObj = new ParseObject("Student");
			$StudentObj->set("name", utf8_encode($name));
			$StudentObj->set("last_name", utf8_encode($lastName));
			$StudentObj->set("school", $SchoolObj);
			$StudentObj->set("studentgroup", $StudentGroupObj);
			$StudentObj->set("birth_date", intval($cols[5]));
			$StudentObj->set("gender", utf8_encode($cols[6]));
			$arrayStudents[] = $StudentObj;
// 		}

//STUDENT GROUP TEACHER -------------------------------------------------------------
		$studentGroupTeacherIndex = StudentGroupTeacherExists($SchoolObj, $StudentGroupObj, $TeacherObj);
		//$StudentGroupTeacherObj = ($studentGroupTeacherIndex == -1 ? null : $arrayStudentGroupTeachers[$studentGroupTeacherIndex]);
		if($studentGroupTeacherIndex == -1){
			$StudentGroupTeacherObj = new ParseObject("studentgroup_teacher");
			$StudentGroupTeacherObj->set("school", $SchoolObj);
			$StudentGroupTeacherObj->set("student_group", $StudentGroupObj);
			$StudentGroupTeacherObj->set("teacher", $TeacherObj);
			$arrayStudentGroupTeachers[] = $StudentGroupTeacherObj;
			
			$newStudentGroupTeacher = array("obj"=>$StudentGroupTeacherObj, "school"=>$SchoolObj, "student_group"=>$StudentGroupObj, "teacher"=>$TeacherObj);
			$generalArray["StudentGroupTeachers"][] = $newStudentGroupTeacher;
		}
		
	} //END FOR
	
	//Saving Data
	ParseObject::saveAll($arraySchools);
	ParseObject::saveAll($arrayTeachers);
	ParseObject::saveAll($arrayStudentGroups);
	ParseObject::saveAll($arrayStudents);
	ParseObject::saveAll($arrayStudentGroupTeachers);
	
	
?>
  
</head>

<body>
  	<div id="main">
	
 	</div>
</body>

</html>
