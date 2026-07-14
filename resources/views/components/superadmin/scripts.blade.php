<script>
function saOpenModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}

function saCloseModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}

// Tutup modal saat klik backdrop
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('sa-modal-backdrop')) {
        e.target.classList.remove('open');
        document.body.style.overflow = '';
    }
});

// Tutup modal saat Escape
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.sa-modal-backdrop.open').forEach(function (el) {
            el.classList.remove('open');
            document.body.style.overflow = '';
        });
    }
});
</script>
