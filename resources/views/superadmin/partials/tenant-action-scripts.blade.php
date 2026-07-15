<script>
function openBackupModal(tenantId, gymName) {
    document.getElementById('backup-gym-name').textContent = gymName;
    document.getElementById('backup-form').action = '/tenant/' + tenantId + '/backup';
    saOpenModal('modal-backup');
}

function openClearModal(poolId, gymName) {
    document.getElementById('clear-gym-name').textContent = gymName;
    document.getElementById('clear-form').action =
        '{{ url("/config-database") }}/' + poolId + '/clear';
    saOpenModal('modal-clear');
}
</script>
