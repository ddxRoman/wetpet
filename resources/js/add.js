document.getElementById("addDoctorForm").addEventListener("submit", function(e){
    e.preventDefault();

    let formData = new FormData(this);

    fetch("/add-doctor", {
        method: "POST",
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                document.getElementById("doctorErrors").classList.remove("d-none");
                document.getElementById("doctorErrors").innerText = data.error;
            } else {
                alert(data.message);
                location.reload();
            }
        })
        .catch(err => console.error(err));
});
