// assets/js/editable.js

document.addEventListener('DOMContentLoaded', () => {
    const editableElements = document.querySelectorAll('[data-editable-key]');

    editableElements.forEach(element => {
        let originalContent = element.innerHTML.trim();

        // 1. Phím tắt: Enter lưu ngay đối với text trơn, Ctrl/Cmd + Enter đối với HTML
        element.addEventListener('keydown', (e) => {
            const type = element.getAttribute('data-editable-type') || 'text';

            if (type === 'text' && e.key === 'Enter') {
                e.preventDefault();
                element.blur();
            }
            
            if ((type === 'textarea' || type === 'html') && e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                e.preventDefault();
                element.blur();
            }
        });

        // 2. Tự động lưu khi mất tiêu điểm (Blur)
        element.addEventListener('blur', () => {
            const currentContent = element.innerHTML.trim();
            
            if (currentContent !== originalContent) {
                saveContent(element, currentContent)
                    .then(success => {
                        if (success) {
                            originalContent = currentContent;
                        } else {
                            element.innerHTML = originalContent; // Hoàn tác về giá trị cũ
                        }
                    });
            }
        });
    });
});

/**
 * Gửi dữ liệu AJAX lên API PHP của admin
 * 
 * @param {HTMLElement} element 
 * @param {string} value 
 * @returns {Promise<boolean>}
 */
async function saveContent(element, value) {
    const key = element.getAttribute('data-editable-key');
    const type = element.getAttribute('data-editable-type') || 'text';
    
    // Lấy CSRF token từ thẻ meta
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('Không tìm thấy CSRF Token meta tag!');
        showIndicator(element, 'error', 'Lỗi bảo mật (CSRF)');
        return false;
    }

    showIndicator(element, 'saving', 'Đang lưu...');

    try {
        const response = await fetch('admin/update-content.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                key: key,
                value: value,
                type: type
            })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            showIndicator(element, 'success', 'Đã lưu!');
            return true;
        } else {
            console.error('Lỗi từ server:', data.message);
            showIndicator(element, 'error', data.message || 'Lưu thất bại');
            return false;
        }
    } catch (error) {
        console.error('Lỗi kết nối mạng:', error);
        showIndicator(element, 'error', 'Mất kết nối mạng');
        return false;
    }
}

/**
 * Hiển thị chỉ báo trạng thái lưu nhỏ gọn dưới phần tử chỉnh sửa
 * 
 * @param {HTMLElement} element 
 * @param {'saving'|'success'|'error'} status 
 * @param {string} message 
 */
function showIndicator(element, status, message) {
    let indicator = element.parentElement.querySelector('.save-indicator');
    if (!indicator) {
        indicator = document.createElement('span');
        indicator.className = 'save-indicator';
        indicator.style.position = 'absolute';
        indicator.style.right = '0';
        indicator.style.bottom = '-18px';
        indicator.style.fontSize = '10px';
        indicator.style.padding = '2px 4px';
        indicator.style.borderRadius = '3px';
        indicator.style.zIndex = '10';
        element.parentElement.appendChild(indicator);
    }

    indicator.textContent = message;
    
    if (status === 'saving') {
        indicator.style.backgroundColor = '#fef3c7';
        indicator.style.color = '#d97706';
        element.style.borderColor = '#d97706';
    } else if (status === 'success') {
        indicator.style.backgroundColor = '#dcfce7';
        indicator.style.color = '#15803d';
        element.style.borderColor = '#15803d';
        setTimeout(() => {
            indicator.remove();
            element.style.borderColor = '#3b82f6';
        }, 1500);
    } else if (status === 'error') {
        indicator.style.backgroundColor = '#fee2e2';
        indicator.style.color = '#b91c1c';
        element.style.borderColor = '#b91c1c';
        setTimeout(() => {
            indicator.remove();
            element.style.borderColor = '#3b82f6';
        }, 3000);
    }
}
