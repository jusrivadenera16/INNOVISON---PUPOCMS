<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.settings-editable-form').forEach(function (form) {
        const fields = Array.from(form.querySelectorAll('[data-edit-field]'));
        const editButton = form.querySelector('[data-edit-trigger]')
            || (form.id ? document.querySelector('[data-edit-target="' + form.id + '"]') : null);
        const cancelButton = form.querySelector('[data-edit-cancel]');

        function setEditing(isEditing) {
            form.classList.toggle('is-editing', isEditing);
            if (editButton) {
                editButton.hidden = isEditing;
            }
            fields.forEach(function (field) {
                field.disabled = !isEditing;
            });
        }

        editButton?.addEventListener('click', function () {
            setEditing(true);
            fields.find(function (field) {
                return !field.readOnly && field.type !== 'hidden';
            })?.focus();
        });

        cancelButton?.addEventListener('click', function () {
            form.reset();
            setEditing(false);
        });

        form.addEventListener('submit', function () {
            setEditing(true);
        });

        setEditing(false);
    });
});
</script>
