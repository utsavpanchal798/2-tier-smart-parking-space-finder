<?php
include('db_config.php');

if (isset($_POST['clear_now'])) {
    $slot_id = intval($_POST['slot_id']);
    $area_id = intval($_POST['area_id']);

    $update_query = "UPDATE parking_slots SET status = 'Available' WHERE id = $slot_id";
    
    if ($conn->query($update_query) === TRUE) {
        $delete_query = "DELETE FROM bookings WHERE slot_id = $slot_id";
        $conn->query($delete_query);
        
        echo "<script>alert('Slot Cleared Successfully! (સ્લોટ ખાલી થઈ ગયો છે)'); window.location.href='index.php?area_id=$area_id';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
