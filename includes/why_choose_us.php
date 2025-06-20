<?php
function getWhyChooseUsFeatures($conn) {
    $features = [];
    $query = "SELECT * FROM why_choose_us ORDER BY id ASC";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $features[] = $row;
        }
    }
    
    return $features;
}
?>