
function confirmarFinalizacao() {
    return confirm('Tem certeza que deseja finalizar este pedido?');
}


document.addEventListener('DOMContentLoaded', function() {
    const finalizarBtns = document.querySelectorAll('button[name="finalizar_pedido"]');
    finalizarBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirmarFinalizacao()) {
                e.preventDefault();
            }
        });
    });
});

// Função para atualizar total dinamicamente (se necessário)
function atualizarTotal() {
    // Implementar se houver necessidade de cálculo client-side
}

// Validação básica de formulários
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos obrigatórios.');
            }
        });
    });
});