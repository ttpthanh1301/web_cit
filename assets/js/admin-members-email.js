(() => {
    const bulkRoot = document.querySelector('#member-email-bulk');
    const tokenInput = bulkRoot?.querySelector('[data-csrf]') || document.querySelector('#email-csrf-token');
    const csrfToken = tokenInput?.value || '';

    const post = async (url, values) => {
        const body = new FormData();
        body.append('csrf_token', csrfToken);
        Object.entries(values).forEach(([key, value]) => body.append(key, String(value)));
        const response = await fetch(url, { method: 'POST', body, credentials: 'same-origin' });
        const text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (error) {
            throw new Error(text || 'Máy chủ trả về dữ liệu không hợp lệ.');
        }
        if (!response.ok || data.success === false) {
            throw new Error(data.message || 'Không thể thực hiện yêu cầu.');
        }
        return data;
    };

    const ensureProgressModal = () => {
        let element = document.querySelector('#emailProgressModal');
        if (!element) {
            element = document.createElement('div');
            element.className = 'modal fade';
            element.id = 'emailProgressModal';
            element.tabIndex = -1;
            element.dataset.bsBackdrop = 'static';
            element.innerHTML = '<div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title h5">Tiến độ gửi email</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Ẩn tiến độ"></button></div><div class="modal-body"><div class="email-progress-number"><strong data-progress-label>0/0</strong><span>đã xử lý</span></div><div class="progress"><div class="progress-bar" data-progress-bar style="width:0%"></div></div><div class="email-progress-stats"><span class="is-sent">Đã gửi <strong data-progress-sent>0</strong></span><span class="is-failed">Lỗi <strong data-progress-failed>0</strong></span><span class="is-skipped">Bỏ qua <strong data-progress-skipped>0</strong></span></div><div class="email-progress-message" data-progress-message>Đang chuẩn bị...</div></div><div class="modal-footer"><button class="btn btn-primary d-none" type="button" data-next-batch><i class="bi bi-send"></i> Gửi nhóm tiếp theo</button><button class="btn btn-light" type="button" data-bs-dismiss="modal">Đóng</button></div></div></div>';
            document.body.appendChild(element);
        }
        return { element, instance: bootstrap.Modal.getOrCreateInstance(element) };
    };

    const updateProgress = (element, batch) => {
        const total = Number(batch.selected || 0);
        const processed = Number(batch.processed || 0);
        const percent = total > 0 ? Math.min(100, Math.round((processed / total) * 100)) : 100;
        element.querySelector('[data-progress-label]').textContent = `${processed}/${total}`;
        element.querySelector('[data-progress-bar]').style.width = `${percent}%`;
        element.querySelector('[data-progress-sent]').textContent = batch.sent || 0;
        element.querySelector('[data-progress-failed]').textContent = batch.failed || 0;
        element.querySelector('[data-progress-skipped]').textContent = batch.skipped || 0;
        const message = element.querySelector('[data-progress-message]');
        message.className = `email-progress-message ${batch.complete ? 'is-complete' : ''}`;
        message.textContent = batch.complete
            ? `Hoàn tất. ${batch.failed ? 'Bạn có thể gửi lại các email lỗi trong chi tiết hồ sơ.' : 'Tất cả email đã được xử lý.'}`
            : 'Đang gửi từng email, bạn có thể ẩn cửa sổ này và tiếp tục làm việc.';
    };

    let activeBatch = 0;
    const runBatch = async (batchId, initialBatch = null, queuedBatches = []) => {
        if (activeBatch !== 0 || batchId < 1) return;
        activeBatch = batchId;
        const modal = ensureProgressModal();
        const nextButton = modal.element.querySelector('[data-next-batch]');
        nextButton.classList.add('d-none');
        nextButton.onclick = null;
        modal.instance.show();
        if (initialBatch) updateProgress(modal.element, initialBatch);
        try {
            let batch = initialBatch;
            while (!batch || !batch.complete) {
                const data = await post('email-batch-process.php', { batch_id: batchId });
                batch = data.batch;
                updateProgress(modal.element, batch);
                if (!batch.complete) await new Promise((resolve) => window.setTimeout(resolve, 180));
            }
            if (queuedBatches.length > 0) {
                const nextBatch = queuedBatches[0];
                const remainingCount = queuedBatches.length;
                const message = modal.element.querySelector('[data-progress-message]');
                message.className = 'email-progress-message is-complete';
                message.textContent = `Nhóm hiện tại đã hoàn tất. Còn ${remainingCount} nhóm đang chờ; nên đợi 10–15 phút trước khi gửi nhóm tiếp theo.`;
                nextButton.innerHTML = '<i class="bi bi-send"></i> Gửi nhóm tiếp theo';
                nextButton.classList.remove('d-none');
                nextButton.onclick = () => runBatch(Number(nextBatch.batch_id), nextBatch, queuedBatches.slice(1));
            }
        } catch (error) {
            const message = modal.element.querySelector('[data-progress-message]');
            message.className = 'email-progress-message is-error';
            message.textContent = `${error.message} Bạn có thể bấm Tiếp tục để thử lại.`;
        } finally {
            activeBatch = 0;
        }
    };

    document.querySelectorAll('[data-resume-batch]').forEach((button) => {
        button.addEventListener('click', () => runBatch(Number(button.dataset.resumeBatch || 0)));
    });

    document.querySelectorAll('[data-retry-delivery]').forEach((button) => {
        button.addEventListener('click', async () => {
            button.disabled = true;
            const original = button.innerHTML;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang gửi';
            try {
                const data = await post('email-delivery-retry.php', { delivery_id: button.dataset.retryDelivery });
                const article = button.closest('article');
                const status = article?.querySelector('[data-delivery-status]');
                if (status) status.textContent = 'Đã gửi';
                button.remove();
                if (article) article.querySelector('p')?.remove();
            } catch (error) {
                button.disabled = false;
                button.innerHTML = original;
                window.alert(error.message);
            }
        });
    });

    if (!bulkRoot) return;
    const rowCheckboxes = [...document.querySelectorAll('[data-submission-checkbox]')];
    const pageCheckbox = document.querySelector('[data-select-page]');
    const selectionLabel = bulkRoot.querySelector('[data-selection-count]');
    const openConfirm = bulkRoot.querySelector('[data-open-email-confirm]');
    const selectAllNotice = bulkRoot.querySelector('[data-select-all-notice]');
    const selectAllFilteredButton = bulkRoot.querySelector('[data-select-all-filtered]');
    const messageType = bulkRoot.querySelector('[data-message-type]');
    const total = Number(bulkRoot.dataset.total || 0);
    const filters = JSON.parse(bulkRoot.dataset.filters || '{}');
    let allFiltered = false;

    const selectedIds = () => rowCheckboxes.filter((checkbox) => checkbox.checked).map((checkbox) => Number(checkbox.value));
    const updateSelection = () => {
        const count = allFiltered ? total : selectedIds().length;
        selectionLabel.textContent = `${new Intl.NumberFormat('vi-VN').format(count)} hồ sơ`;
        openConfirm.disabled = count < 1;
        if (pageCheckbox) {
            pageCheckbox.checked = rowCheckboxes.length > 0 && rowCheckboxes.every((checkbox) => checkbox.checked);
            pageCheckbox.indeterminate = !pageCheckbox.checked && rowCheckboxes.some((checkbox) => checkbox.checked);
        }
        const showAllOffer = !allFiltered && total > rowCheckboxes.length && rowCheckboxes.length > 0 && rowCheckboxes.every((checkbox) => checkbox.checked);
        selectAllNotice.classList.toggle('d-none', !showAllOffer && !allFiltered);
        if (allFiltered) {
            selectAllNotice.querySelector('span').textContent = `Đã chọn toàn bộ ${new Intl.NumberFormat('vi-VN').format(total)} kết quả phù hợp bộ lọc.`;
            selectAllFilteredButton.textContent = 'Chỉ chọn trang hiện tại';
        } else {
            selectAllNotice.querySelector('span').textContent = `Đã chọn ${rowCheckboxes.length} hồ sơ trên trang này.`;
            selectAllFilteredButton.textContent = `Chọn tất cả ${new Intl.NumberFormat('vi-VN').format(total)} kết quả`;
        }
    };

    pageCheckbox?.addEventListener('change', () => {
        allFiltered = false;
        rowCheckboxes.forEach((checkbox) => { checkbox.checked = pageCheckbox.checked; });
        updateSelection();
    });
    rowCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', () => {
        allFiltered = false;
        updateSelection();
    }));
    selectAllFilteredButton.addEventListener('click', () => {
        allFiltered = !allFiltered;
        rowCheckboxes.forEach((checkbox) => { checkbox.checked = true; });
        updateSelection();
    });

    const confirmElement = document.querySelector('#emailConfirmModal');
    const confirmModal = bootstrap.Modal.getOrCreateInstance(confirmElement);
    openConfirm.addEventListener('click', () => {
        const count = allFiltered ? total : selectedIds().length;
        const approved = messageType.value === 'approved';
        confirmElement.querySelector('[data-confirm-count]').textContent = `${new Intl.NumberFormat('vi-VN').format(count)} hồ sơ`;
        confirmElement.querySelector('[data-confirm-result]').textContent = approved ? 'Chuyển sang Đã duyệt và gửi thư chấp nhận.' : 'Chuyển sang Từ chối và gửi thư thông báo.';
        confirmElement.querySelector('[data-confirm-subject]').textContent = approved ? bulkRoot.dataset.approvedSubject : bulkRoot.dataset.rejectedSubject;
        confirmModal.show();
    });

    confirmElement.querySelector('[data-confirm-send]').addEventListener('click', async (event) => {
        const button = event.currentTarget;
        button.disabled = true;
        const original = button.innerHTML;
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang tạo đợt gửi';
        try {
            const values = {
                message_type: messageType.value,
                selection_mode: allFiltered ? 'filtered' : 'page',
                ids: JSON.stringify(selectedIds()),
                ...filters
            };
            const data = await post('email-batch-create.php', values);
            confirmModal.hide();
            const approved = messageType.value === 'approved';
            rowCheckboxes.filter((checkbox) => checkbox.checked).forEach((checkbox) => {
                const cell = checkbox.closest('tr')?.querySelector('[data-status-cell]');
                if (cell) cell.innerHTML = approved ? '<span class="badge text-bg-success">Đã duyệt</span>' : '<span class="badge text-bg-danger">Từ chối</span>';
            });
            const batches = Array.isArray(data.batches) && data.batches.length > 0 ? data.batches : [data.batch];
            runBatch(Number(batches[0].batch_id), batches[0], batches.slice(1));
        } catch (error) {
            window.alert(error.message);
        } finally {
            button.disabled = false;
            button.innerHTML = original;
        }
    });

    updateSelection();
})();
