document.addEventListener('DOMContentLoaded', function () {
    // ---- Category dropdown follows the selected type (add/edit form) ----
    var form = document.getElementById('transactionForm');

    if (form) {
        var categories = JSON.parse(form.dataset.categories || '{}');
        var typeSelect = document.getElementById('type');
        var categorySelect = document.getElementById('category');
        var selected = form.dataset.selected || '';

        var fillCategories = function (keepSelected) {
            var options = categories[typeSelect.value] || [];
            categorySelect.innerHTML = '';

            var placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Select category';
            categorySelect.appendChild(placeholder);

            options.forEach(function (name) {
                var option = document.createElement('option');
                option.value = name;
                option.textContent = name;
                if (keepSelected && name === selected) {
                    option.selected = true;
                }
                categorySelect.appendChild(option);
            });
        };

        typeSelect.addEventListener('change', function () { fillCategories(false); });
        fillCategories(true);
    }

    // ---- Ask before deleting ----
    document.querySelectorAll('.js-delete-form').forEach(function (deleteForm) {
        deleteForm.addEventListener('submit', function (event) {
            if (!confirm('Delete this transaction? This cannot be undone.')) {
                event.preventDefault();
            }
        });
    });
});
