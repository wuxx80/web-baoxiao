document.addEventListener('DOMContentLoaded', () => {
    // 这是一个示例脚本文件，你可以根据需要在此添加其他JavaScript功能。

    const toggleSearchBtn = document.getElementById('toggleSearchBtn');
    if (toggleSearchBtn) {
        toggleSearchBtn.addEventListener('click', () => {
            const searchFormContainer = document.getElementById('searchFormContainer');
            if (searchFormContainer) {
                if (searchFormContainer.style.display === 'none' || !searchFormContainer.style.display) {
                    searchFormContainer.style.display = 'block';
                } else {
                    searchFormContainer.style.display = 'none';
                }
            }
        });
    }

    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', (event) => {
            const checkboxes = document.querySelectorAll('input[name="selected_expenses[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = event.target.checked;
            });
        });
    }
});