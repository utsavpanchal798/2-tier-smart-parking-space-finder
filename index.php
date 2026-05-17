<?php
include('db_config.php');

$selected_area = isset($_GET['area_id']) ? intval($_GET['area_id']) : 1;

$areas_query = "SELECT * FROM areas";
$areas_result = $conn->query($areas_query);

$slots_query = "SELECT * FROM parking_slots WHERE area_id = $selected_area";
$slots_result = $conn->query($slots_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Parking Space Finder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .slot-card {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            padding: 25px;
            border-radius: 12px;
            color: white;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .slot-card:hover { transform: scale(1.05); }
        .available { background-color: #198754; } 
        .booked { background-color: #dc3545; } 
    </style>
</head>
<body class="bg-light">

<div class="container my-5">
    <h2 class="text-center mb-2">🚗 Smart Parking Space Finder</h2>
    <p class="text-center text-muted mb-4">Designed by: Utsav Panchal</p>

    <div class="row justify-content-center mb-5">
        <div class="col-md-8">
            <div class="card p-4 shadow-sm">
                <form method="GET" action="index.php" id="areaForm">
                    <label for="area_id" class="form-label fw-bold">Select Location (લોકેશન પસંદ કરો):</label>
                    <div class="d-flex gap-2">
                        <select name="area_id" id="area_id" class="form-select" onchange="document.getElementById('areaForm').submit()">
                            <?php while($area = $areas_result->fetch_assoc()): ?>
                                <option value="<?php echo $area['id']; ?>" <?php if($area['id'] == $selected_area) echo 'selected'; ?>>
                                    <?php echo $area['area_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <a href="index.php?area_id=<?php echo $selected_area; ?>" class="btn btn-primary d-flex align-items-center gap-1">
                            🔄 <span>Refresh</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <h4 class="mb-4 text-secondary">Live Parking Slots Status:</h4>
    <div class="row g-4">
        <?php if($slots_result->num_rows > 0): ?>
            <?php while($slot = $slots_result->fetch_assoc()): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card slot-card <?php echo ($slot['status'] == 'Available') ? 'available' : 'booked'; ?> shadow-sm" 
                         data-bs-toggle="modal" 
                         data-bs-target="<?php echo ($slot['status'] == 'Available') ? '#bookingModal' : '#clearModal'; ?>" 
                         data-slotid="<?php echo $slot['id']; ?>" 
                         data-slotnum="<?php echo $slot['slot_number']; ?>">
                        <div><?php echo $slot['slot_number']; ?></div>
                        <div class="fs-6 mt-2 fw-normal">
                            <?php echo ($slot['status'] == 'Available') ? '🟢 Empty (Click to Book)' : '🔴 Full (Click to Clear)'; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-danger">No slots found.</p>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Book Parking Slot</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="book_slot.php" method="POST">
          <div class="modal-body">
                <input type="hidden" name="slot_id" id="modal_slot_id">
                <input type="hidden" name="area_id" value="<?php echo $selected_area; ?>">
                
                <div class="mb-3">
                    <label class="form-label">Selected Slot:</label>
                    <input type="text" id="modal_slot_number" class="form-control" readonly>
                </div>
                <div class="mb-3">
                    <label for="vehicle_number" class="form-label">Vehicle Number (ગાડીનો નંબર):</label>
                    <input type="text" name="vehicle_number" class="form-control" placeholder="e.g. GJ01AA1234" required>
                </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="book_now" class="btn btn-success">Confirm Booking</button>
          </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="clearModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Release / Clear Parking Slot</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="clear_slot.php" method="POST">
          <div class="modal-body">
                <input type="hidden" name="slot_id" id="clear_slot_id">
                <input type="hidden" name="area_id" value="<?php echo $selected_area; ?>">
                
                <p class="fs-5 text-center">શું તમે આ પાર્કિંગ સ્લોટ ખાલી કરવા માંગો છો?</p>
                <div class="mb-3 text-center fw-bold fs-4 text-danger">
                    Slot: <span id="clear_slot_number"></span>
                </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep It</button>
            <button type="submit" name="clear_now" class="btn btn-danger">Yes, Clear Slot</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
    const bookingModal = document.getElementById('bookingModal');
    if (bookingModal) {
        bookingModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            document.getElementById('modal_slot_id').value = button.getAttribute('data-slotid');
            document.getElementById('modal_slot_number').value = button.getAttribute('data-slotnum');
        });
    }

    const clearModal = document.getElementById('clearModal');
    if (clearModal) {
        clearModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            document.getElementById('clear_slot_id').value = button.getAttribute('data-slotid');
            document.getElementById('clear_slot_number').innerText = button.getAttribute('data-slotnum');
        });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
