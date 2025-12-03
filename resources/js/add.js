// Добавление врача
document.getElementById('addDoctorForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    let form = e.target;
    let data = new FormData(form);

    let res = await fetch('/doctors/store', {
        method: 'POST',
        body: data
    });

    let json = await res.json();

    if (json.errors) {
        document.getElementById('doctorErrors').classList.remove('d-none');
        document.getElementById('doctorErrors').innerHTML =
            Object.values(json.errors).join('<br>');
        return;
    }

    location.reload();
});
