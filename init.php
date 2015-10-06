<?php

date_default_timezone_set("US/Central");
if ($_REQUEST && $_REQUEST['action'] != "uploadFileImg")
    header("content-type:application/json;charset=utf-8");
require './autoload.php';

use Parse\ParseClient;

ParseClient::initialize(APPID, RESTKEY, MASTERKEY);

use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseACL;
use Parse\ParsePush;
use Parse\ParseUser;
use Parse\ParseInstallation;
use Parse\ParseException;
use Parse\ParseAnalytics;
use Parse\ParseFile;
use Parse\ParseCloud;

switch ($_REQUEST['action']) {
    case "login":

        $user = ParseUser::logIn($_REQUEST['username'], $_REQUEST['password']);
        $returnData = json_encode(array("success" => true, "data" => $user));
        break;
    case 'Employers':
        $employers = new ParseQuery("Employers");
        if (isset($_GET['objectId'])) {
            $objarr = explode(",", str_replace('"', "", $_GET['objectId']));
            $employers->containedIn("objectId", $objarr);
        }
        if (isset($_GET['agencyId'])) {
            $objarr = explode(",", str_replace('"', "", $_GET['agencyId']));
            $employers->containedIn("agencyId", $objarr);
        }
        $employers->limit(1000); 
        $results = $employers->find();
        $returnData = json_encode(array("success" => true, "data" => $results));
        break;
    case 'Agency':
        $agency = new ParseQuery("Agency");
		$agency->limit(1000);
        $agency->descending("createdAt");
        $results = $agency->find();
        $returnData = json_encode(array("success" => true, "data" => $results));
        break;
    case 'agencySpecific':
        $agency = new ParseQuery("Agency");
		$agency->limit(1000);
        $agency->equalTo("objectId", $_GET['objectId']);
        $results = $agency->find();
        $returnData = json_encode(array("success" => true, "data" => $results));
        break;
    case 'getAllAgencyAdmin':
        $users = new ParseQuery("_User");
		$users->limit(1000);
        $users->equalTo("usertype", "AgencyAdmin");
        $results = $users->find();
        $returnData = json_encode(array("success" => true, "data" => $results));
        break;
    case 'getAgencyAdmin':
        $users = new ParseQuery("_User");
		$users->limit(1000);
        $users->equalTo("agencyId", $_GET['agencyId']);
        $users->equalTo("usertype", "AgencyAdmin");
        $results = $users->find();
        $returnData = json_encode(array("success" => true, "data" => $results));
        break;
    case 'updateAgency':
        $dataObject = json_decode(file_get_contents("php://input"), true);
        $updateAgency = new ParseObject('Agency', $_GET['objectId']);
        $updateAgency->set("name", $dataObject['name']);
        $dataSuccess = $updateAgency->save();
        $returnData = '{"code":1}';
        break;
    case 'deleteAgency':
        $deleteagency = new ParseObject('Agency', $_GET['objectId']);
        $deleteagency->destroy();
        $deleteagency->save();
        $returnData = '{"code":1}';
        break;
    case 'updateAgencyAdmin':
//                $user = ParseUser::logIn($_REQUEST['username'], $_REQUEST['password']);
//                $user->set("email", $_REQUEST['email']);  // attempt to change username
//                $user->save();                 
        $query = ParseUser::query();
		$query->limit(1000);
        $userAgain = $query->get($user->getObjectId());
        $userAgain->set("email", $_REQUEST['email']);
        $userAgain->save();
        $returnData = '{"code":1}';
        break;
//	case 'deleteUser':
//		$deleteUser = new ParseObject('_User', $_REQUEST['objectId']);
//		$deleteUser->destroy();
//		$deleteUser->save();
//		break;
    case 'userSpecific':
        $users = new ParseQuery("_User");
		$users->limit(1000);
		$users->equalTo("objectId", $_GET['objectId']);
        $results = $users->find();
        $returnData = json_encode(array("success" => true, "data" => $results));
        break;
    case 'userAgencySpecific':
        $users = new ParseQuery("_User");
		$users->limit(1000);
        $users->equalTo("agencyId", $_GET['agencyId']);
        $results = $users->find();
        $returnData = json_encode(array("success" => true, "data" => $results));
        break;
    case 'getEmployee':
        $employee = new ParseQuery("Employers");
		$employee->limit(1000);
        $employee->equalTo("objectId", $_GET['objectId']);
        $results = $employee->find();
        $returnData = json_encode(array("success" => true, "data" => $results));
        break;
    case 'gallery':
        $gallery = new ParseQuery("gallery");
		$gallery->limit(1000);
        $results = $gallery->find();
        $returnData = json_encode(array("success" => true, "data" => $results));
        break;
    case 'uploadFile':
        $file = ParseFile::createFromData(file_get_contents("php://input"), str_replace(" ", "", $_REQUEST['name']));
        $file->save();
        $url = $file->getURL();
        $name = $file->getName();
        $returnData = '{"name":"' . $name . '","url":"' . $url . '"}';
        break;
    case 'uploadFileImg':
        $file = ParseFile::createFromFile($_FILES['pic']['tmp_name'], str_replace(" ", "", $_FILES['pic']['name']));
        $file->save();
        $url = $file->getURL();
        $name = $file->getName();
        echo '{"name":"' . $name . '","url":"' . $url . '"}';
        exit;
        break;
    case 'updateEmployee':
        $dataObject = json_decode(file_get_contents("php://input"), true);
        $updateEmp = new ParseObject('Employers', $_GET['objectId']);
        $updateEmp->set("name", $dataObject['name']);
        $updateEmp->set("primaryColor", $dataObject['primaryColor']);
        $updateEmp->set("secondaryColor", $dataObject['secondaryColor']);
        $updateEmp->set("agencyId", $dataObject['agencyId']);
        $updateEmp->set("loginCode", $dataObject['loginCode']);
        $updateEmp->setArray("logo", $dataObject['logo']);
        $updateEmp->setArray("html_content", $dataObject['html_content']);
        $dataSuccess = $updateEmp->save();
        $returnData = '{"code":1}';
        break;
    case 'deleteEmployee':
        $deleteEmp = new ParseObject('Employers', $_GET['objectId']);
        $deleteEmp->destroy();
        $deleteEmp->save();
        $returnData = json_encode(array("success" => true));
        break;
    case 'getAgencyEmployee':
        $employee = new ParseQuery("Employers");
		$employee->limit(1000);
        $employee->equalTo("agencyId", $_GET['agencyId']);
        $results = $employee->find();
        //echo "<pre>";
        //print_r($results);
        $returnData = json_encode(array("success" => true, "data" => $results));
        break;
    case 'Users':
        $users = new ParseQuery("_User");
		$users->limit(1000);
        $users->equalTo("usertype", "User");
        $users->descending("createdAt");
        $results = $users->find();
        $returnData = json_encode(array("success" => true, "data" => $results));
        break;
    case 'getAgencyUser':
        $users = new ParseQuery("_User");
		$users->limit(1000);
        $users->equalTo("agencyId", $_GET['agencyId']);
        $users->equalTo("usertype", "User");
        $users->descending("createdAt");
        $results = $users->find();
        $returnData = json_encode(array("success" => true, "data" => $results));
        break;
    case 'updateUser':
        $dataObject = json_decode(file_get_contents("php://input"), true);
        $updateEmp = new ParseObject('Employers', $_REQUEST['objectId']);
        $updateEmp->set("name", $dataObject['name']);
        $updateEmp->set("primaryColor", $dataObject['primaryColor']);
        $updateEmp->set("secondaryColor", $dataObject['secondaryColor']);
        $updateEmp->set("agencyId", $dataObject['agencyId']);
        $updateEmp->set("loginCode", $dataObject['loginCode']);
        $updateEmp->setArray("logo", $dataObject['logo']);
        $updateEmp->setArray("html_content", $dataObject['html_content']);
        $dataSuccess = $updateEmp->save();
        $returnData = '{"code":1}';
        break;
    case 'addAgency':
        $dataObject = json_decode(file_get_contents("php://input"), true);
        $agency = new ParseObject("Agency");
        $agency->set("name", $dataObject['name']);
        $agency->save();
        $returnData = json_encode(array("success" => true, "objectId" => $agency->getObjectId()));
        break;
    case 'checkLoginCodeUnique':
        $employee = new ParseQuery("Employers");
		$employee->limit(1000);
        $employee->equalTo("loginCode", $_REQUEST['loginCode']);
        $results = $employee->find();
        $returnData = json_encode(array("success" => true, "data" => $results));
        break;
    case "updateDirectly":
        $dataObject = json_decode(file_get_contents("php://input"), true);
        $user = ParseUser::logIn($dataObject['username'], $dataObject['password']);
        $objectId = $user['objectId'];
        $userObject = new ParseObject("_User", $objectId);
        if (isset($dataObject['name']) != "")
            $userObject->set("name", $dataObject['name']);
        if (isset($dataObject['username']) != "")
            $userObject->set("username", $dataObject['username']);
        if (isset($dataObject['email']) != "")
            $userObject->set("email", $dataObject['email']);
        if (isset($dataObject['password']) != "")
            $userObject->set("password", $dataObject['password']);

        if (isset($dataObject['usertype']) != "")
            $userObject->set("usertype", $dataObject['usertype']);
        if (isset($dataObject['agencyId']) != "")
            $userObject->set("agencyId", $dataObject['agencyId']);

        if (isset($dataObject['refemployerid']) != "")
            $userObject->set("refemployerid", $dataObject['refemployerid']);




        $userObject->save();
        $returnData = '{"code":1}';
        break;
    case "addUser":
        $users = new ParseQuery("_User");
        $resultUsers = $users->find();
        $resultData = $resultUsers['results'];
        $dataObject = json_decode(file_get_contents("php://input"), true);

        if (count($resultData) > 0) {
            for ($i = 0; $i < count($resultData); $i++) {
                if (trim($resultData[$i]['email']) != "" && $resultData[$i]['email'] === $dataObject['email']) {
                    echo '{"code":4,"message":"Username or Email Alredy Exists"}';
                    exit;
                }
                if (trim($resultData[$i]['username']) === trim($dataObject['username'])) {
                    echo '{"code":4,"message":"Username or Email Alredy Exists"}';
                    exit;
                }
            }
        }

        //$dataObject = json_decode(file_get_contents("php://input"), true);
        $userObject = new ParseObject("_User");
        if (isset($dataObject['name']) != "") {

            $userObject->set("name", $dataObject['name']);
        }
        if (isset($dataObject['username']) != "") {

            $userObject->set("username", $dataObject['username']);
        }
        if (isset($dataObject['email']) != "") {

            $userObject->set("email", $dataObject['email']);
        }
        if (isset($dataObject['password']) != "") {

            $userObject->set("password", $dataObject['password']);
        }
        if (isset($dataObject['passwordNew']) != "") {

            $userObject->set("passwordNew", $dataObject['passwordNew']);
        }
        if (isset($dataObject['usertype']) != "") {

            $userObject->set("usertype", $dataObject['usertype']);
        }
        if (isset($dataObject['agencyId']) != "") {

            $userObject->set("agencyId", $dataObject['agencyId']);
        }
        if (isset($dataObject['emailVerify']) != "") {

            $userObject->set("emailVerify", $dataObject['emailVerify']);
        }
        if (isset($dataObject['refemployerid']) != "") {

            $userObject->set("refemployerid", $dataObject['refemployerid']);
        }

        //send mail
        $message = "Your Username Is : " . $dataObject['username'];
        $message.="\n Your Password Is : " . $dataObject['password'];
        $message.="\n Your Email Is : " . $dataObject['email'];
        $message.="\n Thank You for Registering";
        $message.="\n\r please  visit http://www.ibenefitsapp.com";

        $url = 'https://api.sendgrid.com/api/mail.send.json';
        $postdata = "api_user=mfsmillie&api_key=Whitebox12&to={$dataObject['email']}&toname={$dataObject['username']}&subject=Your iBenefits Credentials&text={$message}&from=info@earthmov.es&fromname=Barney And Barney";
        $httpRequest = curl_init();
        curl_setopt($httpRequest, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($httpRequest, CURLOPT_POST, 1);
        curl_setopt($httpRequest, CURLOPT_HEADER, 1);
        curl_setopt($httpRequest, CURLOPT_URL, $url);
        curl_setopt($httpRequest, CURLOPT_POSTFIELDS, $postdata);
        $returnHeader = curl_exec($httpRequest);
        curl_close($httpRequest);



        $userObject->save();

        $returnData = '{"code":1}';
        break;
    case "delete_User":
        $users = new ParseQuery("_User");
		$users->limit(1000);
        $users->equalTo("agencyId", $_GET['agencyId']);
        $results = $users->find();
        if (count($results['results']) > 0) {
            //$dataObject = json_decode(file_get_contents("php://input"), true);
            $user = ParseUser::logIn($results['results'][0]['username'], $results['results'][0]['passwordNew']);
            $deleteUser = new ParseObject('_User', $user['objectId']);
            $deleteUser->destroy();
            $deleteUser->save();
        }
        $returnData = json_encode(array("code" => 1));
        break;
    case "deleteUser":
        $users = new ParseQuery("_User");
		$users->limit(1000);
        $users->equalTo("objectId", $_GET['objectId']);
        $results = $users->find();
        if (count($results['results']) > 0) {
            //$dataObject = json_decode(file_get_contents("php://input"), true);
            $user = ParseUser::logIn($results['results'][0]['username'], $results['results'][0]['passwordNew']);
            $deleteUser = new ParseObject('_User', $_GET['objectId']);
            $deleteUser->destroy();
            $deleteUser->save();
        }
        $returnData = json_encode(array("code" => 1));
        break;
    case "assignUser":
        $dataObject = json_decode(file_get_contents("php://input"), true);
        $users = new ParseQuery("_User");
		$users->limit(1000);
        $users->equalTo("objectId", $_GET['objectId']);
        $results = $users->find();
        $user = ParseUser::logIn($results['results'][0]['username'], $results['results'][0]['passwordNew']);
        $userObject = new ParseObject("_User", $_GET['objectId']);
        $userObject->set("refemployerid", $dataObject['refemployerid']);
        $userObject->save();
        $returnData = json_encode(array("code" => 1));
        break;
    case "addEmployee":
        $dataObject = json_decode(file_get_contents("php://input"), true);
        $addEmployee = new ParseObject('Employers');
        $addEmployee->set("name", $dataObject['name']);
        $addEmployee->set("primaryColor", $dataObject['primaryColor']);
        $addEmployee->set("secondaryColor", $dataObject['secondaryColor']);
        $addEmployee->set("agencyId", $dataObject['agencyId']);
        $addEmployee->set("loginCode", $dataObject['loginCode']);
        $addEmployee->setArray("logo", $dataObject['logo']);
        $addEmployee->setArray("html_content", $dataObject['html_content']);
        $dataSuccess = $addEmployee->save();
        $returnData = '{"code":1}';
        break;
    case "push":
        $dataObject = json_decode(file_get_contents("php://input"), true);
        ParsePush::send($dataObject);
        $returnData = '{"code":1}';
        break;
    case "tynidata":
        $returnData['data'] = file_get_contents($_REQUEST['stringurl']);
        $returnData = json_encode($returnData['data']);
        break;
}
echo $returnData;
exit;
