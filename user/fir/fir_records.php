<?php
// ==========================================
// FILE: user/fir/fir_records.php
// FIR Records Page (All public FIRs)
// ==========================================
include_once '../../includes/header.php';
?>

<div class="container my-5">
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h4><i class="fas fa-database me-2"></i>All FIR Records</h4>
        </div>
        <div class="card-body">
            <input type="text" id="searchInput" class="form-control mb-3" placeholder="Search FIR by number or type...">
            <div class="table-responsive">
                <table class="table table-striped" id="firTable">
                    <thead>
                        <tr><th>FIR No.</th><th>Date</th><th>Type</th><th>Status</th><th>Details</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>#FIR001</td><td>2024-01-15</td><td>Cyber Crime</td><td>Investigation</td><td><a href="fir_details.php?id=1" class="btn btn-sm btn-info">View</a></td></tr>
                        <tr><td>#FIR002</td><td>2024-02-20</td><td>Theft</td><td>Resolved</td><td><a href="fir_details.php?id=2" class="btn btn-sm btn-info">View</a></td></tr>
                        <tr><td>#FIR003</td><td>2024-03-10</td><td>Fraud</td><td>Pending</td><td><a href="fir_details.php?id=3" class="btn btn-sm btn-info">View</a></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#firTable tbody tr');
    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

<?php include_once '../../includes/footer.php'; ?>