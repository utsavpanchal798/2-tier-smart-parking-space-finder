<?php
include('db_config.php');

if (isset($_POST['book_now'])) {
    $slot_id = intval($_POST['slot_id']);
    $vehicle_number = mysqli_real_escape_string($conn, $_POST['vehicle_number']);
    $area_id = intval($_POST['area_id']);

    if (!empty($vehicle_number)) {
   
        $insert_query = "INSERT INTO bookings (slot_id, vehicle_number) VALUES ($slot_id, '$vehicle_number')";
        
        if ($conn->query($insert_query) === TRUE) {
         
            $update_query = "UPDATE parking_slots SET status = 'Booked' WHERE id = $slot_id";
            $conn->query($update_query);
            
            echo "<script>alert('Slot Booked Successfully! (સ્લોટ બુક થઈ ગયો છે)'); window.location.href='index.php?area_id=$area_id';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "<script>alert('Please enter vehicle number!'); window.location.href='index.php?area_id=$area_id';</script>";
    }
}
?>
