(() => {
    document.querySelector('.profile-edit')?.addEventListener('click', () => {
        document.getElementById('profile-modal')?.showModal();
    });

    const dialog = document.getElementById('order-dialog');
    if (!dialog) return;

    const chosen = new Map();
    const formatRupiah = (value) => 'Rp ' + Number(value).toLocaleString('id-ID');

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.add-order');
        if (!button) return;

        const merchant = button.dataset.merchant;
        if (chosen.size && ![...chosen.values()].every((item) => item.merchant === merchant)) {
            alert('Satu pesanan hanya dapat berisi menu dari katering yang sama.');
            return;
        }

        const id = String(button.dataset.menuId || '');
        if (!/^\d+$/.test(id)) return;

        chosen.set(id, {
            id,
            name: button.dataset.menuName || '',
            price: Number(button.dataset.price) || 0,
            merchant,
            qty: chosen.get(id)?.qty || 1,
        });

        render();
        dialog.showModal();
    });

    function render() {
        const container = document.getElementById('selected-items');
        if (!container) return;
        container.replaceChildren();

        for (const item of chosen.values()) {
            const line = document.createElement('div');
            line.className = 'selected-menu-line';

            const details = document.createElement('div');
            const name = document.createElement('strong');
            name.textContent = item.name;
            const price = document.createElement('small');
            price.textContent = formatRupiah(item.price) + ' / porsi';
            details.append(name, price);

            const menuId = document.createElement('input');
            menuId.type = 'hidden';
            menuId.name = 'items[' + item.id + '][menu_id]';
            menuId.value = item.id;

            const quantity = document.createElement('input');
            quantity.type = 'number';
            quantity.min = '1';
            quantity.max = '500';
            quantity.name = 'items[' + item.id + '][quantity]';
            quantity.value = String(item.qty);
            quantity.setAttribute('aria-label', 'Jumlah porsi ' + item.name);
            quantity.addEventListener('input', () => {
                item.qty = Math.max(1, Number(quantity.value) || 1);
                updateTotal();
            });

            line.append(details, menuId, quantity);
            container.append(line);
        }

        updateTotal();
    }

    function updateTotal() {
        const total = document.getElementById('order-total');
        if (!total) return;
        total.textContent = formatRupiah([...chosen.values()].reduce(
            (sum, item) => sum + item.price * item.qty,
            0
        ));
    }
})();