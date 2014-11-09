<?php
header('content-type: application/json; charset=utf-8');
header("access-control-allow-origin: *");

$servername = "localhost";
$username = "root";
$password = "12345";
$dbname = "motive";

// Create connection
$conn = new mysqli($servername, $username); //, $password, $dbname
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->select_db($dbname);

$use_average = false;
if (isset($_GET["use_average"])) {
    $use_average = ($_GET["use_average"] == "1");
}
$weightings = array("jobs" => 0.15, "businessEnterprise" => 0.15, "businessLocal" => 0.15, "interests" => 0.55);
if (isset($_GET["weightings"])) {
    //$weightings = ($_GET["weightings"] == "1");
}

$mms_ids = $_GET["mms_ids"];
$resMPs = $conn->query("SELECT mms_id, age, gender, name, image, dept, position, party, people_id, interests FROM mp WHERE mms_id IN ($mms_ids)");
$resJobs = $conn->query("SELECT mp.mms_id, agriculture, production, construction, carsales, wholesale, retail, transport, hospitality, comms, finance, property, science, business, defense, education, health, entertainment FROM jobs INNER JOIN mp WHERE jobs.mms_id = mp.mms_id AND mp.mms_id IN ($mms_ids)");
$resBusinessEnterprise = $conn->query("SELECT mp.mms_id, agriculture, production, construction, carsales, wholesale, retail, transport, hospitality, comms, finance, property, science, business, defense, education, health, entertainment FROM business_enterprise INNER JOIN mp WHERE business_enterprise.mms_id = mp.mms_id AND mp.mms_id IN ($mms_ids)");
$resBusinessLocal = $conn->query("SELECT mp.mms_id, agriculture, production, construction, carsales, wholesale, retail, transport, hospitality, comms, finance, property, science, business, defense, education, health, entertainment FROM business_local INNER JOIN mp WHERE business_local.mms_id = mp.mms_id AND mp.mms_id IN ($mms_ids)");
$resInterests = $conn->query("SELECT mp.mms_id, agriculture, production, construction, carsales, wholesale, retail, transport, hospitality, comms, finance, property, science, business, defense, education, health, entertainment FROM interests INNER JOIN mp WHERE interests.mms_id = mp.mms_id AND mp.mms_id IN ($mms_ids)");

$types = array("jobs", "businessEnterprise", "businessLocal", "interests");
$regular_fields = array("mms_id", "age", "name", "gender", "image", "dept", "position", "party", "people_id", "interests");
$quantification_fields = array("agriculture", "production", "construction", "carsales","wholesale","retail","transport","hospitality","comms","finance","property","science","business","defense","education","health","entertainment");
$inter = array();
if ($resJobs) {
    while ($row = $resJobs->fetch_assoc()) {
        $inter[$row["mms_id"]]["jobs"] = $row;
    }
}
if ($resBusinessEnterprise) {
    while ($row = $resBusinessEnterprise->fetch_assoc()) {
        $inter[$row["mms_id"]]["businessEnterprise"] = $row;
    }
}
if ($resBusinessLocal) {
    while ($row = $resBusinessLocal->fetch_assoc()) {
        $inter[$row["mms_id"]]["businessLocal"] = $row;
    }
}
if ($resInterests) {
    while ($row = $resInterests->fetch_assoc()) {
        $inter[$row["mms_id"]]["interests"] = $row;
    }
}
$result = array();
if ($resMPs->num_rows > 0) {
    while($row = $resMPs->fetch_assoc()) {
        foreach ($regular_fields as $regular_field) {
            $result[$row["mms_id"]][$regular_field] = $row[$regular_field];
        }
        foreach ($quantification_fields as $quantification_field) {
            $result[$row["mms_id"]][$quantification_field] = 0;
        }
        $sum = 0;
        foreach ($types as $type) {
            if (isset($inter[$row["mms_id"]][$type])) {
                foreach ($quantification_fields as $quantification_field) {
                    $result[$row["mms_id"]][$quantification_field] = $result[$row["mms_id"]][$quantification_field] + ($weightings[$type] * floatval($inter[$row["mms_id"]][$type][$quantification_field]));
                }
                $sum = $sum + $weightings[$type];
            }
        }
        if ($sum > 0) {
            foreach ($quantification_fields as $quantification_field) {
                $result[$row["mms_id"]][$quantification_field] = $result[$row["mms_id"]][$quantification_field] / $sum;
            }
        }
    }
}

$batch = "[";
$isFirst = true;
if ($resMPs->num_rows > 0) {
    mysqli_data_seek($resMPs, 0);
}
while ($row = $resMPs->fetch_assoc()) {
    if ($isFirst) {
        $isFirst = false;
    } else {
        $batch = $batch.",";
    }
    $batch = $batch."{\"mms_id\":\"".$result[$row["mms_id"]]["mms_id"]."\",".
        "\"age\":\"".$result[$row["mms_id"]]["age"]."\",".
        "\"name\":\"".$result[$row["mms_id"]]["name"]."\",".
        "\"dept\":\"".$result[$row["mms_id"]]["dept"]."\",".
        "\"position\":\"".$result[$row["mms_id"]]["position"]."\",".
        "\"party\":\"".$result[$row["mms_id"]]["party"]."\",".
        "\"gender\":\"".$result[$row["mms_id"]]["gender"]."\",".
        "\"people_id\":\"".$result[$row["mms_id"]]["people_id"]."\",".
        "\"image\":\"".$result[$row["mms_id"]]["image"]."\",".
        "\"agriculture\":\"".$result[$row["mms_id"]]["agriculture"]."\",".
        "\"production\":\"".$result[$row["mms_id"]]["production"]."\",".
        "\"construction\":\"".$result[$row["mms_id"]]["construction"]."\",".
        "\"carsales\":\"".$result[$row["mms_id"]]["carsales"]."\",".
        "\"wholesale\":\"".$result[$row["mms_id"]]["wholesale"]."\",".
        "\"retail\":\"".$result[$row["mms_id"]]["retail"]."\",".
        "\"transport\":\"".$result[$row["mms_id"]]["transport"]."\",".
        "\"hospitality\":\"".$result[$row["mms_id"]]["hospitality"]."\",".
        "\"comms\":\"".$result[$row["mms_id"]]["comms"]."\",".
        "\"finance\":\"".$result[$row["mms_id"]]["finance"]."\",".
        "\"property\":\"".$result[$row["mms_id"]]["property"]."\",".
        "\"science\":\"".$result[$row["mms_id"]]["science"]."\",".
        "\"business\":\"".$result[$row["mms_id"]]["business"]."\",".
        "\"defense\":\"".$result[$row["mms_id"]]["defense"]."\",".
        "\"education\":\"".$result[$row["mms_id"]]["education"]."\",".
        "\"health\":\"".$result[$row["mms_id"]]["health"]."\",".
        "\"entertainment\":\"".$result[$row["mms_id"]]["entertainment"]."\"}";
    }
    $batch = $batch."]";
if (!$use_average) {
    echo $batch;
} else {
    if ($resMPs->num_rows > 0) {
        mysqli_data_seek($resMPs, 0);
    } else {
        echo "{}";
        return;
    }
    $resultAverage = array();
    foreach ($quantification_fields as $quantification_field) {
        $resultAverage[$quantification_field] = 0;
    }
    while ($row = $resMPs->fetch_assoc()) {
        foreach ($quantification_fields as $quantification_field) {
            $resultAverage[$quantification_field] = $resultAverage[$quantification_field] + $result[$row["mms_id"]][$quantification_field];
        }
    }
    foreach ($quantification_fields as $quantification_field) {
        $resultAverage[$quantification_field] = $resultAverage[$quantification_field] / $resMPs->num_rows;
    }
    echo "{\"mms_ids\":\"".$_GET["mms_ids"]."\",".
        "\"individual_results\":".$batch.",".
        "\"agriculture\":\"".$resultAverage["agriculture"]."\",".
        "\"production\":\"".$resultAverage["production"]."\",".
        "\"construction\":\"".$resultAverage["construction"]."\",".
        "\"carsales\":\"".$resultAverage["carsales"]."\",".
        "\"wholesale\":\"".$resultAverage["wholesale"]."\",".
        "\"retail\":\"".$resultAverage["retail"]."\",".
        "\"transport\":\"".$resultAverage["transport"]."\",".
        "\"hospitality\":\"".$resultAverage["hospitality"]."\",".
        "\"comms\":\"".$resultAverage["comms"]."\",".
        "\"finance\":\"".$resultAverage["finance"]."\",".
        "\"property\":\"".$resultAverage["property"]."\",".
        "\"science\":\"".$resultAverage["science"]."\",".
        "\"business\":\"".$resultAverage["business"]."\",".
        "\"defense\":\"".$resultAverage["defense"]."\",".
        "\"education\":\"".$resultAverage["education"]."\",".
        "\"health\":\"".$resultAverage["health"]."\",".
        "\"entertainment\":\"".$resultAverage["entertainment"]."\"}";
}
$conn->close();
?>
