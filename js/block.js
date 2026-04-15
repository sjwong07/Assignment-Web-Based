function toggleBlock(userId, currentStatus) {
    fetch("../admin/block_user.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "user_id=" + userId
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // SUCCESS POPUP
            showPopup(data.new_status ? "User Blocked!" : "User Unblocked!");

            // UPDATE BUTTON TEXT
            btn.textContent = data.new_status ? "Unblock" : "Block";

            // UPDATE STATUS LABEL
            let row = btn.closest("tr");
            let statusCell = row.querySelector(".status");

            if (statusCell) {
                statusCell.textContent = data.new_status ? "Blocked" : "Active";
                statusCell.classList.toggle("blocked", data.new_status);
                statusCell.classList.toggle("active", !data.new_status);
            }

            // UPDATE STATUS VALUE
            btn.setAttribute("data-status", data.new_status);

        } else {
            alert("Failed to update user");
        }
    });
}