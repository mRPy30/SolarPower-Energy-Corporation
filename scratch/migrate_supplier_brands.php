<?php
include 'config/dbconn.php';

// 2. Migrate existing suppliers into brands table
$res = $conn->query("SELECT * FROM supplier");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $name = $row['supplierName'];
        $contact = $row['contactPerson'];
        $phone = $row['phone'];
        $country = $row['country'];
        
        // Check if brand already exists
        $chk = $conn->prepare("SELECT brand_id FROM brands WHERE brand_name = ?");
        $chk->bind_param("s", $name);
        $chk->execute();
        $chkRes = $chk->get_result();
        if ($chkRes->num_rows == 0) {
            $ins = $conn->prepare("INSERT INTO brands (brand_name, contact_person, phone, location_country) VALUES (?, ?, ?, ?)");
            $ins->bind_param("ssss", $name, $contact, $phone, $country);
            if ($ins->execute()) {
                echo "Migrated supplier '$name' to brands.\n";
            }
            $ins->close();
        } else {
            // Update existing brand with supplier details
            $upd = $conn->prepare("UPDATE brands SET contact_person = ?, phone = ?, location_country = ? WHERE brand_name = ?");
            $upd->bind_param("ssss", $contact, $phone, $country, $name);
            if ($upd->execute()) {
                echo "Updated brand '$name' with supplier details.\n";
            }
            $upd->close();
        }
        $chk->close();
    }
}

echo "Migration finished.\n";
