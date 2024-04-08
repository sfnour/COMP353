<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL FINAL PROJECT</title>
</head>

<body>

    <h1>Health Facility Employee Status Tracking System (HFESTS) - Final Project</h1>


    <h2>Enter Query Number</h2>
    <form action="index.php" method="post">
        <label for="number">Number:</label>
        <input type="number" id="number" name="number">
        <button type="submit">Submit</button>
    </form>

    <form action="index.php" method="post">
        <label for="query">Enter Query</label>
        <textarea id="query" name="query" rows="15" cols="150"></textarea>
        <button type="submit">Submit</button>
    </form>

    <?php
    $servername = "ojc353.encs.concordia.ca";
    $username = "ojc353_4";
    $password = "XqAsw8Y";
    $database = "ojc353_4";
    $number = "";
    $sql = "";
    $query = "";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Connected successfully<br><br>";
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Check if "number" or "query" field exists in the $_POST array
        if (isset($_POST["number"])) {
            // Access the value of the "number" field
            $number = (int)$_POST["number"];
        } elseif (isset($_POST["query"])) {
            // Access the value of the "query" field
            $query = $_POST["query"];
        }
    }

    if ($number == 8) {
        $sql = "SELECT Facility.*,
        CONCAT(FirstName,' ',LastName) AS GeneralManager,
        COUNT(*) AS NumberEmployees,
        COALESCE(DoctorCount.NumberDoctors, 0) AS NumberOfDoctors,
        COALESCE(NurseCount.NumberNurses, 0) AS NumberOfNurses
 FROM Facility
 JOIN (SELECT MedicareNumber, FacilityName, FacilityPostalCode FROM EmploymentRecord WHERE endDate IS NULL) 
 AS FilteredEmploymentRecord ON Facility.Name = FilteredEmploymentRecord.FacilityName AND Facility.PostalCode = FilteredEmploymentRecord.FacilityPostalCode
 JOIN (
     SELECT EmploymentRecord.MedicareNumber, FacilityName, FacilityPostalCode
     FROM EmploymentRecord
     JOIN Employee ON EmploymentRecord.MedicareNumber = Employee.MedicareNumber
     WHERE endDate IS NULL AND EmployeeRole = 'General Manager'
 ) AS GMEmploymentRecord ON Facility.Name = GMEmploymentRecord.FacilityName AND Facility.PostalCode = GMEmploymentRecord.FacilityPostalCode
 JOIN Person ON GMEmploymentRecord.MedicareNumber = Person.MedicareNumber 
 LEFT JOIN (
     SELECT FacilityName, FacilityPostalCode, COUNT(*) AS NumberDoctors
     FROM EmploymentRecord
     JOIN Employee ON EmploymentRecord.MedicareNumber = Employee.MedicareNumber
     WHERE endDate IS NULL AND EmployeeRole = 'Doctor'
     GROUP BY FacilityName, FacilityPostalCode
 ) AS DoctorCount ON Facility.Name = DoctorCount.FacilityName AND Facility.PostalCode = DoctorCount.FacilityPostalCode
 LEFT JOIN (
     SELECT FacilityName, FacilityPostalCode, COUNT(*) AS NumberNurses
     FROM EmploymentRecord
     JOIN Employee ON EmploymentRecord.MedicareNumber = Employee.MedicareNumber
     WHERE endDate IS NULL AND EmployeeRole = 'Nurse'
     GROUP BY FacilityName, FacilityPostalCode
 ) AS NurseCount ON Facility.Name = NurseCount.FacilityName AND Facility.PostalCode = NurseCount.FacilityPostalCode
 GROUP BY Facility.Name
 ORDER BY Facility.Province, Facility.City, Facility.Type, NumberOfDoctors;";

        $result = $conn->query($sql);

        echo "<table border='1'>";
        echo "<tr><th>Name</th><th>Postal Code</th><th>Address</th><th>City</th><th>Province</th><th>Phone Number</th><th>Web Address</th><th>Type</th><th>Capacity Employees</th><th>General Manager</th><th>Number of Employees</th><th>Number of Doctors</th><th>Number of Nurses</th></tr>";

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['Name'] . "</td>";
            echo "<td>" . $row['PostalCode'] . "</td>";
            echo "<td>" . $row['Address'] . "</td>";
            echo "<td>" . $row['City'] . "</td>";
            echo "<td>" . $row['Province'] . "</td>";
            echo "<td>" . $row['PhoneNumber'] . "</td>";
            echo "<td>" . $row['WebAddress'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['CapacityEmployees'] . "</td>";
            echo "<td>" . $row['GeneralManager'] . "</td>";
            echo "<td>" . $row['NumberEmployees'] . "</td>";
            echo "<td>" . $row['NumberOfDoctors'] . "</td>";
            echo "<td>" . $row['NumberOfNurses'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } elseif ($number == 9) {


        $sql = "SELECT FirstName, LastName, startDate, DateOfBirth, Person.MedicareNumber , Person.PhoneNumber, PrimaryResidence.Address AS PrimaryAddress, PrimaryResidence.City AS City, PrimaryResidence.Province AS Province,PrimaryResidence.PostalCode AS 'Postal-Code', 
            Citizenship, EmailAddress, COUNT(Homes.SSN) AS NumberSecondaryResidences FROM EmploymentRecord
            JOIN Person ON EmploymentRecord.MedicareNumber = Person.MedicareNumber
            JOIN (
             SELECT * FROM PersonResidence
             WHERE IsPrimaryResidence = 1
            ) AS Homes ON Person.SSN = Homes.SSN
            JOIN (
              SELECT * FROM PersonResidence
              JOIN Residence ON PersonResidence.ResidencePhoneN = Residence.PhoneNumber AND PersonResidence.ResidencePostalCode = Residence.PostalCode
              WHERE IsPrimaryResidence = 0
            ) AS PrimaryResidence ON Person.SSN = PrimaryResidence.SSN
            WHERE endDate IS NULL
            GROUP BY Person.SSN
            ORDER BY startDate, FirstName, LastName;";





        $result = $conn->query($sql);

        echo "<table border='1'>";
        echo "<tr><th>FirstName</th><th>LastName</th><th>startDate</th><th>DateOfBirth</th><th>MedicareNumber</th><th>PhoneNumber</th><th>Primary Address</th><th>City</th><th>Province</th><th>Postal-Code</th><th>Citizenship</th><th>EmailAddress</th><th>NumberSecondaryResidences</th></tr>";

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['FirstName'] . "</td>";
            echo "<td>" . $row['LastName'] . "</td>";
            echo "<td>" . $row['startDate'] . "</td>";
            echo "<td>" . $row['DateOfBirth'] . "</td>";
            echo "<td>" . $row['MedicareNumber'] . "</td>";
            echo "<td>" . $row['PhoneNumber'] . "</td>";
            echo "<td>" . $row['PrimaryAddress'] . "</td>";
            echo "<td>" . $row['City'] . "</td>";
            echo "<td>" . $row['Province'] . "</td>";
            echo "<td>" . $row['Postal-Code'] . "</td>";
            echo "<td>" . $row['Citizenship'] . "</td>";
            echo "<td>" . $row['EmailAddress'] . "</td>";
            echo "<td>" . $row['NumberSecondaryResidences'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } elseif ($number == 10) {

        $sql = "SELECT 
      Facility.Name AS FacilityName,
      Schedule.ScheduleDate AS Date,
      Schedule.StartTime,
      Schedule.EndTime
  FROM 
      Employee
  JOIN 
      Schedule ON Employee.MedicareNumber = Schedule.Medicare
  JOIN 
      Facility ON Schedule.FacilityName = Facility.Name AND Schedule.FacilityPostalCode = Facility.PostalCode
  WHERE 
      Employee.MedicareNumber = 'B3V6-5DE-7L4N'
      AND Schedule.ScheduleDate BETWEEN '2024-02-01' AND '2024-02-05'
  ORDER BY 
      Facility.Name ASC,
      Schedule.ScheduleDate ASC,
      Schedule.StartTime ASC;";

        $result = $conn->query($sql);

        echo "<table border='1'>";
        echo "<tr><th>FacilityName</th><th>Date</th><th>StartTime</th><th>EndTime</th></tr>";

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['FacilityName'] . "</td>";
            echo "<td>" . $row['Date'] . "</td>";
            echo "<td>" . $row['StartTime'] . "</td>";
            echo "<td>" . $row['EndTime'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } elseif ($number == 11) {
        $sql="SELECT 
        Employee.MedicareNumber AS EmployeeMedicareNumber,
        Person.FirstName, 
        Person.LastName, 
        Person.Occupation, 
        Relationship.Relation, 
        Residence.Address,
        Residence.Type,
        PersonResidence.IsPrimaryResidence
    FROM 
        Employee
    JOIN 
        Relationship ON Employee.MedicareNumber = Relationship.MedicareNumber
    JOIN 
        Person ON Relationship.SSN = Person.SSN
    JOIN 
        PersonResidence ON PersonResidence.SSN = Person.SSN
    JOIN 
        Residence ON PersonResidence.ResidencePhoneN = Residence.PhoneNumber 
                  AND PersonResidence.ResidencePostalCode = Residence.PostalCode
    WHERE 
        Employee.MedicareNumber = 'BV36-5DE7-L4LN'
        AND PersonResidence.IsPrimaryResidence = 1
    
    UNION
    
    SELECT 
        Employee.MedicareNumber AS EmployeeMedicareNumber,
        Person.FirstName, 
        Person.LastName, 
        Person.Occupation, 
        Relationship.Relation, 
        Residence.Address,
        Residence.Type,
        PersonResidence.IsPrimaryResidence
    FROM 
        Employee
    JOIN 
        Relationship ON Employee.MedicareNumber = Relationship.MedicareNumber
    JOIN 
        Person ON Relationship.SSN = Person.SSN
    JOIN 
        PersonResidence ON PersonResidence.SSN = Person.SSN
    JOIN 
        Residence ON PersonResidence.ResidencePhoneN = Residence.PhoneNumber 
                  AND PersonResidence.ResidencePostalCode = Residence.PostalCode
    WHERE 
        Employee.MedicareNumber = 'BV36-5DE7-L4LN'
        AND PersonResidence.IsPrimaryResidence = 0
    GROUP BY 
        Employee.MedicareNumber, Person.FirstName, Person.LastName, Person.MedicareNumber, Person.Occupation, Relationship.Relation, Residence.Address, Residence.Type;";

$result = $conn->query($sql);

echo "<table border='1'>";
echo "<tr><th>EmployeeMedicareNumber</th><th>FirstName</th><th>LastName</th><th>Occupation</th><th>Relation</th><th>Address</th><th>Type</th><th>IsPrimaryResidence</th></tr>";

while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>" . $row['EmployeeMedicareNumber'] . "</td>";
    echo "<td>" . $row['FirstName'] . "</td>";
    echo "<td>" . $row['LastName'] . "</td>";
    echo "<td>" . $row['Occupation'] . "</td>";
    echo "<td>" . $row['Relation'] . "</td>";
    echo "<td>" . $row['Address'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['IsPrimaryResidence'] . "</td>";
    echo "</tr>";
}
echo "</table>";

    } elseif ($number == 12) {


        $sql = "SELECT Person.SSN, FirstName, LastName, InfectionDate, FacilityName, (SELECT COUNT(*)
            FROM PersonResidence
            WHERE SSN = Person.SSN AND IsPrimaryResidence = 0) AS SecondaryResidenceCount
            FROM Infection
            JOIN Person ON Infection.SSN = Person.SSN
            JOIN Employee ON Person.MedicareNumber = Employee.MedicareNumber
            JOIN EmploymentRecord ON Person.MedicareNumber = EmploymentRecord.MedicareNumber
            WHERE  Employee.EmployeeRole = 'Doctor'
            AND InfectionType = 'COVID-19'
            AND InfectionDate >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
            AND endDate IS NULL
            GROUP BY Person.SSN
            ORDER BY FacilityName, SecondaryResidenceCount;";

        $result = $conn->query($sql);

        echo "<table border='1'>";
        echo "<tr><th>SSN</th><th>FirstName</th><th>LastName</th><th>InfectionDate</th><th>FacilityName</th><th>SecondaryResidenceCount</th></tr>";

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['SSN'] . "</td>";
            echo "<td>" . $row['FirstName'] . "</td>";
            echo "<td>" . $row['LastName'] . "</td>";
            echo "<td>" . $row['InfectionDate'] . "</td>";
            echo "<td>" . $row['FacilityName'] . "</td>";
            echo "<td>" . $row['SecondaryResidenceCount'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } elseif ($number == 13) {
        // Logic for number 13
    } elseif ($number == 14) {

        $sql = "SELECT Facility.Name, FirstName, LastName, EmployeeRole, COUNT(IsPrimaryResidence) AS NbOfSecondary
        FROM PersonResidence
        JOIN Person ON PersonResidence.SSN = Person.SSN
        JOIN Schedule ON Schedule.Medicare = Person.SSN
        JOIN Employee ON Employee.MedicareNumber = Schedule.Medicare
        JOIN EmploymentRecord ON EmploymentRecord.MedicareNumber = Employee.MedicareNumber
        JOIN Facility ON Facility.Name = EmploymentRecord.FacilityName AND Facility.PostalCode = EmploymentRecord.FacilityPostalCode
        WHERE Facility.Name = 'Hospital Maisonneuve Rosemont' AND Facility.PostalCode = 'H1T 2M4' AND Schedule.ScheduleDate >= DATE_SUB(CURDATE(), INTERVAL 4 WEEK)
        GROUP BY EmploymentRecord.FacilityName, FirstName, LastName, EmployeeRole
        HAVING NbOfSecondary > 2 
        ORDER BY EmployeeRole ASC, NbOfSecondary;";

        $result = $conn->query($sql);

        echo "<table border='1'>";
        echo "<tr><th>Name</th><th>FirstName</th><th>LastName</th><th>EmployeeRole</th><th>NbOfSecondary</th></tr>";

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['Name'] . "</td>";
            echo "<td>" . $row['FirstName'] . "</td>";
            echo "<td>" . $row['LastName'] . "</td>";
            echo "<td>" . $row['EmployeeRole'] . "</td>";
            echo "<td>" . $row['NbOfSecondary'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        // Logic for number 14
    } elseif ($number == 15) {

        $sql = "SELECT FirstName, LastName, DateOfBirth, MAX(NumberInfected) AS TotalInfections, MAX(DoseNumber) AS TotalVaccines, MIN(EmploymentRecord.StartDate) AS FirstDayAsNurse, EmailAddress, SUM(Hours) AS TotalHours, COUNT(IsPrimaryResidence) AS NbOfSecondary, COUNT(EmploymentRecord.FacilityName) AS CountFacilities
	FROM Infection
	JOIN Vaccination ON Vaccination.SSN = Infection.SSN
	JOIN PersonResidence ON Vaccination.SSN = PersonResidence.SSN
	JOIN Person ON Person.SSN = PersonResidence.SSN
	JOIN Employee ON Employee.MedicareNumber = Person.MedicareNumber
	JOIN EmploymentRecord ON EmploymentRecord.MedicareNumber = Person.MedicareNumber
	JOIN Schedule on EmploymentRecord.MedicareNumber = Schedule.Medicare
	WHERE
		IsPrimaryResidence = '0' AND
		EmployeeRole = 'Nurse' AND
		EmploymentRecord.endDate IS NULL AND
		Infection.InfectionType = 'COVID-19' AND
		Infection.InfectionDate >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND
		Schedule.ScheduleDate >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)  
	GROUP BY FirstName, LastName
	HAVING CountFacilities  > 1
	ORDER BY FirstDayAsNurse ASC, FirstName, LastName;";

        $result = $conn->query($sql);

        echo "<table border='1'>";
        echo "<tr><th>FirstName</th><th>FirstName</th><th>LastName</th><th>DateOfBirth</th><th>TotalInfection</th><th>TotalVaccines</th> <th>FirstDayAsNurse</th><th>EmailAddress</th><th>TotalHours</th><th>NbOfSecondary</th><th>CountFacilities</th></tr>";

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['SSN'] . "</td>";
            echo "<td>" . $row['FirstName'] . "</td>";
            echo "<td>" . $row['LastName'] . "</td>";
            echo "<td>" . $row['DateOfBirth'] . "</td>";
            echo "<td>" . $row['TotalInfection'] . "</td>";
            echo "<td>" . $row['TotalVaccines'] . "</td>";
            echo "<td>" . $row['FirstDayAsNurse'] . "</td>";
            echo "<td>" . $row['EmailAddress'] . "</td>";
            echo "<td>" . $row['TotalHours'] . "</td>";
            echo "<td>" . $row['NbOfSecondary'] . "</td>";
            echo "<td>" . $row['CountFacilities'] . "</td>";

            echo "</tr>";
        }
        echo "</table>";
    } elseif ($number == 16) {

        $sql = "SELECT 
        Employee.EmployeeRole AS Role,
        COUNT(DISTINCT CASE WHEN EmploymentRecord.endDate IS NULL THEN EmploymentRecord.MedicareNumber END) AS TotalEmployees,
        COUNT(DISTINCT CASE WHEN EmploymentRecord.endDate IS NULL AND Infection.InfectionDate >= DATE_SUB(CURRENT_DATE(), INTERVAL 14 DAY) THEN EmploymentRecord.MedicareNumber END) AS CurrentlyInfectedEmployees
    FROM 
        Employee
    JOIN 
        Person ON Employee.MedicareNumber = Person.MedicareNumber
    LEFT JOIN 
        Infection ON Person.SSN = Infection.SSN
    LEFT JOIN 
        EmploymentRecord ON Employee.MedicareNumber = EmploymentRecord.MedicareNumber
    GROUP BY 
        Employee.EmployeeRole
    ORDER BY 
        Employee.EmployeeRole ASC;";

        $result = $conn->query($sql);

        echo "<table border='1'>";
        echo "<tr><th>Role</th><th>TotalEmployees</th><th>CurrentlyInfectedEmployees</th></tr>";

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['Role'] . "</td>";
            echo "<td>" . $row['TotalEmployees'] . "</td>";
            echo "<td>" . $row['CurrentlyInfectedEmployees'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } elseif ($number == 17) {
        $sql = "SELECT 
       Employee.EmployeeRole AS Role,
       COUNT(DISTINCT CASE WHEN EmploymentRecord.endDate IS NULL THEN EmploymentRecord.MedicareNumber END) AS TotalEmployees,
       COUNT(DISTINCT CASE WHEN Infection.SSN IS NULL THEN Person.MedicareNumber END) AS NeverInfectedEmployees
   FROM 
       Employee
   JOIN Person ON Employee.MedicareNumber=Person.MedicareNumber
   JOIN 
       EmploymentRecord ON Person.MedicareNumber = EmploymentRecord.MedicareNumber
   LEFT JOIN 
       Infection ON Employee.MedicareNumber = Infection.SSN
   GROUP BY 
       Employee.EmployeeRole
   ORDER BY 
       Employee.EmployeeRole ASC;";

        $result = $conn->query($sql);

        echo "<table border='1'>";
        echo "<tr><th>Role</th><th>TotalEmployees</th><th>NeverInfectedEmployees</th></tr>";

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['Role'] . "</td>";
            echo "<td>" . $row['TotalEmployees'] . "</td>";
            echo "<td>" . $row['NeverInfectedEmployees'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } elseif ($number == 18) {
        $sql = "SELECT 
        Facility.Province AS Province, 
        COUNT(DISTINCT Facility.Name) AS TotalFacilities, 
        COUNT(DISTINCT CASE WHEN EmploymentRecord.endDate IS NULL THEN EmploymentRecord.MedicareNumber END) AS TotalEmployees, 
        COUNT(DISTINCT CASE WHEN EmploymentRecord.endDate IS NULL AND Infection.InfectionType = 'COVID-19' THEN EmploymentRecord.MedicareNumber END) AS InfectedEmployees, 
        MAX(Facility.CapacityEmployees) AS MaxCapacity, 
        SUM(CASE WHEN Schedule.ScheduleDate BETWEEN CURDATE() AND '2024-05-07' THEN Schedule.Hours ELSE 0 END) AS TotalScheduledHours 
    FROM Facility 
    LEFT JOIN EmploymentRecord ON Facility.Name = EmploymentRecord.FacilityName AND Facility.PostalCode = EmploymentRecord.FacilityPostalCode 
    LEFT JOIN Person ON EmploymentRecord.MedicareNumber = Person.MedicareNumber 
    LEFT JOIN Schedule ON Facility.Name = Schedule.FacilityName AND Facility.PostalCode = Schedule.FacilityPostalCode 
    LEFT JOIN Infection ON Person.SSN = Infection.SSN 
    GROUP BY Facility.Province 
    ORDER BY Facility.Province ASC;";

        $result = $conn->query($sql);

        echo "<table border='1'>";
        echo "<tr><th>Province</th><th>TotalFacilities</th><th>TotalEmployees</th><th>InfectedEmployees</th><th>MaxCapacity</th><th>TotalScheduledHours</th></tr>";
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['Province'] . "</td>";
            echo "<td>" . $row['TotalFacilities'] . "</td>";
            echo "<td>" . $row['TotalEmployees'] . "</td>";
            echo "<td>" . $row['InfectedEmployees'] . "</td>";
            echo "<td>" . $row['MaxCapacity'] . "</td>";
            echo "<td>" . $row['TotalScheduledHours'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } elseif (isset($query)) {

       $result = $conn->query($query);

        // Assuming $result contains the result of your query
        if ($result->rowCount() > 0) {
            // Fetching all rows as an associative array
            $rows = $result->fetchAll(PDO::FETCH_ASSOC);

            // Check if any rows were returned
            if (!empty($rows)) {
                // Output table header
                echo "<table border='1'><tr>";
                foreach ($rows[0] as $key => $value) {
                    echo "<th>" . htmlspecialchars($key) . "</th>";
                }
                echo "</tr>";

                // Output table rows
                foreach ($rows as $row) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars($value) . "</td>";
                    }
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "No results found.";
            }
        } else {
            echo "No results found.";
        }
    }

    if (isset($conn)) {
        $conn = null; // Close the connection
    }
    ?>

    <h2>Query List</h2>
    <ol start="8">
        <li>SELECT Facility.*,
            CONCAT(FirstName,' ',LastName) AS GeneralManager,
            COUNT(*) AS NumberEmployees,
            COALESCE(DoctorCount.NumberDoctors, 0) AS NumberOfDoctors,
            COALESCE(NurseCount.NumberNurses, 0) AS NumberOfNurses
            FROM Facility
            JOIN (SELECT MedicareNumber, FacilityName, FacilityPostalCode FROM EmploymentRecord WHERE endDate IS NULL)
            AS FilteredEmploymentRecord ON Facility.Name = FilteredEmploymentRecord.FacilityName AND Facility.PostalCode = FilteredEmploymentRecord.FacilityPostalCode
            JOIN (
            SELECT EmploymentRecord.MedicareNumber, FacilityName, FacilityPostalCode
            FROM EmploymentRecord
            JOIN Employee ON EmploymentRecord.MedicareNumber = Employee.MedicareNumber
            WHERE endDate IS NULL AND EmployeeRole = 'General Manager'
            ) AS GMEmploymentRecord ON Facility.Name = GMEmploymentRecord.FacilityName AND Facility.PostalCode = GMEmploymentRecord.FacilityPostalCode
            JOIN Person ON GMEmploymentRecord.MedicareNumber = Person.MedicareNumber
            LEFT JOIN (
            SELECT FacilityName, FacilityPostalCode, COUNT(*) AS NumberDoctors
            FROM EmploymentRecord
            JOIN Employee ON EmploymentRecord.MedicareNumber = Employee.MedicareNumber
            WHERE endDate IS NULL AND EmployeeRole = 'Doctor'
            GROUP BY FacilityName, FacilityPostalCode
            ) AS DoctorCount ON Facility.Name = DoctorCount.FacilityName AND Facility.PostalCode = DoctorCount.FacilityPostalCode
            LEFT JOIN (
            SELECT FacilityName, FacilityPostalCode, COUNT(*) AS NumberNurses
            FROM EmploymentRecord
            JOIN Employee ON EmploymentRecord.MedicareNumber = Employee.MedicareNumber
            WHERE endDate IS NULL AND EmployeeRole = 'Nurse'
            GROUP BY FacilityName, FacilityPostalCode
            ) AS NurseCount ON Facility.Name = NurseCount.FacilityName AND Facility.PostalCode = NurseCount.FacilityPostalCode
            GROUP BY Facility.Name
            ORDER BY Facility.Province, Facility.City, Facility.Type, NumberOfDoctors;</li>
        <li>SELECT FirstName, LastName, startDate, DateOfBirth, Person.MedicareNumber , Person.PhoneNumber, PrimaryResidence.Address AS PrimaryAddress, PrimaryResidence.City AS City, PrimaryResidence.Province AS Province,PrimaryResidence.PostalCode AS 'Postal-Code',
            Citizenship, EmailAddress, COUNT(Homes.SSN) AS NumberSecondaryResidences FROM EmploymentRecord
            JOIN Person ON EmploymentRecord.MedicareNumber = Person.MedicareNumber
            JOIN (
            SELECT * FROM PersonResidence
            WHERE IsPrimaryResidence = 1
            ) AS Homes ON Person.SSN = Homes.SSN
            JOIN (
            SELECT * FROM PersonResidence
            JOIN Residence ON PersonResidence.ResidencePhoneN = Residence.PhoneNumber AND PersonResidence.ResidencePostalCode = Residence.PostalCode
            WHERE IsPrimaryResidence = 0
            ) AS PrimaryResidence ON Person.SSN = PrimaryResidence.SSN
            WHERE endDate IS NULL
            GROUP BY Person.SSN
            ORDER BY startDate, FirstName, LastName;</li>
        <li>SELECT
            Facility.Name AS FacilityName,
            Schedule.ScheduleDate AS Date,
            Schedule.StartTime,
            Schedule.EndTime
            FROM
            Employee
            JOIN
            Schedule ON Employee.MedicareNumber = Schedule.Medicare
            JOIN
            Facility ON Schedule.FacilityName = Facility.Name AND Schedule.FacilityPostalCode = Facility.PostalCode
            WHERE
            Employee.MedicareNumber = 'B3V6-5DE-7L4N'
            AND Schedule.ScheduleDate BETWEEN '2024-02-01' AND '2024-02-05'
            ORDER BY
            Facility.Name ASC,
            Schedule.ScheduleDate ASC,
            Schedule.StartTime ASC;</li>
        <li>SELECT 
        Employee.MedicareNumber AS EmployeeMedicareNumber,
        Person.FirstName, 
        Person.LastName, 
        Person.Occupation, 
        Relationship.Relation, 
        Residence.Address,
        Residence.Type,
        PersonResidence.IsPrimaryResidence
    FROM 
        Employee
    JOIN 
        Relationship ON Employee.MedicareNumber = Relationship.MedicareNumber
    JOIN 
        Person ON Relationship.SSN = Person.SSN
    JOIN 
        PersonResidence ON PersonResidence.SSN = Person.SSN
    JOIN 
        Residence ON PersonResidence.ResidencePhoneN = Residence.PhoneNumber 
                  AND PersonResidence.ResidencePostalCode = Residence.PostalCode
    WHERE 
        Employee.MedicareNumber = 'BV36-5DE7-L4LN'
        AND PersonResidence.IsPrimaryResidence = 1
    
    UNION
    
    SELECT 
        Employee.MedicareNumber AS EmployeeMedicareNumber,
        Person.FirstName, 
        Person.LastName, 
        Person.Occupation, 
        Relationship.Relation, 
        Residence.Address,
        Residence.Type,
        PersonResidence.IsPrimaryResidence
    FROM 
        Employee
    JOIN 
        Relationship ON Employee.MedicareNumber = Relationship.MedicareNumber
    JOIN 
        Person ON Relationship.SSN = Person.SSN
    JOIN 
        PersonResidence ON PersonResidence.SSN = Person.SSN
    JOIN 
        Residence ON PersonResidence.ResidencePhoneN = Residence.PhoneNumber 
                  AND PersonResidence.ResidencePostalCode = Residence.PostalCode
    WHERE 
        Employee.MedicareNumber = 'BV36-5DE7-L4LN'
        AND PersonResidence.IsPrimaryResidence = 0
    GROUP BY 
        Employee.MedicareNumber, Person.FirstName, Person.LastName, Person.MedicareNumber, Person.Occupation, Relationship.Relation, Residence.Address, Residence.Type;
</li>
        <li>SELECT Person.SSN, FirstName, LastName, InfectionDate, FacilityName, (SELECT COUNT(*)
            FROM PersonResidence
            WHERE SSN = Person.SSN AND IsPrimaryResidence = 0) AS SecondaryResidenceCount
            FROM Infection
            JOIN Person ON Infection.SSN = Person.SSN
            JOIN Employee ON Person.MedicareNumber = Employee.MedicareNumber
            JOIN EmploymentRecord ON Person.MedicareNumber =
            EmploymentRecord.MedicareNumber
            WHERE Employee.EmployeeRole = 'Doctor'
            AND InfectionType = 'COVID-19'
            AND InfectionDate >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
            AND endDate IS NULL
            GROUP BY Person.SSN
            ORDER BY FacilityName, SecondaryResidenceCount;
        </li>
        <li></li>
        <li>SELECT Facility.Name, FirstName, LastName, EmployeeRole, COUNT(IsPrimaryResidence) AS NbOfSecondary
            FROM PersonResidence
            JOIN Person ON PersonResidence.SSN = Person.SSN
            JOIN Schedule ON Schedule.Medicare = Person.SSN
            JOIN Employee ON Employee.MedicareNumber = Schedule.Medicare
            JOIN EmploymentRecord ON EmploymentRecord.MedicareNumber = Employee.MedicareNumber
            JOIN Facility ON Facility.Name = EmploymentRecord.FacilityName AND Facility.PostalCode = EmploymentRecord.FacilityPostalCode
            WHERE Facility.Name = 'Hospital Maisonneuve Rosemont' AND Facility.PostalCode = 'H1T 2M4' AND Schedule.ScheduleDate >= DATE_SUB(CURDATE(), INTERVAL 4 WEEK)
            GROUP BY EmploymentRecord.FacilityName, FirstName, LastName, EmployeeRole
            HAVING NbOfSecondary > 2
            ORDER BY EmployeeRole ASC, NbOfSecondary;</li>

        <li>
            SELECT FirstName, LastName, DateOfBirth, MAX(NumberInfected) AS TotalInfections, MAX(DoseNumber) AS TotalVaccines, MIN(EmploymentRecord.StartDate) AS FirstDayAsNurse, EmailAddress, SUM(Hours) AS TotalHours, COUNT(IsPrimaryResidence) AS NbOfSecondary, COUNT(EmploymentRecord.FacilityName) AS CountFacilities
            FROM Infection
            JOIN Vaccination ON Vaccination.SSN = Infection.SSN
            JOIN PersonResidence ON Vaccination.SSN = PersonResidence.SSN
            JOIN Person ON Person.SSN = PersonResidence.SSN
            JOIN Employee ON Employee.MedicareNumber = Person.MedicareNumber
            JOIN EmploymentRecord ON EmploymentRecord.MedicareNumber = Person.MedicareNumber
            JOIN Schedule on EmploymentRecord.MedicareNumber = Schedule.Medicare
            WHERE
            IsPrimaryResidence = '0' AND
            EmployeeRole = 'Nurse' AND
            EmploymentRecord.endDate IS NULL AND
            Infection.InfectionType = 'COVID-19' AND
            Infection.InfectionDate >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND
            Schedule.ScheduleDate >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
            GROUP BY FirstName, LastName
            HAVING CountFacilities > 1
            ORDER BY FirstDayAsNurse ASC, FirstName, LastName;
        </li>
        <li>SELECT
            Employee.EmployeeRole AS Role,
            COUNT(DISTINCT CASE WHEN EmploymentRecord.endDate IS NULL THEN EmploymentRecord.MedicareNumber END) AS TotalEmployees,
            COUNT(DISTINCT CASE WHEN EmploymentRecord.endDate IS NULL AND Infection.InfectionDate >= DATE_SUB(CURRENT_DATE(), INTERVAL 14 DAY) THEN EmploymentRecord.MedicareNumber END) AS CurrentlyInfectedEmployees
            FROM
            Employee
            JOIN
            Person ON Employee.MedicareNumber = Person.MedicareNumber
            LEFT JOIN
            Infection ON Person.SSN = Infection.SSN
            LEFT JOIN
            EmploymentRecord ON Employee.MedicareNumber = EmploymentRecord.MedicareNumber
            GROUP BY
            Employee.EmployeeRole
            ORDER BY
            Employee.EmployeeRole ASC;</li>
        <li>SELECT
            Employee.EmployeeRole AS Role,
            COUNT(DISTINCT CASE WHEN EmploymentRecord.endDate IS NULL THEN EmploymentRecord.MedicareNumber END) AS TotalEmployees,
            COUNT(DISTINCT CASE WHEN Infection.SSN IS NULL THEN Person.MedicareNumber END) AS NeverInfectedEmployees
            FROM
            Employee
            JOIN Person ON Employee.MedicareNumber=Person.MedicareNumber
            JOIN
            EmploymentRecord ON Person.MedicareNumber = EmploymentRecord.MedicareNumber
            LEFT JOIN
            Infection ON Employee.MedicareNumber = Infection.SSN
            GROUP BY
            Employee.EmployeeRole
            ORDER BY
            Employee.EmployeeRole ASC;</li>
        <li>SELECT
            Facility.Province AS Province,
            COUNT(DISTINCT Facility.Name) AS TotalFacilities,
            COUNT(DISTINCT CASE WHEN EmploymentRecord.endDate IS NULL THEN EmploymentRecord.MedicareNumber END) AS TotalEmployees,
            COUNT(DISTINCT CASE WHEN EmploymentRecord.endDate IS NULL AND Infection.InfectionType = 'COVID-19' THEN EmploymentRecord.MedicareNumber END) AS InfectedEmployees,
            MAX(Facility.CapacityEmployees) AS MaxCapacity,
            SUM(CASE WHEN Schedule.ScheduleDate BETWEEN CURDATE() AND '2024-05-07' THEN Schedule.Hours ELSE 0 END) AS TotalScheduledHours
            FROM Facility
            LEFT JOIN EmploymentRecord ON Facility.Name = EmploymentRecord.FacilityName AND Facility.PostalCode = EmploymentRecord.FacilityPostalCode
            LEFT JOIN Person ON EmploymentRecord.MedicareNumber = Person.MedicareNumber
            LEFT JOIN Schedule ON Facility.Name = Schedule.FacilityName AND Facility.PostalCode = Schedule.FacilityPostalCode
            LEFT JOIN Infection ON Person.SSN = Infection.SSN
            GROUP BY Facility.Province
            ORDER BY Facility.Province ASC;</li>



    </ol>

</body>

</html>