document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('typeBesoin');
    const besoinSelect = document.getElementById('besoinDon');

    const allOptions = Array.from(besoinSelect.querySelectorAll('option[data-type]'));
    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = '-- Sélectionner un type d\'abord --';

    function filterBesoins() {
        const typeId = typeSelect.value;
        console.log('Type sélectionné:', typeId);
        console.log('Options disponibles:', allOptions.map(o => ({ val: o.value, type: o.getAttribute('data-type'), text: o.textContent.trim() })));


        while (besoinSelect.options.length > 0) {
            besoinSelect.remove(0);
        }


        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = typeId ? '-- Sélectionner un besoin --' : '-- Sélectionner un type d\'abord --';
        besoinSelect.appendChild(placeholder);


        allOptions.forEach(function(opt) {
            if (typeId && opt.getAttribute('data-type') === typeId) {
                besoinSelect.appendChild(opt.cloneNode(true));
            }
        });

        besoinSelect.value = '';
    }

    typeSelect.addEventListener('change', filterBesoins);
    filterBesoins();
});