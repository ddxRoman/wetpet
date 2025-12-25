{{-- ===== СЛАЙДЕР ВРАЧЕЙ ===== --}}
<div class="simple-slider" id="doctorSlider">
    <img id="slidePhoto" src="" alt="Доктор" class="slider-photo">

    <div class="slider-info">
        <h3 id="slideName"></h3>
        <p id="slideSpecialization"></p>
    </div>
</div>

{{-- ===== ДАННЫЕ (ПОКА МОК, ПОТОМ ИЗ БД) ===== --}}
<script>
    window.sliderData = [
        {
            photo: '/storage/doctors/biosfera/lopyshanskay.png',
            name: 'Лопушинская Ангелина Михайловна',
            specialization: 'Эндокринология, офтальмология'
        },
    ];

    // ⬇ ПОТОМ ПРОСТО ЗАМЕНИШЬ НА:
    // window.sliderData = @json($doctors);
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const data = window.sliderData || [];
    if (!data.length) return;

    const photo = document.getElementById('slidePhoto');
    const name = document.getElementById('slideName');
    const spec = document.getElementById('slideSpecialization');

    let index = 0;
    const delay = 5000;

    function renderSlide(i) {
        photo.classList.remove('active');

        setTimeout(() => {
            photo.src = data[i].photo;
            name.textContent = data[i].name;
            spec.textContent = data[i].specialization;
            photo.classList.add('active');
        }, 200);
    }

    renderSlide(index);

    setInterval(() => {
        index = (index + 1) % data.length;
        renderSlide(index);
    }, delay);
});
</script>
