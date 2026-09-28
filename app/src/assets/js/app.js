/**
 * Javascript cho He thong Quan ly kho hang - DTC245200356
 */

// Quan ly Modal
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

// Dong modal khi click ra ngoai
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
});

// Chuyen doi Dark / Light Theme
function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
}

// Khoi phuc Theme da luu
(function initTheme() {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
})();

// Thong bao Toast dep mat
function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const colors = {
        success: 'border-left: 4px solid #10b981; background: #131d31;',
        danger: 'border-left: 4px solid #ef4444; background: #131d31;',
        warning: 'border-left: 4px solid #f59e0b; background: #131d31;',
        info: 'border-left: 4px solid #3b82f6; background: #131d31;'
    };

    toast.style.cssText = `padding: 14px 20px; border-radius: 8px; color: #fff; font-size: 13px; font-weight: 500; box-shadow: 0 10px 15px rgba(0,0,0,0.5); min-width: 280px; display: flex; align-items: center; justify-content: space-between; gap: 10px; animation: slideIn 0.3s ease; ${colors[type] || colors.info}`;
    toast.innerHTML = `<span>${message}</span><button style="background:none; border:none; color:#94a3b8; cursor:pointer;" onclick="this.parentElement.remove()">&times;</button>`;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Xuat du lieu bang thanh file CSV
function exportTableToCSV(tableId, filename = 'export_data.csv') {
    const table = document.getElementById(tableId);
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        // Bo qua cot thao tac cuoi cung neu la 'Thao tác'
        for (let j = 0; j < cols.length; j++) {
            if (cols[j].classList.contains('no-export')) continue;
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s+)/gm, ' ');
            data = data.replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        csv.push(row.join(','));
    }

    const csvFile = new Blob(['\uFEFF' + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const downloadLink = document.createElement('a');
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
    showToast('Đã xuất báo cáo CSV thành công!', 'success');
}

// Ham trigger Log Test cho chuong LogQL & Loki
function triggerTestLog(actionType) {
    fetch('api/log_generator.php?action=' + encodeURIComponent(actionType))
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Lỗi sinh log', 'danger');
            }
        })
        .catch(err => {
            showToast('Lỗi gửi request tạo log: ' + err, 'danger');
        });
}

// In phieu xuat / nhap kho
function printReceipt(modalId) {
    window.print();
}
