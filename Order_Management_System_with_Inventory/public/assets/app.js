document.addEventListener('DOMContentLoaded', () => {
    const lineItemsBody = document.getElementById('line-items-body');
    const addLineBtn = document.getElementById('add-line');

    if (addLineBtn && lineItemsBody) {
        addLineBtn.addEventListener('click', () => {
            const row = lineItemsBody.querySelector('.line-items__row');
            if (!row) return;

            const clone = row.cloneNode(true);
            clone.querySelectorAll('select, input').forEach((el) => {
                if (el.tagName === 'SELECT') {
                    el.selectedIndex = 0;
                } else {
                    el.value = el.name.startsWith('quantity') ? '1' : '0.00';
                }
            });
            lineItemsBody.appendChild(clone);
        });

        lineItemsBody.addEventListener('click', (event) => {
            const target = event.target;
            if (target.classList.contains('remove-line')) {
                const rows = lineItemsBody.querySelectorAll('.line-items__row');
                if (rows.length > 1) {
                    target.closest('.line-items__row').remove();
                }
            }
        });

        lineItemsBody.addEventListener('change', (event) => {
            const select = event.target;
            if (select.tagName === 'SELECT' && select.name === 'product_id[]') {
                const option = select.selectedOptions[0];
                const price = option?.dataset?.price;
                if (price) {
                    const row = select.closest('.line-items__row');
                    const priceInput = row.querySelector('.unit-price');
                    if (priceInput) {
                        priceInput.value = parseFloat(price).toFixed(2);
                    }
                }
            }
        });
    }
});

